/* =============================================
   COLEGIO SAN CRISTÓBAL — LISTADOS JS
   Tabs · Filtros · Búsqueda · Ordenamiento
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─────────────────────────────────────
     1. TABS — cambio con slider animado
  ───────────────────────────────────────── */
  const tabs      = document.querySelectorAll('.lt-tab');
  const panels    = document.querySelectorAll('.lt-panel');
  const slider    = document.getElementById('tabSlider');
  const btnNuevo  = document.getElementById('btnNuevo');
  const btnNuevoLabel = document.getElementById('btnNuevoLabel');

  function moveSlider(tab) {
    slider.style.left  = tab.offsetLeft + 'px';
    slider.style.width = tab.offsetWidth + 'px';
  }

  // Posición inicial
  const activeTab = document.querySelector('.lt-tab.active');
  if (activeTab) moveSlider(activeTab);

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      // Quitar activos
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));

      // Activar el seleccionado
      tab.classList.add('active');
      const target = document.getElementById(`panel-${tab.dataset.tab}`);
      if (target) target.classList.add('active');

      // Mover slider
      moveSlider(tab);

      // Actualizar botón "Nuevo"
      if (tab.dataset.tab === 'estudiantes') {
        btnNuevoLabel.textContent = 'Nuevo estudiante';
        btnNuevo.href = '#nuevo-estudiante';
      } else {
        btnNuevoLabel.textContent = 'Nuevo docente';
        btnNuevo.href = 'RegistroDocentes';
      }
    });
  });

  // Recalcular slider en resize
  window.addEventListener('resize', () => {
    const current = document.querySelector('.lt-tab.active');
    if (current) moveSlider(current);
  });

  /* ─────────────────────────────────────
     2. SCROLL REVEAL
  ───────────────────────────────────────── */
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('visible'), i * 80);
        revealObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.07 });

  document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

  /* ─────────────────────────────────────
     3. BÚSQUEDA + FILTROS — ESTUDIANTES
  ───────────────────────────────────────── */
  const searchEst       = document.getElementById('searchEst');
  const clearSearchEst  = document.getElementById('clearSearchEst');
  const filtroGrado     = document.getElementById('filtroGrado');
  const filtroEstadoEst = document.getElementById('filtroEstadoEst');
  const filtroJornada   = document.getElementById('filtroJornadaEst');
  const emptyEst        = document.getElementById('emptyEst');
  const rowsEst         = document.querySelectorAll('#bodyEstudiantes .lt-row');

  function filtrarEstudiantes() {
    const q       = searchEst.value.toLowerCase().trim();
    const grado   = filtroGrado.value;
    const estado  = filtroEstadoEst.value;
    const jornada = filtroJornada.value;

    clearSearchEst.style.display = q ? 'flex' : 'none';

    let visible = 0;

    rowsEst.forEach(row => {
      const texto    = row.textContent.toLowerCase();
      const matchQ   = !q || texto.includes(q);
      const matchG   = !grado   || row.dataset.grado   === grado.replace('°','');
      const matchE   = !estado  || row.dataset.estado  === estado;
      const matchJ   = !jornada || row.dataset.jornada === jornada;
      const show = matchQ && matchG && matchE && matchJ;
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    emptyEst.style.display = visible === 0 ? 'flex' : 'none';
  }

  searchEst.addEventListener('input',         filtrarEstudiantes);
  filtroGrado.addEventListener('change',      filtrarEstudiantes);
  filtroEstadoEst.addEventListener('change',  filtrarEstudiantes);
  filtroJornada.addEventListener('change',    filtrarEstudiantes);
  clearSearchEst.addEventListener('click', () => {
    searchEst.value = '';
    filtrarEstudiantes();
    searchEst.focus();
  });

  window.resetFiltrosEst = function() {
    searchEst.value       = '';
    filtroGrado.value     = '';
    filtroEstadoEst.value = '';
    filtroJornada.value   = '';
    filtrarEstudiantes();
  };

  /* ─────────────────────────────────────
     4. BÚSQUEDA + FILTROS — DOCENTES
  ───────────────────────────────────────── */
  const searchDoc       = document.getElementById('searchDoc');
  const clearSearchDoc  = document.getElementById('clearSearchDoc');
  const filtroArea      = document.getElementById('filtroArea');
  const filtroEstadoDoc = document.getElementById('filtroEstadoDoc');
  const filtroContrato  = document.getElementById('filtroContrato');
  const emptyDoc        = document.getElementById('emptyDoc');
  const rowsDoc         = document.querySelectorAll('#bodyDocentes .lt-row');

  function filtrarDocentes() {
    const q        = searchDoc.value.toLowerCase().trim();
    const area     = filtroArea.value;
    const estado   = filtroEstadoDoc.value;
    const contrato = filtroContrato.value;

    clearSearchDoc.style.display = q ? 'flex' : 'none';

    let visible = 0;

    rowsDoc.forEach(row => {
      const texto   = row.textContent.toLowerCase();
      const matchQ  = !q       || texto.includes(q);
      const matchA  = !area    || row.dataset.area     === area;
      const matchE  = !estado  || row.dataset.estado   === estado;
      const matchC  = !contrato|| row.dataset.contrato === contrato;
      const show = matchQ && matchA && matchE && matchC;
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    emptyDoc.style.display = visible === 0 ? 'flex' : 'none';
  }

  searchDoc.addEventListener('input',         filtrarDocentes);
  filtroArea.addEventListener('change',       filtrarDocentes);
  filtroEstadoDoc.addEventListener('change',  filtrarDocentes);
  filtroContrato.addEventListener('change',   filtrarDocentes);
  clearSearchDoc.addEventListener('click', () => {
    searchDoc.value = '';
    filtrarDocentes();
    searchDoc.focus();
  });

  window.resetFiltrosDoc = function() {
    searchDoc.value       = '';
    filtroArea.value      = '';
    filtroEstadoDoc.value = '';
    filtroContrato.value  = '';
    filtrarDocentes();
  };

  /* ─────────────────────────────────────
     5. SELECT ALL — CHECKBOXES
  ───────────────────────────────────────── */
  function setupCheckAll(checkAllId, bodyId) {
    const checkAll = document.getElementById(checkAllId);
    if (!checkAll) return;
    checkAll.addEventListener('change', () => {
      document.querySelectorAll(`#${bodyId} input[type="checkbox"]`).forEach(cb => {
        cb.checked = checkAll.checked;
        cb.closest('.lt-row')?.classList.toggle('selected', checkAll.checked);
      });
    });
    document.querySelectorAll(`#${bodyId} input[type="checkbox"]`).forEach(cb => {
      cb.addEventListener('change', () => {
        cb.closest('.lt-row')?.classList.toggle('selected', cb.checked);
        const all  = document.querySelectorAll(`#${bodyId} .lt-row:not([style*="none"]) input[type="checkbox"]`);
        const chkd = [...all].filter(c => c.checked);
        checkAll.checked       = all.length > 0 && chkd.length === all.length;
        checkAll.indeterminate = chkd.length > 0 && chkd.length < all.length;
      });
    });
  }

  setupCheckAll('checkAllEst', 'bodyEstudiantes');
  setupCheckAll('checkAllDoc', 'bodyDocentes');

  /* ─────────────────────────────────────
     6. ORDENAMIENTO DE COLUMNAS
  ───────────────────────────────────────── */
  function setupSort(tbodyId) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;

    document.querySelectorAll(`#${tbodyId}`).forEach(() => {});

    const headers = tbody.closest('table').querySelectorAll('.th-sort');

    headers.forEach(th => {
      let asc = true;
      th.addEventListener('click', () => {
        headers.forEach(h => h.classList.remove('asc','desc'));
        th.classList.add(asc ? 'asc' : 'desc');

        const col  = th.dataset.col;
        const rows = [...tbody.querySelectorAll('.lt-row')];

        rows.sort((a, b) => {
          const aVal = getCellValue(a, col);
          const bVal = getCellValue(b, col);
          return asc
            ? aVal.localeCompare(bVal, 'es', { numeric: true })
            : bVal.localeCompare(aVal, 'es', { numeric: true });
        });

        rows.forEach(r => tbody.appendChild(r));
        asc = !asc;
      });
    });
  }

  function getCellValue(row, col) {
    const cells = row.querySelectorAll('td');
    const colMap = {
      nombre:   1,
      codigo:   2,
      grado:    3,
      area:     3,
      prom:     4,
      asist:    5,
      contrato: 5,
    };
    const idx = colMap[col];
    return idx !== undefined ? (cells[idx]?.textContent.trim() || '') : '';
  }

  setupSort('bodyEstudiantes');
  setupSort('bodyDocentes');

  /* ─────────────────────────────────────
     7. TOGGLE VISTA TABLA / TARJETAS
        (preparado para extensión futura)
  ───────────────────────────────────────── */
  function setupViewToggle(tableBtnId, cardBtnId, tableId) {
    const tableBtn = document.getElementById(tableBtnId);
    const cardBtn  = document.getElementById(cardBtnId);
    if (!tableBtn || !cardBtn) return;

    cardBtn.addEventListener('click', () => {
      tableBtn.classList.remove('active');
      cardBtn.classList.add('active');
      showToast('Vista de tarjetas próximamente disponible.', 'info');
    });
    tableBtn.addEventListener('click', () => {
      cardBtn.classList.remove('active');
      tableBtn.classList.add('active');
    });
  }

  setupViewToggle('viewTableEst', 'viewCardEst', 'tableViewEst');
  setupViewToggle('viewTableDoc', 'viewCardDoc', 'tableViewDoc');

  /* ─────────────────────────────────────
     8. EXPORTAR
  ───────────────────────────────────────── */
  document.getElementById('btnExport')?.addEventListener('click', () => {
    const activeTab  = document.querySelector('.lt-tab.active')?.dataset.tab;
    const label = activeTab === 'docentes' ? 'docentes' : 'estudiantes';
    showToast(`Exportando listado de ${label} a Excel…`, 'success');
  });

  /* ─────────────────────────────────────
     9. BOTONES DE ACCIÓN EN FILAS
  ───────────────────────────────────────── */
  document.querySelectorAll('.lt-act-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const row  = btn.closest('.lt-row');
      const name = row?.querySelector('.lt-user-name')?.textContent.trim().split(' ⚠')[0] || 'Registro';
      const icon = btn.querySelector('i');

      if (icon.classList.contains('fa-eye')) {
        showToast(`Abriendo perfil de ${name}`, 'info');
      } else if (icon.classList.contains('fa-pen')) {
        showToast(`Editando: ${name}`, 'info');
      } else if (icon.classList.contains('fa-ban')) {
        if (confirm(`¿Desactivar a ${name}?`)) {
          row.style.opacity = '0.4';
          const statusEl = row.querySelector('.lt-status');
          if (statusEl) {
            statusEl.className = 'lt-status inactivo';
            statusEl.textContent = 'Inactivo';
          }
          showToast(`${name} desactivado.`, 'success');
        }
      }
    });
  });

  /* ─────────────────────────────────────
     10. BÚSQUEDA GLOBAL Ctrl+K
  ───────────────────────────────────────── */
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      const activePanel = document.querySelector('.lt-panel.active');
      const searchInput = activePanel?.querySelector('.lt-search');
      searchInput?.focus();
    }
  });

  /* ─────────────────────────────────────
     11. BARRAS DE ASISTENCIA — animación
  ───────────────────────────────────────── */
  const fills = document.querySelectorAll('.lt-asist-fill');
  fills.forEach(f => { f.dataset.w = f.style.width; f.style.width = '0'; });

  const barObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        setTimeout(() => { e.target.style.width = e.target.dataset.w; }, 200);
        barObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.3 });
  fills.forEach(f => barObs.observe(f));

  /* ─────────────────────────────────────
     12. TOAST
  ───────────────────────────────────────── */
  function showToast(msg, type = 'info') {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'fa-check-circle', info: 'fa-info-circle', error: 'fa-exclamation-circle' };
    const toast = document.createElement('div');
    toast.className = `lt-toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i>${msg}`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity   = '0';
      toast.style.transform = 'translateX(20px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

});