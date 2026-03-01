<aside class="student-sidebar">
    <h3 class="sidebar-title">لوحة الطالب</h3>

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
       class="sidebar-link {{ request()->routeIs('student.schedule.index') ? 'active' : '' }}">
        📅 الجدول
    </a>

    <form method="POST" action="{{ route('student.logout') }}">
        @csrf
        <button type="submit" class="sidebar-link sidebar-link-button sidebar-logout">
            🚪 تسجيل الخروج
        </button>
    </form>
</aside>
