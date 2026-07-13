/* =============================================
   COLEGIO SAN CRISTÓBAL — DASHBOARD JS
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─────────────────────────────────────────────
     1. NAVBAR — scroll + hamburger + dropdown + búsqueda
  ───────────────────────────────────────────── */
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

  // Sombra dinámica
  window.addEventListener('scroll', () => {
    const s = window.scrollY > 10;
    navbar.style.boxShadow      = s ? '0 4px 24px rgba(30,84,54,0.14)' : '0 2px 20px rgba(30,84,54,0.10)';
    navbar.style.borderBottomColor = s ? '#c8d8c8' : '#e2e8e2';
  }, { passive: true });

  // Animación de entrada navbar
  navbar.style.transform  = 'translateY(-100%)';
  navbar.style.transition = 'transform 0.4s cubic-bezier(0.4,0,0.2,1)';
  requestAnimationFrame(() => setTimeout(() => { navbar.style.transform = 'translateY(0)'; }, 80));

  // Hamburger ↔ Drawer
  const openDrawer  = () => { drawer.classList.add('open');    hamburger.classList.add('active');    overlay.classList.add('visible');    document.body.style.overflow = 'hidden'; };
  const closeDrawer = () => { drawer.classList.remove('open'); hamburger.classList.remove('active'); overlay.classList.remove('visible'); document.body.style.overflow = '';       };

  hamburger.addEventListener('click', () => drawer.classList.contains('open') ? closeDrawer() : openDrawer());
  drawerClose.addEventListener('click', closeDrawer);
  document.querySelectorAll('.drawer-link').forEach(l => l.addEventListener('click', closeDrawer));

  // Dropdown perfil
  const openDropdown  = () => { dropdown.classList.add('open');    chevron.classList.add('open');    avatarBtn.setAttribute('aria-expanded','true');  overlay.classList.add('visible'); };
  const closeDropdown = () => { dropdown.classList.remove('open'); chevron.classList.remove('open'); avatarBtn.setAttribute('aria-expanded','false'); overlay.classList.remove('visible'); };

  avatarBtn?.addEventListener('click', e => { e.stopPropagation(); dropdown.classList.contains('open') ? closeDropdown() : openDropdown(); });
  document.addEventListener('click', e => { if (!dropdown?.contains(e.target) && !avatarBtn?.contains(e.target)) closeDropdown(); });

  // Búsqueda
  const openSearchBar  = () => { searchBarWrap.classList.add('open');    setTimeout(() => searchInput.focus(), 150); searchBtn.style.background = 'var(--verde-claro)'; searchBtn.style.color = 'var(--verde-principal)'; };
  const closeSearchBar = () => { searchBarWrap.classList.remove('open'); searchInput.value = ''; searchBtn.style.background = ''; searchBtn.style.color = ''; };

  searchBtn.addEventListener('click', e => { e.stopPropagation(); searchBarWrap.classList.contains('open') ? closeSearchBar() : openSearchBar(); });
  searchClose.addEventListener('click', closeSearchBar);
  searchInput.addEventListener('keydown', e => { if (e.key === 'Enter' && searchInput.value.trim()) closeSearchBar(); });

  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openSearchBar(); }
    if (e.key === 'Escape') { closeDropdown(); closeSearchBar(); closeDrawer(); }
  });

  overlay.addEventListener('click', () => { closeDropdown(); closeDrawer(); closeSearchBar(); });

  // Links activos
  document.querySelectorAll('.dash-link').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      document.querySelectorAll('.dash-link').forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    });
  });

  // Cerrar sesión
  const handleLogout = () => {
    if (confirm('¿Estás seguro que deseas cerrar sesión?')) {
      document.body.style.opacity    = '0';
      document.body.style.transition = 'opacity 0.4s ease';
      setTimeout(() => { window.location.href = 'login.html'; }, 400);
    }
  };
  btnLogout?.addEventListener('click', handleLogout);
  drawerLogout?.addEventListener('click', handleLogout);

  /* ─────────────────────────────────────────────
     2. FECHA Y HORA EN TIEMPO REAL
  ───────────────────────────────────────────── */
  const dias   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
  const meses  = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

  function updateClock() {
    const now  = new Date();
    const dia  = dias[now.getDay()];
    const d    = now.getDate();
    const mes  = meses[now.getMonth()];
    const year = now.getFullYear();
    let h      = now.getHours();
    const m    = String(now.getMinutes()).padStart(2,'0');
    const ampm = h >= 12 ? 'pm' : 'am';
    h = h % 12 || 12;

    const fechaEl = document.getElementById('fechaHoy');
    const horaEl  = document.getElementById('horaActual');
    if (fechaEl) fechaEl.textContent = `${dia}, ${d} de ${mes} de ${year}`;
    if (horaEl)  horaEl.textContent  = `${h}:${m} ${ampm}`;
  }
  updateClock();
  setInterval(updateClock, 1000);

  /* ─────────────────────────────────────────────
     3. SCROLL REVEAL — animación de entrada
  ───────────────────────────────────────────── */
  const revealEls = document.querySelectorAll('.reveal');

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        const delay = parseInt(entry.target.dataset.delay || 0);
        setTimeout(() => entry.target.classList.add('visible'), delay);
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08 });

  revealEls.forEach((el, i) => {
    el.dataset.delay = i * 80;
    revealObserver.observe(el);
  });

  /* ─────────────────────────────────────────────
     4. BARRAS KPI — animación al entrar en vista
  ───────────────────────────────────────────── */
  const barFills = document.querySelectorAll('.kpi-bar-fill');

  // Guarda el width target y lo resetea a 0 para animar
  barFills.forEach(bar => {
    const target = bar.style.width;
    bar.style.width = '0%';
    bar.dataset.target = target;
  });

  const barObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.style.width = entry.target.dataset.target;
        }, 300);
        barObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  barFills.forEach(b => barObserver.observe(b));

  /* ─────────────────────────────────────────────
     5. CONTADOR ANIMADO en KPI values
  ───────────────────────────────────────────── */
  function animateCount(el) {
    const target   = parseFloat(el.dataset.count);
    const decimals = parseInt(el.dataset.decimals || 0);
    const suffix   = el.dataset.suffix || '';
    const duration = 1400;
    const step     = target / (duration / 16);
    let current    = 0;

    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toFixed(decimals) + suffix;
      if (current >= target) clearInterval(timer);
    }, 16);
  }

  const kpiObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const countEl = entry.target.querySelector('[data-count]');
        if (countEl) animateCount(countEl);
        kpiObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.kpi-card').forEach(card => kpiObserver.observe(card));


  /* ─────────────────────────────────────────────
     8. TAREAS — marcar como entregada
  ───────────────────────────────────────────── */
  document.querySelectorAll('.tarea-check').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.tarea-item');
      const done = btn.classList.toggle('done');

      if (done) {
        item.style.opacity   = '0.5';
        item.style.transform = 'scale(0.98)';
        item.style.filter    = 'grayscale(0.5)';
        btn.title = 'Marcar como pendiente';

        // Actualizar badge
        const currentBadge = document.getElementById('badgeTareas');
        if (currentBadge) {
          const count = Math.max(0, parseInt(currentBadge.textContent) - 1);
          currentBadge.textContent = count;
          if (count === 0) currentBadge.style.display = 'none';
        }
      } else {
        item.style.opacity   = '';
        item.style.transform = '';
        item.style.filter    = '';
        btn.title = 'Marcar como entregada';

        const currentBadge = document.getElementById('badgeTareas');
        if (currentBadge) {
          currentBadge.style.display = '';
          currentBadge.textContent = parseInt(currentBadge.textContent || 0) + 1;
        }
      }
    });
  });

  /* ─────────────────────────────────────────────
     9. COMUNICADOS — marcar como leído al hacer click
  ───────────────────────────────────────────── */
  document.querySelectorAll('.comunic-item.unread').forEach(item => {
    item.addEventListener('click', () => {
      if (!item.classList.contains('unread')) return;
      item.classList.remove('unread');
      item.style.transition = 'background 0.4s ease, border-color 0.4s ease';

      const badge = document.getElementById('badgeComunicados');
      if (badge) {
        const count = Math.max(0, parseInt(badge.textContent) - 1);
        badge.textContent = count;
        if (count === 0) badge.style.display = 'none';
      }
    });
  });

  /* ─────────────────────────────────────────────
     10. LEYENDA RESPONSIVE — tooltip en materias
  ───────────────────────────────────────────── */
  document.querySelectorAll('.materia-nota').forEach(el => {
    const val = parseFloat(el.textContent);
    let nivel = '';
    if (val >= 4.6) nivel = 'Excelente';
    else if (val >= 4.0) nivel = 'Bueno';
    else if (val >= 3.5) nivel = 'Aceptable';
    else nivel = 'Bajo';
    el.title = `${val} — ${nivel}`;
  });

  /* ─────────────────────────────────────────────
     11. FUNCIÓN GLOBAL — actualizar badges
         (para usar desde otros módulos)
  ───────────────────────────────────────────── */
  window.updateNavBadge = function(page, count) {
    const map = { boletin: 'badgeBoletin', tareas: 'badgeTareas', comunicados: 'badgeComunicados' };
    const el  = document.getElementById(map[page]);
    if (!el) return;
    el.style.display = count <= 0 ? 'none' : '';
    if (count > 0) el.textContent = count > 99 ? '99+' : count;
  };

});