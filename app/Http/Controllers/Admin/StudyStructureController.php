<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\RegistrableEntity;
use App\Models\StudyTerm;
use App\Models\StudyYear;
use App\Services\CollegeSubjectSyncService;
use App\Services\StudyStructureService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudyStructureController extends Controller
{
    public function __construct(
        private readonly CollegeSubjectSyncService $collegeSubjectSyncService,
        private readonly StudyStructureService $studyStructureService
    ) {
    }

    public function collegeYears(Request $request, College $college)
    {
        $entity = $this->collegeSubjectSyncService->ensureCollegeEntity($college);

        return view('admin.study_years.index', array_merge(
            $this->buildYearsViewData($entity, $college),
            $this->portalViewData($request)
        ));
    }

    public function registrableYears(Request $request, RegistrableEntity $entity)
    {
        $this->studyStructureService->ensureDefaultStructureForEntity($entity);

        return view('admin.study_years.index', array_merge(
            $this->buildYearsViewData($entity),
            $this->portalViewData($request)
        ));
    }

    public function storeYear(Request $request, RegistrableEntity $entity)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('study_years', 'sort_order')
                    ->where(fn ($query) => $query->where('registrable_entity_id', $entity->id)),
            ],
        ]);

        $sortOrder = $data['sort_order']
            ?? ((int) $entity->studyYears()->max('sort_order') + 1);

        StudyYear::create([
            'registrable_entity_id' => $entity->id,
            'name' => $data['name'],
            'sort_order' => $sortOrder,
        ]);

        return back()->with('success', 'تمت إضافة السنة بنجاح');
    }

    public function updateYear(Request $request, StudyYear $studyYear)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('study_years', 'sort_order')
                    ->where(fn ($query) => $query->where('registrable_entity_id', $studyYear->registrable_entity_id))
                    ->ignore($studyYear->id),
            ],
        ]);

        $studyYear->update($data);

        return back()->with('success', 'تم تحديث السنة بنجاح');
    }

    public function destroyYear(StudyYear $studyYear)
    {
        $studyYear->loadMissing('studyTerms.registrableSubjects', 'studyTerms.legacySubjects');

        foreach ($studyYear->studyTerms as $studyTerm) {
            if ($studyTerm->registrableSubjects->isNotEmpty() || $studyTerm->legacySubjects->isNotEmpty()) {
                return back()->withErrors(['status' => 'لا يمكن حذف سنة تحتوي على فصول مرتبطة بمواد.']);
            }
        }

        $studyYear->delete();

        return back()->with('success', 'تم حذف السنة بنجاح');
    }

    public function terms(Request $request, StudyYear $studyYear)
    {
        $studyYear->load([
            'registrableEntity',
            'studyTerms' => function ($query) {
                $query->withCount(['registrableSubjects', 'legacySubjects'])
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        return view('admin.study_terms.index', array_merge(
            [
                'studyYear' => $studyYear,
                'entity' => $studyYear->registrableEntity,
                'college' => $studyYear->registrableEntity?->entity_type === 'college'
                    ? College::find($studyYear->registrableEntity->entity_id)
                    : null,
            ],
            $this->portalViewData($request)
        ));
    }

    public function storeTerm(Request $request, StudyYear $studyYear)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'sort_order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('study_terms', 'sort_order')
                    ->where(fn ($query) => $query->where('study_year_id', $studyYear->id)),
            ],
        ]);

        $sortOrder = $data['sort_order']
            ?? ((int) $studyYear->studyTerms()->max('sort_order') + 1);

        StudyTerm::create([
            'study_year_id' => $studyYear->id,
            'name' => $data['name'],
            'code' => $data['code'] ?: null,
            'sort_order' => $sortOrder,
        ]);

        return back()->with('success', 'تمت إضافة الفصل بنجاح');
    }

    public function updateTerm(Request $request, StudyTerm $studyTerm)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('study_terms', 'sort_order')
                    ->where(fn ($query) => $query->where('study_year_id', $studyTerm->study_year_id))
                    ->ignore($studyTerm->id),
            ],
        ]);

        $studyTerm->update([
            'name' => $data['name'],
            'code' => $data['code'] ?: null,
            'sort_order' => $data['sort_order'],
        ]);

        return back()->with('success', 'تم تحديث الفصل بنجاح');
    }

    public function destroyTerm(StudyTerm $studyTerm)
    {
        if ($studyTerm->registrableSubjects()->exists() || $studyTerm->legacySubjects()->exists()) {
            return back()->withErrors(['status' => 'لا يمكن حذف فصل مرتبط بمواد.']);
        }

        $studyTerm->delete();

        return back()->with('success', 'تم حذف الفصل بنجاح');
    }

    public function subjects(Request $request, StudyTerm $studyTerm)
    {
        $studyTerm->loadMissing('studyYear.registrableEntity');
        $entity = $studyTerm->studyYear->registrableEntity;
        $college = $entity?->entity_type === 'college'
            ? College::find($entity->entity_id)
            : null;

        $subjects = $entity?->entity_type === 'college'
            ? $studyTerm->legacySubjects()->with('registrableSubject')->orderBy('name')->get()
            : $studyTerm->registrableSubjects()->orderBy('name')->get();

        return view('admin.study_terms.subjects', array_merge(
            compact('studyTerm', 'entity', 'college', 'subjects'),
            $this->portalViewData($request)
        ));
    }

    private function buildYearsViewData(RegistrableEntity $entity, ?College $college = null): array
    {
        $entity->load([
            'studyYears' => function ($query) {
                $query->withCount('studyTerms')
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        return compact('entity', 'college');
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
}
