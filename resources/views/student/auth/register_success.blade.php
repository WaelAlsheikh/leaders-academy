@extends('layouts.app')

@section('content')
<div class="container" style="max-width:500px;margin:80px auto;">
    <div class="card" style="padding:30px;text-align:center">

        <h2 style="color:green">تم إنشاء الحساب بنجاح ✅</h2>

        <p style="margin-top:20px">
            اسم المستخدم الخاص بك:
        </p>

        <div style="
            background:#f5f5f5;
            padding:12px;
            font-size:1.2rem;
            font-weight:bold;
            border-radius:8px;
        ">
            {{ $student->username }}
        </div>

        <p style="margin-top:15px;color:#666">
            يرجى الاحتفاظ باسم المستخدم وكلمة المرور
        </p>

        <a href="{{ route('student.login') }}" class="btn-primary" style="margin-top:20px;display:inline-block">
            تسجيل الدخول
        </a>

    </div>
</div>
@endsection
