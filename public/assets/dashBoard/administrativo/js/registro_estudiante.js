/* =============================================
   COLEGIO SAN CRISTÓBAL — REGISTRO ESTUDIANTE
   JS: Wizard, validación y resumen
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  // ─── Estado del wizard ───
  let currentStep = 1;
  const TOTAL_STEPS = 4;

  // ─── Referencias DOM ───
  const form        = document.getElementById('wizardForm');
  const btnNext     = document.getElementById('btnNext');
  const btnPrev     = document.getElementById('btnPrev');
  const btnSubmit   = document.getElementById('btnSubmit');
  const wsSteps     = document.querySelectorAll('.ws-step');
  const wsFill      = document.getElementById('wsFill');
  const headerProg  = document.getElementById('headerProgress');
  const stepLabel   = document.getElementById('currentStepLabel');
  const dotInds     = document.querySelectorAll('.wf-dot-ind');
  const modalExito  = document.getElementById('modalExito');
  const codigoEl    = document.getElementById('codigoGenerado');
  const btnNuevo    = document.getElementById('btnNuevoRegistro');
  const neeRadios   = document.querySelectorAll('input[name="nee"]');
  const neeDetalle  = document.getElementById('neeDetalle');

  // ─── Validación por paso ───
  const validationRules = {
    1: [
      { id: 'primerNombre',  msg: 'El primer nombre es obligatorio.' },
      { id: 'primerApellido',msg: 'El primer apellido es obligatorio.' },
      { id: 'tipoDoc',       msg: 'Selecciona el tipo de documento.' },
      { id: 'numDoc',        msg: 'El número de documento es obligatorio.' },
      { id: 'fechaNac',      msg: 'La fecha de nacimiento es obligatoria.' },
      { id: 'genero',        msg: 'Selecciona el género.' },
    ],
    2: [
      { id: 'direccion', msg: 'La dirección es obligatoria.' },
      { id: 'localidad', msg: 'Selecciona la localidad.' },
      { id: 'ciudad',    msg: 'La ciudad es obligatoria.' },
      {
        id: 'emailEstudiante',
        msg: 'Ingresa un correo válido.',
        optional: true,
        validator: val => val === '' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
      },
    ],
    3: [
      { id: 'tipoMatricula', msg: 'Selecciona el tipo de matrícula.' },
      { id: 'anioLectivo',   msg: 'Selecciona el año lectivo.' },
      { id: 'grado',         msg: 'Selecciona el grado.' },
      { id: 'jornada',       msg: 'Selecciona la jornada.' },
    ],
    4: [
      { id: 'acuNombres',    msg: 'El nombre del acudiente es obligatorio.' },
      { id: 'acuParentesco', msg: 'Selecciona el parentesco.' },
      { id: 'acuTipoDoc',    msg: 'Selecciona el tipo de documento del acudiente.' },
      { id: 'acuNumDoc',     msg: 'El número de documento del acudiente es obligatorio.' },
      { id: 'acuTel',        msg: 'El teléfono del acudiente es obligatorio.' },
      {
        id: 'acuEmail',
        msg: 'Ingresa un correo válido para el acudiente.',
        validator: val => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
      },
    ],
  };

  // ─── Validar un paso ───
  function validateStep(step) {
    const rules = validationRules[step];
    if (!rules) return true;

    let valid = true;

    rules.forEach(rule => {
      const el  = document.getElementById(rule.id);
      const err = document.getElementById('err-' + rule.id);
      if (!el) return;

      const val = el.value.trim();
      let fieldOk = true;

      if (rule.validator) {
        fieldOk = rule.validator(val);
      } else if (!rule.optional) {
        fieldOk = val !== '';
      }

      if (!fieldOk) {
        el.classList.add('error');
        el.classList.remove('valid');
        if (err) err.textContent = rule.msg;
        valid = false;
      } else {
        el.classList.remove('error');
        if (val !== '') el.classList.add('valid');
        if (err) err.textContent = '';
      }
    });

    // Validación NEE en paso 3
    if (step === 3) {
      const neeVal = document.querySelector('input[name="nee"]:checked')?.value;
      if (neeVal === 'si') {
        const neeDescEl  = document.getElementById('neeDesc');
        const neeDescErr = document.getElementById('err-neeDesc');
        if (neeDescEl && neeDescEl.value.trim() === '') {
          neeDescEl.classList.add('error');
          if (neeDescErr) neeDescErr.textContent = 'Describe la necesidad educativa especial.';
          valid = false;
        } else if (neeDescEl) {
          neeDescEl.classList.remove('error');
          neeDescEl.classList.add('valid');
          if (neeDescErr) neeDescErr.textContent = '';
        }
      }
    }

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
      if (n < currentStep)  step.classList.add('done');
      if (n === currentStep) step.classList.add('active');
    });

    // Fill line
    const fillPct = Math.max(0, ((currentStep - 1) / (TOTAL_STEPS - 1)) * 100);
    wsFill.style.width = fillPct + '%';

    // Header progress
    const headPct = (currentStep / TOTAL_STEPS) * 100;
    if (headerProg) headerProg.style.width = headPct + '%';
    if (stepLabel)  stepLabel.textContent = currentStep;

    // Dot indicators
    dotInds.forEach((dot, i) => {
      const n = i + 1;
      dot.classList.remove('active', 'done');
      if (n === currentStep) dot.classList.add('active');
      if (n < currentStep)   dot.classList.add('done');
    });

    // Botones
    btnPrev.disabled = currentStep === 1;
    if (currentStep === TOTAL_STEPS) {
      btnNext.style.display   = 'none';
      btnSubmit.style.display = 'flex';
    } else {
      btnNext.style.display   = 'flex';
      btnSubmit.style.display = 'none';
    }

    // Scroll top del wizard suavemente
    const wizBody = document.querySelector('.wizard-body');
    if (wizBody) wizBody.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Siguiente ───
  btnNext.addEventListener('click', () => {
    if (!validateStep(currentStep)) return;
    if (currentStep < TOTAL_STEPS) {
      currentStep++;
      updateWizardUI('forward');
      if (currentStep === TOTAL_STEPS) buildSummary();
    }
  });

  // ─── Anterior ───
  btnPrev.addEventListener('click', () => {
    if (currentStep > 1) {
      currentStep--;
      updateWizardUI('back');
    }
  });

  // ─── NEE toggle ───
  neeRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      if (neeDetalle) {
        if (radio.value === 'si') {
          neeDetalle.style.display = 'flex';
          neeDetalle.style.flexDirection = 'column';
          neeDetalle.style.gap = '5px';
        } else {
          neeDetalle.style.display = 'none';
          const d = document.getElementById('neeDesc');
          if (d) { d.value = ''; d.classList.remove('error','valid'); }
        }
      }
    });
  });

  // ─── Construir resumen (paso 4) ───
  function getVal(id) {
    const el = document.getElementById(id);
    if (!el) return '—';
    if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '—';
    return el.value.trim() || '—';
  }

  function buildSummary() {
    const container = document.getElementById('summaryContent');
    if (!container) return;

    const sections = [
      {
        title: 'Datos personales',
        rows: [
          { key: 'Nombre completo', val: [getVal('primerNombre'), getVal('segundoNombre'), getVal('primerApellido'), getVal('segundoApellido')].filter(v => v !== '—').join(' ') },
          { key: 'Documento',       val: getVal('tipoDoc') + ' ' + getVal('numDoc') },
          { key: 'Fecha de nac.',   val: formatDate(getVal('fechaNac')) },
          { key: 'Género',          val: getVal('genero') },
        ],
      },
      {
        title: 'Contacto',
        rows: [
          { key: 'Dirección',  val: getVal('direccion') },
          { key: 'Localidad',  val: getVal('localidad') },
          { key: 'Teléfono',   val: getVal('telEstudiante') },
          { key: 'Correo',     val: getVal('emailEstudiante') },
        ],
      },
      {
        title: 'Académico',
        rows: [
          { key: 'Grado',     val: getVal('grado') },
          { key: 'Jornada',   val: getVal('jornada') },
          { key: 'Matrícula', val: getVal('tipoMatricula') },
          { key: 'Acudiente', val: getVal('acuNombres') },
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

  function formatDate(dateStr) {
    if (!dateStr || dateStr === '—') return '—';
    const [y, m, d] = dateStr.split('-');
    const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return `${parseInt(d)} ${meses[parseInt(m)-1]} ${y}`;
  }

  // ─── Submit ───
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!validateStep(currentStep)) return;

    // Simular envío
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

  // ─── Generar código único ───
  function generarCodigo() {
    const year = new Date().getFullYear();
    const rand = String(Math.floor(Math.random() * 9000) + 1000);
    return `${year}-EST-${rand}`;
  }

  // ─── Cerrar modal éxito ───
  if (btnNuevo) {
    btnNuevo.addEventListener('click', () => {
      modalExito.classList.remove('open');
      resetWizard();
    });
  }

  // ─── Reset del wizard ───
  function resetWizard() {
    form.reset();
    form.querySelectorAll('.wf-input').forEach(el => el.classList.remove('error','valid'));
    form.querySelectorAll('.wf-error').forEach(el => el.textContent = '');
    if (neeDetalle) neeDetalle.style.display = 'none';
    currentStep = 1;
    updateWizardUI('back');
  }

  // ─── Inicializar ───
  updateWizardUI('forward');

});