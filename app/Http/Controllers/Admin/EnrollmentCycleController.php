<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\EnrollmentCycle;
use App\Models\Registration;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnrollmentCycleController extends Controller
{
    public function index()
    {
        RegistrableEntity::syncFromSources();

        $cycles = EnrollmentCycle::with(['college', 'semester', 'registrableEntity'])->latest()->get();
        $registrableEntities = RegistrableEntity::query()
            ->where('is_active', true)
            ->orderBy('entity_type')
            ->orderBy('title_snapshot')
            ->get();

        return view('admin.enrollment_cycles.index', compact('cycles', 'registrableEntities'));
    }

    public function store(Request $request)
    {
        RegistrableEntity::syncFromSources();

        $data = $request->validate([
            'registrable_entity_id' => 'required|integer|exists:registrable_entities,id',
            'name' => 'required|string|max:255',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after_or_equal:registration_starts_at',
        ]);

        $entity = RegistrableEntity::findOrFail($data['registrable_entity_id']);
        $collegeId = $entity->entity_type === 'college' ? $entity->entity_id : null;

        EnrollmentCycle::create([
            'college_id' => $collegeId,
            'registrable_entity_id' => $data['registrable_entity_id'],
            'name' => $data['name'],
            'registration_starts_at' => $data['registration_starts_at'] ?? null,
            'registration_ends_at' => $data['registration_ends_at'] ?? null,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.enrollment_cycles.index')
            ->with('success', 'تم إنشاء دورة التسجيل بنجاح');
    }

    public function show(Request $request, EnrollmentCycle $cycle)
    {
        $cycle->load(['college', 'subjects', 'semester', 'registrableEntity', 'registrableSubjects']);

        $subjects = RegistrableSubject::where('registrable_entity_id', $cycle->registrable_entity_id)
            ->orderBy('name')
            ->get();

        $subjectStats = Registration::query()
            ->where('enrollment_cycle_id', $cycle->id)
            ->select('registration_registrable_subject.registrable_subject_id', DB::raw('COUNT(DISTINCT registrations.id) as registrations_count'))
            ->join('registration_registrable_subject', 'registrations.id', '=', 'registration_registrable_subject.registration_id')
            ->groupBy('registration_registrable_subject.registrable_subject_id')
            ->pluck('registrations_count', 'registrable_subject_id');

        $registrationsQuery = Registration::with(['student', 'registrableSubjects'])
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

        return view('admin.enrollment_cycles.show', compact(
            'cycle',
            'subjects',
            'subjectStats',
            'registrations',
            'statusCounts',
            'filterStatus',
            'filterSubjectId',
            'semesters'
        ));
    }

    public function update(Request $request, EnrollmentCycle $cycle)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after_or_equal:registration_starts_at',
            'status' => 'required|in:draft,open,closed,approved,cancelled',
        ]);

        $cycle->update($data);

        return back()->with('success', 'تم تحديث الدورة');
    }

    public function updateSubjects(Request $request, EnrollmentCycle $cycle)
    {
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

    public function open(EnrollmentCycle $cycle)
    {
        $otherOpen = EnrollmentCycle::where('registrable_entity_id', $cycle->registrable_entity_id)
            ->where('id', '!=', $cycle->id)
            ->where('status', 'open')
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

    public function close(EnrollmentCycle $cycle)
    {
        $cycle->update(['status' => 'closed']);

        return back()->with('success', 'تم إغلاق التسجيل');
    }

    public function approve(EnrollmentCycle $cycle)
    {
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

    public function startSemester(Request $request, EnrollmentCycle $cycle)
    {
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

                $sectionMap = [];
                foreach ($subjectIds as $subjectId) {
                    $legacySubjectId = RegistrableSubject::where('id', $subjectId)->value('legacy_subject_id');
                    $section = ClassSection::firstOrCreate(
                        [
                            'semester_id' => $semester->id,
                            'registrable_subject_id' => $subjectId,
                            'name' => 'A',
                        ],
                        [
                            'subject_id' => $legacySubjectId,
                            'mode' => 'online',
                            'zoom_url' => null,
                        ]
                    );
                    $sectionMap[$subjectId] = $section;
                }

                $registrations = Registration::with('registrableSubjects')
                    ->where('enrollment_cycle_id', $cycle->id)
                    ->where('status', 'accepted')
                    ->get();

                foreach ($registrations as $registration) {
                    foreach ($registration->registrableSubjects as $subject) {
                        if (!isset($sectionMap[$subject->id])) {
                            continue;
                        }
                        $sectionMap[$subject->id]
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

                $targetSection = ClassSection::query()
                    ->where('semester_id', $semester->id)
                    ->where('registrable_subject_id', $subject->id)
                    ->withCount('students')
                    ->orderBy('students_count')
                    ->orderBy('id')
                    ->first();

                if (!$targetSection) {
                    $legacySubjectId = RegistrableSubject::where('id', $subject->id)->value('legacy_subject_id');
                    $targetSection = ClassSection::create([
                        'semester_id' => $semester->id,
                        'subject_id' => $legacySubjectId,
                        'registrable_subject_id' => $subject->id,
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
}
