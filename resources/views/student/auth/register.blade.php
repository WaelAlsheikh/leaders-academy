@extends('layouts.app')

@section('content')
<div class="site-content">
    <div class="container">

        <div style="max-width:500px;margin:60px auto;">
            <div class="card" style="padding:30px 25px;">

                {{-- Title --}}
                <h2 style="margin-bottom:20px;color:var(--secondary);font-size:1.4rem;text-align:center;">
                    تسجيل طالب جديد
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
                        <ul style="margin:0;padding-right:18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Register Form --}}
                <form method="POST" action="{{ route('student.register.submit') }}">
                    @csrf

                    {{-- First Name --}}
                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            الاسم الأول
                        </label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}"
                               required placeholder="مثال: أحمد"
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;">
                    </div>

                    {{-- Last Name --}}
                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            الكنية
                        </label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                               required placeholder="مثال: النصر"
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;">
                    </div>

                    {{-- English Name --}}
                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            الاسم بالإنكليزي
                        </label>
                        <input type="text" name="first_name_en" value="{{ old('first_name_en') }}"
                               required placeholder="Ahmed"
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;direction:ltr;">
                    </div>

                    {{-- Email --}}
                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            البريد الإلكتروني
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               required placeholder="example@email.com"
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;direction:ltr;">
                    </div>

                    {{-- Phone --}}
                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            رقم الهاتف
                        </label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               required placeholder="09XXXXXXXX"
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;direction:ltr;">
                    </div>

                    {{-- Password --}}
                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            كلمة المرور
                        </label>
                        <input type="password" name="password" required
                               placeholder="********"
                               style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;">
                    </div>

                    {{-- Confirm Password --}}
                    <div style="margin-bottom:20px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            تأكيد كلمة المرور
                        </label>
                        <input type="password" name="password_confirmation" required
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
                            transition:0.3s;
                        ">
                        إنشاء الحساب
                    </button>
                </form>

                {{-- Footer --}}
                <div style="margin-top:18px;text-align:center;font-size:0.9rem;color:#666;">
                    لديك حساب بالفعل؟
                    <a href="{{ route('student.login') }}"
                       style="color:var(--primary);font-weight:600;">
                        تسجيل الدخول
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
