/* =============================================
   COLEGIO SAN CRISTÓBAL — CMS EDITOR JS
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─────────────────────────────────────
     1. TABS DE SECCIÓN (topbar)
  ───────────────────────────────────────── */
  const sectionTabs = document.querySelectorAll('.ct-stab[data-section]');
  const sectionEditors = document.querySelectorAll('.section-editor');

  const sectionMeta = {
    inicio:    { icon:'fas fa-home',          title:'Sección Inicio',    desc:'Edita el hero, badge, estadísticas y botones.' },
    nosotros:  { icon:'fas fa-users',          title:'Sección Nosotros',  desc:'Edita misión, visión, valores e imagen del equipo.' },
    noticias:  { icon:'fas fa-newspaper',      title:'Noticias y Eventos',desc:'Agrega, edita u oculta noticias del sitio.' },
    galeria:   { icon:'fas fa-images',         title:'Galería de Fotos',  desc:'Administra las imágenes de la galería.' },
    contacto:  { icon:'fas fa-envelope',       title:'Contacto',          desc:'Edita dirección, teléfonos, horarios y redes.' },
  };

  sectionTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const section = tab.dataset.section;
      if (!section) return;

      sectionTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      sectionEditors.forEach(ed => ed.classList.remove('active'));
      const target = document.getElementById(`editor-${section}`);
      if (target) target.classList.add('active');

      // Actualizar header del panel
      const meta = sectionMeta[section];
      if (meta) {
        document.getElementById('ephTitle').textContent = meta.title;
        document.getElementById('ephDesc').textContent  = meta.desc;
        document.getElementById('ephIcon').innerHTML    = `<i class="${meta.icon}"></i>`;
      }

      // Scroll al inicio del editor
      document.querySelector('.editor-scroll')?.scrollTo({ top:0, behavior:'smooth' });

      // Resaltar sección en preview (simulado)
      highlightSection(section);
    });
  });

  function highlightSection(section) {
    // En producción esto manejaría postMessage al iframe
    showToast(`Vista: ${section}`, 'info');
  }

  /* ─────────────────────────────────────
     2. TABS DEL PANEL EDITOR
  ───────────────────────────────────────── */
  const etabs      = document.querySelectorAll('.etab');
  const etabPanels = document.querySelectorAll('.etab-panel');

  etabs.forEach(tab => {
    tab.addEventListener('click', () => {
      etabs.forEach(t => t.classList.remove('active'));
      etabPanels.forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      const panel = document.getElementById(`etab-${tab.dataset.etab}`);
      if (panel) panel.classList.add('active');
    });
  });

  /* ─────────────────────────────────────
     3. COLAPSAR BLOQUES
  ───────────────────────────────────────── */
  document.querySelectorAll('.eb-collapse').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const body = btn.closest('.editor-block').querySelector('.eb-body');
      const isCollapsed = body.classList.toggle('collapsed');
      btn.classList.toggle('collapsed', isCollapsed);
    });
  });

  // Click en header también colapsa
  document.querySelectorAll('.eb-header').forEach(header => {
    header.addEventListener('click', () => {
      const btn  = header.querySelector('.eb-collapse');
      const body = header.closest('.editor-block').querySelector('.eb-body');
      const isCollapsed = body.classList.toggle('collapsed');
      btn?.classList.toggle('collapsed', isCollapsed);
    });
  });

  /* ─────────────────────────────────────
     4. PREVIEW EN VIVO — campos CMS
  ───────────────────────────────────────── */
  let updateTimer = null;

  document.querySelectorAll('.cms-field').forEach(field => {
    field.addEventListener('input', () => {
      clearTimeout(updateTimer);
      updateTimer = setTimeout(() => {
        triggerLiveUpdate(field);
      }, 400);
    });
  });

  function triggerLiveUpdate(field) {
    const iframe = document.getElementById('sitePreview');
    const key    = field.dataset.field;
    const value  = field.value;

    // Mostrar indicador "guardando…"
    showSavingIndicator();

    // En producción: postMessage al iframe o llamada API
    try {
      if (iframe && iframe.contentDocument) {
        const doc = iframe.contentDocument;

        if (key === 'hero_titulo') {
          const el = doc.querySelector('.hero-title');
          if (el) el.innerHTML = value;
        }
        if (key === 'hero_desc') {
          const el = doc.querySelector('.hero-desc');
          if (el) el.textContent = value;
        }
        if (key === 'hero_badge') {
          const el = doc.querySelector('.hero-badge');
          if (el) el.textContent = value;
        }
        if (key === 'hero_btn1') {
          const el = doc.querySelector('.btn-primary');
          if (el) el.textContent = value;
        }
      }
    } catch(e) {
      // iframe cross-origin — en producción se usa postMessage
    }
  }

  function showSavingIndicator() {
    const bar = document.querySelector('.ct-btn-save');
    const orig = bar.innerHTML;
    bar.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Guardando…';
    bar.style.opacity = '0.7';
    setTimeout(() => {
      bar.innerHTML = orig;
      bar.style.opacity = '';
    }, 800);
  }

  /* ─────────────────────────────────────
     5. UPLOAD DE IMÁGENES
  ───────────────────────────────────────── */
  window.triggerUpload = function(input) {
    const el = typeof input === 'string'
      ? document.getElementById(input)
      : input;
    if (el) el.click();
  };

  // Todos los inputs de archivo
  document.querySelectorAll('input[type="file"].ef-file-hidden').forEach(input => {
    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;

      const targetId = input.dataset.target;
      const preview  = targetId ? document.getElementById(targetId) : input.previousElementSibling?.previousElementSibling;

      if (preview && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          preview.innerHTML = `<img src="${e.target.result}" alt="Preview" />`;
          // Mostrar botón quitar si existe
          const removeBtn = input.parentElement.querySelector('.eiu-remove');
          if (removeBtn) removeBtn.style.display = 'flex';
        };
        reader.readAsDataURL(file);
        showToast(`Imagen "${file.name}" lista para subir`, 'success');
      }
    });
  });

  // Galería — imágenes individuales
  document.querySelectorAll('.egg-input').forEach(input => {
    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      const preview = input.closest('.egg-item').querySelector('.egg-preview');
      const reader = new FileReader();
      reader.onload = e => { preview.innerHTML = `<img src="${e.target.result}" alt="" />`; };
      reader.readAsDataURL(file);
      showToast('Foto de galería actualizada', 'success');
    });
  });

  // Agregar foto a galería
  const galleryNewImg = document.getElementById('galleryNewImg');
  galleryNewImg?.addEventListener('change', () => {
    const files = Array.from(galleryNewImg.files);
    const grid  = document.getElementById('galleryGrid');
    const addBtn= document.getElementById('galleryAddBtn');

    files.forEach(file => {
      const item = document.createElement('div');
      item.className = 'egg-item';
      const reader = new FileReader();
      reader.onload = e => {
        item.innerHTML = `
          <div class="egg-preview"><img src="${e.target.result}" alt="" /></div>
          <div class="egg-actions">
            <input type="file" accept="image/*" class="ef-file-hidden egg-input" />
            <button class="egg-btn"><i class="fas fa-upload"></i></button>
            <button class="egg-btn red egg-del"><i class="fas fa-trash"></i></button>
          </div>
          <input type="text" class="egg-caption ef-input" placeholder="Descripción…" value="${file.name.split('.')[0]}" />
        `;
        grid.insertBefore(item, addBtn);
        attachGalleryItemEvents(item);
      };
      reader.readAsDataURL(file);
    });
    showToast(`${files.length} foto${files.length!==1?'s':''} agregada${files.length!==1?'s':''}`, 'success');
  });

  function attachGalleryItemEvents(item) {
    const delBtn = item.querySelector('.egg-del');
    delBtn?.addEventListener('click', () => {
      if (confirm('¿Eliminar esta foto de la galería?')) {
        item.style.opacity = '0';
        item.style.transition = 'opacity 0.3s';
        setTimeout(() => item.remove(), 300);
        showToast('Foto eliminada', 'success');
      }
    });
  }

  // Eventos en fotos existentes
  document.querySelectorAll('.egg-del').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.egg-item');
      if (confirm('¿Eliminar esta foto?')) {
        item.style.opacity = '0';
        item.style.transition = 'opacity 0.3s';
        setTimeout(() => item.remove(), 300);
        showToast('Foto eliminada', 'success');
      }
    });
  });

  /* ─────────────────────────────────────
     6. TOGGLE VISIBILIDAD DE SECCIÓN
  ───────────────────────────────────────── */
  document.getElementById('sectionVisible')?.addEventListener('change', function() {
    const section = document.querySelector('.ct-stab.active')?.dataset.section || 'inicio';
    const label   = document.querySelector('.eph-toggle-label');
    if (this.checked) {
      label.textContent = 'Visible';
      showToast(`Sección "${section}" visible en el sitio`, 'success');
    } else {
      label.textContent = 'Oculta';
      showToast(`Sección "${section}" ocultada del sitio`, 'info');
    }
  });

  /* ─────────────────────────────────────
     7. DISPOSITIVO DE PREVIEW
  ───────────────────────────────────────── */
  const devBtns = document.querySelectorAll('.ct-dev-btn');
  const iframe  = document.getElementById('sitePreview');

  devBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      devBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const device = btn.dataset.device;
      iframe.className = `preview-iframe ${device}`;

      const wrap = document.getElementById('previewDeviceWrap');
      if (device === 'desktop') {
        wrap.style.alignItems = 'flex-start';
        wrap.style.padding = '16px';
      } else {
        wrap.style.alignItems = 'flex-start';
        wrap.style.padding = '16px';
      }
    });
  });

  /* ─────────────────────────────────────
     8. DIVISOR REDIMENSIONABLE
  ───────────────────────────────────────── */
  const divider    = document.getElementById('cmsDivider');
  const previewCol = document.getElementById('cmsPreviewCol');
  const editorCol  = document.getElementById('cmsEditorCol');
  const layout     = document.querySelector('.cms-layout');

  let isDragging = false;

  divider.addEventListener('mousedown', e => {
    isDragging = true;
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
    e.preventDefault();
  });

  document.addEventListener('mousemove', e => {
    if (!isDragging) return;
    const rect = layout.getBoundingClientRect();
    const offset = e.clientX - rect.left;
    const totalW = rect.width - 8; // restamos el divisor

    const previewW = Math.max(280, Math.min(offset, totalW - 300));
    const editorW  = totalW - previewW;

    previewCol.style.flex = 'none';
    previewCol.style.width = previewW + 'px';
    editorCol.style.width = editorW + 'px';
  });

  document.addEventListener('mouseup', () => {
    if (isDragging) {
      isDragging = false;
      document.body.style.cursor = '';
      document.body.style.userSelect = '';
    }
  });

  /* ─────────────────────────────────────
     9. NOTICIAS — agregar / ordenar
  ───────────────────────────────────────── */
  let noticiasCount = 2;

  document.getElementById('btnAddNoticia')?.addEventListener('click', () => {
    noticiasCount++;
    const list = document.getElementById('newsList');
    const item = document.createElement('div');
    item.className = 'ef-news-item';
    item.dataset.id = noticiasCount;
    item.innerHTML = `
      <div class="eni-header">
        <span class="eni-num">Noticia ${noticiasCount}</span>
        <div class="eni-controls">
          <button class="eni-btn" title="Mover arriba"><i class="fas fa-arrow-up"></i></button>
          <button class="eni-btn" title="Mover abajo"><i class="fas fa-arrow-down"></i></button>
          <button class="eni-btn red" title="Eliminar"><i class="fas fa-trash"></i></button>
        </div>
      </div>
      <div class="ef-group">
        <label class="ef-label">Título</label>
        <input type="text" class="ef-input cms-field" placeholder="Título de la noticia…" />
      </div>
      <div class="ef-group">
        <label class="ef-label">Resumen</label>
        <textarea class="ef-input ef-textarea cms-field" rows="2" placeholder="Descripción breve…"></textarea>
      </div>
      <div class="ef-row">
        <div class="ef-group ef-col-2">
          <label class="ef-label">Categoría</label>
          <select class="ef-input ef-select cms-field">
            <option>General</option><option>Logros</option>
            <option>Cultura</option><option>Ciencia</option><option>Deporte</option>
          </select>
        </div>
        <div class="ef-group ef-col-2">
          <label class="ef-label">Fecha</label>
          <input type="date" class="ef-input cms-field" value="${new Date().toISOString().split('T')[0]}" />
        </div>
      </div>
    `;

    // Botón eliminar
    item.querySelector('.eni-btn.red').addEventListener('click', () => {
      if (confirm('¿Eliminar esta noticia?')) {
        item.style.opacity = '0'; item.style.transition = 'opacity 0.3s';
        setTimeout(() => item.remove(), 300);
        showToast('Noticia eliminada', 'success');
      }
    });

    list.appendChild(item);
    item.scrollIntoView({ behavior:'smooth', block:'nearest' });
    showToast(`Noticia ${noticiasCount} agregada`, 'success');
  });

  // Eliminar noticias existentes
  document.querySelectorAll('.eni-btn.red').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.ef-news-item');
      if (confirm('¿Eliminar esta noticia?')) {
        item.style.opacity = '0'; item.style.transition = 'opacity 0.3s';
        setTimeout(() => item.remove(), 300);
        showToast('Noticia eliminada', 'success');
      }
    });
  });

  /* ─────────────────────────────────────
     10. COLORES DEL TEMA
  ───────────────────────────────────────── */
  document.querySelectorAll('.ef-color').forEach(colorInput => {
    const hexInput = colorInput.parentElement.querySelector('.ef-color-hex');

    colorInput.addEventListener('input', () => {
      if (hexInput) hexInput.value = colorInput.value;
      applyColorToPreview(colorInput.id, colorInput.value);
    });

    hexInput?.addEventListener('input', () => {
      if (/^#[0-9a-fA-F]{6}$/.test(hexInput.value)) {
        colorInput.value = hexInput.value;
        applyColorToPreview(colorInput.id, hexInput.value);
      }
    });
  });

  function applyColorToPreview(colorId, value) {
    const varMap = {
      colorPrincipal: '--verde-principal',
      colorOscuro:    '--verde-oscuro',
      colorAcento:    '--dorado',
      colorFondo:     '--gris-claro',
    };
    const varName = varMap[colorId];
    if (varName) {
      try {
        const iframeDoc = document.getElementById('sitePreview')?.contentDocument;
        if (iframeDoc) iframeDoc.documentElement.style.setProperty(varName, value);
      } catch(e) { /* cross-origin */ }
    }
  }

  // Paletas predefinidas
  document.querySelectorAll('.ef-palette').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.ef-palette').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const colors = JSON.parse(btn.dataset.colors);
      const ids = ['colorPrincipal','colorOscuro','colorAcento','colorFondo'];

      ids.forEach((id, i) => {
        const colorInp = document.getElementById(id);
        const hexInp   = colorInp?.parentElement.querySelector('.ef-color-hex');
        if (colorInp && colors[i]) {
          colorInp.value = colors[i];
          if (hexInp) hexInp.value = colors[i];
          applyColorToPreview(id, colors[i]);
        }
      });
      showToast('Paleta de colores aplicada', 'success');
    });
  });

  /* ─────────────────────────────────────
     11. BOTÓN AGREGAR SECCIÓN
  ───────────────────────────────────────── */
  const modalAddSection = document.getElementById('modalAddSection');
  document.getElementById('btnAddSection')?.addEventListener('click', () => {
    modalAddSection.classList.add('open');
    document.body.style.overflow = 'hidden';
  });
  document.getElementById('masClose')?.addEventListener('click', () => {
    modalAddSection.classList.remove('open'); document.body.style.overflow = '';
  });
  document.getElementById('masCancel')?.addEventListener('click', () => {
    modalAddSection.classList.remove('open'); document.body.style.overflow = '';
  });
  modalAddSection?.addEventListener('click', e => { if (e.target === modalAddSection) { modalAddSection.classList.remove('open'); document.body.style.overflow = ''; }});

  // Tipos de sección
  document.querySelectorAll('.mas-type').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.mas-type').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

  document.getElementById('masConfirm')?.addEventListener('click', () => {
    const name = document.getElementById('masSectionName').value.trim();
    if (!name) { showToast('Escribe el nombre de la sección', 'error'); return; }

    // Agregar tab al topbar
    const tabsBar = document.getElementById('ctSectionTabs');
    const addBtn  = document.getElementById('btnAddSection');
    const newTab  = document.createElement('button');
    newTab.className = 'ct-stab';
    newTab.dataset.section = name.toLowerCase().replace(/\s+/g, '-');
    newTab.innerHTML = `<i class="fas fa-star"></i> ${name}`;
    tabsBar.insertBefore(newTab, addBtn);

    // Crear editor vacío
    const editorWrap = document.querySelector('.section-editor.active')?.parentElement;
    const newEditor  = document.createElement('div');
    newEditor.className = 'section-editor';
    newEditor.id = `editor-${newTab.dataset.section}`;
    newEditor.innerHTML = `
      <div class="editor-block">
        <div class="eb-header">
          <div class="eb-icon"><i class="fas fa-star"></i></div>
          <h3 class="eb-title">${name}</h3>
          <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
        </div>
        <div class="eb-body">
          <div class="ef-group">
            <label class="ef-label">Título de la sección</label>
            <input type="text" class="ef-input cms-field" value="${name}" />
          </div>
          <div class="ef-group">
            <label class="ef-label">Contenido</label>
            <textarea class="ef-input ef-textarea cms-field" rows="4" placeholder="Escribe el contenido de esta sección…"></textarea>
          </div>
          <div class="ef-group">
            <label class="ef-label">Imagen</label>
            <div class="ef-img-upload">
              <div class="eiu-preview"><div class="eiu-placeholder"><i class="fas fa-image"></i><span>Sin imagen</span></div></div>
              <div class="eiu-actions"><button class="eiu-btn"><i class="fas fa-upload"></i> Subir imagen</button></div>
            </div>
          </div>
        </div>
      </div>
    `;
    editorWrap?.appendChild(newEditor);

    // Activar el nuevo tab
    newTab.click();
    modalAddSection.classList.remove('open');
    document.body.style.overflow = '';
    showToast(`Sección "${name}" creada`, 'success');
  });

  /* ─────────────────────────────────────
     12. MODAL PERMISOS
  ───────────────────────────────────────── */
  const modalPermisos = document.getElementById('modalPermisos');
  document.getElementById('btnPermisos')?.addEventListener('click', () => {
    modalPermisos.classList.add('open'); document.body.style.overflow = 'hidden';
  });
  document.getElementById('mpClose')?.addEventListener('click', () => {
    modalPermisos.classList.remove('open'); document.body.style.overflow = '';
  });
  document.getElementById('mpCancel')?.addEventListener('click', () => {
    modalPermisos.classList.remove('open'); document.body.style.overflow = '';
  });
  modalPermisos?.addEventListener('click', e => { if(e.target === modalPermisos){modalPermisos.classList.remove('open');document.body.style.overflow = '';} });

  document.getElementById('btnAssignPerm')?.addEventListener('click', () => {
    const docSelect = document.getElementById('mpDocente');
    const docName   = docSelect.options[docSelect.selectedIndex]?.text;
    if (!docSelect.value) { showToast('Selecciona un docente', 'error'); return; }

    const checks  = [...document.querySelectorAll('#mpPermChecks input:checked')].map(c => c.parentElement.textContent.trim());
    if (!checks.length) { showToast('Selecciona al menos un permiso', 'error'); return; }

    // Agregar a lista activa
    const list = document.getElementById('mpActivePerms');
    const initials = docName.split(' ').slice(0,2).map(w => w[0]).join('').toUpperCase();
    const item = document.createElement('div');
    item.className = 'mpap-item';
    item.innerHTML = `
      <div class="mpap-av" style="background:#e8f5ee;color:#2d7a4f">${initials}</div>
      <div class="mpap-info">
        <p class="mpap-name">${docName.split('—')[0].trim()}</p>
        <p class="mpap-perms">${checks.join(', ')}</p>
      </div>
      <button class="mpap-remove" title="Revocar"><i class="fas fa-times"></i></button>
    `;
    item.querySelector('.mpap-remove').addEventListener('click', () => {
      item.remove(); showToast('Permiso revocado', 'info');
    });
    list.appendChild(item);
    showToast(`Permisos asignados a ${docName.split('—')[0].trim()}`, 'success');
    docSelect.value = '';
    document.querySelectorAll('#mpPermChecks input').forEach(c => c.checked = false);
  });

  document.getElementById('mpSave')?.addEventListener('click', () => {
    modalPermisos.classList.remove('open'); document.body.style.overflow = '';
    showToast('Configuración de permisos guardada', 'success');
  });

  // Revocar permisos existentes
  document.querySelectorAll('.mpap-remove').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.mpap-item').remove();
      showToast('Permiso revocado', 'info');
    });
  });

  /* ─────────────────────────────────────
     13. PUBLICAR CAMBIOS
  ───────────────────────────────────────── */
  document.getElementById('btnSaveCMS')?.addEventListener('click', async () => {
    const btn = document.getElementById('btnSaveCMS');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publicando…';
    btn.disabled  = true;

    await new Promise(r => setTimeout(r, 1800));

    btn.innerHTML = '<i class="fas fa-check"></i> Publicado';
    btn.style.background = '#4a9e6b';
    setTimeout(() => {
      btn.innerHTML = '<i class="fas fa-save"></i> Publicar cambios';
      btn.style.background = '';
      btn.disabled  = false;
    }, 2500);

    showToast('¡Cambios publicados en el sitio exitosamente!', 'success');
  });

  /* ─────────────────────────────────────
     14. VISTA PREVIA COMPLETA
  ───────────────────────────────────────── */
  document.getElementById('btnPreviewFull')?.addEventListener('click', () => {
    window.open('<?= BASE_URL ?>/', '_blank');
});

  /* ─────────────────────────────────────
     15. BOTÓN IA — sugerir texto
  ───────────────────────────────────────── */
  document.querySelectorAll('.ef-ai-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.ef-input-wrap').querySelector('.ef-input');
      if (!input) return;

      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
      btn.disabled = true;

      const sugerencias = [
        'Formamos líderes con valores y excelencia académica',
        'Educamos con pasión, innovación y propósito',
        'Construyendo el futuro de Colombia desde 1985',
        'Comprometidos con la educación de calidad para todos',
      ];

      setTimeout(() => {
        input.value = sugerencias[Math.floor(Math.random() * sugerencias.length)];
        input.dispatchEvent(new Event('input'));
        btn.innerHTML = '<i class="fas fa-magic"></i>';
        btn.disabled = false;
        showToast('Sugerencia de IA aplicada', 'info');
      }, 900);
    });
  });

  /* ─────────────────────────────────────
     16. HISTORIAL — restaurar
  ───────────────────────────────────────── */
  document.querySelectorAll('.hi-restore').forEach(btn => {
    btn.addEventListener('click', () => {
      const desc = btn.closest('.hist-item').querySelector('.hi-desc').textContent;
      if (confirm(`¿Restaurar "${desc}"?`)) {
        showToast(`Versión restaurada: ${desc}`, 'success');
      }
    });
  });

  /* ─────────────────────────────────────
     17. TOAST
  ───────────────────────────────────────── */
  window.showToast = function(msg, type = 'info') {
    const container = document.getElementById('toastContainer');
    const icons = { success:'fa-check-circle', info:'fa-info-circle', error:'fa-exclamation-circle' };
    const toast = document.createElement('div');
    toast.className = `cms-toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type]||icons.info}"></i><span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(16px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  };

  /* Escape cierra modales */
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(m => {
        m.classList.remove('open');
        document.body.style.overflow = '';
      });
    }
  });

});