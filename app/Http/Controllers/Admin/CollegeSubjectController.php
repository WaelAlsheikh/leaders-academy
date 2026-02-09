<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Subject;
use Illuminate\Http\Request;

class CollegeSubjectController extends Controller
{
    // عرض جميع الكليات
    public function colleges()
    {
        $colleges = College::all();
        return view('admin.colleges.index', compact('colleges'));
    }

    // عرض مواد كلية محددة
    public function subjects(College $college)
    {
        $subjects = $college->subjects()->orderBy('name')->get();
        return view('admin.subjects.index', compact('college', 'subjects'));
    }

    // إضافة مادة
    public function store(Request $request, College $college)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects,code',
            'credit_hours' => 'required|integer|min:1',
        ]);

        $college->subjects()->create([
            'name' => $request->name,
            'code' => $request->code,
            'credit_hours' => $request->credit_hours,
        ]);

        return back()->with('success', 'تمت إضافة المادة بنجاح');
    }

    // تعديل مادة
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string',
            'credit_hours' => 'required|integer|min:1',
        ]);

        $subject->update($request->only('name', 'credit_hours'));

        return back()->with('success', 'تم تحديث المادة');
    }

    // حذف مادة
    public function destroy(Subject $subject)
    {
        $subject->delete();
        return back()->with('success', 'تم حذف المادة');
    }
}
