<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\ClassSection;
use App\Models\EnrollmentCycle;
use App\Models\Registration;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrollmentCycleController extends Controller
{
    public function index()
    {
        $cycles = EnrollmentCycle::with(['college', 'semester'])->latest()->get();
        $colleges = College::orderBy('title')->get();

        return view('admin.enrollment_cycles.index', compact('cycles', 'colleges'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'college_id' => 'required|integer|exists:colleges,id',
            'name' => 'required|string|max:255',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after_or_equal:registration_starts_at',
        ]);

        EnrollmentCycle::create([
            'college_id' => $data['college_id'],
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
        $cycle->load(['college', 'subjects', 'semester']);

        $subjects = Subject::where('college_id', $cycle->college_id)
            ->orderBy('name')
            ->get();

        $subjectStats = Registration::query()
            ->where('enrollment_cycle_id', $cycle->id)
            ->select('registration_subject.subject_id', DB::raw('COUNT(DISTINCT registrations.id) as registrations_count'))
            ->join('registration_subject', 'registrations.id', '=', 'registration_subject.registration_id')
            ->groupBy('registration_subject.subject_id')
            ->pluck('registrations_count', 'subject_id');

        $registrationsQuery = Registration::with(['student', 'subjects'])
            ->where('enrollment_cycle_id', $cycle->id);

        $filterStatus = $request->get('status');
        if ($filterStatus && in_array($filterStatus, ['under_review', 'accepted', 'rejected'], true)) {
            $registrationsQuery->where('status', $filterStatus);
        }

        $filterSubjectId = $request->get('subject_id');
        if ($filterSubjectId) {
            $registrationsQuery->whereHas('subjects', function ($q) use ($filterSubjectId) {
                $q->where('subjects.id', $filterSubjectId);
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
            'subjects.*' => 'integer|exists:subjects,id',
        ]);

        $subjectIds = $data['subjects'] ?? [];

        $validSubjectIds = Subject::where('college_id', $cycle->college_id)
            ->whereIn('id', $subjectIds)
            ->pluck('id')
            ->toArray();

        $syncData = [];
        foreach ($validSubjectIds as $subjectId) {
            $syncData[$subjectId] = ['is_open' => true];
        }

        $cycle->subjects()->sync($syncData);

        return back()->with('success', 'تم تحديث المواد المتاحة للدورة');
    }

    public function open(EnrollmentCycle $cycle)
    {
        $otherOpen = EnrollmentCycle::where('college_id', $cycle->college_id)
            ->where('id', '!=', $cycle->id)
            ->where('status', 'open')
            ->exists();

        if ($otherOpen) {
            return back()->withErrors(['status' => 'يوجد دورة تسجيل مفتوحة لهذه الكلية بالفعل']);
        }

        if ($cycle->subjects()->count() === 0) {
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

        if ($cycle->subjects()->count() === 0) {
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

        if ($cycle->semester) {
            return back()->withErrors(['status' => 'لا يمكن تعديل حالة التسجيل بعد بدء الفصل']);
        }

        $data = $request->validate([
            'status' => 'required|in:under_review,accepted,rejected',
        ]);

        $registration->update([
            'status' => $data['status'],
        ]);

        return back()->with('success', 'تم تحديث الحالة');
    }

    public function bulkUpdateRegistrationStatus(Request $request, EnrollmentCycle $cycle)
    {
        if ($cycle->semester) {
            return back()->withErrors(['status' => 'لا يمكن تعديل حالة التسجيل بعد بدء الفصل']);
        }

        $data = $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'integer',
            'status' => 'required|in:under_review,accepted,rejected',
        ]);

        Registration::where('enrollment_cycle_id', $cycle->id)
            ->whereIn('id', $data['registration_ids'])
            ->update(['status' => $data['status']]);

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

                $subjectIds = $cycle->subjects()->wherePivot('is_open', true)->pluck('subjects.id')->toArray();
                if (count($subjectIds) === 0) {
                    throw new \RuntimeException('لا توجد مواد معتمدة لبدء الفصل');
                }
                $syncData = [];
                foreach ($subjectIds as $subjectId) {
                    $syncData[$subjectId] = ['is_active' => true, 'registered_count' => 0];
                }
                $semester->subjects()->sync($syncData);

                Registration::where('enrollment_cycle_id', $cycle->id)
                    ->where('status', 'accepted')
                    ->update(['semester_id' => $semester->id]);

                $sectionMap = [];
                foreach ($subjectIds as $subjectId) {
                    $section = ClassSection::firstOrCreate(
                        [
                            'semester_id' => $semester->id,
                            'subject_id' => $subjectId,
                            'name' => 'A',
                        ],
                        [
                            'mode' => 'online',
                            'zoom_url' => null,
                        ]
                    );
                    $sectionMap[$subjectId] = $section;
                }

                $registrations = Registration::with('subjects')
                    ->where('enrollment_cycle_id', $cycle->id)
                    ->where('status', 'accepted')
                    ->get();

                foreach ($registrations as $registration) {
                    foreach ($registration->subjects as $subject) {
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
}
