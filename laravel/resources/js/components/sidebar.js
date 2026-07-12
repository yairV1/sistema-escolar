/**
 * Sidebar del panel: accordion, colapso (persistido en localStorage),
 * overlay/responsive en móvil. Port 1:1 del comportamiento legacy
 * (public/assets/layouts/admin/js/Sidebar.js).
 */
export function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('sidebarCollapse');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    if (collapseBtn && localStorage.getItem('sidebar-collapsed') === 'true') {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
        const icon = collapseBtn.querySelector('i');
        icon.classList.remove('fa-chevron-left');
        icon.classList.add('fa-chevron-right');
        collapseBtn.setAttribute('title', 'Expandir menú');
    }

    sidebar.querySelectorAll('.slink-toggle').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const parentLi = toggle.closest('.nav-parent');
            const childrenList = parentLi.querySelector('.nav-children');
            const isOpen = parentLi.classList.contains('is-open');
            isOpen ? closeAccordion(parentLi, childrenList, toggle) : openAccordion(parentLi, childrenList, toggle);
        });

        toggle.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' && toggle.closest('.nav-parent').classList.contains('is-open')) {
                const firstChild = toggle.closest('.nav-parent').querySelector('.slink-child');
                if (firstChild) {
                    e.preventDefault();
                    firstChild.focus();
                }
            }
        });
    });

    function openAccordion(parentLi, childrenList, toggle) {
        parentLi.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        childrenList.style.display = 'block';

        const height = childrenList.scrollHeight;
        childrenList.style.maxHeight = '0px';
        requestAnimationFrame(() => {
            childrenList.style.transition = 'max-height 220ms ease';
            childrenList.style.maxHeight = height + 'px';
        });

        setTimeout(() => {
            childrenList.style.maxHeight = '';
            childrenList.style.transition = '';
        }, 240);
    }

    function closeAccordion(parentLi, childrenList, toggle) {
        const height = childrenList.scrollHeight;
        childrenList.style.maxHeight = height + 'px';

        requestAnimationFrame(() => {
            childrenList.style.transition = 'max-height 220ms ease';
            childrenList.style.maxHeight = '0px';
        });

        setTimeout(() => {
            parentLi.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            childrenList.style.display = 'none';
            childrenList.style.transition = '';
        }, 220);
    }

    if (collapseBtn) {
        collapseBtn.addEventListener('click', () => {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed', isCollapsed);
            localStorage.setItem('sidebar-collapsed', isCollapsed);

            const icon = collapseBtn.querySelector('i');
            icon.classList.toggle('fa-chevron-left', !isCollapsed);
            icon.classList.toggle('fa-chevron-right', isCollapsed);
            collapseBtn.setAttribute('title', isCollapsed ? 'Expandir menú' : 'Contraer menú');
        });
    }

    const mobileBreakpoint = window.matchMedia('(max-width: 1024px)');

    function openMobileSidebar() {
        sidebar.classList.add('mobile-open');
        overlay?.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        overlay?.classList.remove('visible');
        document.body.style.overflow = '';
    }

    const topbarMenuBtn = document.getElementById('topbarMenu');
    topbarMenuBtn?.addEventListener('click', () => {
        if (mobileBreakpoint.matches) {
            window.SidebarControls.toggle();
        } else {
            collapseBtn?.click();
        }
    });

    overlay?.addEventListener('click', closeMobileSidebar);

    sidebar.addEventListener('click', (e) => {
        if (mobileBreakpoint.matches && e.target.closest('.slink:not(.slink-toggle)')) {
            closeMobileSidebar();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
            closeMobileSidebar();
        }
    });

    window.SidebarControls = {
        open: openMobileSidebar,
        close: closeMobileSidebar,
        toggle: () => (sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar()),
    };
}
