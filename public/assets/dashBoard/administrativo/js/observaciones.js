/* =============================================
   COLEGIO SAN CRISTÓBAL — OBSERVACIONES JS
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─────────────────────────────────────
     1. SCROLL REVEAL + KPI ANIMADOS
  ───────────────────────────────────────── */
  const revObs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add('visible'), i * 70);
        revObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.07 });
  document.querySelectorAll('.reveal').forEach(el => revObs.observe(el));

  // Contadores KPI
  const kpiObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCount(entry.target);
        kpiObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.ok-val[data-count]').forEach(el => kpiObs.observe(el));

  function animateCount(el) {
    const target = parseInt(el.dataset.count);
    let cur = 0;
    const step = target / (1200 / 16);
    const t = setInterval(() => {
      cur = Math.min(cur + step, target);
      el.textContent = Math.floor(cur);
      if (cur >= target) clearInterval(t);
    }, 16);
  }

  /* ─────────────────────────────────────
     2. SIDEBAR — selección de estudiante
  ───────────────────────────────────────── */
  const estItems = document.querySelectorAll('.obs-est-item');

  estItems.forEach(item => {
    item.addEventListener('click', () => {
      estItems.forEach(i => i.classList.remove('active'));
      item.classList.add('active');

      // Datos ficticios por estudiante (en producción vendrían del backend)
      const datos = {
        '1': { initials:'JS', bg:'#e8f5ee', color:'#2d7a4f', nombre:'Juan Suárez',   meta:'Grado 11°A · Cód. 2024-EST-0001', total:5, disc:2, pos:2, acad:1, cit:0 },
        '2': { initials:'LM', bg:'#eff6ff', color:'#3182ce', nombre:'Laura Martínez',meta:'Grado 11°A · Cód. 2024-EST-0002', total:3, disc:0, pos:3, acad:0, cit:0 },
        '3': { initials:'DG', bg:'#fff5f5', color:'#e53e3e', nombre:'Diego González',meta:'Grado 8°A  · Cód. 2024-EST-0187', total:9, disc:5, pos:0, acad:2, cit:2 },
        '4': { initials:'SR', bg:'#faf5ff', color:'#6b46c1', nombre:'Sofía Rojas',   meta:'Grado 9°C  · Cód. 2024-EST-0112', total:2, disc:0, pos:1, acad:1, cit:0 },
        '5': { initials:'CP', bg:'#fef3c7', color:'#d97706', nombre:'Carlos Pérez',  meta:'Grado 10°B · Cód. 2024-EST-0058', total:6, disc:3, pos:0, acad:1, cit:2 },
        '6': { initials:'AM', bg:'#e0f2fe', color:'#0891b2', nombre:'Ana Moreno',    meta:'Grado 7°B  · Cód. 2024-EST-0234', total:1, disc:0, pos:1, acad:0, cit:0 },
      };

      const id = item.dataset.id;
      const d  = datos[id];
      if (!d) return;

      // Actualizar header del detalle
      const avatar = document.getElementById('detAvatar');
      if (avatar) {
        avatar.textContent = d.initials;
        avatar.style.background = d.bg;
        avatar.style.color = d.color;
      }
      document.getElementById('detNombre').textContent = d.nombre;
      document.getElementById('detMeta').textContent   = d.meta;

      // Actualizar resumen
      const sums = document.querySelectorAll('.ods-item strong');
      if (sums.length >= 5) {
        sums[0].textContent = d.total;
        sums[1].textContent = d.disc;
        sums[2].textContent = d.pos;
        sums[3].textContent = d.acad;
        sums[4].textContent = d.cit;
      }

      showToast(`Cargando observaciones de ${d.nombre}`, 'info');
    });
  });

  /* ─────────────────────────────────────
     3. BÚSQUEDA + FILTROS DEL SIDEBAR
  ───────────────────────────────────────── */
  const obsSearch  = document.getElementById('obsSearch');
  const obsClear   = document.getElementById('obsClear');
  const obsFilGrado= document.getElementById('obsFilGrado');
  const obsFilTipo = document.getElementById('obsFilTipo');

  function filtrarLista() {
    const q     = obsSearch.value.toLowerCase().trim();
    const grado = obsFilGrado.value;

    obsClear.style.display = q ? 'flex' : 'none';

    estItems.forEach(item => {
      const nombre = item.querySelector('.oei-name')?.textContent.toLowerCase() || '';
      const matchQ = !q     || nombre.includes(q);
      const matchG = !grado || item.dataset.grado === grado;
      item.style.display = matchQ && matchG ? '' : 'none';
    });
  }

  obsSearch.addEventListener('input', filtrarLista);
  obsFilGrado.addEventListener('change', filtrarLista);
  obsClear.addEventListener('click', () => { obsSearch.value = ''; obsClear.style.display = 'none'; filtrarLista(); });

  /* ─────────────────────────────────────
     4. TOGGLE TIMELINE / TABLA
  ───────────────────────────────────────── */
  const btnTimeline  = document.getElementById('btnTimeline');
  const btnTabla     = document.getElementById('btnTabla');
  const timelineView = document.getElementById('timelineView');
  const tablaView    = document.getElementById('tablaView');

  btnTimeline.addEventListener('click', () => {
    btnTimeline.classList.add('active');
    btnTabla.classList.remove('active');
    timelineView.classList.add('active');
    tablaView.classList.remove('active');
  });

  btnTabla.addEventListener('click', () => {
    btnTabla.classList.add('active');
    btnTimeline.classList.remove('active');
    tablaView.classList.add('active');
    timelineView.classList.remove('active');
  });

  /* ─────────────────────────────────────
     5. FILTRO DE TABLA POR TIPO
  ───────────────────────────────────────── */
  const otFilTipo = document.getElementById('otFilTipo');
  if (otFilTipo) {
    otFilTipo.addEventListener('change', () => {
      const tipo = otFilTipo.value;
      document.querySelectorAll('#obsTableBody tr').forEach(row => {
        row.style.display = !tipo || row.dataset.tipo === tipo ? '' : 'none';
      });
    });
  }

  // Exportar tabla
  document.getElementById('btnExportObs')?.addEventListener('click', () => {
    showToast('Exportando observaciones a Excel…', 'success');
  });

  /* ─────────────────────────────────────
     6. ACCIONES EN TABLA
  ───────────────────────────────────────── */
  document.querySelectorAll('.ot-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const row  = btn.closest('tr');
      const tipo = row?.querySelector('.ot-tipo')?.textContent.trim() || 'observación';
      const i    = btn.querySelector('i');

      if (i.classList.contains('fa-eye'))   showToast(`Viendo: ${tipo}`, 'info');
      if (i.classList.contains('fa-pen'))   { showToast(`Editando observación`, 'info'); openModal(); }
      if (i.classList.contains('fa-trash')) {
        if (confirm('¿Eliminar esta observación?')) {
          row.style.opacity   = '0';
          row.style.transition = 'opacity 0.3s ease';
          setTimeout(() => row.remove(), 300);
          showToast('Observación eliminada', 'success');
        }
      }
    });
  });

  /* ─────────────────────────────────────
     7. MENÚ DE OPCIONES EN TIMELINE
  ───────────────────────────────────────── */
  document.querySelectorAll('.tl-menu-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();

      // Cerrar cualquier menú abierto
      document.querySelectorAll('.tl-ctx-menu').forEach(m => m.remove());

      const menu = document.createElement('div');
      menu.className = 'tl-ctx-menu';
      menu.style.cssText = `
        position: absolute; right: 0; top: 32px; z-index: 100;
        background: white; border: 1.5px solid var(--gris-borde);
        border-radius: 10px; box-shadow: 0 8px 28px rgba(0,0,0,0.12);
        padding: 4px; min-width: 150px;
        font-family: 'DM Sans', sans-serif;
        animation: toastIn 0.15s ease;
      `;
      menu.innerHTML = `
        <button class="ctx-item"><i class="fas fa-pen"></i> Editar</button>
        <button class="ctx-item"><i class="fas fa-envelope"></i> Notificar acudiente</button>
        <button class="ctx-item"><i class="fas fa-print"></i> Imprimir</button>
        <button class="ctx-item red"><i class="fas fa-trash"></i> Eliminar</button>
      `;
      menu.querySelectorAll('.ctx-item').forEach(it => {
        it.style.cssText = `
          display: flex; align-items: center; gap: 8px;
          width: 100%; padding: 8px 12px; border: none; background: none;
          font-family: 'DM Sans', sans-serif; font-size: 0.8rem; font-weight: 500;
          color: #4a5568; cursor: pointer; border-radius: 6px; transition: background 0.15s;
          text-align: left;
        `;
        it.querySelector('i').style.cssText = 'width:14px;color:#2d7a4f;font-size:0.78rem;';
        it.addEventListener('mouseenter', () => it.style.background = '#f4f6f4');
        it.addEventListener('mouseleave', () => it.style.background = 'none');
      });

      // Acción "Eliminar" en rojo
      const delBtn = menu.querySelectorAll('.ctx-item')[3];
      delBtn.style.color = '#e53e3e';
      delBtn.querySelector('i').style.color = '#e53e3e';
      delBtn.addEventListener('click', () => {
        const card = btn.closest('.tl-item');
        if (card && confirm('¿Eliminar esta observación?')) {
          card.style.opacity   = '0';
          card.style.transform = 'scale(0.95)';
          card.style.transition = 'all 0.3s ease';
          setTimeout(() => card.remove(), 300);
          showToast('Observación eliminada', 'success');
        }
        menu.remove();
      });

      menu.querySelectorAll('.ctx-item').forEach((it, i) => {
        if (i < 3) {
          it.addEventListener('click', () => {
            const labels = ['Editando observación', 'Notificando al acudiente…', 'Preparando impresión…'];
            showToast(labels[i], 'info');
            menu.remove();
          });
        }
      });

      const wrap = btn.closest('.tl-card-menu') || btn.parentElement;
      wrap.style.position = 'relative';
      wrap.appendChild(menu);

      // Cerrar al hacer click fuera
      setTimeout(() => {
        document.addEventListener('click', function closeMenu() {
          menu.remove();
          document.removeEventListener('click', closeMenu);
        });
      }, 50);
    });
  });

  /* ─────────────────────────────────────
     8. MODAL — Nueva observación
  ───────────────────────────────────────── */
  const modalObs = document.getElementById('modalObs');
  const mobClose = document.getElementById('mobClose');
  const mobCancel= document.getElementById('mobCancel');
  const obsForm  = document.getElementById('obsForm');
  let selectedTipo = null;

  function openModal(preselect = null) {
    if (preselect) {
      // Preseleccionar estudiante activo
      const activo = document.querySelector('.obs-est-item.active');
      if (activo) {
        const sel = document.getElementById('obsEstudiante');
        if (sel) sel.value = activo.dataset.id;
      }
    }

    // Fecha de hoy por defecto
    const today = new Date().toISOString().split('T')[0];
    const fechaEl = document.getElementById('obsFecha');
    if (fechaEl) fechaEl.value = today;

    modalObs.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modalObs.classList.remove('open');
    document.body.style.overflow = '';
    obsForm.reset();
    selectedTipo = null;
    document.querySelectorAll('.mob-tipo-btn').forEach(b => b.classList.remove('active'));
    updateModalIcon(null);
    tags = [];
    renderTags();
    document.getElementById('charCount').textContent = '0';
  }

  document.getElementById('btnNuevaObs')?.addEventListener('click', () => openModal(true));
  document.getElementById('btnAddObs')?.addEventListener('click',   () => openModal(true));
  mobClose?.addEventListener('click', closeModal);
  mobCancel?.addEventListener('click', closeModal);
  modalObs?.addEventListener('click', e => { if (e.target === modalObs) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  // Selector de tipo
  const tipoIcons = {
    academica:     { icon: 'fas fa-book-open',       label: 'Observación Académica' },
    disciplinaria: { icon: 'fas fa-exclamation-circle', label: 'Observación Disciplinaria' },
    asistencia:    { icon: 'fas fa-user-clock',      label: 'Observación de Asistencia' },
    psicosocial:   { icon: 'fas fa-brain',            label: 'Observación Psicosocial' },
    positiva:      { icon: 'fas fa-star',             label: 'Reconocimiento' },
    citacion:      { icon: 'fas fa-users',            label: 'Citación a Acudiente' },
  };

  document.querySelectorAll('.mob-tipo-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.mob-tipo-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      selectedTipo = btn.dataset.tipo;
      updateModalIcon(selectedTipo);

      const errTipo = document.getElementById('err-obsTipo');
      if (errTipo) { errTipo.textContent = ''; errTipo.classList.remove('show'); }
    });
  });

  function updateModalIcon(tipo) {
    const iconWrap = document.getElementById('mobIconWrap');
    const title    = document.getElementById('mobTitle');
    if (!iconWrap) return;

    iconWrap.className = 'mob-icon';
    if (tipo) {
      iconWrap.classList.add(tipo);
      iconWrap.innerHTML = `<i class="${tipoIcons[tipo].icon}"></i>`;
      if (title) title.textContent = tipoIcons[tipo].label;
    } else {
      iconWrap.innerHTML = '<i class="fas fa-clipboard-list"></i>';
      if (title) title.textContent = 'Nueva observación';
    }
  }

  // Contador de caracteres
  const textarea = document.getElementById('obsDescripcion');
  const charCount= document.getElementById('charCount');
  textarea?.addEventListener('input', () => {
    const len = textarea.value.length;
    charCount.textContent = len;
    charCount.style.color = len > 450 ? '#e53e3e' : len > 400 ? '#dd6b20' : 'var(--gris-texto)';
    if (len > 500) textarea.value = textarea.value.slice(0, 500);
  });

  /* ─────────────────────────────────────
     9. TAGS
  ───────────────────────────────────────── */
  let tags = [];
  const tagInput = document.getElementById('mobTagInput');
  const tagsList = document.getElementById('mobTagsList');

  tagInput?.addEventListener('keydown', e => {
    if ((e.key === 'Enter' || e.key === ',') && tagInput.value.trim()) {
      e.preventDefault();
      addTag(tagInput.value.trim());
      tagInput.value = '';
    }
    if (e.key === 'Backspace' && !tagInput.value && tags.length) {
      tags.pop();
      renderTags();
    }
  });

  document.querySelectorAll('.mob-tag-sug').forEach(sug => {
    sug.addEventListener('click', () => addTag(sug.dataset.tag));
  });

  function addTag(val) {
    const clean = val.replace(/,/g,'').trim();
    if (!clean || tags.includes(clean) || tags.length >= 5) return;
    tags.push(clean);
    renderTags();
  }

  function renderTags() {
    if (!tagsList) return;
    tagsList.innerHTML = tags.map(t => `
      <span class="mob-tag-item">
        ${t}
        <span class="mob-tag-remove" data-tag="${t}">×</span>
      </span>
    `).join('');
    tagsList.querySelectorAll('.mob-tag-remove').forEach(r => {
      r.addEventListener('click', () => {
        tags = tags.filter(t => t !== r.dataset.tag);
        renderTags();
      });
    });
  }

  /* ─────────────────────────────────────
     10. SUBMIT DEL FORMULARIO
  ───────────────────────────────────────── */
  obsForm?.addEventListener('submit', async e => {
    e.preventDefault();
    let valid = true;

    // Validar estudiante
    const estVal = document.getElementById('obsEstudiante').value;
    const errEst = document.getElementById('err-obsEstudiante');
    if (!estVal) { showFieldError(errEst, 'Selecciona un estudiante'); valid = false; }
    else clearFieldError(errEst);

    // Validar tipo
    const errTipo = document.getElementById('err-obsTipo');
    if (!selectedTipo) { showFieldError(errTipo, 'Selecciona el tipo de observación'); valid = false; }
    else clearFieldError(errTipo);

    // Validar fecha
    const fechaVal = document.getElementById('obsFecha').value;
    const errFecha = document.getElementById('err-obsFecha');
    if (!fechaVal) { showFieldError(errFecha, 'Selecciona la fecha'); valid = false; }
    else clearFieldError(errFecha);

    // Validar descripción
    const descVal = document.getElementById('obsDescripcion').value.trim();
    const errDesc = document.getElementById('err-obsDescripcion');
    if (!descVal || descVal.length < 10) { showFieldError(errDesc, 'Escribe al menos 10 caracteres'); valid = false; }
    else clearFieldError(errDesc);

    if (!valid) return;

    // Simular guardado
    const btn = document.getElementById('mobSave');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';
    btn.disabled  = true;

    await new Promise(r => setTimeout(r, 1400));

    btn.innerHTML = '<i class="fas fa-save"></i> Guardar observación';
    btn.disabled  = false;

    closeModal();
    showToast('Observación registrada correctamente', 'success');
  });

  function showFieldError(el, msg) { if (!el) return; el.textContent = msg; el.classList.add('show'); }
  function clearFieldError(el)     { if (!el) return; el.textContent = ''; el.classList.remove('show'); }

  /* ─────────────────────────────────────
     11. TOAST
  ───────────────────────────────────────── */
  window.showToast = function(msg, type = 'info') {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'fa-check-circle', info: 'fa-info-circle', error: 'fa-exclamation-circle' };
    const toast = document.createElement('div');
    toast.className = `obs-toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity   = '0';
      toast.style.transform = 'translateX(20px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  };

  /* ─────────────────────────────────────
     12. CTRL+K → buscar estudiante
  ───────────────────────────────────────── */
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      document.getElementById('obsSearch')?.focus();
    }
  });

});