<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArchivedEnrollmentCycle;
use App\Models\ClassSection;
use App\Models\EnrollmentCycle;
use App\Models\Registration;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class EnrollmentCycleController extends Controller
{
    public function index(Request $request)
    {
        RegistrableEntity::syncFromSources();

        $cycles = EnrollmentCycle::activeListing()
            ->with(['college', 'semester', 'registrableEntity'])
            ->latest()
            ->get();
        $registrableEntities = RegistrableEntity::query()
            ->where('is_active', true)
            ->orderBy('entity_type')
            ->orderBy('title_snapshot')
            ->get();

        return view('admin.enrollment_cycles.index', array_merge(
            compact('cycles', 'registrableEntities'),
            $this->portalViewData($request)
        ));
    }

    public function archivedIndex(Request $request)
    {
        $cycles = EnrollmentCycle::archivedListing()
            ->with(['college', 'semester', 'registrableEntity', 'archiveRecord.archivedBy'])
            ->latest()
            ->get();

        return view('admin.enrollment_cycles.archived_index', array_merge(
            compact('cycles'),
            $this->portalViewData($request)
        ));
    }

    public function store(Request $request)
    {
        RegistrableEntity::syncFromSources();

        $data = $request->validate([
            'registrable_entity_id' => 'required|integer|exists:registrable_entities,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after_or_equal:registration_starts_at',
        ]);

        $entity = RegistrableEntity::findOrFail($data['registrable_entity_id']);
        $collegeId = $entity->entity_type === 'college' ? $entity->entity_id : null;

        EnrollmentCycle::create([
            'college_id' => $collegeId,
            'registrable_entity_id' => $data['registrable_entity_id'],
            'name' => $data['name'],
            'code' => $data['code'] ?: null,
            'registration_starts_at' => $data['registration_starts_at'] ?? null,
            'registration_ends_at' => $data['registration_ends_at'] ?? null,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route($this->routeName($request, 'enrollment_cycles.index'))
            ->with('success', 'تم إنشاء دورة التسجيل بنجاح');
    }

    public function show(Request $request, EnrollmentCycle $cycle)
    {
        if ($cycle->is_archived) {
            return redirect()->route($this->routeName($request, 'archived_enrollment_cycles.show'), $cycle);
        }

        return view('admin.enrollment_cycles.show', $this->buildCycleViewData($request, $cycle, false));
    }

    public function archivedShow(Request $request, EnrollmentCycle $cycle)
    {
        if (!$cycle->is_archived) {
            return redirect()->route($this->routeName($request, 'enrollment_cycles.show'), $cycle);
        }

        return view('admin.enrollment_cycles.archived_show', $this->buildCycleViewData($request, $cycle, true));
    }

    public function update(Request $request, EnrollmentCycle $cycle)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after_or_equal:registration_starts_at',
            'status' => 'required|in:draft,open,closed,approved,cancelled',
        ]);

        $data['code'] = $data['code'] ?: null;

        $cycle->update($data);

        return back()->with('success', 'تم تحديث الدورة');
    }

    public function updateSubjects(Request $request, EnrollmentCycle $cycle)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        $data = $request->validate([
            'subjects' => 'array',
            'subjects.*' => 'integer|exists:registrable_subjects,id',
        ]);

        $subjectIds = $data['subjects'] ?? [];

        $validSubjectIds = RegistrableSubject::where('registrable_entity_id', $cycle->registrable_entity_id)
            ->whereIn('id', $subjectIds)
            ->pluck('id')
            ->toArray();

        $syncData = [];
        foreach ($validSubjectIds as $subjectId) {
            $syncData[$subjectId] = ['is_open' => true];
        }

        $cycle->registrableSubjects()->sync($syncData);

        return back()->with('success', 'تم تحديث المواد المتاحة للدورة');
    }

    public function open(Request $request, EnrollmentCycle $cycle)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        $otherOpen = EnrollmentCycle::where('registrable_entity_id', $cycle->registrable_entity_id)
            ->where('id', '!=', $cycle->id)
            ->where('status', 'open')
            ->doesntHave('archiveRecord')
            ->exists();

        if ($otherOpen) {
            return back()->withErrors(['status' => 'يوجد دورة تسجيل مفتوحة لهذا الكيان بالفعل']);
        }

        if ($cycle->registrableSubjects()->count() === 0) {
            return back()->withErrors(['status' => 'يجب تحديد مواد للدورة قبل فتح التسجيل']);
        }

        $cycle->update(['status' => 'open']);

        return back()->with('success', 'تم فتح التسجيل');
    }

    public function close(Request $request, EnrollmentCycle $cycle)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        $cycle->update(['status' => 'closed']);

        return back()->with('success', 'تم إغلاق التسجيل');
    }

    public function approve(Request $request, EnrollmentCycle $cycle)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        if ($cycle->status !== 'closed') {
            return back()->withErrors(['status' => 'يجب إغلاق التسجيل قبل الاعتماد']);
        }

        if ($cycle->registrableSubjects()->count() === 0) {
            return back()->withErrors(['status' => 'لا يمكن اعتماد دورة بدون مواد']);
        }

        $cycle->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد الدورة');
    }

    public function updateRegistrationStatus(Request $request, EnrollmentCycle $cycle, Registration $registration)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        if ($registration->enrollment_cycle_id !== $cycle->id) {
            return back()->withErrors(['status' => 'هذا التسجيل لا ينتمي لهذه الدورة']);
        }

        $data = $request->validate([
            'status' => 'required|in:under_review,accepted,rejected',
        ]);

        DB::transaction(function () use ($cycle, $registration, $data) {
            $registration->update([
                'status' => $data['status'],
            ]);

            $this->syncRegistrationWithSemester($cycle, $registration);
        });

        return back()->with('success', 'تم تحديث الحالة');
    }

    public function bulkUpdateRegistrationStatus(Request $request, EnrollmentCycle $cycle)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        $data = $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'integer',
            'status' => 'required|in:under_review,accepted,rejected',
        ]);

        $registrations = Registration::with('registrableSubjects')
            ->where('enrollment_cycle_id', $cycle->id)
            ->whereIn('id', $data['registration_ids'])
            ->get();

        DB::transaction(function () use ($cycle, $registrations, $data) {
            foreach ($registrations as $registration) {
                $registration->update(['status' => $data['status']]);
                $this->syncRegistrationWithSemester($cycle, $registration);
            }
        });

        return back()->with('success', 'تم تحديث الحالات المحددة');
    }

    public function updateResultStatuses(Request $request, EnrollmentCycle $cycle, Registration $registration)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        if ($registration->enrollment_cycle_id !== $cycle->id) {
            return back()->withErrors(['status' => 'هذا التسجيل لا ينتمي لهذه الدورة']);
        }

        $data = $request->validate([
            'result_statuses' => 'required|array',
            'result_statuses.*' => 'required|in:undefined,passed,failed',
        ]);

        $allowedSubjectIds = $registration->registrableSubjects()->pluck('registrable_subjects.id')->all();

        DB::transaction(function () use ($registration, $data, $allowedSubjectIds) {
            foreach ($data['result_statuses'] as $subjectId => $resultStatus) {
                if (!in_array((int) $subjectId, $allowedSubjectIds, true)) {
                    continue;
                }

                $registration->registrableSubjects()->updateExistingPivot((int) $subjectId, [
                    'result_status' => $resultStatus,
                ]);
            }
        });

        return back()->with('success', 'تم تحديث حالات المواد للطالب.');
    }

    public function startSemester(Request $request, EnrollmentCycle $cycle)
    {
        if ($guardResponse = $this->ensureCycleIsEditable($request, $cycle)) {
            return $guardResponse;
        }

        if ($cycle->status !== 'approved') {
            return back()->withErrors(['status' => 'يجب اعتماد الدورة قبل بدء الفصل']);
        }

        if ($cycle->semester) {
            return back()->withErrors(['status' => 'تم إنشاء فصل لهذه الدورة مسبقاً']);
        }

        $data = $request->validate([
            'semester_name' => 'required|string|max:255',
            'semester_code' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            DB::transaction(function () use ($cycle, $data) {
                $semester = Semester::create([
                    'college_id' => $cycle->college_id,
                    'enrollment_cycle_id' => $cycle->id,
                    'name' => $data['semester_name'],
                    'code' => $data['semester_code'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'] ?? null,
                    'status' => 'active',
                    'created_by' => Auth::id(),
                ]);

                $subjectIds = $cycle->registrableSubjects()->wherePivot('is_open', true)->pluck('registrable_subjects.id')->toArray();
                if (count($subjectIds) === 0) {
                    throw new \RuntimeException('لا توجد مواد معتمدة لبدء الفصل');
                }
                $syncData = [];
                foreach ($subjectIds as $subjectId) {
                    $legacySubjectId = RegistrableSubject::where('id', $subjectId)->value('legacy_subject_id');
                    $syncData[$subjectId] = [
                        'is_active' => true,
                        'registered_count' => 0,
                        'subject_id' => $legacySubjectId,
                    ];
                }
                $semester->registrableSubjects()->sync($syncData);

                Registration::where('enrollment_cycle_id', $cycle->id)
                    ->where('status', 'accepted')
                    ->update(['semester_id' => $semester->id]);

                $this->provisionSemesterSections($cycle, $semester, $subjectIds);

                $registrations = Registration::with('registrableSubjects')
                    ->where('enrollment_cycle_id', $cycle->id)
                    ->where('status', 'accepted')
                    ->get();

                foreach ($registrations as $registration) {
                    foreach ($registration->registrableSubjects as $subject) {
                        $targetSection = $this->resolveTargetSection($semester, $subject->id);
                        if (!$targetSection) {
                            continue;
                        }
                        $targetSection
                            ->students()
                            ->syncWithoutDetaching([
                                $registration->student_id => ['status' => 'active'],
                            ]);
                    }
                }
            });
        } catch (\RuntimeException $ex) {
            return back()->withErrors(['status' => $ex->getMessage()]);
        }

        return back()->with('success', 'تم بدء الفصل بنجاح');
    }

    public function archive(Request $request, EnrollmentCycle $cycle)
    {
        if ($cycle->is_archived) {
            return back()->withErrors(['status' => 'هذه الدورة مؤرشفة بالفعل']);
        }

        ArchivedEnrollmentCycle::create([
            'enrollment_cycle_id' => $cycle->id,
            'archived_by' => Auth::id(),
            'archived_at' => now(),
        ]);

        return redirect()
            ->route($this->routeName($request, 'enrollment_cycles.index'))
            ->with('success', 'تمت أرشفة الدورة بنجاح');
    }

    public function restore(Request $request, EnrollmentCycle $cycle)
    {
        if (!$cycle->is_archived) {
            return redirect()
                ->route($this->routeName($request, 'enrollment_cycles.show'), $cycle)
                ->withErrors(['status' => 'هذه الدورة غير مؤرشفة']);
        }

        DB::transaction(function () use ($cycle) {
            $archiveRecord = $cycle->archiveRecord;

            $hasOpenConflict = $cycle->status === 'open'
                && EnrollmentCycle::activeListing()
                    ->where('registrable_entity_id', $cycle->registrable_entity_id)
                    ->where('id', '!=', $cycle->id)
                    ->where('status', 'open')
                    ->exists();

            if ($hasOpenConflict) {
                $cycle->update(['status' => 'closed']);
            }

            $archiveRecord?->delete();
        });

        return redirect()
            ->route($this->routeName($request, 'enrollment_cycles.index'))
            ->with('success', 'تمت استعادة الدورة بنجاح');
    }

    public function destroyArchived(Request $request, EnrollmentCycle $cycle)
    {
        if (!$cycle->is_archived) {
            return redirect()
                ->route($this->routeName($request, 'enrollment_cycles.show'), $cycle)
                ->withErrors(['status' => 'لا يمكن حذف دورة غير مؤرشفة من هذه الصفحة']);
        }

        DB::transaction(function () use ($cycle) {
            Registration::where('enrollment_cycle_id', $cycle->id)->delete();
            $cycle->delete();
        });

        return redirect()
            ->route($this->routeName($request, 'archived_enrollment_cycles.index'))
            ->with('success', 'تم حذف الدورة نهائياً مع جميع توابعها');
    }

    private function syncRegistrationWithSemester(EnrollmentCycle $cycle, Registration $registration): void
    {
        if (!$cycle->semester) {
            return;
        }

        $semester = $cycle->semester;
        $registration->loadMissing('registrableSubjects');

        if ($registration->status === 'accepted') {
            $registration->update(['semester_id' => $semester->id]);

            foreach ($registration->registrableSubjects as $subject) {
                $alreadyAssigned = ClassSection::query()
                    ->where('semester_id', $semester->id)
                    ->where('registrable_subject_id', $subject->id)
                    ->whereHas('students', function ($query) use ($registration) {
                        $query->where('students.id', $registration->student_id);
                    })
                    ->exists();

                if ($alreadyAssigned) {
                    continue;
                }

                $targetSection = $this->resolveTargetSection($semester, $subject->id);

                if (!$targetSection) {
                    $legacySubjectId = RegistrableSubject::where('id', $subject->id)->value('legacy_subject_id');
                    $targetSection = ClassSection::create([
                        'semester_id' => $semester->id,
                        'subject_id' => $legacySubjectId,
                        'registrable_subject_id' => $subject->id,
                        'doctor_id' => null,
                        'name' => 'A',
                        'mode' => 'online',
                        'zoom_url' => null,
                        'notes' => null,
                    ]);
                }

                $targetSection->students()->syncWithoutDetaching([
                    $registration->student_id => ['status' => 'active'],
                ]);
            }

            return;
        }

        if ($registration->semester_id === $semester->id) {
            $registration->update(['semester_id' => null]);
        }

        foreach ($registration->registrableSubjects as $subject) {
            $hasOtherAcceptedRegistration = Registration::query()
                ->where('id', '!=', $registration->id)
                ->where('student_id', $registration->student_id)
                ->where('semester_id', $semester->id)
                ->where('status', 'accepted')
                ->whereHas('registrableSubjects', function ($query) use ($subject) {
                    $query->where('registrable_subjects.id', $subject->id);
                })
                ->exists();

            if ($hasOtherAcceptedRegistration) {
                continue;
            }

            $sections = ClassSection::query()
                ->where('semester_id', $semester->id)
                ->where('registrable_subject_id', $subject->id)
                ->get();

            foreach ($sections as $section) {
                $section->students()->detach($registration->student_id);
            }
        }
    }

    private function provisionSemesterSections(EnrollmentCycle $cycle, Semester $semester, array $subjectIds): void
    {
        $previousSemester = Semester::query()
            ->where('id', '!=', $semester->id)
            ->whereHas('enrollmentCycle', function ($query) use ($cycle) {
                $query->where('registrable_entity_id', $cycle->registrable_entity_id);
            })
            ->with(['classSections.meetings'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        foreach ($subjectIds as $subjectId) {
            $legacySubjectId = RegistrableSubject::where('id', $subjectId)->value('legacy_subject_id');

            $sourceSections = collect();
            if ($previousSemester) {
                $sourceSections = $previousSemester->classSections
                    ->where('registrable_subject_id', $subjectId)
                    ->values();
            }

            if ($sourceSections->isEmpty()) {
                ClassSection::firstOrCreate(
                    [
                        'semester_id' => $semester->id,
                        'registrable_subject_id' => $subjectId,
                        'name' => 'A',
                    ],
                    [
                        'subject_id' => $legacySubjectId,
                        'doctor_id' => null,
                        'mode' => 'online',
                        'zoom_url' => null,
                        'notes' => null,
                    ]
                );

                continue;
            }

            foreach ($sourceSections as $sourceSection) {
                $newSection = ClassSection::firstOrCreate(
                    [
                        'semester_id' => $semester->id,
                        'registrable_subject_id' => $subjectId,
                        'name' => $sourceSection->name,
                    ],
                    [
                        'subject_id' => $legacySubjectId,
                        'doctor_id' => $sourceSection->doctor_id,
                        'mode' => $sourceSection->mode,
                        'zoom_url' => $sourceSection->zoom_url,
                        'notes' => $sourceSection->notes,
                    ]
                );

                foreach ($sourceSection->meetings as $meeting) {
                    $newSection->meetings()->updateOrCreate(
                        [
                            'day_of_week' => $meeting->day_of_week,
                            'starts_at' => $meeting->starts_at,
                        ],
                        [
                            'ends_at' => $meeting->ends_at,
                            'start_date' => $meeting->start_date,
                            'end_date' => $meeting->end_date,
                        ]
                    );
                }
            }
        }
    }

    private function resolveTargetSection(Semester $semester, int $registrableSubjectId): ?ClassSection
    {
        return ClassSection::query()
            ->where('semester_id', $semester->id)
            ->where('registrable_subject_id', $registrableSubjectId)
            ->withCount(['students', 'meetings'])
            ->orderByDesc('meetings_count')
            ->orderBy('students_count')
            ->orderBy('id')
            ->first();
    }

    private function buildCycleViewData(Request $request, EnrollmentCycle $cycle, bool $readonly): array
    {
        $cycle->load(['college', 'subjects', 'semester', 'registrableEntity', 'registrableSubjects', 'archiveRecord.archivedBy']);

        $subjects = RegistrableSubject::where('registrable_entity_id', $cycle->registrable_entity_id)
            ->with('studyTerm.studyYear')
            ->orderBy('name')
            ->get();

        $groupedSubjects = $this->groupSubjectsByPlan($subjects);

        $subjectStats = Registration::query()
            ->where('enrollment_cycle_id', $cycle->id)
            ->select('registration_registrable_subject.registrable_subject_id', DB::raw('COUNT(DISTINCT registrations.id) as registrations_count'))
            ->join('registration_registrable_subject', 'registrations.id', '=', 'registration_registrable_subject.registration_id')
            ->groupBy('registration_registrable_subject.registrable_subject_id')
            ->pluck('registrations_count', 'registrable_subject_id');

        $registrationsQuery = Registration::with(['student', 'registrableSubjects.studyTerm.studyYear', 'semester', 'enrollmentCycle.archiveRecord'])
            ->where('enrollment_cycle_id', $cycle->id);

        $filterStatus = $request->get('status');
        if ($filterStatus && in_array($filterStatus, ['under_review', 'accepted', 'rejected'], true)) {
            $registrationsQuery->where('status', $filterStatus);
        }

        $filterSubjectId = $request->get('subject_id');
        if ($filterSubjectId) {
            $registrationsQuery->whereHas('registrableSubjects', function ($q) use ($filterSubjectId) {
                $q->where('registrable_subjects.id', $filterSubjectId);
            });
        }

        $registrations = $registrationsQuery->latest()->get();

        $statusCounts = Registration::where('enrollment_cycle_id', $cycle->id)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $semesters = Semester::where('enrollment_cycle_id', $cycle->id)
            ->orderByDesc('start_date')
            ->get();

        return array_merge(compact(
            'cycle',
            'subjects',
            'groupedSubjects',
            'subjectStats',
            'registrations',
            'statusCounts',
            'filterStatus',
            'filterSubjectId',
            'semesters',
            'readonly'
        ), $this->portalViewData($request));
    }

    private function ensureCycleIsEditable(Request $request, EnrollmentCycle $cycle)
    {
        if (!$cycle->is_archived) {
            return null;
        }

        return redirect()
            ->route($this->routeName($request, 'archived_enrollment_cycles.show'), $cycle)
            ->withErrors(['status' => 'هذه الدورة مؤرشفة وتُعرض للقراءة فقط']);
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

    private function routeName(Request $request, string $suffix): string
    {
        return $this->portalViewData($request)['routeBase'].'.'.$suffix;
    }

    private function groupSubjectsByPlan(Collection $subjects): Collection
    {
        return $subjects
            ->groupBy(fn ($subject) => $subject->studyTerm?->studyYear?->id ?? 'ungrouped')
            ->map(function ($yearSubjects) {
                $studyYear = $yearSubjects->first()?->studyTerm?->studyYear;

                return [
                    'study_year' => $studyYear,
                    'terms' => $yearSubjects
                        ->groupBy(fn ($subject) => $subject->studyTerm?->id ?? 'ungrouped')
                        ->map(function ($termSubjects) {
                            $studyTerm = $termSubjects->first()?->studyTerm;

                            return [
                                'study_term' => $studyTerm,
                                'subjects' => $termSubjects->sortBy('name')->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    }
}
