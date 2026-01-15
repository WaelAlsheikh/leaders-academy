@extends('layouts.app')

@section('content')
<div class="site-content">
    <div class="container">

        <div style="max-width:420px;margin:60px auto;">
            <div class="card" style="padding:30px 25px;">

                {{-- Title --}}
                <h2 style="margin-bottom:20px;color:var(--secondary);font-size:1.4rem;text-align:center;">
                    تسجيل دخول الطالب
                </h2>

                {{-- Errors --}}
                @if ($errors->any())
                    <div style="
                        background:#ffecec;
                        border:1px solid #f5c2c2;
                        color:#b00020;
                        padding:10px 12px;
                        border-radius:8px;
                        margin-bottom:15px;
                        font-size:0.9rem;
                        text-align:right;">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ route('student.login.submit') }}">
                    @csrf

                    {{-- Username --}}
                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            اسم المستخدم
                        </label>
                        <input type="text" name="username" value="{{ old('username') }}"
                               required placeholder="مثال: ahmed_123456"
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;direction:ltr;">
                    </div>

                    {{-- Password --}}
                    <div style="margin-bottom:20px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            كلمة المرور
                        </label>
                        <input type="password" name="password" required
                               placeholder="********"
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;">
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        style="
                            width:100%;
                            padding:14px;
                            background-color:var(--primary);
                            color:#fff;
                            border:none;
                            border-radius:10px;
                            font-size:1rem;
                            font-weight:600;
                            cursor:pointer;
                        ">
                        دخول
                    </button>
                </form>

                {{-- Register Link --}}
                <div style="margin-top:20px;text-align:center;font-size:0.9rem;color:#666;">
                    ليس لديك حساب؟
                    <a href="{{ route('student.register') }}"
                       style="color:var(--primary);font-weight:600;">
                        سجل من هنا
                    </a>
                </div>

                {{-- Footer --}}
                <div style="margin-top:18px;text-align:center;font-size:0.9rem;color:#666;">
                    في حال وجود مشكلة بتسجيل الدخول<br>
                    يرجى التواصل مع الإدارة
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
