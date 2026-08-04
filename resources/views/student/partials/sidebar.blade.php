<x-portal-sidebar title="لوحة الطالب" portal="student">
    <a href="{{ route('student.dashboard') }}"
       class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        🏠 الرئيسية
    </a>

    <a href="{{ route('student.registration.create') }}"
       class="sidebar-link {{ request()->routeIs('student.registration.create') ? 'active' : '' }}">
        📝 تسجيل جديد
    </a>

    <a href="{{ route('student.registrations.index') }}"
       class="sidebar-link {{ request()->routeIs('student.registrations.index') ? 'active' : '' }}">
        📚 تسجيلاتي
    </a>

    <a href="{{ route('student.invoices.index') }}"
       class="sidebar-link {{ request()->routeIs('student.invoices.index') ? 'active' : '' }}">
        💳 الفواتير
    </a>

    <a href="{{ route('student.schedule.index') }}"
       class="sidebar-link {{ request()->routeIs('student.schedule.index') || request()->routeIs('student.live_sessions.*') ? 'active' : '' }}">
        📅 الجدول
    </a>

    <a href="{{ route('student.materials.index') }}"
       class="sidebar-link {{ request()->routeIs('student.materials.*') ? 'active' : '' }}">
        📁 ملفات المواد
    </a>

    <a href="{{ route('student.exams.index') }}"
       class="sidebar-link {{ request()->routeIs('student.exams.*') ? 'active' : '' }}">
        📝 الامتحانات
    </a>

    <a href="{{ route('student.assignments.index') }}"
       class="sidebar-link {{ request()->routeIs('student.assignments.*', 'student.assignment_files.*') ? 'active' : '' }}">
        📂 الوظائف
    </a>

    <a href="{{ route('student.my_email.show') }}"
       class="sidebar-link {{ request()->routeIs('student.my_email.*') ? 'active' : '' }}">
        ✉️ الإيميل
    </a>

    <form method="POST" action="{{ route('student.logout') }}">
        @csrf
        <button type="submit" class="sidebar-link sidebar-link-button sidebar-logout">
            🚪 تسجيل الخروج
        </button>
    </form>
</x-portal-sidebar>
