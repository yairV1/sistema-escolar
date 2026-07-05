/**
 * =====================================================
 * SIDEBAR — Comportamiento
 * =====================================================
 * Maneja:
 *  - Accordion (apertura/cierre suave de submenús)
 *  - Colapso del sidebar completo (desktop)
 *  - Overlay + apertura lateral (móvil)
 *  - Persistencia de estado en localStorage
 *  - Accesibilidad: aria-expanded, navegación con teclado
 * =====================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('sidebarCollapse');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    /* -------------------------------------------------
       0. RESTAURAR ESTADO GUARDADO
       ------------------------------------------------- */
    if (collapseBtn && localStorage.getItem('sidebar-collapsed') === 'true') {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
        const icon = collapseBtn.querySelector('i');
        icon.classList.remove('fa-chevron-left');
        icon.classList.add('fa-chevron-right');
        collapseBtn.setAttribute('title', 'Expandir menú');
    }

    /* -------------------------------------------------
       1. ACCORDION
       ------------------------------------------------- */
    const toggles = sidebar.querySelectorAll('.slink-toggle');

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const parentLi = toggle.closest('.nav-parent');
            const childrenList = parentLi.querySelector('.nav-children');
            const isOpen = parentLi.classList.contains('is-open');

            if (isOpen) {
                closeAccordion(parentLi, childrenList, toggle);
            } else {
                openAccordion(parentLi, childrenList, toggle);
            }
        });

        // Navegación con teclado: Enter/Space ya disparan click nativamente
        // en <button>, pero añadimos soporte de flechas para mejor UX.
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

        // Animación suave de altura
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

    /* -------------------------------------------------
       2. COLAPSO DEL SIDEBAR (desktop)
       ------------------------------------------------- */
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

    /* -------------------------------------------------
       3. RESPONSIVE / MÓVIL
       ------------------------------------------------- */
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

    // Botón hamburguesa del topbar: en móvil abre/cierra el overlay;
    // en escritorio actúa como atajo del botón de colapso.
    const topbarMenuBtn = document.getElementById('topbarMenu');
    topbarMenuBtn?.addEventListener('click', () => {
        if (mobileBreakpoint.matches) {
            window.SidebarControls.toggle();
        } else {
            collapseBtn?.click();
        }
    });

    overlay?.addEventListener('click', closeMobileSidebar);

    // Cierre automático al hacer click en un link (móvil)
    sidebar.addEventListener('click', (e) => {
        if (mobileBreakpoint.matches && e.target.closest('.slink:not(.slink-toggle)')) {
            closeMobileSidebar();
        }
    });

    // Cierre con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
            closeMobileSidebar();
        }
    });

    // Exponer funciones para el botón hamburguesa del header (fuera de este partial)
    window.SidebarControls = {
        open: openMobileSidebar,
        close: closeMobileSidebar,
        toggle: () => {
            sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar();
        },
    };
});