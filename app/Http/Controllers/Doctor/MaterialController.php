<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\RegistrableSubject;
use App\Models\SubjectMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    private const VIDEO_MIMES = 'mp4,mov,webm,m4v';
    private const FILE_MIMES = 'pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png,webp';
    private const VIDEO_MAX_KB = 102400;
    private const FILE_MAX_KB = 25600;

    public function index(Request $request)
    {
        $doctor = $this->doctor();

        $subjects = RegistrableSubject::query()
            ->whereHas('classSections', function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id)
                    ->whereHas('semester.enrollmentCycle', function ($cycleQuery) {
                        $cycleQuery->doesntHave('archiveRecord');
                    });
            })
            ->with('registrableEntity')
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();

        $selectedSubject = null;
        $videos = collect();
        $files = collect();

        $subjectId = $request->integer('subject');
        if ($subjectId > 0) {
            $selectedSubject = $subjects->firstWhere('id', $subjectId);

            if ($selectedSubject) {
                $materials = SubjectMaterial::query()
                    ->with('doctor')
                    ->where('doctor_id', $doctor->id)
                    ->where('registrable_subject_id', $selectedSubject->id)
                    ->orderBy('sort_order')
                    ->orderByDesc('created_at')
                    ->get();

                $videos = $materials->where('material_type', 'video')->values();
                $files = $materials->where('material_type', 'file')->values();
            }
        }

        return view('doctor.materials.index', compact(
            'doctor',
            'subjects',
            'selectedSubject',
            'videos',
            'files'
        ));
    }

    public function storeVideo(Request $request)
    {
        $doctor = $this->doctor();
        $subject = $this->authorizedSubject($doctor, $request->integer('subject_id'));

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'upload' => 'required|file|mimes:' . self::VIDEO_MIMES . '|max:' . self::VIDEO_MAX_KB,
        ]);

        $this->createMaterialFromUpload($doctor, $subject, 'video', $request->file('upload'), $data);

        return redirect()
            ->route('doctor.materials.index', ['subject' => $subject->id])
            ->with('success', 'تم رفع الفيديو بنجاح.');
    }

    public function storeFile(Request $request)
    {
        $doctor = $this->doctor();
        $subject = $this->authorizedSubject($doctor, $request->integer('subject_id'));

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'upload' => 'required|file|mimes:' . self::FILE_MIMES . '|max:' . self::FILE_MAX_KB,
        ]);

        $this->createMaterialFromUpload($doctor, $subject, 'file', $request->file('upload'), $data);

        return redirect()
            ->route('doctor.materials.index', ['subject' => $subject->id])
            ->with('success', 'تم رفع الملف بنجاح.');
    }

    public function update(Request $request, SubjectMaterial $material)
    {
        $doctor = $this->doctor();
        $this->authorizeMaterial($doctor, $material);

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        if ($material->material_type === 'video') {
            $rules['upload'] = 'nullable|file|mimes:' . self::VIDEO_MIMES . '|max:' . self::VIDEO_MAX_KB;
        } else {
            $rules['upload'] = 'nullable|file|mimes:' . self::FILE_MIMES . '|max:' . self::FILE_MAX_KB;
        }

        $data = $request->validate($rules);

        $updates = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('upload')) {
            Storage::disk('public')->delete($material->file_path);

            $storedFile = $request->file('upload');
            $updates['file_path'] = $storedFile->store(
                $material->material_type === 'video' ? 'subject-materials/videos' : 'subject-materials/files',
                'public'
            );
            $updates['original_name'] = $storedFile->getClientOriginalName();
            $updates['mime_type'] = $storedFile->getMimeType() ?: $storedFile->getClientMimeType();
            $updates['file_size'] = $storedFile->getSize();
        }

        $material->update($updates);

        return redirect()
            ->route('doctor.materials.index', ['subject' => $material->registrable_subject_id])
            ->with('success', 'تم تحديث العنصر بنجاح.');
    }

    public function destroy(SubjectMaterial $material)
    {
        $doctor = $this->doctor();
        $this->authorizeMaterial($doctor, $material);

        Storage::disk('public')->delete($material->file_path);
        $subjectId = $material->registrable_subject_id;
        $material->delete();

        return redirect()
            ->route('doctor.materials.index', ['subject' => $subjectId])
            ->with('success', 'تم حذف العنصر بنجاح.');
    }

    public function download(Request $request, SubjectMaterial $material)
    {
        $doctor = $this->doctor();
        $this->authorizeMaterial($doctor, $material);
        abort_unless(Storage::disk('public')->exists($material->file_path), 404);

        $path = Storage::disk('public')->path($material->file_path);
        $headers = ['Content-Type' => $material->mime_type];

        if ($request->boolean('download')) {
            return response()->download($path, $material->original_name, $headers);
        }

        return response()->file($path, $headers);
    }

    private function doctor(): Doctor
    {
        $doctor = Auth::guard('doctor')->user();
        abort_unless($doctor instanceof Doctor, 403);

        return $doctor;
    }

    private function authorizedSubject(Doctor $doctor, int $subjectId): RegistrableSubject
    {
        $subject = RegistrableSubject::query()
            ->where('id', $subjectId)
            ->whereHas('classSections', function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id)
                    ->whereHas('semester.enrollmentCycle', function ($cycleQuery) {
                        $cycleQuery->doesntHave('archiveRecord');
                    });
            })
            ->first();

        abort_unless($subject, 403);

        return $subject;
    }

    private function authorizeMaterial(Doctor $doctor, SubjectMaterial $material): void
    {
        abort_unless($material->doctor_id === $doctor->id, 403);
        $this->authorizedSubject($doctor, $material->registrable_subject_id);
    }

    private function createMaterialFromUpload(
        Doctor $doctor,
        RegistrableSubject $subject,
        string $type,
        \Illuminate\Http\UploadedFile $file,
        array $data
    ): SubjectMaterial {
        return SubjectMaterial::create([
            'doctor_id' => $doctor->id,
            'registrable_subject_id' => $subject->id,
            'material_type' => $type,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $file->store(
                $type === 'video' ? 'subject-materials/videos' : 'subject-materials/files',
                'public'
            ),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'is_active' => request()->boolean('is_active', true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }
}
