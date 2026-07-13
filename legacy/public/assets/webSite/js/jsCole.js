/* =============================================
   COLEGIO SAN CRISTÓBAL — SCRIPT PRINCIPAL
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ==========================================
     1. NAVBAR — scroll effect + hamburger
     ========================================== */
  const navbar    = document.getElementById('navbar');
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');
  const overlay   = createOverlay();

  // Scroll: agrega/quita clase 'scrolled'
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
  });

  // Hamburger toggle
  hamburger.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('open');
    hamburger.classList.toggle('active', isOpen);
    overlay.classList.toggle('visible', isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
  });

  // Cerrar al hacer click en un link
  navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  // Cerrar con overlay
  overlay.addEventListener('click', closeMenu);

  function createOverlay() {
    const el = document.createElement('div');
    el.style.cssText = `
      position:fixed;inset:0;background:rgba(0,0,0,0.4);
      z-index:998;opacity:0;pointer-events:none;
      transition:opacity 0.3s ease;
    `;
    el.addEventListener('transitionend', () => {
      if (!el.classList.contains('visible')) el.style.pointerEvents = 'none';
    });
    document.body.appendChild(el);
    return el;
  }

  overlay.classList.constructor.prototype; // no-op
  const origToggle = overlay.classList.toggle.bind(overlay.classList);
  Object.defineProperty(overlay, '_toggle', { value: origToggle });

  // Parche simple para que el overlay sea accesible
  const style = document.createElement('style');
  style.textContent = `
    div[style*="position:fixed"][style*="inset:0"].visible {
      opacity:1 !important;
      pointer-events:auto !important;
    }
  `;
  document.head.appendChild(style);

  function closeMenu() {
    navLinks.classList.remove('open');
    hamburger.classList.remove('active');
    overlay.classList.remove('visible');
    document.body.style.overflow = '';
  }

  // Active nav-link en scroll
  const sections  = document.querySelectorAll('section[id]');
  const navItems  = document.querySelectorAll('.nav-link');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(sec => {
      if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
    });
    navItems.forEach(link => {
      link.classList.toggle('active', link.getAttribute('href') === `#${current}`);
    });
  });

  /* ==========================================
     2. SCROLL REVEAL — intersection observer
     ========================================== */
  const revealTargets = document.querySelectorAll(
    '.info-card, .noticia-card, .galeria-item, .contacto-card, ' +
    '.valor, .stat, .registro-form-wrap, .registro-info, .nosotros-text'
  );

  revealTargets.forEach(el => el.classList.add('reveal'));

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        // Escalonado por índice para efecto cascada
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, (entry.target.dataset.delay || 0));
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  // Asignar delay escalonado a grupos
  document.querySelectorAll('.noticias-grid .noticia-card').forEach((el, i) => {
    el.dataset.delay = i * 120;
  });
  document.querySelectorAll('.galeria-grid .galeria-item').forEach((el, i) => {
    el.dataset.delay = i * 80;
  });
  document.querySelectorAll('.contacto-card').forEach((el, i) => {
    el.dataset.delay = i * 100;
  });
  document.querySelectorAll('.valor').forEach((el, i) => {
    el.dataset.delay = i * 80;
  });

  revealTargets.forEach(el => observer.observe(el));

  /* ==========================================
     3. CONTADOR ANIMADO en hero-stats
     ========================================== */
  const statsSection = document.querySelector('.hero-stats');
  let counted = false;

  const counterObserver = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !counted) {
      counted = true;
      animateCounters();
    }
  }, { threshold: 0.5 });

  if (statsSection) counterObserver.observe(statsSection);

  function animateCounters() {
    const counters = [
      { el: document.querySelector('.stat:nth-child(1) strong'), target: 1200, suffix: '+' },
      { el: document.querySelector('.stat:nth-child(3) strong'), target: 85,   suffix: '+' },
      { el: document.querySelector('.stat:nth-child(5) strong'), target: 38,   suffix: ''  },
    ];
    counters.forEach(({ el, target, suffix }) => {
      if (!el) return;
      let start = 0;
      const duration = 1800;
      const step = target / (duration / 16);
      const timer = setInterval(() => {
        start = Math.min(start + step, target);
        el.textContent = Math.floor(start).toLocaleString('es-CO') + suffix;
        if (start >= target) clearInterval(timer);
      }, 16);
    });
  }

  /* ==========================================
     4. FORMULARIO DE REGISTRO
     ========================================== */
  const form        = document.getElementById('registroForm');
  const formSuccess = document.getElementById('formSuccess');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      // Validación básica
      const inputs = form.querySelectorAll('[required]');
      let valid = true;

      inputs.forEach(input => {
        clearError(input);
        if (!input.value.trim()) {
          showError(input, 'Este campo es obligatorio');
          valid = false;
        } else if (input.type === 'email' && !isValidEmail(input.value)) {
          showError(input, 'Ingresa un correo válido');
          valid = false;
        }
      });

      if (!valid) return;

      // Simular envío
      const btn = form.querySelector('.btn-submit');
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
      btn.disabled = true;

      setTimeout(() => {
        btn.style.display = 'none';
        formSuccess.classList.add('show');
        form.reset();
        setTimeout(() => {
          btn.style.display = 'flex';
          btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar solicitud';
          btn.disabled = false;
          formSuccess.classList.remove('show');
        }, 5000);
      }, 1500);
    });

    // Limpiar error al escribir
    form.querySelectorAll('input, select, textarea').forEach(input => {
      input.addEventListener('input', () => clearError(input));
    });
  }

  function showError(input, msg) {
    input.style.borderColor = '#e53e3e';
    input.style.boxShadow   = '0 0 0 3px rgba(229,62,62,0.12)';
    let span = input.parentNode.querySelector('.error-msg');
    if (!span) {
      span = document.createElement('span');
      span.className = 'error-msg';
      span.style.cssText = 'color:#e53e3e;font-size:0.75rem;margin-top:4px;display:block;';
      input.parentNode.appendChild(span);
    }
    span.textContent = msg;
  }

  function clearError(input) {
    input.style.borderColor = '';
    input.style.boxShadow   = '';
    const span = input.parentNode.querySelector('.error-msg');
    if (span) span.remove();
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  /* ==========================================
     5. GALERÍA — lightbox simple
     ========================================== */
  const galeriaItems = document.querySelectorAll('.galeria-item');
  galeriaItems.forEach(item => {
    item.addEventListener('click', () => {
      const label = item.querySelector('p')?.textContent || 'Imagen';
      openLightbox(label);
    });
    item.setAttribute('tabindex', '0');
    item.setAttribute('role', 'button');
    item.setAttribute('aria-label', 'Ver imagen ampliada');
    item.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') item.click();
    });
  });

  function openLightbox(label) {
    const lb = document.createElement('div');
    lb.style.cssText = `
      position:fixed;inset:0;background:rgba(0,0,0,0.85);
      display:flex;align-items:center;justify-content:center;
      z-index:9999;animation:fadeIn 0.25s ease;cursor:pointer;
    `;
    lb.innerHTML = `
      <div style="text-align:center;color:white;padding:20px;">
        <div style="width:600px;max-width:90vw;height:380px;background:rgba(255,255,255,0.08);
          border-radius:16px;display:flex;align-items:center;justify-content:center;
          flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.15);">
          <i class="fas fa-image" style="font-size:3rem;opacity:0.4;"></i>
          <p style="opacity:0.7;font-size:0.9rem;">${label}</p>
        </div>
        <p style="margin-top:20px;opacity:0.5;font-size:0.8rem;">Click para cerrar</p>
      </div>
    `;
    document.body.appendChild(lb);
    document.body.style.overflow = 'hidden';
    lb.addEventListener('click', () => {
      lb.remove();
      document.body.style.overflow = '';
    });
  }

  /* ==========================================
     6. SMOOTH SCROLL con offset para navbar
     ========================================== */
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', (e) => {
      const target = document.querySelector(link.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      const offset = navbar.offsetHeight + 16;
      const top = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: 'smooth' });
    });
  });

  /* ==========================================
     7. CSS animation para lightbox
     ========================================== */
  const anim = document.createElement('style');
  anim.textContent = `
    @keyframes fadeIn {
      from { opacity: 0; }
      to   { opacity: 1; }
    }
    .nav-link.active {
      color: var(--verde-principal) !important;
      background: var(--verde-claro) !important;
      border-radius: 8px;
    }
  `;
  document.head.appendChild(anim);

});