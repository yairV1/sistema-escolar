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
        icon.classList.remove('bi-chevron-left');
        icon.classList.add('bi-chevron-right');
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
            icon.classList.toggle('bi-chevron-left', !isCollapsed);
            icon.classList.toggle('bi-chevron-right', isCollapsed);
            collapseBtn.setAttribute('title', isCollapsed ? 'Expandir menú' : 'Contraer menú');

            if (!isCollapsed) {
                sidebar.querySelectorAll('.nav-parent.flyout-open').forEach((li) => li.classList.remove('flyout-open'));
            }
        });
    }

    // Con el sidebar colapsado, los ítems con hijos (Usuarios, Matrículas...)
    // no tienen accordion visible — este flyout es la única forma de llegar
    // a sus sub-páginas. Ver el comentario en _panel.scss (.flyout-open)
    // sobre por qué la posición se calcula acá y no solo en CSS.
    sidebar.querySelectorAll('.nav-parent').forEach((parentLi) => {
        if (!parentLi.querySelector('.nav-children')) return;

        let closeTimer = null;

        function openFlyout() {
            if (!sidebar.classList.contains('collapsed')) return;
            clearTimeout(closeTimer);
            const rect = parentLi.getBoundingClientRect();
            parentLi.style.setProperty('--flyout-top', rect.top + 'px');
            parentLi.classList.add('flyout-open');
        }

        function closeFlyout() {
            closeTimer = setTimeout(() => parentLi.classList.remove('flyout-open'), 120);
        }

        parentLi.addEventListener('mouseenter', openFlyout);
        parentLi.addEventListener('mouseleave', closeFlyout);
        parentLi.addEventListener('focusin', openFlyout);
        parentLi.addEventListener('focusout', (e) => {
            if (!parentLi.contains(e.relatedTarget)) closeFlyout();
        });
    });

    // ---------- Buscador del menú ----------
    // Filtra por texto visible: un ítem sin hijos se oculta si no matchea;
    // un padre se queda visible si él mismo matchea o si alguno de sus
    // hijos lo hace (y en ese caso se abre el accordion para mostrarlo).
    const searchInput = document.getElementById('sidebarSearch');
    if (searchInput) {
        // Mapa fijo de acentos en vez de normalize('NFD') + rango de marcas
        // diacríticas: evita depender de caracteres Unicode combinados
        // sueltos en el código fuente, cubre lo que aparece en los títulos
        // del menú (español).
        const ACENTOS = { á: 'a', é: 'e', í: 'i', ó: 'o', ú: 'u', ü: 'u', ñ: 'n' };
        const normalize = (str) => (str || '')
            .toLowerCase()
            .split('')
            .map((ch) => ACENTOS[ch] || ch)
            .join('');

        const topItems = sidebar.querySelectorAll('.sidebar-nav > .nav-top-level > li, .sidebar-nav .nav-section > ul > li');

        searchInput.addEventListener('input', () => {
            const query = normalize(searchInput.value.trim());

            topItems.forEach((li) => {
                const isParent = li.classList.contains('nav-parent');
                const ownLabel = li.querySelector(isParent ? '.slink-toggle > span:not(.slink-badge)' : '.slink > span:not(.slink-badge)');
                const ownMatch = query === '' || normalize(ownLabel?.textContent).includes(query);

                let childMatch = false;
                if (isParent) {
                    li.querySelectorAll('.slink-child').forEach((child) => {
                        const label = child.querySelector('span:not(.slink-badge):not(.child-dot)');
                        const matches = query === '' || normalize(label?.textContent).includes(query);
                        child.classList.toggle('search-hidden', query !== '' && !ownMatch && !matches);
                        if (matches) childMatch = true;
                    });

                    if (query !== '' && childMatch && !ownMatch && !li.classList.contains('is-open')) {
                        openAccordion(li, li.querySelector('.nav-children'), li.querySelector('.slink-toggle'));
                    }
                }

                li.classList.toggle('search-hidden', query !== '' && !ownMatch && !childMatch);
            });

            sidebar.querySelectorAll('.nav-section').forEach((section) => {
                const anyVisible = Array.from(section.querySelectorAll(':scope > ul > li')).some((li) => !li.classList.contains('search-hidden'));
                section.classList.toggle('search-hidden', query !== '' && !anyVisible);
            });
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

    const userMenuToggle = document.getElementById('userMenuToggle');
    const userMenuDropup = document.getElementById('userMenuDropup');

    function closeUserMenu() {
        userMenuDropup?.classList.remove('open');
        userMenuToggle?.setAttribute('aria-expanded', 'false');
    }

    userMenuToggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        // La campanita de notificaciones también usa stopPropagation() en su
        // propio toggle, así que el listener "cerrar al hacer clic afuera"
        // de más abajo nunca la ve — sin este cierre explícito, los dos
        // paneles pueden quedar abiertos y superpuestos a la vez.
        document.getElementById('campanitaPanel')?.classList.remove('open');
        document.getElementById('campanitaToggle')?.setAttribute('aria-expanded', 'false');

        const isOpen = userMenuDropup.classList.toggle('open');
        userMenuToggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', (e) => {
        if (userMenuDropup?.classList.contains('open')
            && !e.target.closest('#userMenuToggle')
            && !e.target.closest('#userMenuDropup')) {
            closeUserMenu();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (sidebar.classList.contains('mobile-open')) closeMobileSidebar();
        if (userMenuDropup?.classList.contains('open')) closeUserMenu();
        if (document.activeElement === searchInput && searchInput.value) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    });

    window.SidebarControls = {
        open: openMobileSidebar,
        close: closeMobileSidebar,
        toggle: () => (sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar()),
    };
}
