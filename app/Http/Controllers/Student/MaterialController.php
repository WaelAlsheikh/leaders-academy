<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\RegistrableEntity;
use App\Models\Student;
use App\Models\SubjectMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $student = $this->student();
        $materialsContext = $this->materialsContextForStudent($student);

        $entities = $materialsContext['entities'];
        $subjectsByEntity = $materialsContext['subjects_by_entity'];
        $allowedMaterialIds = $materialsContext['allowed_material_ids'];

        $selectedEntity = null;
        $selectedSubject = null;
        $videos = collect();
        $files = collect();

        $entityId = $request->integer('entity');
        if ($entityId > 0) {
            $selectedEntity = $entities->firstWhere('id', $entityId);
        }

        $subjectId = $request->integer('subject');
        if ($selectedEntity && $subjectId > 0) {
            $selectedSubject = ($subjectsByEntity[$selectedEntity->id] ?? collect())->firstWhere('id', $subjectId);
        }

        if ($selectedSubject) {
            $materials = SubjectMaterial::query()
                ->with(['doctor', 'registrableSubject.registrableEntity'])
                ->whereIn('id', $allowedMaterialIds)
                ->where('registrable_subject_id', $selectedSubject->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get();

            $videos = $materials->where('material_type', 'video')->values();
            $files = $materials->where('material_type', 'file')->values();
        }

        return view('student.materials.index', compact(
            'entities',
            'subjectsByEntity',
            'selectedEntity',
            'selectedSubject',
            'videos',
            'files'
        ));
    }

    public function download(Request $request, SubjectMaterial $material)
    {
        $student = $this->student();
        $allowedMaterialIds = $this->materialsContextForStudent($student)['allowed_material_ids'];

        abort_unless(in_array($material->id, $allowedMaterialIds, true), 403);
        abort_unless(Storage::disk('public')->exists($material->file_path), 404);

        $path = Storage::disk('public')->path($material->file_path);
        $headers = ['Content-Type' => $material->mime_type];

        if ($request->boolean('download')) {
            return response()->download($path, $material->original_name, $headers);
        }

        return response()->file($path, $headers);
    }

    private function student(): Student
    {
        $student = Auth::guard('student')->user();
        abort_unless($student instanceof Student, 403);

        return $student;
    }

    private function materialsContextForStudent(Student $student): array
    {
        $activeSections = $student->sections()
            ->wherePivot('status', 'active')
            ->whereHas('semester.enrollmentCycle', function ($query) {
                $query->doesntHave('archiveRecord');
            })
            ->with([
                'doctor',
                'registrableSubject.registrableEntity',
                'semester.enrollmentCycle',
            ])
            ->get();

        $latestStatusesBySubject = $this->latestAcceptedStatusesBySubject($student);

        $eligibleSections = $activeSections->filter(function ($section) use ($latestStatusesBySubject) {
            $subjectId = (int) $section->registrable_subject_id;

            return ($latestStatusesBySubject[$subjectId] ?? null) !== 'passed';
        })->values();

        $entities = $eligibleSections
            ->map(fn ($section) => $section->registrableSubject?->registrableEntity)
            ->filter()
            ->unique('id')
            ->sortBy('title_snapshot')
            ->values();

        $subjectsByEntity = $eligibleSections
            ->groupBy(fn ($section) => $section->registrableSubject?->registrable_entity_id)
            ->map(function (Collection $sections) {
                return $sections
                    ->map(fn ($section) => $section->registrableSubject)
                    ->filter()
                    ->unique('id')
                    ->sortBy('name')
                    ->values();
            });

        $allowedMaterialIds = [];

        if ($eligibleSections->isNotEmpty()) {
            $allowedMaterialIds = SubjectMaterial::query()
                ->where('is_active', true)
                ->where(function ($query) use ($eligibleSections) {
                    foreach ($eligibleSections as $section) {
                        $query->orWhere(function ($nested) use ($section) {
                            $nested->where('doctor_id', $section->doctor_id)
                                ->where('registrable_subject_id', $section->registrable_subject_id);
                        });
                    }
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return [
            'entities' => $entities,
            'subjects_by_entity' => $subjectsByEntity,
            'allowed_material_ids' => $allowedMaterialIds,
        ];
    }

    private function latestAcceptedStatusesBySubject(Student $student): array
    {
        $registrations = Registration::query()
            ->with('registrableSubjects')
            ->where('student_id', $student->id)
            ->where('status', 'accepted')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $statuses = [];

        foreach ($registrations as $registration) {
            foreach ($registration->registrableSubjects as $subject) {
                $statuses[(int) $subject->id] = $subject->pivot->result_status ?? 'undefined';
            }
        }

        return $statuses;
    }
}
