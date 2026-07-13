/* =============================================
   COLEGIO SAN CRISTÓBAL — PERFIL JS
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─────────────────────────────────────
     1. REVEAL + KPI
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

  /* ─────────────────────────────────────
     2. TABS
  ───────────────────────────────────────── */
  const tabs    = document.querySelectorAll('.pt-tab');
  const panels  = document.querySelectorAll('.perfil-panel');
  const slider  = document.getElementById('ptSlider');

  function moveSlider(tab) {
    slider.style.left  = tab.offsetLeft + 'px';
    slider.style.width = tab.offsetWidth + 'px';
  }

  const firstTab = document.querySelector('.pt-tab.active');
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
    const cur = document.querySelector('.pt-tab.active');
    if (cur) moveSlider(cur);
  });

  /* ─────────────────────────────────────
     3. FOTO DE PERFIL — upload
  ───────────────────────────────────────── */
  // Foto grande (hero)
  const avatarInput = document.getElementById('avatarInput');
  const phAvatar    = document.getElementById('phAvatar');
  const phAvatarImg = document.getElementById('phAvatarImg');
  const phInitials  = document.getElementById('phInitials');

  // Foto card lateral
  const fotoInput    = document.getElementById('fotoInput');
  const pfFotoPreview= document.getElementById('pfFotoPreview');
  const pfFotoImg    = document.getElementById('pfFotoImg');
  const pfFotoInit   = document.getElementById('pfFotoInitials');
  const btnFotoRemove= document.getElementById('btnFotoRemove');

  document.getElementById('btnEditAvatar')?.addEventListener('click', () => avatarInput.click());
  document.getElementById('btnFotoUpload')?.addEventListener('click', () => fotoInput.click());

  function loadImage(file, imgEl, initialsEl) {
    if (!file || !file.type.startsWith('image/')) {
      showToast('Solo se aceptan imágenes (JPG, PNG, WebP)', 'error'); return;
    }
    if (file.size > 2 * 1024 * 1024) {
      showToast('La imagen supera los 2MB permitidos', 'error'); return;
    }
    const reader = new FileReader();
    reader.onload = e => {
      imgEl.src = e.target.result;
      imgEl.style.display = 'block';
      if (initialsEl) initialsEl.style.display = 'none';
      showToast('Foto de perfil actualizada', 'success');
    };
    reader.readAsDataURL(file);
  }

  avatarInput.addEventListener('change', () => {
    loadImage(avatarInput.files[0], phAvatarImg, phInitials);
    // Sincronizar con la card lateral
    loadImage(avatarInput.files[0], pfFotoImg, pfFotoInit);
    btnFotoRemove.style.display = 'flex';
  });

  fotoInput.addEventListener('change', () => {
    loadImage(fotoInput.files[0], pfFotoImg, pfFotoInit);
    // Sincronizar con el hero
    loadImage(fotoInput.files[0], phAvatarImg, phInitials);
    btnFotoRemove.style.display = 'flex';
  });

  btnFotoRemove?.addEventListener('click', () => {
    pfFotoImg.src = ''; pfFotoImg.style.display = 'none';
    pfFotoInit.style.display = 'block';
    phAvatarImg.src = ''; phAvatarImg.style.display = 'none';
    phInitials.style.display = 'block';
    btnFotoRemove.style.display = 'none';
    showToast('Foto de perfil eliminada', 'info');
  });

  /* ─────────────────────────────────────
     4. BANNER — upload
  ───────────────────────────────────────── */
  const bannerInput = document.getElementById('bannerInput');
  const phBanner    = document.querySelector('.ph-banner');

  document.getElementById('btnEditBanner')?.addEventListener('click', () => bannerInput.click());

  bannerInput.addEventListener('change', () => {
    const file = bannerInput.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      // Crear o actualizar img del banner
      let img = phBanner.querySelector('img.banner-img');
      if (!img) {
        img = document.createElement('img');
        img.className = 'banner-img';
        img.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;';
        phBanner.appendChild(img);
      }
      img.src = e.target.result;
      showToast('Imagen de portada actualizada', 'success');
    };
    reader.readAsDataURL(file);
  });

  /* ─────────────────────────────────────
     5. NOMBRE EN TIEMPO REAL
  ───────────────────────────────────────── */
  const pfPrimerNombre   = document.getElementById('pfPrimerNombre');
  const pfSegundoNombre  = document.getElementById('pfSegundoNombre');
  const pfPrimerApellido = document.getElementById('pfPrimerApellido');
  const pfSegundoApellido= document.getElementById('pfSegundoApellido');
  const phNombreEl       = document.getElementById('phNombre');
  const phInitialsEl     = document.getElementById('phInitials');
  const pfFotoInitialsEl = document.getElementById('pfFotoInitials');

  function updateNombre() {
    const nombre   = pfPrimerNombre.value.trim();
    const apellido = pfPrimerApellido.value.trim();
    const segundo  = pfSegundoApellido.value.trim();

    const fullName = [nombre, pfSegundoNombre.value.trim(), apellido, segundo]
      .filter(Boolean).join(' ');

    if (phNombreEl) phNombreEl.textContent = fullName || 'Sin nombre';

    // Actualizar iniciales
    const initials = [nombre[0] || '', apellido[0] || ''].join('').toUpperCase();
    if (phInitialsEl)     phInitialsEl.textContent = initials;
    if (pfFotoInitialsEl) pfFotoInitialsEl.textContent = initials;
  }

  [pfPrimerNombre, pfSegundoNombre, pfPrimerApellido, pfSegundoApellido]
    .forEach(el => el?.addEventListener('input', updateNombre));

  /* ─────────────────────────────────────
     6. BIO — contador de caracteres
  ───────────────────────────────────────── */
  const pfBio    = document.getElementById('pfBio');
  const bioCount = document.getElementById('bioCharCount');

  pfBio?.addEventListener('input', () => {
    const len = pfBio.value.length;
    if (bioCount) bioCount.textContent = `${len} / 300`;
    if (len > 280) bioCount.style.color = '#e53e3e';
    else if (len > 250) bioCount.style.color = '#dd6b20';
    else bioCount.style.color = 'var(--gris-texto)';
    if (len > 300) pfBio.value = pfBio.value.slice(0, 300);
  });

  /* ─────────────────────────────────────
     7. GUARDAR PERFIL
  ───────────────────────────────────────── */
  document.getElementById('btnGuardarPerfil')?.addEventListener('click', async () => {
    const btn = document.getElementById('btnGuardarPerfil');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';
    btn.disabled  = true;

    await new Promise(r => setTimeout(r, 1400));

    btn.innerHTML = '<i class="fas fa-save"></i> Guardar cambios';
    btn.disabled  = false;
    showToast('Perfil actualizado correctamente', 'success');
  });

  /* ─────────────────────────────────────
     8. CONTRASEÑA — fortaleza + reglas
  ───────────────────────────────────────── */
  const pwNueva = document.getElementById('pwNueva');
  const pwStrengthWrap  = document.getElementById('pwStrengthWrap');
  const pwStrengthFill  = document.getElementById('pwStrengthFill');
  const pwStrengthLabel = document.getElementById('pwStrengthLabel');

  const rules = {
    len:     { el: document.getElementById('rule-len'),     test: pw => pw.length >= 8 },
    upper:   { el: document.getElementById('rule-upper'),   test: pw => /[A-Z]/.test(pw) },
    num:     { el: document.getElementById('rule-num'),     test: pw => /[0-9]/.test(pw) },
    special: { el: document.getElementById('rule-special'), test: pw => /[^a-zA-Z0-9]/.test(pw) },
  };

  const strengthLevels = [
    { label:'Muy débil', color:'#e53e3e', width:'15%' },
    { label:'Débil',     color:'#e53e3e', width:'30%' },
    { label:'Regular',   color:'#dd6b20', width:'55%' },
    { label:'Buena',     color:'#c8a84b', width:'75%' },
    { label:'Fuerte',    color:'#2d7a4f', width:'100%'},
  ];

  pwNueva?.addEventListener('input', () => {
    const pw = pwNueva.value;
    pwStrengthWrap.style.display = pw ? 'flex' : 'none';

    let score = 0;
    Object.values(rules).forEach(r => {
      const ok = r.test(pw);
      r.el?.classList.toggle('ok', ok);
      if (ok) score++;
    });

    const level = strengthLevels[score];
    pwStrengthFill.style.width      = level.width;
    pwStrengthFill.style.background = level.color;
    pwStrengthLabel.textContent     = level.label;
    pwStrengthLabel.style.color     = level.color;
  });

  /* ─────────────────────────────────────
     9. MOSTRAR / OCULTAR CONTRASEÑAS
  ───────────────────────────────────────── */
  document.querySelectorAll('.pf-toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      if (!target) return;
      const show = target.type === 'password';
      target.type = show ? 'text' : 'password';
      btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
  });

  /* ─────────────────────────────────────
     10. CAMBIAR CONTRASEÑA — submit
  ───────────────────────────────────────── */
  document.getElementById('btnCambiarPw')?.addEventListener('click', async () => {
    const actual  = document.getElementById('pwActual').value;
    const nueva   = document.getElementById('pwNueva').value;
    const confirm = document.getElementById('pwConfirm').value;
    let valid = true;

    const clearErr = id => {
      const el = document.getElementById(id);
      if (el) { el.textContent=''; el.classList.remove('show'); }
    };
    const showErr = (id, msg) => {
      const el = document.getElementById(id);
      if (el) { el.textContent=msg; el.classList.add('show'); }
      valid = false;
    };

    clearErr('err-pwActual'); clearErr('err-pwNueva'); clearErr('err-pwConfirm');

    if (!actual)          showErr('err-pwActual',  'Ingresa tu contraseña actual');
    if (nueva.length < 8) showErr('err-pwNueva',   'Mínimo 8 caracteres');
    if (nueva !== confirm) showErr('err-pwConfirm', 'Las contraseñas no coinciden');
    if (!valid) return;

    const btn = document.getElementById('btnCambiarPw');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando…';
    btn.disabled  = true;

    await new Promise(r => setTimeout(r, 1500));

    btn.innerHTML = '<i class="fas fa-save"></i> Actualizar contraseña';
    btn.disabled  = false;

    // Limpiar campos
    document.getElementById('pwActual').value  = '';
    document.getElementById('pwNueva').value   = '';
    document.getElementById('pwConfirm').value = '';
    pwStrengthWrap.style.display = 'none';
    Object.values(rules).forEach(r => r.el?.classList.remove('ok'));

    showToast('Contraseña actualizada correctamente', 'success');
  });

  /* ─────────────────────────────────────
     11. CERRAR SESIONES
  ───────────────────────────────────────── */
  document.querySelectorAll('.si-close').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.sesion-item');
      const name = item.querySelector('.si-name').textContent.trim();
      if (confirm(`¿Cerrar sesión en "${name}"?`)) {
        item.style.opacity = '0';
        item.style.transition = 'opacity 0.3s';
        setTimeout(() => item.remove(), 300);
        showToast(`Sesión cerrada en ${name}`, 'success');
      }
    });
  });

  document.getElementById('btnCerrarTodas')?.addEventListener('click', async () => {
    if (!confirm('¿Cerrar todas las sesiones excepto esta?')) return;
    document.querySelectorAll('.sesion-item:not(.current)').forEach(item => {
      item.style.opacity = '0';
      item.style.transition = 'opacity 0.3s';
      setTimeout(() => item.remove(), 300);
    });
    await new Promise(r => setTimeout(r, 400));
    showToast('Todas las demás sesiones fueron cerradas', 'success');
  });

  /* ─────────────────────────────────────
     12. TOGGLE 2FA
  ───────────────────────────────────────── */
  document.getElementById('toggle2FA')?.addEventListener('change', function() {
    const info   = document.getElementById('twofaInfo');
    const active = document.getElementById('twofaActive');
    if (this.checked) {
      info.style.display   = 'none';
      active.style.display = 'block';
      showToast('Autenticación en dos pasos activada', 'success');
    } else {
      info.style.display   = 'block';
      active.style.display = 'none';
      showToast('Autenticación en dos pasos desactivada', 'info');
    }
  });

  /* ─────────────────────────────────────
     13. NO MOLESTAR
  ───────────────────────────────────────── */
  document.getElementById('toggleNoMolestar')?.addEventListener('change', function() {
    const horario = document.getElementById('nmHorario');
    horario.style.display = this.checked ? 'block' : 'none';
  });

  /* ─────────────────────────────────────
     14. GUARDAR NOTIFICACIONES
  ───────────────────────────────────────── */
  document.getElementById('btnSaveNoti')?.addEventListener('click', async () => {
    const btn = document.getElementById('btnSaveNoti');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';
    btn.disabled  = true;
    await new Promise(r => setTimeout(r, 1000));
    btn.innerHTML = '<i class="fas fa-save"></i> Guardar preferencias';
    btn.disabled  = false;
    showToast('Preferencias de notificaciones guardadas', 'success');
  });

  /* ─────────────────────────────────────
     15. FORMACIÓN ACADÉMICA
  ───────────────────────────────────────── */
  const modalFormacion = document.getElementById('modalFormacion');

  document.getElementById('btnAddFormacion')?.addEventListener('click', () => {
    modalFormacion.classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('mfTitulo').value = '';
    document.getElementById('mfInst').value   = '';
    document.getElementById('mfAnio').value   = '';
  });

  const closeMF = () => { modalFormacion.classList.remove('open'); document.body.style.overflow = ''; };
  document.getElementById('mfClose')?.addEventListener('click', closeMF);
  document.getElementById('mfCancel')?.addEventListener('click', closeMF);
  modalFormacion?.addEventListener('click', e => { if (e.target === modalFormacion) closeMF(); });

  document.getElementById('mfConfirm')?.addEventListener('click', () => {
    const titulo = document.getElementById('mfTitulo').value.trim();
    const inst   = document.getElementById('mfInst').value.trim();
    const anio   = document.getElementById('mfAnio').value.trim();
    const nivel  = document.getElementById('mfNivel').value;

    const errTitulo = document.getElementById('err-mfTitulo');
    if (!titulo) {
      errTitulo.textContent = 'Este campo es obligatorio';
      errTitulo.classList.add('show'); return;
    }
    errTitulo.textContent = ''; errTitulo.classList.remove('show');

    const list = document.getElementById('pfFormacionList');
    const item = document.createElement('div');
    item.className = 'pf-formacion-item';
    item.innerHTML = `
      <div class="pfi-icon"><i class="fas fa-graduation-cap"></i></div>
      <div class="pfi-data">
        <p class="pfi-titulo">${titulo}</p>
        <p class="pfi-inst">${inst}${anio ? ' · ' + anio : ''}</p>
      </div>
      <button class="pfi-del" title="Eliminar"><i class="fas fa-times"></i></button>
    `;
    item.querySelector('.pfi-del').addEventListener('click', () => {
      item.remove();
      showToast('Título eliminado', 'info');
    });
    list.appendChild(item);
    closeMF();
    showToast(`Título "${titulo}" agregado`, 'success');
  });

  // Eliminar formación existente
  document.querySelectorAll('.pfi-del').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.pf-formacion-item').remove();
      showToast('Título eliminado', 'info');
    });
  });

  /* ─────────────────────────────────────
     16. ESC cierra modales
  ───────────────────────────────────────── */
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(m => {
        m.classList.remove('open'); document.body.style.overflow = '';
      });
    }
  });

  /* ─────────────────────────────────────
     17. TOAST
  ───────────────────────────────────────── */
  window.showToast = function(msg, type = 'info') {
    const container = document.getElementById('toastContainer');
    const icons = { success:'fa-check-circle', info:'fa-info-circle', error:'fa-exclamation-circle' };
    const toast = document.createElement('div');
    toast.className = `pf-toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type]||icons.info}"></i><span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(16px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  };

});