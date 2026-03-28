<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\ProgramBranch;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Models\Subject;
use App\Models\TrainingProgramBranch;
use App\Services\CollegeSubjectSyncService;
use Illuminate\Http\Request;

class RegistrableController extends Controller
{
    public function __construct(
        private readonly CollegeSubjectSyncService $collegeSubjectSyncService
    ) {
    }

    public function index()
    {
        RegistrableEntity::syncFromSources();

        $entities = RegistrableEntity::query()
            ->orderBy('entity_type')
            ->orderBy('title_snapshot')
            ->get();

        return view('admin.registrables.index', compact('entities'));
    }

    public function updatePrice(Request $request, RegistrableEntity $entity)
    {
        $data = $request->validate([
            'price_per_credit_hour' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $entity->update([
            'price_per_credit_hour' => $data['price_per_credit_hour'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($entity->entity_type === 'college') {
            College::where('id', $entity->entity_id)
                ->update(['price_per_credit_hour' => $data['price_per_credit_hour']]);
        } elseif ($entity->entity_type === 'program_branch') {
            ProgramBranch::where('id', $entity->entity_id)
                ->update(['price_per_credit_hour' => $data['price_per_credit_hour']]);
        } elseif ($entity->entity_type === 'training_program_branch') {
            TrainingProgramBranch::where('id', $entity->entity_id)
                ->update(['price_per_credit_hour' => $data['price_per_credit_hour']]);
        }

        return back()->with('success', 'تم تحديث الإعدادات');
    }

    public function subjects(RegistrableEntity $entity)
    {
        if ($entity->entity_type === 'college') {
            $college = College::find($entity->entity_id);
            $college?->subjects()->get()->each(function (Subject $legacySubject) {
                $this->collegeSubjectSyncService->syncLegacySubject($legacySubject);
            });
        }

        $subjects = $entity->subjects()
            ->orderBy('name')
            ->get();

        return view('admin.registrables.subjects', compact('entity', 'subjects'));
    }

    public function storeSubject(Request $request, RegistrableEntity $entity)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ];

        if ($entity->entity_type === 'college') {
            $rules['code'] = 'required|string|max:255|unique:subjects,code';
        }

        $data = $request->validate($rules);

        if ($entity->entity_type === 'college') {
            $college = College::findOrFail($entity->entity_id);
            $this->collegeSubjectSyncService->createLegacyAndSync($college, [
                'name' => $data['name'],
                'code' => $data['code'],
                'credit_hours' => $data['credit_hours'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            return back()->with('success', 'تمت إضافة المادة');
        }

        RegistrableSubject::create([
            'registrable_entity_id' => $entity->id,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'credit_hours' => $data['credit_hours'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'تمت إضافة المادة');
    }

    public function updateSubject(Request $request, RegistrableSubject $subject)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ];

        if ($subject->registrableEntity?->entity_type === 'college') {
            $rules['code'] = 'required|string|max:255|unique:subjects,code,' . ($subject->legacy_subject_id ?? 'NULL');
        }

        $data = $request->validate($rules);

        if ($subject->registrableEntity?->entity_type === 'college' && $subject->legacy_subject_id) {
            $legacySubject = Subject::findOrFail($subject->legacy_subject_id);
            $this->collegeSubjectSyncService->updateLegacyAndSync($legacySubject, [
                'name' => $data['name'],
                'code' => $data['code'],
                'credit_hours' => $data['credit_hours'],
                'is_active' => $request->boolean('is_active'),
            ]);

            return back()->with('success', 'تم تحديث المادة');
        }

        $subject->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'credit_hours' => $data['credit_hours'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'تم تحديث المادة');
    }

    public function destroySubject(RegistrableSubject $subject)
    {
        if (
            $subject->enrollmentCycles()->exists()
            || $subject->registrations()->exists()
            || $subject->classSections()->exists()
            || $subject->semesters()->exists()
        ) {
            return back()->withErrors(['status' => 'لا يمكن حذف مادة مرتبطة بتسجيلات أو دورات أو شعب']);
        }

        if ($subject->registrableEntity?->entity_type === 'college' && $subject->legacy_subject_id) {
            $legacySubject = Subject::find($subject->legacy_subject_id);
            if ($legacySubject) {
                $this->collegeSubjectSyncService->deleteLegacyAndSyncedSubject($legacySubject);
                return back()->with('success', 'تم حذف المادة');
            }
        }

        $subject->delete();

        return back()->with('success', 'تم حذف المادة');
    }
}
