<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentAdminController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('status', 'all');

        $query = Student::query();

        if ($filter === 'active') {
            $query->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('is_active', false);
        }

        $students = $query
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.students.index', compact(
            'students',
            'filter'
        ));
    }

    public function toggle(Student $student)
    {
        $student->update([
            'is_active' => ! $student->is_active
        ]);

        return back()->with('success', 'تم تحديث حالة الطالب بنجاح');
    }

    public function resetPassword(Request $request, Student $student)
    {
        $data = $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $student->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return back()->with('success', 'تمت إعادة تعيين كلمة مرور الطالب بنجاح.');
    }
}
