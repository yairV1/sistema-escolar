<header class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="topbar-menu" id="topbarMenu" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-breadcrumb">
                <span class="breadcrumb-root">Panel Rector</span>
                <i class="fas fa-chevron-right"></i>
                <span class="breadcrumb-page" id="breadcrumbPage">Inicio</span>
            </div>
        </div>
        <div class="topbar-right">
            <!-- Buscador -->
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar estudiante, docente…" id="adminSearch" />
                <kbd>Ctrl+K</kbd>
            </div>
            <!-- Notificaciones -->
            <button class="topbar-icon-btn notif" id="notiBtn" title="Notificaciones">
                <i class="fas fa-bell"></i>
                <span class="topbar-dot"></span>
            </button>
            <!-- Perfil -->
            <div class="topbar-profile" id="topbarProfile">
                <button class="topbar-avatar-btn" id="topbarAvatarBtn">
                    <div class="topbar-avatar">RC</div>
                    <span class="topbar-avatar-name">Rosa Cardona</span>
                    <i class="fas fa-chevron-down" id="topbarChevron"></i>
                </button>
                <div class="topbar-dropdown" id="topbarDropdown">
                    <div class="td-header">
                        <div class="td-avatar">RC</div>
                        <div>
                            <p class="td-name">Rosa Cardona</p>
                            <p class="td-email">rcardona@sancristobal.edu.co</p>
                            <span class="td-badge">Rectora</span>
                        </div>
                    </div>
                    <div class="td-divider"></div>
                    <ul class="td-menu">
                        <li><a href="#" class="td-item"><i class="fas fa-user-circle"></i> Mi perfil</a></li>
                        <li><a href="#" class="td-item"><i class="fas fa-cog"></i> Configuración</a></li>
                        <li><a href="#" class="td-item"><i class="fas fa-question-circle"></i> Ayuda</a></li>
                    </ul>
                    <div class="td-divider"></div>
                    <button class="td-logout" id="topbarLogout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
                </div>
            </div>
        </div>
    </header>