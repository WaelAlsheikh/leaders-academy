@extends('layouts.app')

@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')

@section('content')
<div class="site-content">
    <div class="container">
        <div style="max-width:420px;margin:60px auto;">
            <div class="card" style="padding:30px 25px;">
                <h2 style="margin-bottom:20px;color:var(--secondary);font-size:1.4rem;text-align:center;">
                    تسجيل دخول الأستاذ الجامعي
                </h2>

                @if ($errors->any())
                    <div style="background:#ffecec;border:1px solid #f5c2c2;color:#b00020;padding:10px 12px;border-radius:8px;margin-bottom:15px;font-size:0.9rem;text-align:right;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('doctor.login.submit') }}" autocomplete="on">
                    @csrf

                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">اسم المستخدم</label>
                        <input type="text"
                               name="username"
                               value="{{ old('username') }}"
                               autocomplete="username"
                               required
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;direction:ltr;">
                    </div>

                    <div style="margin-bottom:20px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">كلمة المرور</label>
                        <input type="password"
                               name="password"
                               autocomplete="current-password"
                               required
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;">
                    </div>

                    <button type="submit"
                        style="width:100%;padding:14px;background-color:var(--primary);color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:600;cursor:pointer;">
                        دخول
                    </button>
                </form>

                <div style="margin-top:18px;text-align:center;font-size:0.9rem;color:#666;">
                    يتم تزويدك ببيانات الدخول من قبل الإدارة الأكاديمية.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
