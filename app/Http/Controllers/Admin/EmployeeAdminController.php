<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $query = Employee::query();

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $employees = $query->latest()->get();

        return view('admin.employees.index', compact('employees', 'status'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:employees,username',
            'email' => 'required|email|max:255|unique:employees,email',
            'password' => 'required|string|min:6',
            'job_title' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        Employee::create([
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'job_title' => $data['job_title'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'تم إنشاء حساب الموظف بنجاح');
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees', 'username')->ignore($employee->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee->id),
            ],
            'job_title' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $employee->update([
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'job_title' => $data['job_title'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'تم تحديث بيانات الموظف');
    }

    public function toggle(Employee $employee)
    {
        $employee->update([
            'is_active' => ! $employee->is_active,
        ]);

        return redirect()->back();
    }

    public function resetPassword(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $employee->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return redirect()->back()->with('success', 'تمت إعادة تعيين كلمة المرور');
    }
}
