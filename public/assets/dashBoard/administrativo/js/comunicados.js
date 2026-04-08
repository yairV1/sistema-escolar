/* =============================================
   COLEGIO SAN CRISTÓBAL — COMUNICADOS JS
   Listado, filtros, editor modal, preview,
   permisos, borrador, envío con confirmación
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─── Datos de muestra ─── */
  let COMUNICADOS = [
    {
      id:1, tipo:'urgente', asunto:'Cambio de horario — Viernes 29 de marzo',
      mensaje:'<p>Estimada comunidad educativa,</p><p>Les informamos que el día <strong>viernes 29 de marzo</strong> habrá un cambio en el horario de clases debido a una reunión institucional. Las clases iniciarán a las <strong>8:00 a.m.</strong> y finalizarán a la <strong>12:00 m.</strong></p><p>Agradecemos su comprensión y puntualidad.</p>',
      destinatarios:['comunidad'], grados:[], estado:'enviado',
      fecha:'2025-03-29', autor:'Rosa Cardona', lecturas:312,
      permisos:{ visibilidad:'todos', editar:['rector'], enviar:['rector'] }
    },
    {
      id:2, tipo:'evento', asunto:'Festival de Ciencias 2025 — Inscripciones abiertas',
      mensaje:'<p>Nos complace invitarles al <strong>Festival de Ciencias 2025</strong>. Este año el evento se realizará el <strong>15 de abril</strong> en las instalaciones del colegio.</p><p>Los estudiantes interesados pueden inscribirse hasta el 10 de abril con su docente de Ciencias Naturales.</p>',
      destinatarios:['docentes', 'grado'], grados:['9','10','11'], estado:'enviado',
      fecha:'2025-03-28', autor:'Rosa Cardona', lecturas:187,
      permisos:{ visibilidad:'todos', editar:['rector','coord'], enviar:['rector'] }
    },
    {
      id:3, tipo:'informativo', asunto:'Entrega de boletines período 1 — 5 de abril',
      mensaje:'<p>Cordial saludo, señores padres de familia.</p><p>Les comunicamos que la entrega de boletines del <strong>Período 1 de 2025</strong> se realizará el día <strong>sábado 5 de abril</strong> de 8:00 a.m. a 12:00 m. en los salones de clase.</p>',
      destinatarios:['directivos','docentes'], grados:[], estado:'enviado',
      fecha:'2025-03-25', autor:'Rosa Cardona', lecturas:95,
      permisos:{ visibilidad:'todos', editar:['rector'], enviar:['rector','secretaria'] }
    },
    {
      id:4, tipo:'informativo', asunto:'Reunión de padres — Abril 2025',
      mensaje:'<p>Estimados padres de familia,</p><p>Los invitamos a la <strong>Reunión de Padres del Período 1</strong> que se llevará a cabo el próximo <strong>sábado 12 de abril</strong>. La asistencia es obligatoria.</p>',
      destinatarios:['grado'], grados:['6','7','8'], estado:'borrador',
      fecha:'2025-03-22', autor:'Rosa Cardona', lecturas:0,
      permisos:{ visibilidad:'todos', editar:['rector','coord'], enviar:['rector'] }
    },
    {
      id:5, tipo:'academico', asunto:'Cierre de notas período 1 — Docentes',
      mensaje:'<p>Estimados docentes,</p><p>Les recordamos que el <strong>cierre de notas del Período 1</strong> es el próximo <strong>viernes 4 de abril a las 5:00 p.m.</strong> Por favor, asegúrense de tener ingresadas todas las calificaciones en el sistema antes de esta fecha.</p>',
      destinatarios:['docentes'], grados:[], estado:'enviado',
      fecha:'2025-03-20', autor:'Rosa Cardona', lecturas:84,
      permisos:{ visibilidad:'todos', editar:['rector'], enviar:['rector','coord'] }
    },
    {
      id:6, tipo:'urgente', asunto:'Alerta: Suspensión de clases — Mañana',
      mensaje:'<p><strong>COMUNICADO URGENTE</strong></p><p>Por motivos de fuerza mayor, las clases del día de mañana quedan <strong>suspendidas</strong>. Se retoman actividades el siguiente día hábil. Más información será enviada oportunamente.</p>',
      destinatarios:['comunidad'], grados:[], estado:'enviado',
      fecha:'2025-03-15', autor:'Rosa Cardona', lecturas:420,
      permisos:{ visibilidad:'todos', editar:['rector'], enviar:['rector'] }
    },
    {
      id:7, tipo:'evento', asunto:'Día del idioma — Actividades culturales',
      mensaje:'<p>Con motivo del <strong>Día del Idioma</strong>, el 23 de abril realizaremos una jornada de actividades culturales. Los docentes de Español y Humanidades coordinarán las actividades por grado.</p>',
      destinatarios:['docentes','grado'], grados:['PRE','1','2','3','4','5'], estado:'programado',
      fecha:'2025-04-10', autor:'Rosa Cardona', lecturas:0,
      permisos:{ visibilidad:'roles', editar:['rector','coord'], enviar:['rector','coord'] }
    },
    {
      id:8, tipo:'academico', asunto:'Capacitación docente — Plataforma MOODLE',
      mensaje:'<p>Estimados docentes,</p><p>Los invitamos a la capacitación sobre el uso de la <strong>Plataforma MOODLE</strong> que se realizará el <strong>sábado 19 de abril</strong> de 8:00 a.m. a 12:00 m. en la sala de sistemas. Asistencia obligatoria.</p>',
      destinatarios:['docentes'], grados:[], estado:'borrador',
      fecha:'2025-03-18', autor:'Rosa Cardona', lecturas:0,
      permisos:{ visibilidad:'autor', editar:['rector'], enviar:['rector'] }
    },
  ];

  let editandoId  = null;
  let filtered    = [...COMUNICADOS];
  let activeTab   = 'todos';
  let activeKpi   = '';

  /* ─── Referencias DOM ─── */
  const comLista       = document.getElementById('comLista');
  const comEmpty       = document.getElementById('comEmpty');
  const comCount       = document.getElementById('comCount');
  const comSearch      = document.getElementById('comSearch');
  const comSearchClear = document.getElementById('comSearchClear');
  const filtroTipo     = document.getElementById('filtroTipo');
  const filtroEstado   = document.getElementById('filtroEstado');
  const filtroDestinatario = document.getElementById('filtroDestinatario');
  const modalEditor    = document.getElementById('modalEditor');
  const modalConfirm   = document.getElementById('modalConfirm');
  const modalExito     = document.getElementById('modalExito');

  /* ─── Helpers ─── */
  const tipoLabel = { urgente:'Urgente', informativo:'Informativo', evento:'Evento', academico:'Académico' };
  const destLabel = { comunidad:'Toda la comunidad', docentes:'Docentes', directivos:'Directivos', grado:'Por grado' };
  const estadoLabel = { enviado:'Enviado', borrador:'Borrador', programado:'Programado' };

  function formatFecha(str) {
    if (!str) return '—';
    const [y,m,d] = str.split('-');
    const ms = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return `${parseInt(d)} ${ms[parseInt(m)-1]} ${y}`;
  }

  function showToast(msg, type='success') {
    const c = type==='success'
      ? {bg:'#e8f5ee',border:'#2d7a4f',text:'#1e5436',icon:'fas fa-check-circle'}
      : type==='error'
      ? {bg:'#fff5f5',border:'#e53e3e',text:'#c53030',icon:'fas fa-exclamation-circle'}
      : {bg:'#eff6ff',border:'#3182ce',text:'#2c5282',icon:'fas fa-info-circle'};
    const t = document.createElement('div');
    t.style.cssText=`position:fixed;bottom:24px;right:24px;z-index:9999;background:${c.bg};border:1.5px solid ${c.border};color:${c.text};padding:12px 18px;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 28px rgba(0,0,0,.12);transform:translateY(16px);opacity:0;transition:all .3s ease;max-width:320px;`;
    t.innerHTML=`<i class="${c.icon}" style="font-size:1rem;flex-shrink:0"></i>${msg}`;
    document.body.appendChild(t);
    requestAnimationFrame(()=>{ t.style.transform='translateY(0)'; t.style.opacity='1'; });
    setTimeout(()=>{ t.style.transform='translateY(16px)'; t.style.opacity='0'; setTimeout(()=>t.remove(),300); },3200);
  }

  function stripHtml(html) {
    const d = document.createElement('div');
    d.innerHTML = html;
    return d.textContent || d.innerText || '';
  }

  /* ─── KPIs ─── */
  function updateKPIs() {
    document.getElementById('kpiTotal').textContent       = COMUNICADOS.length;
    document.getElementById('kpiEnviados').textContent    = COMUNICADOS.filter(c=>c.estado==='enviado').length;
    document.getElementById('kpiBorradores').textContent  = COMUNICADOS.filter(c=>c.estado==='borrador').length;
    document.getElementById('kpiUrgentes').textContent    = COMUNICADOS.filter(c=>c.tipo==='urgente').length;
    document.getElementById('kpiProgramados').textContent = COMUNICADOS.filter(c=>c.estado==='programado').length;
  }

  /* ─── Filtrar ─── */
  function applyFilters() {
    const q    = comSearch.value.trim().toLowerCase();
    const tipo = filtroTipo.value;
    const est  = filtroEstado.value || activeKpi;
    const dest = filtroDestinatario.value;
    const tab  = activeTab === 'todos' ? '' : activeTab;

    filtered = COMUNICADOS.filter(c => {
      const matchQ    = !q    || c.asunto.toLowerCase().includes(q) || stripHtml(c.mensaje).toLowerCase().includes(q);
      const matchTipo = !tipo || c.tipo === tipo;
      const matchEst  = !est  || c.estado === est || (activeKpi === 'urgente' && c.tipo === 'urgente');
      const matchDest = !dest || c.destinatarios.includes(dest);
      const matchTab  = !tab  || c.estado === tab;
      return matchQ && matchTipo && matchEst && matchDest && matchTab;
    });

    renderLista();
  }

  /* ─── Render lista ─── */
  function renderLista() {
    comCount.innerHTML = `Mostrando <strong>${filtered.length}</strong> comunicados`;

    if (filtered.length === 0) {
      comLista.innerHTML = '';
      comEmpty.style.display = 'flex';
      return;
    }
    comEmpty.style.display = 'none';

    comLista.innerHTML = filtered.map(c => {
      const preview = stripHtml(c.mensaje).slice(0, 100) + (stripHtml(c.mensaje).length > 100 ? '…' : '');
      const destText = c.destinatarios.map(d => destLabel[d] || d).join(', ');
      const gradosText = c.grados.length ? ` · Grados: ${c.grados.join(', ')}` : '';

      return `
        <div class="com-item ${c.estado === 'borrador' ? 'borrador' : ''}" data-id="${c.id}">
          <div class="com-tipo-dot ${c.tipo}"></div>
          <div class="com-item-body">
            <div class="com-item-top">
              <span class="com-tipo-badge ${c.tipo}">${tipoLabel[c.tipo]}</span>
              <span class="com-item-asunto">${c.asunto}</span>
              <span class="com-estado-badge ${c.estado}">${estadoLabel[c.estado]}</span>
            </div>
            <div class="com-item-meta">
              <span><i class="fas fa-calendar"></i> ${formatFecha(c.fecha)}</span>
              <span><i class="fas fa-users"></i> ${destText}${gradosText}</span>
              ${c.estado === 'enviado' ? `<span><i class="fas fa-eye"></i> ${c.lecturas} lecturas</span>` : ''}
              <span><i class="fas fa-user"></i> ${c.autor}</span>
            </div>
            <div class="com-item-preview">${preview}</div>
          </div>
          <div class="com-item-actions">
            <button class="cia-btn" data-action="ver" data-id="${c.id}" title="Ver detalle">
              <i class="fas fa-eye"></i>
            </button>
            <button class="cia-btn" data-action="editar" data-id="${c.id}" title="Editar">
              <i class="fas fa-edit"></i>
            </button>
            ${c.estado === 'borrador' ? `
              <button class="cia-btn enviar" data-action="enviar" data-id="${c.id}" title="Enviar ahora">
                <i class="fas fa-paper-plane"></i>
              </button>
            ` : ''}
            <button class="cia-btn eliminar" data-action="eliminar" data-id="${c.id}" title="Eliminar">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      `;
    }).join('');

    // Eventos de filas
    comLista.querySelectorAll('.com-item').forEach(item => {
      item.addEventListener('click', e => {
        const btn = e.target.closest('.cia-btn');
        if (btn) {
          e.stopPropagation();
          const action = btn.dataset.action;
          const id     = parseInt(btn.dataset.id);
          if (action === 'editar')   abrirEditor(id);
          if (action === 'eliminar') eliminarComunicado(id);
          if (action === 'enviar')   enviarDirecto(id);
          if (action === 'ver')      abrirEditor(id, true);
          return;
        }
        abrirEditor(parseInt(item.dataset.id), true);
      });
    });
  }

  /* ─── Tabs ─── */
  document.querySelectorAll('.com-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.com-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      activeTab = tab.dataset.tab;
      applyFilters();
    });
  });

  /* ─── KPI clic ─── */
  document.querySelectorAll('.ckpi-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.ckpi-card').forEach(c => c.classList.remove('active-kpi'));
      if (activeKpi === card.dataset.filter) {
        activeKpi = '';
        applyFilters();
      } else {
        activeKpi = card.dataset.filter;
        card.classList.add('active-kpi');
        applyFilters();
      }
    });
  });

  /* ─── Búsqueda y filtros ─── */
  comSearch.addEventListener('input', () => {
    comSearchClear.style.display = comSearch.value ? 'flex' : 'none';
    applyFilters();
  });
  comSearchClear.addEventListener('click', () => { comSearch.value=''; comSearchClear.style.display='none'; applyFilters(); });
  filtroTipo.addEventListener('change',         applyFilters);
  filtroEstado.addEventListener('change',       applyFilters);
  filtroDestinatario.addEventListener('change', applyFilters);
  document.getElementById('btnClearFilters').addEventListener('click', limpiarFiltros);
  document.getElementById('btnEmptyReset').addEventListener('click',   limpiarFiltros);

  function limpiarFiltros() {
    comSearch.value=''; comSearchClear.style.display='none';
    filtroTipo.value=''; filtroEstado.value=''; filtroDestinatario.value='';
    activeKpi=''; activeTab='todos';
    document.querySelectorAll('.ckpi-card').forEach(c=>c.classList.remove('active-kpi'));
    document.querySelectorAll('.com-tab').forEach(t=>t.classList.remove('active'));
    document.querySelector('.com-tab[data-tab="todos"]').classList.add('active');
    applyFilters();
  }

  /* ─── EDITOR ─── */
  function limpiarEditor() {
    document.querySelector('input[name="tipo"][value="informativo"]').checked = true;
    document.getElementById('comAsunto').value = '';
    document.getElementById('asuntoCount').textContent = '0';
    document.querySelectorAll('input[name="dest"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="gradosDest"]').forEach(cb => cb.checked = false);
    document.getElementById('gradoSelectorWrap').style.display = 'none';
    document.getElementById('comMensaje').innerHTML = '';
    document.getElementById('toggleProgramar').checked = false;
    document.getElementById('programarWrap').style.display = 'none';
    document.getElementById('fechaProgramada').value = '';
    document.querySelector('input[name="visibilidad"][value="todos"]').checked = true;
    document.querySelectorAll('input[name="puedeEditar"]:not(:disabled)').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="puedeEnviar"]:not(:disabled)').forEach(cb => cb.checked = false);
    // Limpiar errores
    ['err-tipo','err-asunto','err-dest','err-mensaje'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.textContent = '';
    });
  }

  function cargarEnEditor(c) {
    document.querySelector(`input[name="tipo"][value="${c.tipo}"]`).checked = true;
    document.getElementById('comAsunto').value = c.asunto;
    document.getElementById('asuntoCount').textContent = c.asunto.length;
    c.destinatarios.forEach(d => {
      const cb = document.getElementById('dest' + d.charAt(0).toUpperCase() + d.slice(1));
      if (cb) cb.checked = true;
    });
    if (c.grados.length) {
      document.getElementById('gradoSelectorWrap').style.display = '';
      c.grados.forEach(g => {
        const cb = document.querySelector(`input[name="gradosDest"][value="${g}"]`);
        if (cb) cb.checked = true;
      });
    }
    document.getElementById('comMensaje').innerHTML = c.mensaje;
    // Permisos
    if (c.permisos) {
      const vis = document.querySelector(`input[name="visibilidad"][value="${c.permisos.visibilidad}"]`);
      if (vis) vis.checked = true;
      c.permisos.editar.forEach(r => {
        const cb = document.querySelector(`input[name="puedeEditar"][value="${r}"]`);
        if (cb && !cb.disabled) cb.checked = true;
      });
      c.permisos.enviar.forEach(r => {
        const cb = document.querySelector(`input[name="puedeEnviar"][value="${r}"]`);
        if (cb && !cb.disabled) cb.checked = true;
      });
    }
  }

  function abrirEditor(id = null, soloVer = false) {
    editandoId = id;
    limpiarEditor();
    if (id) {
      const c = COMUNICADOS.find(x => x.id === id);
      if (c) {
        cargarEnEditor(c);
        document.getElementById('editorTitle').textContent    = soloVer ? c.asunto : 'Editar comunicado';
        document.getElementById('editorSubtitle').textContent = soloVer ? `${tipoLabel[c.tipo]} · ${formatFecha(c.fecha)}` : 'Modifica y guarda los cambios';
      }
    } else {
      document.getElementById('editorTitle').textContent    = 'Nuevo comunicado';
      document.getElementById('editorSubtitle').textContent = 'Completa el formulario y previsualiza antes de enviar';
    }

    // Activar tab editor
    activarTabEditor('editor');
    modalEditor.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  document.getElementById('btnNuevoComunicado').addEventListener('click', () => abrirEditor());

  function cerrarEditor() {
    modalEditor.classList.remove('open');
    document.body.style.overflow = '';
    editandoId = null;
  }
  document.getElementById('modalEditorClose').addEventListener('click', cerrarEditor);
  document.getElementById('btnEditorCancelar').addEventListener('click', cerrarEditor);
  modalEditor.addEventListener('click', e => { if (e.target === modalEditor) cerrarEditor(); });

  /* Tabs del editor */
  function activarTabEditor(tab) {
    document.querySelectorAll('.me-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.me-panel').forEach(p => p.classList.remove('active'));
    document.querySelector(`.me-tab[data-etab="${tab}"]`).classList.add('active');
    document.getElementById(`etab-${tab}`).classList.add('active');
    if (tab === 'preview') actualizarPreview();
  }
  document.querySelectorAll('.me-tab').forEach(tab => {
    tab.addEventListener('click', () => activarTabEditor(tab.dataset.etab));
  });

  /* Contador asunto */
  document.getElementById('comAsunto').addEventListener('input', function() {
    document.getElementById('asuntoCount').textContent = this.value.length;
  });

  /* Toggle programar */
  document.getElementById('toggleProgramar').addEventListener('change', function() {
    document.getElementById('programarWrap').style.display = this.checked ? '' : 'none';
  });

  /* Toggle grado selector */
  document.getElementById('destGrado').addEventListener('change', function() {
    document.getElementById('gradoSelectorWrap').style.display = this.checked ? '' : 'none';
  });

  /* Toolbar WYSIWYG */
  document.querySelectorAll('.me-tb-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.execCommand(btn.dataset.cmd, false, null);
      document.getElementById('comMensaje').focus();
    });
  });

  /* ─── Vista previa ─── */
  function actualizarPreview() {
    const tipo    = document.querySelector('input[name="tipo"]:checked')?.value || 'informativo';
    const asunto  = document.getElementById('comAsunto').value || 'Sin asunto';
    const mensaje = document.getElementById('comMensaje').innerHTML || '<p style="color:#b0bfb0;font-style:italic">El contenido del comunicado aparecerá aquí…</p>';
    const dests   = [...document.querySelectorAll('input[name="dest"]:checked')].map(cb => destLabel[cb.value] || cb.value);
    const hoy     = new Date().toLocaleDateString('es-CO',{year:'numeric',month:'long',day:'numeric'});

    const pvHeader = document.getElementById('pvHeader');
    pvHeader.className = 'mpv-header ' + tipo;

    document.getElementById('pvTipo').textContent   = tipoLabel[tipo];
    document.getElementById('pvFecha').textContent  = hoy;
    document.getElementById('pvAsunto').textContent = asunto;
    document.getElementById('pvDest').innerHTML     = `<i class="fas fa-users"></i><span>${dests.length ? dests.join(', ') : 'Sin destinatarios'}</span>`;
    document.getElementById('pvMensaje').innerHTML  = mensaje;

    // Color tipo en badge preview
    const tipoBadge = document.getElementById('pvTipo');
    tipoBadge.className = 'mpv-tipo';
  }

  /* Preview rápido desde footer */
  document.getElementById('btnPreviewQuick').addEventListener('click', () => {
    activarTabEditor('preview');
  });

  /* ─── Validar formulario ─── */
  function validarFormulario() {
    let ok = true;
    const asunto  = document.getElementById('comAsunto').value.trim();
    const mensaje = document.getElementById('comMensaje').innerText.trim();
    const dests   = document.querySelectorAll('input[name="dest"]:checked');

    if (!asunto) {
      document.getElementById('err-asunto').textContent = 'El asunto es obligatorio.';
      ok = false;
    } else {
      document.getElementById('err-asunto').textContent = '';
    }
    if (!mensaje) {
      document.getElementById('err-mensaje').textContent = 'El mensaje no puede estar vacío.';
      ok = false;
    } else {
      document.getElementById('err-mensaje').textContent = '';
    }
    if (dests.length === 0) {
      document.getElementById('err-dest').textContent = 'Selecciona al menos un destinatario.';
      ok = false;
    } else {
      document.getElementById('err-dest').textContent = '';
    }
    if (!ok) activarTabEditor('editor');
    return ok;
  }

  /* ─── Recoger datos del editor ─── */
  function recogerDatos(estado) {
    const tipo       = document.querySelector('input[name="tipo"]:checked')?.value || 'informativo';
    const asunto     = document.getElementById('comAsunto').value.trim();
    const mensaje    = document.getElementById('comMensaje').innerHTML;
    const dests      = [...document.querySelectorAll('input[name="dest"]:checked')].map(cb => cb.value);
    const grados     = [...document.querySelectorAll('input[name="gradosDest"]:checked')].map(cb => cb.value);
    const visib      = document.querySelector('input[name="visibilidad"]:checked')?.value || 'todos';
    const editar     = ['rector', ...[...document.querySelectorAll('input[name="puedeEditar"]:checked:not(:disabled)')].map(cb=>cb.value)];
    const enviar     = ['rector', ...[...document.querySelectorAll('input[name="puedeEnviar"]:checked:not(:disabled)')].map(cb=>cb.value)];
    const programado = document.getElementById('toggleProgramar').checked;
    const fechaProg  = document.getElementById('fechaProgramada').value;
    const hoy        = new Date().toISOString().split('T')[0];

    return {
      tipo, asunto, mensaje, destinatarios:dests, grados, estado,
      fecha: programado && fechaProg ? fechaProg.split('T')[0] : hoy,
      autor:'Rosa Cardona', lecturas:0,
      permisos:{ visibilidad:visib, editar, enviar }
    };
  }

  /* ─── Guardar borrador ─── */
  document.getElementById('btnGuardarBorrador').addEventListener('click', () => {
    if (!document.getElementById('comAsunto').value.trim()) {
      document.getElementById('err-asunto').textContent = 'El asunto es obligatorio.';
      activarTabEditor('editor');
      return;
    }
    const datos = recogerDatos('borrador');
    if (editandoId) {
      const idx = COMUNICADOS.findIndex(c => c.id === editandoId);
      if (idx !== -1) COMUNICADOS[idx] = { ...COMUNICADOS[idx], ...datos };
    } else {
      COMUNICADOS.unshift({ id: Date.now(), ...datos });
    }
    cerrarEditor();
    applyFilters();
    updateKPIs();
    showToast('Borrador guardado correctamente', 'success');
  });

  /* ─── Enviar ─── */
  document.getElementById('btnEnviarComunicado').addEventListener('click', () => {
    if (!validarFormulario()) return;
    const dests = [...document.querySelectorAll('input[name="dest"]:checked')].map(cb => destLabel[cb.value] || cb.value);
    const programado = document.getElementById('toggleProgramar').checked;

    document.getElementById('mccTitle').textContent = programado ? '¿Programar envío?' : '¿Enviar comunicado ahora?';
    document.getElementById('mccMsg').textContent   = programado
      ? 'El comunicado se enviará en la fecha y hora programada.'
      : 'Esta acción enviará el comunicado a los destinatarios seleccionados.';
    document.getElementById('mccDestPreview').innerHTML = dests.map(d => `<span class="mcc-dest-tag">${d}</span>`).join('');
    document.getElementById('mccIcon').innerHTML = programado ? '<i class="fas fa-clock"></i>' : '<i class="fas fa-paper-plane"></i>';

    modalConfirm.classList.add('open');
  });

  document.getElementById('btnConfirmCancel').addEventListener('click', () => modalConfirm.classList.remove('open'));
  modalConfirm.addEventListener('click', e => { if (e.target === modalConfirm) modalConfirm.classList.remove('open'); });

  document.getElementById('btnConfirmOk').addEventListener('click', () => {
    const programado = document.getElementById('toggleProgramar').checked;
    const datos = recogerDatos(programado ? 'programado' : 'enviado');
    if (editandoId) {
      const idx = COMUNICADOS.findIndex(c => c.id === editandoId);
      if (idx !== -1) COMUNICADOS[idx] = { ...COMUNICADOS[idx], ...datos };
    } else {
      COMUNICADOS.unshift({ id: Date.now(), ...datos });
    }
    modalConfirm.classList.remove('open');
    cerrarEditor();
    applyFilters();
    updateKPIs();
    document.getElementById('mexTitle').textContent = programado ? '¡Comunicado programado!' : '¡Comunicado enviado!';
    document.getElementById('mexMsg').textContent   = programado
      ? 'El comunicado fue programado correctamente.'
      : 'El comunicado fue enviado a todos los destinatarios.';
    modalExito.classList.add('open');
  });

  // Éxito
  document.getElementById('btnMexOk').addEventListener('click', () => modalExito.classList.remove('open'));
  document.getElementById('btnMexNuevo').addEventListener('click', () => { modalExito.classList.remove('open'); abrirEditor(); });
  modalExito.addEventListener('click', e => { if (e.target === modalExito) modalExito.classList.remove('open'); });

  /* ─── Enviar borrador directamente ─── */
  function enviarDirecto(id) {
    const c = COMUNICADOS.find(x => x.id === id);
    if (!c) return;
    document.getElementById('mccTitle').textContent = `¿Enviar "${c.asunto}"?`;
    document.getElementById('mccMsg').textContent   = 'El comunicado pasará de borrador a enviado.';
    document.getElementById('mccDestPreview').innerHTML = c.destinatarios.map(d => `<span class="mcc-dest-tag">${destLabel[d]||d}</span>`).join('');
    document.getElementById('mccIcon').innerHTML = '<i class="fas fa-paper-plane"></i>';
    modalConfirm.classList.add('open');
    document.getElementById('btnConfirmOk').onclick = () => {
      c.estado = 'enviado';
      c.fecha  = new Date().toISOString().split('T')[0];
      modalConfirm.classList.remove('open');
      applyFilters(); updateKPIs();
      showToast('Comunicado enviado correctamente', 'success');
      // Restaurar handler original
      document.getElementById('btnConfirmOk').onclick = null;
      document.getElementById('btnConfirmOk').addEventListener('click', confirmOkHandler);
    };
  }
  const confirmOkHandler = document.getElementById('btnConfirmOk').onclick;

  /* ─── Eliminar ─── */
  function eliminarComunicado(id) {
    const c = COMUNICADOS.find(x => x.id === id);
    if (!c) return;
    if (!confirm(`¿Eliminar el comunicado "${c.asunto}"?`)) return;
    COMUNICADOS = COMUNICADOS.filter(x => x.id !== id);
    applyFilters(); updateKPIs();
    showToast('Comunicado eliminado', 'info');
  }

  /* ─── Init ─── */
  updateKPIs();
  applyFilters();

});