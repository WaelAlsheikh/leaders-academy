@extends('layouts.app')

@section('hide-navbar', '1')
@section('body-class', 'employee-shell')

@section('content')
<div class="student-layout">
    @include('employee.partials.sidebar')

    <main class="student-content doctor-portal employee-portal">
        <section class="doctor-portal-hero">
            <h1 class="doctor-portal-title">أهلاً {{ $employee->full_name }}</h1>
            <p class="doctor-portal-subtitle">
                اسم المستخدم: <strong>{{ $employee->username }}</strong>
                @if($employee->job_title)
                    — {{ $employee->job_title }}
                @endif
            </p>
        </section>

        <section class="doctor-portal-summary">
            <div class="doctor-portal-stat">
                <span>إجمالي الكليات</span>
                <strong>{{ $collegeCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>إجمالي المواد</span>
                <strong>{{ $subjectCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>الكليات ذات المواد النشطة</span>
                <strong>{{ $activeCollegeCount }}</strong>
            </div>
        </section>

        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <div>
                    <h3>الوصول السريع</h3>
                    <p class="doctor-portal-meta">يمكنك من هنا متابعة إدارة الكليات والمواد التابعة لها.</p>
                </div>
            </div>

            <div class="employee-quick-links">
                <a href="{{ route('employee.colleges.index') }}" class="employee-quick-link">
                    <strong>إدارة الكليات</strong>
                    <span>إضافة الكليات وتعديلها وحذفها ضمن الصلاحيات المتاحة.</span>
                </a>

                <a href="{{ route('employee.colleges.index') }}#employee-subjects-anchor" class="employee-quick-link">
                    <strong>إدارة مواد الكليات</strong>
                    <span>الدخول إلى مواد كل كلية وإضافة المواد وتحديثها أو حذفها.</span>
                </a>
            </div>
        </section>
    </main>
</div>
@endsection
