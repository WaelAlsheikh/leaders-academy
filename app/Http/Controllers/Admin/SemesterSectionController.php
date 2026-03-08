<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\Registration;
use App\Models\RegistrableSubject;
use App\Models\Student;
use App\Models\Semester;
use App\Models\SectionMeeting;
use Illuminate\Http\Request;

class SemesterSectionController extends Controller
{
    public function index(Semester $semester)
    {
        $semester->load(['college', 'registrableSubjects', 'classSections.registrableSubject', 'classSections.students']);
        $subjects = $semester->registrableSubjects()->orderBy('name')->get();

        return view('admin.semesters.sections.index', compact('semester', 'subjects'));
    }

    public function store(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'registrable_subject_id' => 'required|integer',
            'name' => 'required|string|max:50',
            'mode' => 'required|in:online,in_person',
            'zoom_url' => 'nullable|url',
            'notes' => 'nullable|string|max:255',
        ]);

        $allowedSubjectIds = $semester->registrableSubjects()->pluck('registrable_subjects.id')->toArray();
        if (!in_array((int) $data['registrable_subject_id'], $allowedSubjectIds, true)) {
            return back()->withErrors(['subject_id' => 'المادة غير مرتبطة بهذا الفصل']);
        }

        $exists = ClassSection::where('semester_id', $semester->id)
            ->where('registrable_subject_id', $data['registrable_subject_id'])
            ->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
            ->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'اسم الشعبة موجود مسبقاً لنفس المادة']);
        }

        $legacySubjectId = RegistrableSubject::where('id', $data['registrable_subject_id'])->value('legacy_subject_id');
        $data['subject_id'] = $legacySubjectId;
        $section = $semester->classSections()->create($data);
        $this->attachAcceptedStudentsForSection($section);

        return back()->with('success', 'تم إضافة الشعبة');
    }

    public function update(Request $request, ClassSection $section)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'mode' => 'required|in:online,in_person',
            'zoom_url' => 'nullable|url',
            'notes' => 'nullable|string|max:255',
        ]);

        $exists = ClassSection::where('semester_id', $section->semester_id)
            ->where('registrable_subject_id', $section->registrable_subject_id)
            ->where('id', '!=', $section->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
            ->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'اسم الشعبة موجود مسبقاً لنفس المادة']);
        }

        $section->update($data);

        return back()->with('success', 'تم تحديث الشعبة');
    }

    public function destroy(ClassSection $section)
    {
        $section->delete();
        return back()->with('success', 'تم حذف الشعبة');
    }

    public function meetings(ClassSection $section)
    {
        $section->load(['semester', 'registrableSubject', 'meetings', 'students']);

        $eligibleStudents = Student::query()
            ->whereIn('id', function ($query) use ($section) {
                $query->select('registrations.student_id')
                    ->from('registrations')
                    ->join('registration_registrable_subject', 'registration_registrable_subject.registration_id', '=', 'registrations.id')
                    ->where('registrations.semester_id', $section->semester_id)
                    ->where('registrations.status', 'accepted')
                    ->where('registration_registrable_subject.registrable_subject_id', $section->registrable_subject_id);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.sections.meetings.index', compact('section', 'eligibleStudents'));
    }

    public function storeMeeting(Request $request, ClassSection $section)
    {
        $data = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $section->meetings()->create($data);

        return back()->with('success', 'تمت إضافة الجلسة');
    }

    public function updateMeeting(Request $request, SectionMeeting $meeting)
    {
        $data = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $meeting->update($data);

        return back()->with('success', 'تم تحديث الجلسة');
    }

    public function destroyMeeting(SectionMeeting $meeting)
    {
        $meeting->delete();
        return back()->with('success', 'تم حذف الجلسة');
    }

    public function attachStudent(Request $request, ClassSection $section)
    {
        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
        ]);

        $isEligible = Registration::query()
            ->where('student_id', $data['student_id'])
            ->where('semester_id', $section->semester_id)
            ->where('status', 'accepted')
            ->whereHas('registrableSubjects', function ($query) use ($section) {
                $query->where('registrable_subjects.id', $section->registrable_subject_id);
            })
            ->exists();

        if (!$isEligible) {
            return back()->withErrors(['student_id' => 'هذا الطالب ليس مقبولاً لهذه المادة ضمن هذا الفصل']);
        }

        ClassSection::query()
            ->where('semester_id', $section->semester_id)
            ->where('registrable_subject_id', $section->registrable_subject_id)
            ->where('id', '!=', $section->id)
            ->get()
            ->each(function (ClassSection $otherSection) use ($data) {
                $otherSection->students()->detach($data['student_id']);
            });

        $section->students()->syncWithoutDetaching([
            $data['student_id'] => ['status' => 'active'],
        ]);

        return back()->with('success', 'تمت إضافة الطالب للشعبة');
    }

    public function detachStudent(ClassSection $section, Student $student)
    {
        $section->students()->detach($student->id);
        return back()->with('success', 'تم حذف الطالب من الشعبة');
    }

    private function attachAcceptedStudentsForSection(ClassSection $section): void
    {
        $studentIds = Registration::query()
            ->where('semester_id', $section->semester_id)
            ->where('status', 'accepted')
            ->whereHas('registrableSubjects', function ($query) use ($section) {
                $query->where('registrable_subjects.id', $section->registrable_subject_id);
            })
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();

        if (empty($studentIds)) {
            return;
        }

        $syncData = [];
        foreach ($studentIds as $studentId) {
            $syncData[$studentId] = ['status' => 'active'];
        }

        $section->students()->syncWithoutDetaching($syncData);
    }
}
