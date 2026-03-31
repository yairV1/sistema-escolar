  <!-- =====================================================
       SIDEBAR
       ===================================================== -->
  <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
          <div class="sidebar-logo">
              <i class="fas fa-graduation-cap"></i>
          </div>
          <div class="sidebar-brand">
              <span class="sb-name">San <strong>Cristóbal</strong></span>
              <span class="sb-sub">Panel Rector</span>
          </div>
          <button class="sidebar-collapse" id="sidebarCollapse" title="Contraer">
              <i class="fas fa-chevron-left"></i>
          </button>
      </div>

      <nav class="sidebar-nav">
          <span class="nav-group-label">Principal</span>
          <ul>
              <li>
                  <a href="<?= BASE_URL ?>Inicio" class="slink active" data-page="inicio">
                      <i class="fas fa-chart-pie"></i>
                      <span>Inicio</span>
                  </a>
              </li>
          </ul>

          <span class="nav-group-label">Comunidad</span>
          <ul>
              <li>
                  <a href="<?= BASE_URL ?>RegistroEstudiantes" class="slink" data-page="estudiantes">
                      <i class="fas fa-user-graduate"></i>
                      <span>Registro de Estudiantes</span>
                      <span class="slink-badge">1,240</span>
                  </a>
              </li>
              <li>
                  <a href="<?= BASE_URL ?>RegistroDocentes" class="slink" data-page="docentes">
                      <i class="fas fa-chalkboard-teacher"></i>
                      <span>Registro de Docentes</span>
                      <span class="slink-badge">87</span>
                  </a>
              </li>
              <li>
                  <a href="<?= BASE_URL ?>Listados" class="slink" data-page="listados">
                      <i class="fas fa-list-alt"></i>
                      <span>Listados</span>
                  </a>
              </li>
          </ul>

          <span class="nav-group-label">Académico</span>
          <ul>
              <li>
                  <a href="<?= BASE_URL ?>Matriculas" class="slink" data-page="matriculas">
                      <i class="fas fa-file-signature"></i>
                      <span>Matrículas</span>
                      <span class="slink-badge new">12 nuevas</span>
                  </a>
              </li>
              <li>
                  <a href="<?= BASE_URL ?>Estadisticas" class="slink" data-page="estadisticas">
                      <i class="fas fa-chart-bar"></i>
                      <span>Estadísticas</span>
                  </a>
              </li>
              <li>
                  <a href="<?= BASE_URL ?>Reportes" class="slink" data-page="reportes">
                      <i class="fas fa-file-alt"></i>
                      <span>Reportes y boletines</span>
                  </a>
              </li>
          </ul>

          <span class="nav-group-label">Comunicación</span>
          <ul>
              <li>
                  <a href="<?= BASE_URL ?>Comunicados" class="slink" data-page="comunicados">
                      <i class="fas fa-bullhorn"></i>
                      <span>Comunicados</span>
                      <span class="slink-badge alert">3</span>
                  </a>
              </li>
          </ul>

           <span class="nav-group-label">Barra de navegación</span>
            <ul>
                <li>
                    <a href="<?= BASE_URL ?>EditarLanding" class="slink" data-page="nav">
                        <i class="fas fa-compass"></i>
                        <span>Editar landing page</span>
                    </a>
                </li>
            </ul>
      </nav>
  </aside>