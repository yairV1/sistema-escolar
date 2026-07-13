/* =============================================
   COLEGIO SAN CRISTÓBAL — REPORTES JS
   Tabs · Filtros · Modal · Descarga · Selección
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─────────────────────────────────────
     1. TABS
  ───────────────────────────────────────── */
  const tabs    = document.querySelectorAll('.rt-tab');
  const panels  = document.querySelectorAll('.rep-panel');
  const slider  = document.getElementById('rtSlider');

  function moveSlider(tab) {
    slider.style.left  = tab.offsetLeft + 'px';
    slider.style.width = tab.offsetWidth + 'px';
  }

  const firstTab = document.querySelector('.rt-tab.active');
  if (firstTab) moveSlider(firstTab);

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      const target = document.getElementById(`panel-${tab.dataset.tab}`);
      if (target) target.classList.add('active');
      moveSlider(tab);
    });
  });

  window.addEventListener('resize', () => {
    const cur = document.querySelector('.rt-tab.active');
    if (cur) moveSlider(cur);
  });

  /* ─────────────────────────────────────
     2. SCROLL REVEAL
  ───────────────────────────────────────── */
  const revObs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add('visible'), i * 60);
        revObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.07 });
  document.querySelectorAll('.reveal').forEach(el => revObs.observe(el));

  /* ─────────────────────────────────────
     3. ANIMACIÓN BARRAS DE NOTAS
  ───────────────────────────────────────── */
  const fills = document.querySelectorAll('.bm-fill');
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
     4. FILTROS
  ───────────────────────────────────────── */
  const filBuscar = document.getElementById('filBuscar');
  const rfClear   = document.getElementById('rfClear');
  const filGrado  = document.getElementById('filGrado');
  const cards     = document.querySelectorAll('.bol-card');

  filBuscar.addEventListener('input', () => {
    rfClear.style.display = filBuscar.value ? 'flex' : 'none';
    filtrarBoletines();
  });

  rfClear.addEventListener('click', () => {
    filBuscar.value = '';
    rfClear.style.display = 'none';
    filtrarBoletines();
  });

  document.getElementById('btnFiltrar')?.addEventListener('click', filtrarBoletines);
  document.getElementById('btnReset')?.addEventListener('click', () => {
    filBuscar.value = '';
    filGrado.value  = '';
    document.getElementById('filPeriodo').value = '1';
    rfClear.style.display = 'none';
    filtrarBoletines();
  });

  function filtrarBoletines() {
    const q     = filBuscar.value.toLowerCase().trim();
    const grado = filGrado.value;
    let visible = 0;

    cards.forEach(card => {
      const texto     = card.textContent.toLowerCase();
      const matchQ    = !q     || texto.includes(q);
      const matchG    = !grado || card.dataset.grado === grado;
      const show = matchQ && matchG;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    showToast(`Mostrando ${visible} boletín${visible !== 1 ? 'es' : ''}`, 'info');
  }

  /* ─────────────────────────────────────
     5. CHECKBOXES INDIVIDUALES + BULK
  ───────────────────────────────────────── */
  const checkAllBol   = document.getElementById('checkAllBol');
  const rbActions     = document.getElementById('rbActions');
  const rbSelected    = document.getElementById('rbSelectedCount');
  const selectedNum   = document.getElementById('selectedNum');
  const bolCheckboxes = document.querySelectorAll('.bol-checkbox');

  function updateBulkUI() {
    const checked = [...bolCheckboxes].filter(c => c.checked && c.closest('.bol-card').style.display !== 'none');
    const count   = checked.length;

    selectedNum.textContent      = count;
    rbSelected.style.display     = count > 0 ? '' : 'none';
    rbActions.style.display      = count > 0 ? 'flex' : 'none';
    checkAllBol.checked          = count > 0 && count === [...bolCheckboxes].filter(c => c.closest('.bol-card').style.display !== 'none').length;
    checkAllBol.indeterminate    = count > 0 && !checkAllBol.checked;

    // Resaltar cards seleccionadas
    bolCheckboxes.forEach(cb => {
      cb.closest('.bol-card')?.classList.toggle('selected', cb.checked);
    });
  }

  checkAllBol.addEventListener('change', () => {
    bolCheckboxes.forEach(cb => {
      if (cb.closest('.bol-card').style.display !== 'none') {
        cb.checked = checkAllBol.checked;
      }
    });
    updateBulkUI();
  });

  bolCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkUI));

  /* ─────────────────────────────────────
     6. ACCIONES MASIVAS
  ───────────────────────────────────────── */
  document.getElementById('btnDescMasivo')?.addEventListener('click', () => {
    const count = [...bolCheckboxes].filter(c => c.checked).length;
    showToast(`Preparando ${count} boletín${count !== 1 ? 'es' : ''} para descarga…`, 'success');
    simulateProgress('Descargando boletines…');
  });

  document.getElementById('btnEnvMasivo')?.addEventListener('click', () => {
    const count = [...bolCheckboxes].filter(c => c.checked).length;
    showToast(`Enviando ${count} boletín${count !== 1 ? 'es' : ''} por correo…`, 'info');
  });

  document.getElementById('btnImpMasivo')?.addEventListener('click', () => {
    showToast('Preparando impresión masiva…', 'info');
    setTimeout(() => window.print(), 500);
  });

  document.getElementById('btnGenMasivo')?.addEventListener('click', () => {
    showToast('Generando boletines para todo el grado…', 'success');
    simulateProgress('Generando PDFs…');
  });

  document.getElementById('btnNuevoReporte')?.addEventListener('click', () => {
    showToast('Módulo de reportes personalizados próximamente.', 'info');
  });

  /* ─────────────────────────────────────
     7. BOTONES INDIVIDUALES EN CARDS
  ───────────────────────────────────────── */

  // Ver boletín → abrir modal
  document.querySelectorAll('.bol-btn-ver').forEach(btn => {
    btn.addEventListener('click', () => {
      const nombre = btn.dataset.estudiante || 'Estudiante';
      const codigo = btn.dataset.codigo     || '—';
      openModal(nombre, codigo);
    });
  });

  // Descargar
  document.querySelectorAll('.bol-btn-desc').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const nombre = btn.dataset.nombre || 'Boletin';
      showToast(`Descargando boletín de ${nombre.replace('_',' ')}…`, 'success');
      simulateProgress(`Generando PDF de ${nombre.replace('_',' ')}…`);
    });
  });

  // Enviar por correo
  document.querySelectorAll('.bol-btn-env').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const email = btn.dataset.email || 'correo@sancristobal.edu.co';
      showToast(`Enviado a ${email}`, 'info');
    });
  });

  /* ─────────────────────────────────────
     8. MODAL BOLETÍN INDIVIDUAL
  ───────────────────────────────────────── */
  const modalBoletin = document.getElementById('modalBoletin');
  const mbClose      = document.getElementById('mbClose');
  const mbNombre     = document.getElementById('mbNombre');
  const mbCodigo     = document.getElementById('mbCodigo');
  const mbFecha      = document.getElementById('mbFecha');
  const mbFechaGen   = document.getElementById('mbFechaGen');

  function openModal(nombre, codigo) {
    // Actualizar datos en el boletín
    if (mbNombre)   mbNombre.textContent   = nombre;
    if (mbCodigo)   mbCodigo.textContent   = codigo;

    const hoy = new Date().toLocaleDateString('es-CO', { day: 'numeric', month: 'long', year: 'numeric' });
    if (mbFecha)    mbFecha.textContent    = hoy;
    if (mbFechaGen) mbFechaGen.textContent = hoy;

    modalBoletin.classList.add('open');
    document.body.style.overflow = 'hidden';

    // Animar barra de asistencia en el modal
    setTimeout(() => {
      const fill = document.querySelector('.mb-asist-fill');
      if (fill) fill.style.width = '96%';
    }, 300);
  }

  function closeModal() {
    modalBoletin.classList.remove('open');
    document.body.style.overflow = '';
  }

  mbClose?.addEventListener('click', closeModal);
  modalBoletin?.addEventListener('click', e => { if (e.target === modalBoletin) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  // Botones dentro del modal
  document.getElementById('mbPrint')?.addEventListener('click', () => {
    showToast('Enviando a impresora…', 'info');
    setTimeout(() => window.print(), 400);
  });

  document.getElementById('mbDownload')?.addEventListener('click', () => {
    showToast('Generando PDF del boletín…', 'success');
    simulateProgress('Creando PDF…');
  });

  document.getElementById('mbSend')?.addEventListener('click', () => {
    const nombre = mbNombre?.textContent || 'estudiante';
    showToast(`Boletín enviado al correo de ${nombre}`, 'info');
  });

  /* ─────────────────────────────────────
     9. PROGRESS TOAST (simulado)
  ───────────────────────────────────────── */
  function simulateProgress(label) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'lt-toast info';
    toast.style.flexDirection = 'column';
    toast.style.gap = '8px';
    toast.style.alignItems = 'flex-start';
    toast.innerHTML = `
      <div style="display:flex;align-items:center;gap:8px;">
        <i class="fas fa-spinner fa-spin"></i>
        <span>${label}</span>
      </div>
      <div style="width:100%;height:4px;background:rgba(49,130,206,0.2);border-radius:4px;overflow:hidden;">
        <div id="progBar" style="height:100%;width:0%;background:#3182ce;border-radius:4px;transition:width 0.1s linear;"></div>
      </div>
    `;
    container.appendChild(toast);

    let pct = 0;
    const progBar = toast.querySelector('#progBar');
    const timer = setInterval(() => {
      pct += Math.random() * 18;
      if (pct >= 100) { pct = 100; clearInterval(timer); }
      progBar.style.width = pct + '%';
    }, 120);

    setTimeout(() => {
      toast.style.opacity   = '0';
      toast.style.transform = 'translateX(20px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
      showToast('¡Listo! Archivo generado correctamente.', 'success');
    }, 2200);
  }

  /* ─────────────────────────────────────
     10. TOAST
  ───────────────────────────────────────── */
  window.showToast = function(msg, type = 'info') {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'fa-check-circle', info: 'fa-info-circle', error: 'fa-exclamation-circle' };
    const toast = document.createElement('div');
    toast.className = `lt-toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity   = '0';
      toast.style.transform = 'translateX(20px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  };

  /* ─────────────────────────────────────
     11. CTRL+K para enfocar búsqueda
  ───────────────────────────────────────── */
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      filBuscar.focus();
    }
  });

});