<aside class="student-sidebar">
    <h3 class="sidebar-title">لوحة الأستاذ</h3>

    <a href="{{ route('doctor.dashboard') }}"
       class="sidebar-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
        الرئيسية
    </a>

    <a href="{{ route('doctor.dashboard') }}#doctor-live-sessions"
       class="sidebar-link {{ request()->routeIs('doctor.live_sessions.*') ? 'active' : '' }}">
        المحاضرات الحية
    </a>

    <a href="{{ route('doctor.dashboard') }}#doctor-subjects"
       class="sidebar-link {{ request()->routeIs('doctor.sections.*') ? 'active' : '' }}">
        موادي / شعبي
    </a>

    <a href="{{ route('doctor.materials.index') }}"
       class="sidebar-link {{ request()->routeIs('doctor.materials.*') ? 'active' : '' }}">
        ملفات الجلسات
    </a>

    <a href="{{ route('doctor.exams.categories.index') }}"
       class="sidebar-link {{ request()->routeIs('doctor.exams.categories.*', 'doctor.exams.questions.*') ? 'active' : '' }}">
        بنك الأسئلة
    </a>

    <a href="{{ route('doctor.exams.index') }}"
       class="sidebar-link {{ request()->routeIs('doctor.exams.index', 'doctor.exams.create', 'doctor.exams.show') ? 'active' : '' }}">
        الامتحانات
    </a>

    <a href="{{ route('doctor.assignments.index') }}"
       class="sidebar-link {{ request()->routeIs('doctor.assignments.*', 'doctor.assignment_submissions.*', 'doctor.assignment_files.*') ? 'active' : '' }}">
        الوظائف
    </a>

    <a href="{{ route('doctor.exam_grades.index') }}"
       class="sidebar-link {{ request()->routeIs('doctor.exam_grades.*', 'doctor.exams.grading.*') ? 'active' : '' }}">
        درجات الامتحانات
    </a>

    <form method="POST" action="{{ route('doctor.logout') }}">
        @csrf
        <button type="submit" class="sidebar-link sidebar-link-button sidebar-logout">
            تسجيل الخروج
        </button>
    </form>
</aside>
