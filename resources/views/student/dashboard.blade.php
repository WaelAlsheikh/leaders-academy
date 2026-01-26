@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    <aside class="student-sidebar">
        <h3 class="sidebar-title">لوحة الطالب</h3>

        <a href="{{ route('student.dashboard') }}" class="sidebar-link active">
            🏠 الرئيسية
        </a>

        <a href="{{ route('student.registration.create') }}" class="sidebar-link">
            📝 تسجيل جديد
        </a>

        {{-- لاحقاً --}}
        <a href="#" class="sidebar-link">📚 تسجيلاتي</a>
        <a href="#" class="sidebar-link">💳 الفواتير</a>
        <a href="#" class="sidebar-link">⚙️ الإعدادات</a>
    </aside>

    {{-- Main Content --}}
    <main class="student-content">
        <div class="card" style="padding:30px;">
            <h2 style="color:var(--primary);margin-bottom:10px;">
                أهلاً بك 👋
            </h2>
            <p>
                مرحباً بك في منصة الطلاب الخاصة بمعهد ليدرز  
                <br>اختر من القائمة الجانبية للبدء
            </p>
        </div>
    </main>

</div>
@endsection
