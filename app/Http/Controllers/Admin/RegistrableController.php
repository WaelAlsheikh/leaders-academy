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
use App\Services\RegistrationSeasonService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegistrableController extends Controller
{
    public function __construct(
        private readonly CollegeSubjectSyncService $collegeSubjectSyncService,
        private readonly RegistrationSeasonService $registrationSeasonService
    ) {
    }

    public function index(Request $request)
    {
        return view('admin.registrables.index', array_merge(
            $this->buildIndexViewData(),
            $this->portalViewData($request),
            [
                'pageTitle' => 'إدارة كيانات التسجيل',
                'pageDescription' => 'إدارة أسعار الكيانات ونشاطها ومتابعة السنوات والفصول التابعة لها.',
            ]
        ));
    }

    public function programBranches(Request $request)
    {
        return view('admin.registrables.index', array_merge(
            $this->buildIndexViewData('program_branch'),
            $this->portalViewData($request),
            [
                'pageTitle' => 'إدارة فروع البرامج الجامعية',
                'pageDescription' => 'متابعة فروع البرامج الجامعية وضبط السنوات والفصول والمواد التابعة لها.',
            ]
        ));
    }

    public function trainingProgramBranches(Request $request)
    {
        return view('admin.registrables.index', array_merge(
            $this->buildIndexViewData('training_program_branch'),
            $this->portalViewData($request),
            [
                'pageTitle' => 'إدارة فروع البرامج التدريبية',
                'pageDescription' => 'متابعة فروع البرامج التدريبية وضبط السنوات والفصول والمواد التابعة لها.',
            ]
        ));
    }

    public function updatePrice(Request $request, RegistrableEntity $entity)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:255',
            'price_per_credit_hour' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $entity->update([
            'price_per_credit_hour' => $data['price_per_credit_hour'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($entity->entity_type === 'college') {
            College::where('id', $entity->entity_id)
                ->update([
                    'code' => $data['code'] ?? null,
                    'price_per_credit_hour' => $data['price_per_credit_hour'],
                ]);
        } elseif ($entity->entity_type === 'program_branch') {
            ProgramBranch::where('id', $entity->entity_id)
                ->update([
                    'code' => $data['code'] ?? null,
                    'price_per_credit_hour' => $data['price_per_credit_hour'],
                ]);
        } elseif ($entity->entity_type === 'training_program_branch') {
            TrainingProgramBranch::where('id', $entity->entity_id)
                ->update([
                    'code' => $data['code'] ?? null,
                    'price_per_credit_hour' => $data['price_per_credit_hour'],
                ]);
        }

        return back()->with('success', 'تم تحديث الإعدادات');
    }

    public function subjects(Request $request, RegistrableEntity $entity)
    {
        return redirect()
            ->route($this->portalViewData($request)['routeBase'].'.registrables.years', $entity);
    }

    public function storeSubject(Request $request, RegistrableEntity $entity)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'study_term_id' => 'nullable|integer|exists:study_terms,id',
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ];

        if ($entity->entity_type === 'college') {
            $rules['code'] = 'required|string|max:255|unique:subjects,code';
        }

        $data = $request->validate($rules);

        if ($entity->entity_type === 'college') {
            $college = College::findOrFail($entity->entity_id);
            $studyTermId = $this->resolveStudyTermId($entity, $request->integer('study_term_id'));
            $this->collegeSubjectSyncService->createLegacyAndSync($college, [
                'name' => $data['name'],
                'code' => $data['code'],
                'study_term_id' => $studyTermId,
                'credit_hours' => $data['credit_hours'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            return back()->with('success', 'تمت إضافة المادة');
        }

        $subject = RegistrableSubject::create([
            'registrable_entity_id' => $entity->id,
            'study_term_id' => $this->resolveStudyTermId($entity, $request->integer('study_term_id')),
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'credit_hours' => $data['credit_hours'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->registrationSeasonService->syncOpenSeasonSubjectsForEntity($subject->registrableEntity);

        return back()->with('success', 'تمت إضافة المادة');
    }

    public function updateSubject(Request $request, RegistrableSubject $subject)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'study_term_id' => 'nullable|integer|exists:study_terms,id',
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
                'study_term_id' => $this->resolveStudyTermId($subject->registrableEntity, $request->integer('study_term_id')),
                'credit_hours' => $data['credit_hours'],
                'is_active' => $request->boolean('is_active'),
            ]);

            return back()->with('success', 'تم تحديث المادة');
        }

        $subject->update([
            'study_term_id' => $this->resolveStudyTermId($subject->registrableEntity, $request->integer('study_term_id')),
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'credit_hours' => $data['credit_hours'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->registrationSeasonService->syncOpenSeasonSubjectsForEntity($subject->registrableEntity);

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

    private function buildIndexViewData(?string $entityType = null): array
    {
        RegistrableEntity::syncFromSources();

        $entities = RegistrableEntity::query()
            ->when($entityType, fn ($query) => $query->where('entity_type', $entityType))
            ->with('studyYears')
            ->withCount('studyYears')
            ->orderBy('entity_type')
            ->orderBy('title_snapshot')
            ->get();

        return compact('entities', 'entityType');
    }

    private function portalViewData(Request $request): array
    {
        $portalContext = str_starts_with((string) $request->route()?->getName(), 'employee.')
            ? 'employee'
            : 'admin';

        return [
            'portalContext' => $portalContext,
            'routeBase' => $portalContext,
            'layout' => $portalContext === 'employee' ? 'layouts.app' : 'voyager::master',
            'hideNavbar' => $portalContext === 'employee',
            'bodyClass' => $portalContext === 'employee' ? 'employee-shell' : '',
        ];
    }

    private function resolveStudyTermId(RegistrableEntity $entity, ?int $studyTermId): ?int
    {
        $allowedStudyTermIds = $entity->studyTerms()->pluck('study_terms.id')->map(fn ($id) => (int) $id)->all();

        if ($studyTermId !== null && !in_array($studyTermId, $allowedStudyTermIds, true)) {
            throw ValidationException::withMessages([
                'study_term_id' => 'الفصل المحدد لا يتبع لهذا الكيان.',
            ]);
        }

        return $studyTermId ?: ($allowedStudyTermIds[0] ?? null);
    }
}
