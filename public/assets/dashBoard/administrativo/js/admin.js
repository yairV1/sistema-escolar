/* =============================================
   COLEGIO SAN CRISTÓBAL — ADMIN JS
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─────────────────────────────────────
     1. SIDEBAR — colapsar / expandir / móvil
  ───────────────────────────────────────── */
  const sidebar = document.getElementById('sidebar');
  const sidebarCollapse = document.getElementById('sidebarCollapse');
  const topbar = document.getElementById('topbar');
  const adminMain = document.getElementById('adminMain');
  const topbarMenu = document.getElementById('topbarMenu');
  const adminOverlay = document.getElementById('adminOverlay');

  // Colapsar / expandir en escritorio
  sidebarCollapse.addEventListener('click', () => {
    const isCollapsed = sidebar.classList.toggle('collapsed');
    topbar.classList.toggle('sidebar-collapsed', isCollapsed);
    adminMain.classList.toggle('sidebar-collapsed', isCollapsed);
  });

  // Abrir / cerrar en móvil
  const openSidebar = () => {
    sidebar.classList.add('mobile-open');
    adminOverlay.classList.add('visible');
    document.body.style.overflow = 'hidden';
  };
  const closeSidebar = () => {
    sidebar.classList.remove('mobile-open');
    adminOverlay.classList.remove('visible');
    document.body.style.overflow = '';
  };

  topbarMenu.addEventListener('click', () =>
    sidebar.classList.contains('mobile-open') ? closeSidebar() : openSidebar()
  );
  adminOverlay.addEventListener('click', closeSidebar);

  /* ─────────────────────────────────────
     2. LINKS ACTIVOS + breadcrumb
  ───────────────────────────────────────── */
  const slinks = document.querySelectorAll('.slink[data-page]');
  const breadcrumbEl = document.getElementById('breadcrumbPage');

  const pageNames = {
    inicio: 'Inicio',
    RegistroEstudiantes: 'Registro de Estudiantes',
    RegistroDocentes: 'Registro de Docentes',
    Listados: 'Listados',
    Matriculas: 'Matrículas',
    Estadisticas: 'Estadísticas',
    Reportes: 'Reportes y boletines',
    Observaciones: 'Observaciones',
    Comunicados: 'Comunicados',
    EditarLanding: 'Editar Landing Page',
  };

  // Detectar la página actual por la URL real del navegador
  const currentPath = window.location.pathname
    .replace('/colegio/', '')   // quitar subcarpeta
    .replace('/colegio', '')    // por si viene sin barra final
    .replace(/^\//, '')         // quitar barra inicial si quedó
    .replace(/\/$/, '')         // quitar barra final
    || 'inicio';                // si está vacío → es el inicio

  slinks.forEach(link => {
    const page = link.dataset.page;

    // Activar el link que coincide con la URL actual
    if (currentPath.toLowerCase() === page.toLowerCase()) {
      slinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      if (breadcrumbEl) breadcrumbEl.textContent = pageNames[page] || '';
    }

    // Al hacer clic: cerrar sidebar en móvil y dejar que el navegador navegue normal
    link.addEventListener('click', () => {
      if (window.innerWidth <= 768) closeSidebar();
      // Sin e.preventDefault() → el href de PHP funciona correctamente ✅
    });
  });

  /* ─────────────────────────────────────
     3. DROPDOWN PERFIL TOPBAR
  ───────────────────────────────────────── */
  const topbarAvatarBtn = document.getElementById('topbarAvatarBtn');
  const topbarDropdown = document.getElementById('topbarDropdown');
  const topbarChevron = document.getElementById('topbarChevron');

  const openDropdown = () => {
    topbarDropdown.classList.add('open');
    topbarChevron.classList.add('open');
  };
  const closeDropdown = () => {
    topbarDropdown.classList.remove('open');
    topbarChevron.classList.remove('open');
  };

  topbarAvatarBtn?.addEventListener('click', e => {
    e.stopPropagation();
    topbarDropdown.classList.contains('open') ? closeDropdown() : openDropdown();
  });
  document.addEventListener('click', e => {
    if (!topbarDropdown?.contains(e.target) && !topbarAvatarBtn?.contains(e.target)) {
      closeDropdown();
    }
  });

  /* ─────────────────────────────────────
     4. FECHA Y HORA EN TIEMPO REAL
  ───────────────────────────────────────── */
  const DIAS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

  function updateClock() {
    const now = new Date();
    const dia = DIAS[now.getDay()];
    const d = now.getDate();
    const mes = MESES[now.getMonth()];
    const year = now.getFullYear();
    let h = now.getHours();
    const m = String(now.getMinutes()).padStart(2, '0');
    const ampm = h >= 12 ? 'pm' : 'am';
    h = h % 12 || 12;
    const fechaEl = document.getElementById('adminFecha');
    const horaEl = document.getElementById('adminHora');
    if (fechaEl) fechaEl.textContent = `${dia}, ${d} de ${mes} de ${year}`;
    if (horaEl) horaEl.textContent = `${h}:${m} ${ampm}`;
  }
  updateClock();
  setInterval(updateClock, 1000);

  /* ─────────────────────────────────────
     5. SCROLL REVEAL
  ───────────────────────────────────────── */
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const d = parseInt(entry.target.dataset.revealDelay || 0);
        setTimeout(() => entry.target.classList.add('visible'), d);
        revealObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.07 });

  document.querySelectorAll('.reveal').forEach((el, i) => {
    el.dataset.revealDelay = i * 70;
    revealObs.observe(el);
  });

  /* ─────────────────────────────────────
     6. BARRAS KPI — animación entrada
  ───────────────────────────────────────── */
  const barFills = document.querySelectorAll('.akpi-fill');
  barFills.forEach(b => {
    b.dataset.target = b.style.width;
    b.style.width = '0%';
  });

  const barObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        setTimeout(() => { entry.target.style.width = entry.target.dataset.target; }, 250);
        barObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.4 });
  barFills.forEach(b => barObs.observe(b));

  /* ─────────────────────────────────────
     7. CONTADORES ANIMADOS EN KPI
  ───────────────────────────────────────── */
  function animateCount(el) {
    const target = parseFloat(el.dataset.count);
    const decimals = parseInt(el.dataset.decimals || 0);
    const suffix = el.dataset.suffix || '';
    const dur = 1400;
    const step = target / (dur / 16);
    let cur = 0;
    const t = setInterval(() => {
      cur = Math.min(cur + step, target);
      el.textContent = cur.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + suffix;
      if (cur >= target) clearInterval(t);
    }, 16);
  }

  const kpiObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const countEl = entry.target.querySelector('[data-count]');
        if (countEl) animateCount(countEl);
        kpiObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.4 });
  document.querySelectorAll('.akpi-card').forEach(c => kpiObs.observe(c));

  /* ─────────────────────────────────────
     8. GRÁFICA DE BARRAS — promedios por grado
  ───────────────────────────────────────── */
  const gradesData = [
    { grado: 'Prees.', prom: 4.6 },
    { grado: '1°', prom: 4.5 },
    { grado: '2°', prom: 4.4 },
    { grado: '3°', prom: 4.3 },
    { grado: '4°', prom: 4.2 },
    { grado: '5°', prom: 4.1 },
    { grado: '6°', prom: 4.0 },
    { grado: '7°', prom: 3.9 },
    { grado: '8°', prom: 3.8 },
    { grado: '9°', prom: 4.0 },
    { grado: '10°', prom: 4.2 },
    { grado: '11°', prom: 4.3 },
  ];

  const chartBars = document.getElementById('chartBars');
  const chartLabels = document.getElementById('chartLabels');
  const maxProm = 5.0;
  const chartH = 180;

  if (chartBars && chartLabels) {
    gradesData.forEach(({ grado, prom }) => {
      const heightPx = Math.round((prom / maxProm) * (chartH - 24));
      const color = prom >= 4.3 ? '#2d7a4f' : prom >= 4.0 ? '#4a9e6b' : prom >= 3.7 ? '#c8a84b' : '#e53e3e';
      const bgColor = prom >= 4.3 ? '#e8f5ee' : prom >= 4.0 ? '#d4edd9' : prom >= 3.7 ? '#fefce8' : '#fff5f5';
      const borderCol = prom >= 4.3 ? '#d4edd9' : prom >= 4.0 ? '#c8e6d4' : prom >= 3.7 ? '#fde68a' : '#fed7d7';

      const group = document.createElement('div');
      group.className = 'chart-bar-group';

      const bar = document.createElement('div');
      bar.className = 'chart-bar';
      bar.style.cssText = `height:0;background:${bgColor};border-color:${borderCol};`;
      bar.dataset.target = `${heightPx}px`;
      bar.dataset.color = color;
      bar.dataset.bg = bgColor;
      bar.innerHTML = `
        <span class="chart-bar-val" style="color:${color}">${prom.toFixed(1)}</span>
        <span class="chart-tooltip">${grado}: ${prom.toFixed(1)}</span>
      `;

      bar.addEventListener('mouseenter', () => {
        bar.style.background = color;
        bar.querySelector('.chart-bar-val').style.color = 'white';
      });
      bar.addEventListener('mouseleave', () => {
        bar.style.background = bgColor;
        bar.querySelector('.chart-bar-val').style.color = color;
      });

      group.appendChild(bar);
      chartBars.appendChild(group);

      const label = document.createElement('div');
      label.className = 'chart-label';
      label.textContent = grado;
      chartLabels.appendChild(label);
    });

    // Animar barras al entrar en viewport
    const chartObs = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) {
        document.querySelectorAll('.chart-bar').forEach((bar, i) => {
          setTimeout(() => {
            bar.style.height = bar.dataset.target;
            bar.style.transition = `height 0.7s cubic-bezier(0.4,0,0.2,1) ${i * 40}ms`;
          }, 100);
        });
        chartObs.disconnect();
      }
    }, { threshold: 0.3 });
    chartObs.observe(chartBars);
  }

  /* ─────────────────────────────────────
     9. BÚSQUEDA MINI — docentes
  ───────────────────────────────────────── */
  const searchDocente = document.getElementById('searchDocente');
  if (searchDocente) {
    searchDocente.addEventListener('input', () => {
      const q = searchDocente.value.toLowerCase();
      document.querySelectorAll('#docentesList .doc-item').forEach(item => {
        const name = item.querySelector('.doc-name')?.textContent.toLowerCase() || '';
        const info = item.querySelector('.doc-info')?.textContent.toLowerCase() || '';
        item.style.display = (name.includes(q) || info.includes(q)) ? '' : 'none';
      });
    });
  }

  /* ─────────────────────────────────────
     10. BÚSQUEDA MINI — estudiantes
  ───────────────────────────────────────── */
  const searchEstudiante = document.getElementById('searchEstudiante');
  if (searchEstudiante) {
    searchEstudiante.addEventListener('input', () => {
      const q = searchEstudiante.value.toLowerCase();
      document.querySelectorAll('#estudiantesList .est-item').forEach(item => {
        const name = item.querySelector('.est-name')?.textContent.toLowerCase() || '';
        const info = item.querySelector('.est-info')?.textContent.toLowerCase() || '';
        item.style.display = (name.includes(q) || info.includes(q)) ? '' : 'none';
      });
    });
  }

  /* ─────────────────────────────────────
     11. BÚSQUEDA TOPBAR — Ctrl+K
  ───────────────────────────────────────── */
  const adminSearch = document.getElementById('adminSearch');
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      adminSearch?.focus();
    }
    if (e.key === 'Escape') {
      closeDropdown();
      closeSidebar();
    }
  });

  /* ─────────────────────────────────────
     12. MODAL NUEVO COMUNICADO
  ───────────────────────────────────────── */
  const modalComunic = document.getElementById('modalComunic');
  const btnNuevoComunic = document.getElementById('btnNuevoComunic');
  const modalComunicClose = document.getElementById('modalComunicClose');

  const openModal = () => {
    modalComunic.classList.add('open');
    document.body.style.overflow = 'hidden';
  };
  const closeModal = () => {
    modalComunic.classList.remove('open');
    document.body.style.overflow = '';
  };

  btnNuevoComunic?.addEventListener('click', openModal);
  modalComunicClose?.addEventListener('click', closeModal);
  modalComunic?.addEventListener('click', e => {
    if (e.target === modalComunic) closeModal();
  });

  // Botones del modal
  const btnSave = modalComunic?.querySelector('.mf-btn-save');
  const btnSend = modalComunic?.querySelector('.mf-btn-send');

  btnSave?.addEventListener('click', () => {
    showToast('Borrador guardado correctamente', 'success');
    closeModal();
  });
  btnSend?.addEventListener('click', () => {
    showToast('Comunicado enviado a la comunidad', 'success');
    closeModal();
  });

  /* ─────────────────────────────────────
     13. BOTONES DE REPORTES
  ───────────────────────────────────────── */
  document.querySelectorAll('.rep-btn.preview').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.closest('.reporte-card').querySelector('h3').textContent;
      showToast(`Abriendo: ${name}`, 'info');
    });
  });
  document.querySelectorAll('.rep-btn.download').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.closest('.reporte-card').querySelector('h3').textContent;
      showToast(`Exportando: ${name}…`, 'success');
    });
  });

  /* ─────────────────────────────────────
     14. CERRAR SESIÓN
  ───────────────────────────────────────── */
  const handleLogout = () => {
    if (confirm('¿Estás seguro que deseas cerrar sesión?')) {
      document.body.style.opacity = '0';
      document.body.style.transition = 'opacity 0.4s ease';
      setTimeout(() => { window.location.href = 'login.html'; }, 400);
    }
  };
  document.getElementById('sidebarLogout')?.addEventListener('click', e => {
    e.preventDefault();
    handleLogout();
  });
  document.getElementById('topbarLogout')?.addEventListener('click', handleLogout);

  /* ─────────────────────────────────────
     15. TOAST NOTIFICATIONS
  ───────────────────────────────────────── */
  function showToast(msg, type = 'info') {
    const colors = {
      success: { bg: '#e8f5ee', border: '#2d7a4f', text: '#1e5436', icon: 'fas fa-check-circle' },
      info: { bg: '#eff6ff', border: '#3182ce', text: '#2c5282', icon: 'fas fa-info-circle' },
      error: { bg: '#fff5f5', border: '#e53e3e', text: '#c53030', icon: 'fas fa-exclamation-circle' },
    };
    const c = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.style.cssText = `
      position:fixed; bottom:24px; right:24px; z-index:9999;
      background:${c.bg}; border:1.5px solid ${c.border}; color:${c.text};
      padding:12px 18px; border-radius:10px;
      font-family:'DM Sans',sans-serif; font-size:0.85rem; font-weight:600;
      display:flex; align-items:center; gap:10px;
      box-shadow:0 8px 28px rgba(0,0,0,0.12);
      transform:translateY(16px); opacity:0;
      transition:all 0.3s ease; max-width:320px;
    `;
    toast.innerHTML = `<i class="${c.icon}" style="font-size:1rem;flex-shrink:0"></i>${msg}`;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
      toast.style.transform = 'translateY(0)';
      toast.style.opacity = '1';
    });

    setTimeout(() => {
      toast.style.transform = 'translateY(16px)';
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  }

});