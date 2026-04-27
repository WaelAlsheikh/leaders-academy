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
                <span>الدورات الفصلية</span>
                <strong>{{ $cycleCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>الدورات المفتوحة</span>
                <strong>{{ $openSeasonCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>الدورات المؤرشفة</span>
                <strong>{{ $archivedCycleCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>إجمالي الفصول</span>
                <strong>{{ $semesterCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>الشعب والجلسات</span>
                <strong>{{ $sectionCount }} / {{ $meetingCount }}</strong>
            </div>
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
            <div class="doctor-portal-stat">
                <span>فروع البرامج الجامعية</span>
                <strong>{{ $programBranchCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>فروع البرامج التدريبية</span>
                <strong>{{ $trainingProgramBranchCount }}</strong>
            </div>
        </section>

        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <div>
                    <h3>الوصول السريع</h3>
                    <p class="doctor-portal-meta">يمكنك من هنا متابعة إدارة الدورات والكليات والمواد المرتبطة بها من نفس بوابة الموظف.</p>
                </div>
            </div>

            <div class="employee-quick-links">
                <a href="{{ route('employee.enrollment_cycles.index') }}" class="employee-quick-link">
                    <strong>إدارة الدورات</strong>
                    <span>إنشاء دورة فصلية عامة واحدة ثم فتح الكليات والبرامج داخلها ومتابعة الشعب والجلسات والطلاب المرتبطين بها.</span>
                </a>

                <a href="{{ route('employee.archived_enrollment_cycles.index') }}" class="employee-quick-link">
                    <strong>الدورات المؤرشفة</strong>
                    <span>عرض الدورات المؤرشفة واستعادتها أو حذفها النهائي عند الحاجة.</span>
                </a>

                <a href="{{ route('employee.colleges.index') }}" class="employee-quick-link">
                    <strong>إدارة الكليات</strong>
                    <span>إضافة الكليات وتعديلها وحذفها ثم إدارة السنوات والفصول والمواد التابعة لها.</span>
                </a>

                <a href="{{ route('employee.program_branches.index') }}" class="employee-quick-link">
                    <strong>إدارة فروع البرامج الجامعية</strong>
                    <span>متابعة الفروع الجامعية ثم إدارة السنوات والفصول والمواد التابعة لكل فرع.</span>
                </a>

                <a href="{{ route('employee.training_program_branches.index') }}" class="employee-quick-link">
                    <strong>إدارة فروع البرامج التدريبية</strong>
                    <span>متابعة الفروع التدريبية ثم إدارة السنوات والفصول والمواد التابعة لكل فرع.</span>
                </a>

                <a href="{{ route('employee.colleges.index') }}" class="employee-quick-link">
                    <strong>إدارة السنوات والفصول</strong>
                    <span>الانتقال من الكيان إلى السنوات ثم الفصول ثم المواد وفق البنية الأكاديمية الجديدة.</span>
                </a>
            </div>
        </section>
    </main>
</div>
@endsection
