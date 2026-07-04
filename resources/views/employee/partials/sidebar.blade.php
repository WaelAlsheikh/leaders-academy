<aside class="student-sidebar">
    <h3 class="sidebar-title">لوحة الموظف</h3>

    <a href="{{ route('employee.dashboard') }}"
       class="sidebar-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
        الرئيسية
    </a>

    <a href="{{ route('employee.enrollment_cycles.index') }}"
       class="sidebar-link {{ request()->routeIs('employee.enrollment_cycles.*', 'employee.registration_seasons.*', 'employee.semesters.sections.*', 'employee.sections.*', 'employee.meetings.*', 'employee.registrables.registrations.index') ? 'active' : '' }}">
        إدارة الدورات
    </a>

    <a href="{{ route('employee.archived_enrollment_cycles.index') }}"
       class="sidebar-link {{ request()->routeIs('employee.archived_enrollment_cycles.*') ? 'active' : '' }}">
        الدورات المؤرشفة
    </a>

    <a href="{{ route('employee.colleges.index') }}"
       class="sidebar-link {{ request()->routeIs('employee.colleges.index', 'employee.colleges.store', 'employee.colleges.update', 'employee.colleges.destroy') ? 'active' : '' }}">
        إدارة الكليات
    </a>

    <a href="{{ route('employee.program_branches.index') }}"
       class="sidebar-link {{ request()->routeIs('employee.program_branches.index') ? 'active' : '' }}">
        إدارة فروع البرامج الجامعية
    </a>

    <a href="{{ route('employee.training_program_branches.index') }}"
       class="sidebar-link {{ request()->routeIs('employee.training_program_branches.index') ? 'active' : '' }}">
        إدارة فروع البرامج التدريبية
    </a>

    <a href="{{ route('employee.colleges.index') }}"
       class="sidebar-link {{ request()->routeIs('employee.colleges.years', 'employee.registrables.years', 'employee.study_years.*', 'employee.study_terms.*', 'employee.registrables.subjects.*', 'employee.registrable_subjects.*', 'employee.subjects.*') ? 'active' : '' }}">
        إدارة السنوات والفصول
    </a>

    <a href="{{ route('employee.exams.index') }}"
       class="sidebar-link {{ request()->routeIs('employee.exams.*', 'employee.exam_settings.*', 'employee.exam_grades.*') ? 'active' : '' }}">
        الامتحانات
    </a>

    <form method="POST" action="{{ route('employee.logout') }}">
        @csrf
        <button type="submit" class="sidebar-link sidebar-link-button sidebar-logout">
            تسجيل الخروج
        </button>
    </form>
</aside>
