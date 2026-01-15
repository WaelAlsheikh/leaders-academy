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
    /* ================== Register ================== */

    public function showRegister()
    {
        return view('student.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'first_name_en' => [
                'required',
                'regex:/^[a-zA-Z]{2,}$/'
            ],
            'email' => 'required|email|unique:students,email',
            'phone' => 'required|string|min:8',
            'password' => 'required|min:6|confirmed',
        ], [
            'first_name_en.regex' => 'الاسم الإنكليزي يجب أن يحتوي أحرف إنكليزية فقط وبدون فراغات',
        ]);

        // توليد username فريد
        $base = strtolower($request->first_name_en);

        do {
            $username = $base . '_' . random_int(100000, 999999);
        } while (Student::where('username', $username)->exists());

        $student = Student::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'first_name_en' => $request->first_name_en,
            'username' => $username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'acceptance_number' => strtoupper(Str::random(10)),
        ]);

        return view('student.auth.register_success', compact('student'));
    }

    /* ================== Login ================== */

    public function showLogin()
    {
        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('student.dashboard');
        }

        return back()->withErrors([
            'login' => 'اسم المستخدم أو كلمة المرور غير صحيحة',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
