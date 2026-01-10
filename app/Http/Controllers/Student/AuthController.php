<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('student.dashboard');
        }

        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
    }

    public function showRegister()
    {
        return view('student.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:students',
        ]);

        $acceptanceNumber = strtoupper(Str::random(8));

        $student = Student::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'acceptance_number' => $acceptanceNumber,
            'password' => Hash::make($acceptanceNumber),
        ]);

        Auth::guard('student')->login($student);

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
