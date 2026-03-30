<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Colegio San Cristóbal | Educando el Futuro</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/webSite/css/styleCole.css" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

  <!-- ========== NAVBAR ========== -->
  <nav class="navbar" id="navbar">
    <div class="nav-container">
      <a href="#inicio" class="nav-logo">
        <div class="logo-icon">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <span class="logo-text">San <strong>Cristóbal</strong></span>
      </a>

      <button class="hamburger" id="hamburger" aria-label="Menú">
        <span></span><span></span><span></span>
      </button>

      <ul class="nav-links" id="navLinks">
        <li><a href="#inicio" class="nav-link">Inicio</a></li>
        <li><a href="#nosotros" class="nav-link">Nosotros</a></li>
        <li><a href="#noticias" class="nav-link">Noticias</a></li>
        <li><a href="#galeria" class="nav-link">Galería</a></li>
        <li><a href="#contacto" class="nav-link">Contacto</a></li>
        <li><a href="<?= BASE_URL ?>login" class="btn-ingresar">Ingresar</a></li>
      </ul>
    </div>
  </nav>

  <!-- ========== HERO ========== -->
  <section class="hero" id="inicio">
    <div class="hero-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>
    <div class="hero-content">
      <span class="hero-badge">🌿 Desde 1985 · Bogotá, Colombia</span>
      <h1 class="hero-title">Educamos con <span class="highlight">pasión</span> y propósito</h1>
      <p class="hero-desc">En el Colegio San Cristóbal formamos personas íntegras, creativas y comprometidas con su entorno. Un ambiente seguro, moderno y lleno de oportunidades.</p>
      <div class="hero-buttons">
        <a href="#registro" class="btn-primary">Inscríbete ahora</a>
        <a href="#nosotros" class="btn-outline">Conócenos <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="hero-stats">
        <div class="stat"><strong>1,200+</strong><span>Estudiantes</span></div>
        <div class="stat-divider"></div>
        <div class="stat"><strong>85+</strong><span>Docentes</span></div>
        <div class="stat-divider"></div>
        <div class="stat"><strong>38</strong><span>Años de historia</span></div>
      </div>
    </div>
    <div class="hero-image-wrap">
      <div class="hero-image">
        <div class="img-placeholder">
          <i class="fas fa-school"></i>
          <p>Foto del colegio</p>
        </div>
      </div>
      <div class="floating-card card-1">
        <i class="fas fa-star"></i>
        <span>Mejor colegio 2023</span>
      </div>
      <div class="floating-card card-2">
        <i class="fas fa-award"></i>
        <span>Acreditado MEN</span>
      </div>
    </div>
  </section>

  <!-- ========== SOBRE NOSOTROS ========== -->
  <section class="nosotros" id="nosotros">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Quiénes somos</span>
        <h2>Una comunidad educativa <span class="highlight">que crece junta</span></h2>
      </div>
      <div class="nosotros-grid">
        <div class="nosotros-text">
          <p>El Colegio San Cristóbal nació en 1985 con la misión de ofrecer una educación de calidad accesible para toda la comunidad. Hoy somos una institución reconocida por nuestra excelencia académica, valores sólidos y enfoque en el desarrollo humano integral.</p>
          <p>Contamos con instalaciones modernas, laboratorios equipados, biblioteca especializada y espacios deportivos de primer nivel para garantizar el mejor ambiente de aprendizaje.</p>
          <div class="valores-grid">
            <div class="valor">
              <i class="fas fa-heart"></i>
              <h4>Respeto</h4>
            </div>
            <div class="valor">
              <i class="fas fa-lightbulb"></i>
              <h4>Innovación</h4>
            </div>
            <div class="valor">
              <i class="fas fa-users"></i>
              <h4>Comunidad</h4>
            </div>
            <div class="valor">
              <i class="fas fa-leaf"></i>
              <h4>Sostenibilidad</h4>
            </div>
          </div>
        </div>
        <div class="nosotros-cards">
          <div class="info-card">
            <div class="info-icon"><i class="fas fa-book-open"></i></div>
            <h3>Misión</h3>
            <p>Formar ciudadanos íntegros con pensamiento crítico, valores éticos y habilidades para transformar su entorno positivamente.</p>
          </div>
          <div class="info-card accent">
            <div class="info-icon"><i class="fas fa-eye"></i></div>
            <h3>Visión</h3>
            <p>Ser en 2030 la institución educativa líder en innovación pedagógica y formación humana de Bogotá.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== NOTICIAS ========== -->
  <section class="noticias" id="noticias">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Actualidad</span>
        <h2>Noticias y <span class="highlight">Eventos</span></h2>
      </div>
      <div class="noticias-grid">
        <article class="noticia-card featured">
          <div class="noticia-img">
            <div class="img-placeholder small"><i class="fas fa-trophy"></i></div>
            <span class="noticia-tag">Logros</span>
          </div>
          <div class="noticia-body">
            <span class="noticia-fecha"><i class="fas fa-calendar"></i> 15 Mar 2025</span>
            <h3>Nuestros estudiantes ganan el campeonato regional de matemáticas</h3>
            <p>Un equipo de 6 estudiantes de grado 11 obtuvo el primer lugar en la Olimpiada Regional de Matemáticas celebrada en la Universidad Nacional.</p>
            <a href="#" class="read-more">Leer más <i class="fas fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="noticia-card">
          <div class="noticia-img">
            <div class="img-placeholder small"><i class="fas fa-paint-brush"></i></div>
            <span class="noticia-tag">Cultura</span>
          </div>
          <div class="noticia-body">
            <span class="noticia-fecha"><i class="fas fa-calendar"></i> 8 Mar 2025</span>
            <h3>Festival de Arte y Cultura 2025</h3>
            <p>Una semana llena de expresión artística, música y teatro protagonizada por nuestros estudiantes.</p>
            <a href="#" class="read-more">Leer más <i class="fas fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="noticia-card">
          <div class="noticia-img">
            <div class="img-placeholder small"><i class="fas fa-flask"></i></div>
            <span class="noticia-tag">Ciencia</span>
          </div>
          <div class="noticia-body">
            <span class="noticia-fecha"><i class="fas fa-calendar"></i> 1 Mar 2025</span>
            <h3>Inauguración del nuevo laboratorio de ciencias</h3>
            <p>Estrenamos un laboratorio completamente equipado con tecnología de punta para nuestros estudiantes de secundaria.</p>
            <a href="#" class="read-more">Leer más <i class="fas fa-arrow-right"></i></a>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- ========== GALERÍA ========== -->
  <section class="galeria" id="galeria">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Momentos</span>
        <h2>Nuestra <span class="highlight">Galería</span></h2>
      </div>
      <div class="galeria-grid">
        <div class="galeria-item big"><div class="img-placeholder"><i class="fas fa-image"></i><p>Acto cívico</p></div></div>
        <div class="galeria-item"><div class="img-placeholder"><i class="fas fa-image"></i><p>Deportes</p></div></div>
        <div class="galeria-item"><div class="img-placeholder"><i class="fas fa-image"></i><p>Laboratorio</p></div></div>
        <div class="galeria-item"><div class="img-placeholder"><i class="fas fa-image"></i><p>Arte</p></div></div>
        <div class="galeria-item"><div class="img-placeholder"><i class="fas fa-image"></i><p>Graduación</p></div></div>
        <div class="galeria-item"><div class="img-placeholder"><i class="fas fa-image"></i><p>Biblioteca</p></div></div>
      </div>
    </div>
  </section>

  <!-- ========== REGISTRO ========== -->
  <section class="registro" id="registro">
    <div class="container">
      <div class="registro-wrapper">
        <div class="registro-info">
          <span class="section-tag light">Inscripciones abiertas</span>
          <h2>¿Quieres hacer parte de <span class="highlight-light">San Cristóbal?</span></h2>
          <p>Completa el formulario y uno de nuestros asesores se comunicará contigo en menos de 24 horas para guiarte en el proceso de admisión.</p>
          <ul class="registro-beneficios">
            <li><i class="fas fa-check-circle"></i> Proceso 100% en línea</li>
            <li><i class="fas fa-check-circle"></i> Respuesta en 24 horas</li>
            <li><i class="fas fa-check-circle"></i> Sin costo de inscripción</li>
            <li><i class="fas fa-check-circle"></i> Disponible para todos los grados</li>
          </ul>
        </div>
        <div class="registro-form-wrap">
          <form class="registro-form" id="registroForm">
            <h3>Formulario de Registro</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="nombre">Nombre del acudiente</label>
                <input type="text" id="nombre" placeholder="Ej. María García" required />
              </div>
              <div class="form-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" placeholder="Ej. González" required />
              </div>
            </div>
            <div class="form-group">
              <label for="email">Correo electrónico</label>
              <input type="email" id="email" placeholder="correo@ejemplo.com" required />
            </div>
            <div class="form-group">
              <label for="telefono">Teléfono / Celular</label>
              <input type="tel" id="telefono" placeholder="300 000 0000" required />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="estudiante">Nombre del estudiante</label>
                <input type="text" id="estudiante" placeholder="Nombre completo" required />
              </div>
              <div class="form-group">
                <label for="grado">Grado a ingresar</label>
                <select id="grado" required>
                  <option value="">Seleccionar...</option>
                  <option>Preescolar</option>
                  <option>Grado 1°</option>
                  <option>Grado 2°</option>
                  <option>Grado 3°</option>
                  <option>Grado 4°</option>
                  <option>Grado 5°</option>
                  <option>Grado 6°</option>
                  <option>Grado 7°</option>
                  <option>Grado 8°</option>
                  <option>Grado 9°</option>
                  <option>Grado 10°</option>
                  <option>Grado 11°</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="mensaje">Mensaje adicional (opcional)</label>
              <textarea id="mensaje" rows="3" placeholder="¿Alguna pregunta o comentario?"></textarea>
            </div>
            <button type="submit" class="btn-submit">
              <i class="fas fa-paper-plane"></i> Enviar solicitud
            </button>
            <div class="form-success" id="formSuccess">
              <i class="fas fa-check-circle"></i>
              <strong>¡Solicitud enviada!</strong> Nos comunicaremos contigo pronto.
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== CONTACTO ========== -->
  <section class="contacto" id="contacto">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Encuéntranos</span>
        <h2>Información de <span class="highlight">Contacto</span></h2>
      </div>
      <div class="contacto-grid">
        <div class="contacto-card">
          <i class="fas fa-map-marker-alt"></i>
          <h4>Dirección</h4>
          <p>Calle 45 #12-34, Barrio San Luis<br>Bogotá D.C., Colombia</p>
        </div>
        <div class="contacto-card">
          <i class="fas fa-phone-alt"></i>
          <h4>Teléfono</h4>
          <p>(601) 234-5678<br>Cel: 310 000 0000</p>
        </div>
        <div class="contacto-card">
          <i class="fas fa-envelope"></i>
          <h4>Correo</h4>
          <p>info@sancristobal.edu.co<br>admisiones@sancristobal.edu.co</p>
        </div>
        <div class="contacto-card">
          <i class="fas fa-clock"></i>
          <h4>Horario</h4>
          <p>Lun – Vie: 6:30 am – 4:00 pm<br>Sáb: 8:00 am – 12:00 pm</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== FOOTER ========== -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="nav-logo">
            <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
            <span class="logo-text">San <strong>Cristóbal</strong></span>
          </div>
          <p>Formando líderes con valores desde 1985. Una institución comprometida con la excelencia educativa y el desarrollo humano integral.</p>
          <div class="social-links">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>
        <div class="footer-links">
          <h4>Navegación</h4>
          <ul>
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#nosotros">Nosotros</a></li>
            <li><a href="#noticias">Noticias</a></li>
            <li><a href="#galeria">Galería</a></li>
            <li><a href="#registro">Registro</a></li>
            <li><a href="#contacto">Contacto</a></li>
          </ul>
        </div>
        <div class="footer-links">
          <h4>Servicios</h4>
          <ul>
            <li><a href="#">Portal estudiantil</a></li>
            <li><a href="#">Plataforma virtual</a></li>
            <li><a href="#">Boletines académicos</a></li>
            <li><a href="#">Calendario escolar</a></li>
            <li><a href="#">PEI Institucional</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2025 Colegio San Cristóbal. Todos los derechos reservados.</p>
        <div class="footer-legal">
          <a href="#">Política de privacidad</a>
          <a href="#">Términos de uso</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="<?= BASE_URL ?>/public/assets/webSite/js/jsCole.js"></script>
</body>
</html>