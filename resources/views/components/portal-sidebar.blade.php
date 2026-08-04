@props([
    'title',
    'portal',
])

<aside
    {{ $attributes->class(['student-sidebar']) }}
    data-portal-sidebar="{{ $portal }}"
>
    <div class="sidebar-toolbar">
        <h3 class="sidebar-title">{{ $title }}</h3>
        <button
            type="button"
            class="sidebar-collapse-btn"
            data-sidebar-toggle
            aria-expanded="true"
            title="طي القائمة"
        >
            <i class="fa-solid fa-angles-left sidebar-collapse-icon" aria-hidden="true"></i>
            <span class="sidebar-collapse-label">طي</span>
        </button>
    </div>

    <nav class="sidebar-nav">
        {{ $slot }}
    </nav>

    <button
        type="button"
        class="sidebar-expand-fab"
        data-sidebar-expand
        aria-label="فتح القائمة الجانبية"
        title="فتح القائمة"
    >
        <i class="fa-solid fa-angles-right" aria-hidden="true"></i>
    </button>
</aside>
