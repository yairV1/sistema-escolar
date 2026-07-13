/* =============================================
   COLEGIO SAN CRISTÓBAL — REGISTRO DOCENTE
   JS: Wizard 5 pasos, validación, archivos, resumen
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  // ─── Estado ───
  let currentStep = 1;
  const TOTAL_STEPS = 5;

  // ─── Referencias DOM ───
  const form       = document.getElementById('wizardForm');
  const btnNext    = document.getElementById('btnNext');
  const btnPrev    = document.getElementById('btnPrev');
  const btnSubmit  = document.getElementById('btnSubmit');
  const wsSteps    = document.querySelectorAll('.ws-step');
  const wsFill     = document.getElementById('wsFill');
  const headerProg = document.getElementById('headerProgress');
  const stepLabel  = document.getElementById('currentStepLabel');
  const dotInds    = document.querySelectorAll('.wf-dot-ind');
  const modalExito = document.getElementById('modalExito');
  const codigoEl   = document.getElementById('codigoGenerado');
  const btnNuevo   = document.getElementById('btnNuevoRegistro');

  // ─── Reglas de validación por paso ───
  const validationRules = {
    1: [
      { id: 'primerNombre',   msg: 'El primer nombre es obligatorio.' },
      { id: 'primerApellido', msg: 'El primer apellido es obligatorio.' },
      { id: 'tipoDoc',        msg: 'Selecciona el tipo de documento.' },
      { id: 'numDoc',         msg: 'El número de documento es obligatorio.' },
      { id: 'fechaNac',       msg: 'La fecha de nacimiento es obligatoria.' },
      { id: 'genero',         msg: 'Selecciona el género.' },
    ],
    2: [
      { id: 'direccion',       msg: 'La dirección es obligatoria.' },
      { id: 'localidad',       msg: 'Selecciona la localidad.' },
      { id: 'ciudad',          msg: 'La ciudad es obligatoria.' },
      {
        id: 'emailPersonal',
        msg: 'Ingresa un correo personal válido.',
        validator: val => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
      },
      { id: 'telPersonal',     msg: 'El teléfono personal es obligatorio.' },
      { id: 'emergNombre',     msg: 'El nombre del contacto de emergencia es obligatorio.' },
      { id: 'emergParentesco', msg: 'Selecciona el parentesco del contacto de emergencia.' },
      { id: 'emergTel',        msg: 'El teléfono de emergencia es obligatorio.' },
    ],
    3: [
      { id: 'nivelEstudios',    msg: 'Selecciona el nivel de estudios.' },
      { id: 'tituloObtenido',   msg: 'El título obtenido es obligatorio.' },
      { id: 'institucionEgreso',msg: 'La institución de egreso es obligatoria.' },
      {
        id: 'anioGrado',
        msg: 'Ingresa un año de graduación válido (1970–2025).',
        validator: val => {
          const n = parseInt(val);
          return !isNaN(n) && n >= 1970 && n <= 2025;
        },
      },
      { id: 'aniosExperiencia', msg: 'Selecciona los años de experiencia.' },
    ],
    4: [
      { id: 'areaEnsenanza', msg: 'Selecciona el área de enseñanza.' },
      { id: 'jornada',       msg: 'Selecciona la jornada.' },
      {
        id: 'grados',
        msg: 'Selecciona al menos un grado asignado.',
        customCheck: () => document.querySelectorAll('input[name="grados"]:checked').length > 0,
      },
      {
        id: 'horasSemana',
        msg: 'Ingresa las horas semanales (1–40).',
        validator: val => {
          const n = parseInt(val);
          return !isNaN(n) && n >= 1 && n <= 40;
        },
      },
    ],
    5: [
      { id: 'cargo',        msg: 'Selecciona el cargo.' },
      { id: 'tipoContrato', msg: 'Selecciona el tipo de contrato.' },
      { id: 'fechaIngreso', msg: 'La fecha de ingreso es obligatoria.' },
    ],
  };

  // ─── Validar paso ───
  function validateStep(step) {
    const rules = validationRules[step];
    if (!rules) return true;
    let valid = true;

    rules.forEach(rule => {
      const errEl = document.getElementById('err-' + rule.id);

      // Validación con customCheck (checkboxes)
      if (rule.customCheck) {
        const ok = rule.customCheck();
        if (!ok) {
          if (errEl) errEl.textContent = rule.msg;
          valid = false;
        } else {
          if (errEl) errEl.textContent = '';
        }
        return;
      }

      const el  = document.getElementById(rule.id);
      if (!el) return;
      const val = el.value.trim();
      let fieldOk = true;

      if (rule.validator) {
        fieldOk = rule.validator(val);
      } else {
        fieldOk = val !== '';
      }

      if (!fieldOk) {
        el.classList.add('error');
        el.classList.remove('valid');
        if (errEl) errEl.textContent = rule.msg;
        valid = false;
      } else {
        el.classList.remove('error');
        if (val !== '') el.classList.add('valid');
        if (errEl) errEl.textContent = '';
      }
    });

    return valid;
  }

  // ─── Limpiar errores al escribir ───
  form.querySelectorAll('.wf-input').forEach(input => {
    input.addEventListener('input', () => {
      if (input.classList.contains('error') && input.value.trim() !== '') {
        input.classList.remove('error');
        input.classList.add('valid');
        const err = document.getElementById('err-' + input.id);
        if (err) err.textContent = '';
      }
    });
  });

  // Limpiar error de grados al marcar un checkbox
  document.querySelectorAll('input[name="grados"]').forEach(cb => {
    cb.addEventListener('change', () => {
      const errGrados = document.getElementById('err-grados');
      if (errGrados && document.querySelectorAll('input[name="grados"]:checked').length > 0) {
        errGrados.textContent = '';
      }
    });
  });

  // ─── Actualizar UI del wizard ───
  function updateWizardUI(direction = 'forward') {
    // Panels
    document.querySelectorAll('.wf-panel').forEach(p => {
      p.classList.remove('active', 'slide-back');
    });
    const panel = document.getElementById('panel-' + currentStep);
    if (panel) {
      panel.classList.add('active');
      if (direction === 'back') panel.classList.add('slide-back');
    }

    // Steps tracker
    wsSteps.forEach((step, i) => {
      const n = i + 1;
      step.classList.remove('active', 'done');
      if (n < currentStep)   step.classList.add('done');
      if (n === currentStep) step.classList.add('active');
    });

    // Fill line
    const fillPct = Math.max(0, ((currentStep - 1) / (TOTAL_STEPS - 1)) * 100);
    wsFill.style.width = fillPct + '%';

    // Header progress
    if (headerProg) headerProg.style.width = ((currentStep / TOTAL_STEPS) * 100) + '%';
    if (stepLabel)  stepLabel.textContent = currentStep;

    // Dot indicators
    dotInds.forEach((dot, i) => {
      const n = i + 1;
      dot.classList.remove('active', 'done');
      if (n === currentStep) dot.classList.add('active');
      if (n < currentStep)   dot.classList.add('done');
    });

    // Botones nav
    btnPrev.disabled = currentStep === 1;
    if (currentStep === TOTAL_STEPS) {
      btnNext.style.display   = 'none';
      btnSubmit.style.display = 'flex';
      buildSummary();
    } else {
      btnNext.style.display   = 'flex';
      btnSubmit.style.display = 'none';
    }

    // Scroll al inicio del wizard
    const wizBody = document.querySelector('.wizard-body');
    if (wizBody) wizBody.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Siguiente ───
  btnNext.addEventListener('click', () => {
    if (!validateStep(currentStep)) return;
    if (currentStep < TOTAL_STEPS) {
      currentStep++;
      updateWizardUI('forward');
    }
  });

  // ─── Anterior ───
  btnPrev.addEventListener('click', () => {
    if (currentStep > 1) {
      currentStep--;
      updateWizardUI('back');
    }
  });

  // ─── Upload de archivos ───
  function setupFileUpload(inputId, nameId, dropId) {
    const input   = document.getElementById(inputId);
    const nameEl  = document.getElementById(nameId);
    const dropEl  = document.getElementById(dropId);
    if (!input || !nameEl || !dropEl) return;

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (file) {
        nameEl.textContent = file.name;
        dropEl.classList.add('has-file');
      } else {
        nameEl.textContent = 'Sin archivo';
        dropEl.classList.remove('has-file');
      }
    });

    // Drag & drop visual
    dropEl.addEventListener('dragover', e => { e.preventDefault(); dropEl.classList.add('drag-over'); });
    dropEl.addEventListener('dragleave', () => dropEl.classList.remove('drag-over'));
    dropEl.addEventListener('drop', e => {
      e.preventDefault();
      dropEl.classList.remove('drag-over');
      const file = e.dataTransfer.files[0];
      if (file) {
        // Asignar al input vía DataTransfer
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        nameEl.textContent = file.name;
        dropEl.classList.add('has-file');
      }
    });
  }

  setupFileUpload('docHojaVida', 'nameHojaVida', 'dropHojaVida');
  setupFileUpload('docTitulos',  'nameTitulos',  'dropTitulos');

  // ─── Construir resumen ───
  function getVal(id) {
    const el = document.getElementById(id);
    if (!el) return '—';
    if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '—';
    return el.value.trim() || '—';
  }

  function getGrados() {
    const checked = [...document.querySelectorAll('input[name="grados"]:checked')];
    return checked.length ? checked.map(c => c.value).join(', ') : '—';
  }

  function formatDate(str) {
    if (!str || str === '—') return '—';
    const [y, m, d] = str.split('-');
    const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return `${parseInt(d)} ${meses[parseInt(m)-1]} ${y}`;
  }

  function buildSummary() {
    const container = document.getElementById('summaryContent');
    if (!container) return;

    const sections = [
      {
        title: 'Datos personales',
        rows: [
          { key: 'Nombre',    val: [getVal('primerNombre'), getVal('segundoNombre'), getVal('primerApellido'), getVal('segundoApellido')].filter(v => v !== '—').join(' ') },
          { key: 'Documento', val: getVal('tipoDoc') + ' ' + getVal('numDoc') },
          { key: 'Nac.',      val: formatDate(getVal('fechaNac')) },
          { key: 'Género',    val: getVal('genero') },
        ],
      },
      {
        title: 'Contacto',
        rows: [
          { key: 'Teléfono', val: getVal('telPersonal') },
          { key: 'Correo',   val: getVal('emailPersonal') },
          { key: 'Ciudad',   val: getVal('ciudad') },
          { key: 'Emergencia', val: getVal('emergNombre') + ' · ' + getVal('emergTel') },
        ],
      },
      {
        title: 'Formación',
        rows: [
          { key: 'Nivel',      val: getVal('nivelEstudios') },
          { key: 'Título',     val: getVal('tituloObtenido') },
          { key: 'Institución',val: getVal('institucionEgreso') },
          { key: 'Experiencia',val: getVal('aniosExperiencia') },
        ],
      },
      {
        title: 'Vinculación',
        rows: [
          { key: 'Cargo',     val: getVal('cargo') },
          { key: 'Contrato',  val: getVal('tipoContrato') },
          { key: 'Ingreso',   val: formatDate(getVal('fechaIngreso')) },
          { key: 'Grados',    val: getGrados() },
        ],
      },
    ];

    container.innerHTML = sections.map(sec => `
      <div class="ws-summary-section">
        <div class="wss-title">${sec.title}</div>
        ${sec.rows.map(r => `
          <div class="wss-row">
            <span class="wss-key">${r.key}</span>
            <span class="wss-val">${r.val !== '—' ? r.val : '<span class="wss-empty">No informado</span>'}</span>
          </div>
        `).join('')}
      </div>
    `).join('');
  }

  // ─── Submit ───
  form.addEventListener('submit', e => {
    e.preventDefault();
    if (!validateStep(currentStep)) return;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';

    setTimeout(() => {
      const codigo = generarCodigo();
      if (codigoEl) codigoEl.textContent = codigo;
      modalExito.classList.add('open');
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar registro';
    }, 1400);
  });

  // ─── Código único ───
  function generarCodigo() {
    const year = new Date().getFullYear();
    const rand = String(Math.floor(Math.random() * 9000) + 1000);
    return `${year}-DOC-${rand}`;
  }

  // ─── Cerrar modal / reset ───
  if (btnNuevo) {
    btnNuevo.addEventListener('click', () => {
      modalExito.classList.remove('open');
      resetWizard();
    });
  }
  modalExito?.addEventListener('click', e => {
    if (e.target === modalExito) modalExito.classList.remove('open');
  });

  function resetWizard() {
    form.reset();
    form.querySelectorAll('.wf-input').forEach(el => el.classList.remove('error', 'valid'));
    form.querySelectorAll('.wf-error').forEach(el => el.textContent = '');
    // Reset file uploads
    ['dropHojaVida', 'dropTitulos'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.remove('has-file');
    });
    document.getElementById('nameHojaVida').textContent = 'Sin archivo';
    document.getElementById('nameTitulos').textContent  = 'Sin archivo';
    currentStep = 1;
    updateWizardUI('back');
  }

  // ─── Init ───
  updateWizardUI('forward');

});