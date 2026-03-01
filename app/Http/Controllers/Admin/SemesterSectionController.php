<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\Semester;
use App\Models\SectionMeeting;
use Illuminate\Http\Request;

class SemesterSectionController extends Controller
{
    public function index(Semester $semester)
    {
        $semester->load(['college', 'subjects', 'classSections.subject']);
        $subjects = $semester->subjects()->orderBy('name')->get();

        return view('admin.semesters.sections.index', compact('semester', 'subjects'));
    }

    public function store(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'subject_id' => 'required|integer',
            'name' => 'required|string|max:50',
            'mode' => 'required|in:online,in_person',
            'zoom_url' => 'nullable|url',
            'notes' => 'nullable|string|max:255',
        ]);

        $allowedSubjectIds = $semester->subjects()->pluck('subjects.id')->toArray();
        if (!in_array((int) $data['subject_id'], $allowedSubjectIds, true)) {
            return back()->withErrors(['subject_id' => 'المادة غير مرتبطة بهذا الفصل']);
        }

        $semester->classSections()->create($data);

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
        $section->load(['semester', 'subject', 'meetings']);
        return view('admin.sections.meetings.index', compact('section'));
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
}
