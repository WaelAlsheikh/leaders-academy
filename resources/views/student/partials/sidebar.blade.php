<!-- Student Sidebar -->
<aside class="w-64 bg-gray-800 text-white min-h-screen p-4">
    <h2 class="text-xl font-bold mb-6">لوحة الطالب</h2>

    <ul class="space-y-3">
        <li>
            <a href="{{ route('student.dashboard') }}"
               class="block px-4 py-2 rounded hover:bg-gray-700">
                🏠 الرئيسية
            </a>
        </li>

        <li>
            <a href="{{ route('student.registration.create') }}"
               class="block px-4 py-2 rounded hover:bg-gray-700">
                📝 تسجيل المواد
            </a>
        </li>

        <li>
            <a href="{{ route('student.registrations.index') }}"
               class="block px-4 py-2 rounded hover:bg-gray-700">
                📚 تسجيلاتي
            </a>
        </li>

        <li>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="block px-4 py-2 rounded hover:bg-red-600">
                🚪 تسجيل الخروج
            </a>
        </li>
    </ul>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</aside>
