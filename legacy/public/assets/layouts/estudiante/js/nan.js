/* =============================================
   COLEGIO SAN CRISTÓBAL — NAVBAR DASHBOARD JS
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ── REFERENCIAS ── */
  const navbar        = document.getElementById('dashNavbar');
  const hamburger     = document.getElementById('dashHamburger');
  const drawer        = document.getElementById('mobileDrawer');
  const drawerClose   = document.getElementById('drawerClose');
  const overlay       = document.getElementById('dashOverlay');
  const avatarBtn     = document.getElementById('avatarBtn');
  const dropdown      = document.getElementById('profileDropdown');
  const chevron       = document.getElementById('avatarChevron');
  const searchBtn     = document.getElementById('searchBtn');
  const searchBarWrap = document.getElementById('searchBarWrap');
  const searchClose   = document.getElementById('searchClose');
  const searchInput   = document.getElementById('searchInput');
  const btnLogout     = document.getElementById('btnLogout');
  const drawerLogout  = document.getElementById('drawerLogout');
  const dashLinks     = document.querySelectorAll('.dash-link');
  const drawerLinks   = document.querySelectorAll('.drawer-link');

  /* ==========================================
     1. SOMBRA AL HACER SCROLL
     ========================================== */
  window.addEventListener('scroll', () => {
    const scrolled = window.scrollY > 10;
    navbar.style.boxShadow = scrolled
      ? '0 4px 24px rgba(30,84,54,0.14)'
      : '0 2px 20px rgba(30,84,54,0.10)';
    navbar.style.borderBottomColor = scrolled ? '#c8d8c8' : '#e2e8e2';
  }, { passive: true });

  /* ==========================================
     2. HAMBURGER → DRAWER MÓVIL
     ========================================== */
  function openDrawer() {
    drawer.classList.add('open');
    hamburger.classList.add('active');
    overlay.classList.add('visible');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    drawer.classList.remove('open');
    hamburger.classList.remove('active');
    overlay.classList.remove('visible');
    document.body.style.overflow = '';
  }

  hamburger.addEventListener('click', () => {
    drawer.classList.contains('open') ? closeDrawer() : openDrawer();
  });

  drawerClose.addEventListener('click', closeDrawer);

  // Cerrar drawer al hacer clic en un link
  drawerLinks.forEach(link => {
    link.addEventListener('click', () => {
      closeDrawer();
      // Sincronizar link activo
      drawerLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    });
  });

  /* ==========================================
     3. DROPDOWN DE PERFIL
     ========================================== */
  function openDropdown() {
    dropdown.classList.add('open');
    chevron.classList.add('open');
    avatarBtn.setAttribute('aria-expanded', 'true');
    overlay.classList.add('visible');
  }

  function closeDropdown() {
    dropdown.classList.remove('open');
    chevron.classList.remove('open');
    avatarBtn.setAttribute('aria-expanded', 'false');
    overlay.classList.remove('visible');
  }

  avatarBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.contains('open') ? closeDropdown() : openDropdown();
  });

  // Cerrar al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target) && !avatarBtn.contains(e.target)) {
      closeDropdown();
    }
  });

  // Cerrar con Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeDropdown();
      closeSearchBar();
      closeDrawer();
    }
  });

  /* ==========================================
     4. OVERLAY → cierra todo
     ========================================== */
  overlay.addEventListener('click', () => {
    closeDropdown();
    closeDrawer();
    closeSearchBar();
  });

  /* ==========================================
     5. BARRA DE BÚSQUEDA
     ========================================== */
  function openSearchBar() {
    searchBarWrap.classList.add('open');
    setTimeout(() => searchInput.focus(), 150);
    searchBtn.style.background = 'var(--verde-claro)';
    searchBtn.style.color      = 'var(--verde-principal)';
  }

  function closeSearchBar() {
    searchBarWrap.classList.remove('open');
    searchInput.value = '';
    searchBtn.style.background = '';
    searchBtn.style.color      = '';
  }

  searchBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    searchBarWrap.classList.contains('open') ? closeSearchBar() : openSearchBar();
  });

  searchClose.addEventListener('click', closeSearchBar);

  // Buscar al presionar Enter (demo)
  searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && searchInput.value.trim()) {
      const query = searchInput.value.trim();
      console.log(`[Búsqueda] → "${query}"`);
      // Aquí conectas con tu lógica de búsqueda real
      closeSearchBar();
    }
  });

  /* ==========================================
     6. LINKS ACTIVOS — resalta el actual
     ========================================== */
  function setActiveLink(page) {
    // Navbar principal
    dashLinks.forEach(link => {
      const isActive = link.dataset.page === page;
      link.classList.toggle('active', isActive);
    });
    // Drawer
    drawerLinks.forEach(link => {
      const isActive = link.getAttribute('href') === `#${page}`;
      link.classList.toggle('active', isActive);
    });
  }

  dashLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const page = link.dataset.page;
      if (page) {
        e.preventDefault();
        setActiveLink(page);
        // Aquí cargarías la página/sección correspondiente
        console.log(`[Navegación] → ${page}`);
        updateDemoBreadcrumb(page);
      }
    });
  });

  /* ==========================================
     7. CERRAR SESIÓN
     ========================================== */
  function handleLogout() {
    const confirmMsg = '¿Estás seguro que deseas cerrar sesión?';
    if (confirm(confirmMsg)) {
      // Animación de salida
      document.body.style.opacity = '0';
      document.body.style.transition = 'opacity 0.4s ease';
      setTimeout(() => {
        window.location.href = 'login.html';
      }, 400);
    }
  }

  btnLogout.addEventListener('click', handleLogout);
  drawerLogout.addEventListener('click', handleLogout);

  /* ==========================================
     8. BADGES — actualizar contadores
     ========================================== */
  // Función pública para actualizar badges desde otras partes del sistema
  window.updateNavBadge = function(page, count) {
    const badgeMap = {
      boletin:      'badgeBoletin',
      tareas:       'badgeTareas',
      comunicados:  'badgeComunicados',
    };
    const id    = badgeMap[page];
    const badge = document.getElementById(id);
    if (!badge) return;

    if (count <= 0) {
      badge.style.display = 'none';
    } else {
      badge.style.display = '';
      badge.textContent   = count > 99 ? '99+' : count;
    }
  };

  /* ==========================================
     9. DEMO — actualizar contenido al navegar
     ========================================== */
  const demoPages = {
    inicio:      { title: 'Buenos días, <span>Juan</span> 👋',   sub: 'Lunes, 28 de marzo de 2025 · Semana 12 del período' },
    materias:    { title: 'Mis <span>Materias</span>',           sub: '8 asignaturas activas · Período 2025-1' },
    horario:     { title: 'Mi <span>Horario</span>',             sub: 'Semana actual · 28 Mar – 1 Abr' },
    boletin:     { title: 'Boletín <span>Académico</span>',      sub: 'Período 2025-1 · Corte 2' },
    tareas:      { title: 'Mis <span>Tareas</span>',             sub: '3 pendientes · 2 esta semana' },
    asistencia:  { title: 'Registro de <span>Asistencia</span>', sub: 'Marzo 2025 · 96% de asistencia' },
    comunicados: { title: '<span>Comunicados</span> institucionales', sub: '2 sin leer · Última actualización hoy' },
  };

  function updateDemoBreadcrumb(page) {
    const info    = demoPages[page] || demoPages.inicio;
    const greeting = document.querySelector('.demo-greeting h1');
    const sub      = document.querySelector('.demo-greeting p');
    if (greeting) greeting.innerHTML = info.title;
    if (sub)      sub.textContent    = info.sub;
  }

  /* ==========================================
     10. ANIMACIÓN DE ENTRADA DE LA NAVBAR
     ========================================== */
  navbar.style.transform   = 'translateY(-100%)';
  navbar.style.transition  = 'transform 0.4s cubic-bezier(0.4,0,0.2,1)';
  requestAnimationFrame(() => {
    setTimeout(() => {
      navbar.style.transform = 'translateY(0)';
    }, 80);
  });

  /* ==========================================
     11. SHORTCUT DE TECLADO — Ctrl+K / ⌘K
         abre la búsqueda
     ========================================== */
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      openSearchBar();
    }
  });

  // Mostrar hint del shortcut en el botón de búsqueda
  searchBtn.title = 'Buscar (Ctrl+K)';

});