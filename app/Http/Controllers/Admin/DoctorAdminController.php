<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:doctors',
        ]);

        Doctor::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'academic_degree' => $request->academic_degree,
            'specialization' => $request->specialization,
            'password' => Hash::make('123456'), // مؤقت
            'is_active' => false,
        ]);

        return redirect()->back()->with('success', 'تم إنشاء حساب الدكتور بنجاح');
    }

    public function toggle(Doctor $doctor)
    {
        $doctor->update([
            'is_active' => ! $doctor->is_active
        ]);

        return redirect()->back();
    }
}
