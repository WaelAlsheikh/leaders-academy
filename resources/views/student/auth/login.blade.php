@extends('layouts.app')

@section('content')
<div class="site-content">
    <div class="container">

        <div style="max-width:420px;margin:60px auto;">
            <div class="card" style="padding:30px 25px;">

                {{-- Title --}}
                <h2 style="margin-bottom:20px;color:var(--secondary);font-size:1.4rem;">
                    تسجيل دخول الطالب
                </h2>

                {{-- Errors --}}
                @if ($errors->any())
                    <div style="background:#ffecec;border:1px solid #f5c2c2;color:#b00020;
                                padding:10px 12px;border-radius:8px;margin-bottom:15px;
                                font-size:0.9rem;text-align:right;">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ route('student.login.submit') }}">
                    @csrf

                    {{-- Email --}}
                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            البريد الإلكتروني
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="example@email.com"
                            style="
                                width:100%;
                                padding:12px;
                                border-radius:8px;
                                border:1px solid #ccc;
                                font-family:var(--font);
                            ">
                    </div>

                    {{-- Password --}}
                    <div style="margin-bottom:20px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            كلمة المرور
                        </label>
                        <input
                            type="password"
                            name="password"
                            required
                            placeholder="رقم القبول"
                            style="
                                width:100%;
                                padding:12px;
                                border-radius:8px;
                                border:1px solid #ccc;
                                font-family:var(--font);
                            ">
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        class="btn-primary"
                        style="width:100%;text-align:center;">
                        دخول
                    </button>
                </form>

                {{-- Registration Link --}}
                <div style="margin-top:20px;text-align:center;font-size:0.9rem;color:#666;">
                    إذا ليس لديك حساب، 
                    <a href="{{ route('student.register') }}" style="color: var(--primary); text-decoration: underline;">
                        سجل هنا
                    </a>
                </div>

                {{-- Footer --}}
                <div style="margin-top:18px;text-align:center;font-size:0.9rem;color:#666;">
                    في حال وجود مشكلة بتسجيل الدخول  
                    <br>
                    يرجى التواصل مع الإدارة
                </div>

            </div>
        </div>

    </div>
</div>
@endsection