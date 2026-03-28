<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $query = Doctor::query();

        if ($status === 'active') {
            $query->where('is_active', 1);
        } elseif ($status === 'inactive') {
            $query->where('is_active', 0);
        }

        $doctors = $query->latest()->get();

        return view('admin.doctors.index', compact('doctors', 'status'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required',
            'username' => 'required|string|max:255|unique:doctors,username',
            'email' => 'required|email|unique:doctors',
            'password' => 'required|string|min:6',
            'is_active' => 'nullable|boolean',
        ]);

        Doctor::create([
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'academic_degree' => $request->academic_degree,
            'specialization' => $request->specialization,
            'password' => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'تم إنشاء حساب الدكتور بنجاح');
    }

    public function update(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('doctors', 'username')->ignore($doctor->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('doctors', 'email')->ignore($doctor->id),
            ],
            'academic_degree' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $doctor->update([
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'academic_degree' => $data['academic_degree'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'تم تحديث بيانات الأستاذ');
    }

    public function toggle(Doctor $doctor)
    {
        $doctor->update([
            'is_active' => ! $doctor->is_active
        ]);

        return redirect()->back();
    }

    public function resetPassword(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $doctor->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return redirect()->back()->with('success', 'تمت إعادة تعيين كلمة المرور');
    }
}
