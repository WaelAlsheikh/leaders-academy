<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Subject;
use App\Services\CollegeSubjectSyncService;
use Illuminate\Http\Request;

class CollegeSubjectController extends Controller
{
    public function __construct(
        private readonly CollegeSubjectSyncService $collegeSubjectSyncService
    ) {
    }

    // عرض جميع الكليات
    public function colleges()
    {
        $colleges = College::all();
        return view('admin.colleges.index', compact('colleges'));
    }

    // عرض مواد كلية محددة
    public function subjects(College $college)
    {
        $this->collegeSubjectSyncService->ensureCollegeEntity($college);
        $college->subjects()->get()->each(function (Subject $subject) {
            $this->collegeSubjectSyncService->syncLegacySubject($subject);
        });

        $subjects = $college->subjects()
            ->with('registrableSubject')
            ->orderBy('name')
            ->get();

        return view('admin.subjects.index', compact('college', 'subjects'));
    }

    // إضافة مادة
    public function store(Request $request, College $college)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects,code',
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $this->collegeSubjectSyncService->createLegacyAndSync($college, [
            'name' => $request->name,
            'code' => $request->code,
            'credit_hours' => $request->credit_hours,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'تمت إضافة المادة بنجاح');
    }

    // تعديل مادة
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects,code,' . $subject->id,
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $this->collegeSubjectSyncService->updateLegacyAndSync($subject, [
            'name' => $request->name,
            'code' => $request->code,
            'credit_hours' => $request->credit_hours,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'تم تحديث المادة');
    }

    // حذف مادة
    public function destroy(Subject $subject)
    {
        $registrableSubject = $subject->registrableSubject;

        if (
            $registrableSubject
            && (
                $registrableSubject->enrollmentCycles()->exists()
                || $registrableSubject->registrations()->exists()
                || $registrableSubject->classSections()->exists()
                || $registrableSubject->semesters()->exists()
            )
        ) {
            return back()->withErrors(['status' => 'لا يمكن حذف مادة مرتبطة بتسجيلات أو دورات أو شعب']);
        }

        $this->collegeSubjectSyncService->deleteLegacyAndSyncedSubject($subject);

        return back()->with('success', 'تم حذف المادة');
    }
}
