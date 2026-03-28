<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('doctor.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('doctor')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'is_active' => true,
        ])) {
            $request->session()->regenerate();

            return redirect()->route('doctor.dashboard');
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors([
                'login' => 'بيانات الدخول غير صحيحة أو أن الحساب غير مفعل.',
            ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('doctor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('doctor.login');
    }
}
