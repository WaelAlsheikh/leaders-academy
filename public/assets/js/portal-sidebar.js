(function () {
    'use strict';

    var STORAGE_PREFIX = 'leaders.portalSidebar.collapsed.';

    function storageKey(portal) {
        return STORAGE_PREFIX + (portal || 'default');
    }

    function isCollapsedStored(portal) {
        try {
            return localStorage.getItem(storageKey(portal)) === '1';
        } catch (e) {
            return false;
        }
    }

    function setCollapsedStored(portal, collapsed) {
        try {
            localStorage.setItem(storageKey(portal), collapsed ? '1' : '0');
        } catch (e) {
            /* ignore */
        }
    }

    function layoutFor(sidebar) {
        return sidebar.closest('.student-layout');
    }

    function applyCollapsed(sidebar, collapsed) {
        var layout = layoutFor(sidebar);
        var toggle = sidebar.querySelector('[data-sidebar-toggle]');
        var label = sidebar.querySelector('.sidebar-collapse-label');
        var icon = sidebar.querySelector('.sidebar-collapse-icon');

        sidebar.classList.toggle('is-collapsed', collapsed);
        if (layout) {
            layout.classList.toggle('is-sidebar-collapsed', collapsed);
        }

        if (toggle) {
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.setAttribute('title', collapsed ? 'فتح القائمة' : 'طي القائمة');
        }

        if (label) {
            label.textContent = collapsed ? 'فتح' : '';
        }

        if (icon) {
            icon.classList.toggle('fa-angles-left', !collapsed);
            icon.classList.toggle('fa-angles-right', collapsed);
        }
    }

    function bindSidebar(sidebar) {
        var portal = sidebar.getAttribute('data-portal-sidebar') || 'default';
        var collapsed = isCollapsedStored(portal);

        applyCollapsed(sidebar, collapsed);

        sidebar.querySelectorAll('[data-sidebar-toggle], [data-sidebar-expand]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var next = !sidebar.classList.contains('is-collapsed');
                applyCollapsed(sidebar, next);
                setCollapsedStored(portal, next);
            });
        });

        // Keep wheel scrolling inside the sidebar when the pointer is over it,
        // and avoid scrolling the page until the sidebar reaches its edge.
        sidebar.addEventListener(
            'wheel',
            function (e) {
                if (sidebar.classList.contains('is-collapsed')) {
                    return;
                }

                var delta = e.deltaY;
                var max = sidebar.scrollHeight - sidebar.clientHeight;
                if (max <= 0) {
                    return;
                }

                var atTop = sidebar.scrollTop <= 0;
                var atBottom = sidebar.scrollTop >= max - 1;

                if ((delta < 0 && atTop) || (delta > 0 && atBottom)) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
                sidebar.scrollTop += delta;
            },
            { passive: false }
        );
    }

    function init() {
        document.querySelectorAll('[data-portal-sidebar]').forEach(bindSidebar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
