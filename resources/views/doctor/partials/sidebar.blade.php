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

    <form method="POST" action="{{ route('doctor.logout') }}">
        @csrf
        <button type="submit" class="sidebar-link sidebar-link-button sidebar-logout">
            تسجيل الخروج
        </button>
    </form>
</aside>
