<aside class="student-sidebar">
    <h3 class="sidebar-title">لوحة الموظف</h3>

    <a href="{{ route('employee.dashboard') }}"
       class="sidebar-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
        الرئيسية
    </a>

    <a href="{{ route('employee.colleges.index') }}"
       class="sidebar-link {{ request()->routeIs('employee.colleges.*') ? 'active' : '' }}">
        إدارة الكليات
    </a>

    <a href="{{ route('employee.colleges.index') }}#employee-subjects-anchor"
       class="sidebar-link {{ request()->routeIs('employee.subjects.*') ? 'active' : '' }}">
        إدارة مواد الكليات
    </a>

    <form method="POST" action="{{ route('employee.logout') }}">
        @csrf
        <button type="submit" class="sidebar-link sidebar-link-button sidebar-logout">
            تسجيل الخروج
        </button>
    </form>
</aside>
