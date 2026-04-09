<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editor del Sitio — Colegio San Cristóbal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/admin.css" />

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/css/ediLanding.css" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body class="cms-body">

    <?php include_once __DIR__ . '/../../layouts/administrativo/sidebar.php'; ?>
    <div class="admin-overlay" id="adminOverlay"></div>
    <?php include_once __DIR__ . '/../../layouts/administrativo/nav.php'; ?>
    <!-- ══════════════════════════════════════
       TOPBAR DEL CMS
       ══════════════════════════════════════ -->
    <header class="cms-topbar">
        <div class="ct-left">
            <div class="ct-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="ct-brand">San <strong>Cristóbal</strong></span>
            <span class="ct-sep"></span>
            <span class="ct-title"><i class="fas fa-paint-brush"></i> Editor del sitio web</span>
        </div>

        <div class="ct-center">
            <!-- Selector de sección activa -->
            <div class="ct-section-tabs" id="ctSectionTabs">
                <button class="ct-stab active" data-section="inicio">
                    <i class="fas fa-home"></i> Inicio
                </button>
                <button class="ct-stab" data-section="nosotros">
                    <i class="fas fa-users"></i> Nosotros
                </button>
                <button class="ct-stab" data-section="noticias">
                    <i class="fas fa-newspaper"></i> Noticias
                </button>
                <button class="ct-stab" data-section="galeria">
                    <i class="fas fa-images"></i> Galería
                </button>
                <button class="ct-stab" data-section="contacto">
                    <i class="fas fa-envelope"></i> Contacto
                </button>
                <button class="ct-stab add-section" id="btnAddSection" title="Agregar sección">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>

        <div class="ct-right">
            <!-- Selector de dispositivo para preview -->
            <div class="ct-device-toggle">
                <button class="ct-dev-btn active" data-device="desktop" title="Escritorio">
                    <i class="fas fa-desktop"></i>
                </button>
                <button class="ct-dev-btn" data-device="tablet" title="Tablet">
                    <i class="fas fa-tablet-alt"></i>
                </button>
                <button class="ct-dev-btn" data-device="mobile" title="Móvil">
                    <i class="fas fa-mobile-alt"></i>
                </button>
            </div>
            <button class="ct-btn-sec" id="btnPreviewFull">
                <i class="fas fa-eye"></i> Vista completa
            </button>
            <button class="ct-btn-save" id="btnSaveCMS">
                <i class="fas fa-save"></i> Publicar cambios
            </button>
        </div>
    </header>

    <!-- ══════════════════════════════════════
       SPLIT-SCREEN LAYOUT
       ══════════════════════════════════════ -->
    <div class="cms-layout">

        <!-- ── COL IZQUIERDA: Preview en vivo ── -->
        <div class="cms-preview-col" id="cmsPreviewCol">
            <div class="preview-bar">
                <div class="pb-url">
                    <i class="fas fa-lock" style="color:#2d7a4f;font-size:0.7rem;"></i>
                    <span>sancristobal.edu.co</span>
                </div>
                <span class="pb-live"><span class="pb-dot"></span> Vista previa en vivo</span>
            </div>

            <div class="preview-device-wrap" id="previewDeviceWrap">
                <iframe
                    id="sitePreview"
                    src="<?= BASE_URL ?>/"
                    class="preview-iframe desktop"
                    title="Vista previa del sitio"
                    sandbox="allow-same-origin allow-scripts">
                </iframe>
            </div>

            <!-- Overlay de sección seleccionada (se mueve con JS) -->
            <div class="preview-section-highlight" id="sectionHighlight" style="display:none;"></div>
        </div>

        <!-- Divisor redimensionable -->
        <div class="cms-divider" id="cmsDivider">
            <div class="cd-handle"><i class="fas fa-grip-lines-vertical"></i></div>
        </div>

        <!-- ── COL DERECHA: Panel de edición ── -->
        <div class="cms-editor-col" id="cmsEditorCol">

            <!-- Header del panel -->
            <div class="editor-panel-header">
                <div class="eph-left">
                    <div class="eph-icon" id="ephIcon"><i class="fas fa-home"></i></div>
                    <div>
                        <h2 class="eph-title" id="ephTitle">Sección Inicio</h2>
                        <p class="eph-desc" id="ephDesc">Edita el hero, estadísticas y botones de acción.</p>
                    </div>
                </div>
                <div class="eph-right">
                    <!-- Toggle visibilidad de sección -->
                    <label class="eph-toggle" title="Mostrar/ocultar sección en el sitio">
                        <input type="checkbox" id="sectionVisible" checked />
                        <span class="eph-toggle-track"><span class="eph-toggle-thumb"></span></span>
                        <span class="eph-toggle-label">Visible</span>
                    </label>
                </div>
            </div>

            <!-- Permisos del usuario actual -->
            <div class="editor-role-banner" id="roleBanner">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Rector — Acceso total</strong>
                    <span>Puedes editar todas las secciones y gestionar permisos.</span>
                </div>
                <button class="erb-permisos" id="btnPermisos">
                    <i class="fas fa-users-cog"></i> Gestionar permisos
                </button>
            </div>

            <!-- Tabs del panel de edición -->
            <div class="editor-tabs">
                <button class="etab active" data-etab="contenido">
                    <i class="fas fa-edit"></i> Contenido
                </button>
                <button class="etab" data-etab="diseno">
                    <i class="fas fa-palette"></i> Diseño
                </button>
                <button class="etab" data-etab="seo">
                    <i class="fas fa-search"></i> SEO
                </button>
                <button class="etab" data-etab="historial">
                    <i class="fas fa-history"></i> Historial
                </button>
            </div>

            <!-- ════════════════════════════════
           TAB CONTENIDO
           ════════════════════════════════ -->
            <div class="etab-panel active" id="etab-contenido">
                <div class="editor-scroll">

                    <!-- ─ SECCIÓN INICIO ─ -->
                    <div class="section-editor active" id="editor-inicio">

                        <!-- Bloque: Hero principal -->
                        <div class="editor-block">
                            <div class="eb-header">
                                <div class="eb-icon"><i class="fas fa-star"></i></div>
                                <h3 class="eb-title">Hero principal</h3>
                                <button class="eb-collapse" title="Colapsar"><i class="fas fa-chevron-up"></i></button>
                            </div>
                            <div class="eb-body">
                                <div class="ef-group">
                                    <label class="ef-label">Título principal</label>
                                    <div class="ef-input-wrap">
                                        <input type="text" class="ef-input cms-field" data-field="hero_titulo"
                                            value="Educamos con pasión y propósito" />
                                        <button class="ef-ai-btn" title="Sugerir con IA"><i class="fas fa-magic"></i></button>
                                    </div>
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Palabra destacada <span class="ef-hint">(aparece en verde)</span></label>
                                    <input type="text" class="ef-input cms-field" data-field="hero_destacado" value="pasión" />
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Descripción</label>
                                    <textarea class="ef-input ef-textarea cms-field" data-field="hero_desc" rows="3">En el Colegio San Cristóbal formamos personas íntegras, creativas y comprometidas con su entorno.</textarea>
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Texto del botón principal</label>
                                    <input type="text" class="ef-input cms-field" data-field="hero_btn1" value="Inscríbete ahora" />
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Texto del botón secundario</label>
                                    <input type="text" class="ef-input cms-field" data-field="hero_btn2" value="Conócenos" />
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Imagen de fondo del hero</label>
                                    <div class="ef-img-upload" id="heroImgUpload">
                                        <div class="eiu-preview" id="heroImgPreview">
                                            <div class="eiu-placeholder">
                                                <i class="fas fa-image"></i>
                                                <span>Sin imagen — usa color de fondo</span>
                                            </div>
                                        </div>
                                        <div class="eiu-actions">
                                            <button class="eiu-btn" onclick="triggerUpload('heroImgInput')">
                                                <i class="fas fa-upload"></i> Subir imagen
                                            </button>
                                            <button class="eiu-btn eiu-remove" id="heroImgRemove" style="display:none;">
                                                <i class="fas fa-trash"></i> Quitar
                                            </button>
                                        </div>
                                        <input type="file" id="heroImgInput" accept="image/*" class="ef-file-hidden" data-target="heroImgPreview" />
                                    </div>
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Etiqueta del badge <span class="ef-hint">(ej. "Desde 1985")</span></label>
                                    <input type="text" class="ef-input cms-field" data-field="hero_badge" value="🌿 Desde 1985 · Bogotá, Colombia" />
                                </div>
                            </div>
                        </div>

                        <!-- Bloque: Estadísticas -->
                        <div class="editor-block">
                            <div class="eb-header">
                                <div class="eb-icon"><i class="fas fa-chart-bar"></i></div>
                                <h3 class="eb-title">Estadísticas del hero</h3>
                                <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                            </div>
                            <div class="eb-body">
                                <div class="ef-stats-grid">
                                    <div class="ef-stat-item">
                                        <label class="ef-label">Número 1</label>
                                        <input type="text" class="ef-input cms-field" data-field="stat1_num" value="1,200+" />
                                        <label class="ef-label" style="margin-top:6px;">Etiqueta 1</label>
                                        <input type="text" class="ef-input cms-field" data-field="stat1_label" value="Estudiantes" />
                                    </div>
                                    <div class="ef-stat-item">
                                        <label class="ef-label">Número 2</label>
                                        <input type="text" class="ef-input cms-field" data-field="stat2_num" value="85+" />
                                        <label class="ef-label" style="margin-top:6px;">Etiqueta 2</label>
                                        <input type="text" class="ef-input cms-field" data-field="stat2_label" value="Docentes" />
                                    </div>
                                    <div class="ef-stat-item">
                                        <label class="ef-label">Número 3</label>
                                        <input type="text" class="ef-input cms-field" data-field="stat3_num" value="38" />
                                        <label class="ef-label" style="margin-top:6px;">Etiqueta 3</label>
                                        <input type="text" class="ef-input cms-field" data-field="stat3_label" value="Años de historia" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /editor-inicio -->

                    <!-- ─ SECCIÓN NOSOTROS ─ -->
                    <div class="section-editor" id="editor-nosotros">
                        <div class="editor-block">
                            <div class="eb-header">
                                <div class="eb-icon"><i class="fas fa-users"></i></div>
                                <h3 class="eb-title">Quiénes somos</h3>
                                <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                            </div>
                            <div class="eb-body">
                                <div class="ef-group">
                                    <label class="ef-label">Título de la sección</label>
                                    <input type="text" class="ef-input cms-field" value="Una comunidad educativa que crece junta" />
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Párrafo 1</label>
                                    <textarea class="ef-input ef-textarea cms-field" rows="3">El Colegio San Cristóbal nació en 1985 con la misión de ofrecer una educación de calidad accesible para toda la comunidad.</textarea>
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Párrafo 2</label>
                                    <textarea class="ef-input ef-textarea cms-field" rows="3">Contamos con instalaciones modernas, laboratorios equipados, biblioteca especializada y espacios deportivos de primer nivel.</textarea>
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Misión</label>
                                    <textarea class="ef-input ef-textarea cms-field" rows="2">Formar ciudadanos íntegros con pensamiento crítico, valores éticos y habilidades para transformar su entorno positivamente.</textarea>
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Visión</label>
                                    <textarea class="ef-input ef-textarea cms-field" rows="2">Ser en 2030 la institución educativa líder en innovación pedagógica y formación humana de Bogotá.</textarea>
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Imagen de la sección</label>
                                    <div class="ef-img-upload">
                                        <div class="eiu-preview">
                                            <div class="eiu-placeholder"><i class="fas fa-image"></i><span>Subir imagen de equipo / institución</span></div>
                                        </div>
                                        <div class="eiu-actions">
                                            <button class="eiu-btn" onclick="triggerUpload('nosotrosImg')">
                                                <i class="fas fa-upload"></i> Subir imagen
                                            </button>
                                        </div>
                                        <input type="file" id="nosotrosImg" accept="image/*" class="ef-file-hidden" data-target="nosotrosImgPreview" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /editor-nosotros -->

                    <!-- ─ SECCIÓN NOTICIAS ─ -->
                    <div class="section-editor" id="editor-noticias">
                        <div class="editor-block">
                            <div class="eb-header">
                                <div class="eb-icon"><i class="fas fa-newspaper"></i></div>
                                <h3 class="eb-title">Noticias y eventos</h3>
                                <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                            </div>
                            <div class="eb-body">
                                <div class="ef-group">
                                    <label class="ef-label">Título de la sección</label>
                                    <input type="text" class="ef-input cms-field" value="Noticias y Eventos" />
                                </div>

                                <!-- Lista de noticias editables -->
                                <div class="ef-news-list" id="newsList">

                                    <div class="ef-news-item" data-id="1">
                                        <div class="eni-header">
                                            <span class="eni-num">Noticia 1</span>
                                            <div class="eni-controls">
                                                <button class="eni-btn" title="Mover arriba"><i class="fas fa-arrow-up"></i></button>
                                                <button class="eni-btn" title="Mover abajo"><i class="fas fa-arrow-down"></i></button>
                                                <button class="eni-btn red" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                        <div class="ef-group">
                                            <label class="ef-label">Título</label>
                                            <input type="text" class="ef-input cms-field" value="Nuestros estudiantes ganan el campeonato regional de matemáticas" />
                                        </div>
                                        <div class="ef-group">
                                            <label class="ef-label">Resumen</label>
                                            <textarea class="ef-input ef-textarea cms-field" rows="2">Un equipo de 6 estudiantes de grado 11 obtuvo el primer lugar en la Olimpiada Regional de Matemáticas.</textarea>
                                        </div>
                                        <div class="ef-row">
                                            <div class="ef-group ef-col-2">
                                                <label class="ef-label">Categoría</label>
                                                <select class="ef-input ef-select cms-field">
                                                    <option selected>Logros</option>
                                                    <option>Cultura</option>
                                                    <option>Ciencia</option>
                                                    <option>Deporte</option>
                                                    <option>General</option>
                                                </select>
                                            </div>
                                            <div class="ef-group ef-col-2">
                                                <label class="ef-label">Fecha</label>
                                                <input type="date" class="ef-input cms-field" value="2025-03-15" />
                                            </div>
                                        </div>
                                        <div class="ef-group">
                                            <label class="ef-label">Imagen de la noticia</label>
                                            <div class="ef-img-upload compact">
                                                <div class="eiu-preview small">
                                                    <div class="eiu-placeholder"><i class="fas fa-image"></i><span>Sin imagen</span></div>
                                                </div>
                                                <button class="eiu-btn" onclick="triggerUpload('noticia1Img')"><i class="fas fa-upload"></i> Subir</button>
                                                <input type="file" id="noticia1Img" accept="image/*" class="ef-file-hidden" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ef-news-item" data-id="2">
                                        <div class="eni-header">
                                            <span class="eni-num">Noticia 2</span>
                                            <div class="eni-controls">
                                                <button class="eni-btn" title="Mover arriba"><i class="fas fa-arrow-up"></i></button>
                                                <button class="eni-btn" title="Mover abajo"><i class="fas fa-arrow-down"></i></button>
                                                <button class="eni-btn red" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                        <div class="ef-group">
                                            <label class="ef-label">Título</label>
                                            <input type="text" class="ef-input cms-field" value="Festival de Arte y Cultura 2025" />
                                        </div>
                                        <div class="ef-group">
                                            <label class="ef-label">Resumen</label>
                                            <textarea class="ef-input ef-textarea cms-field" rows="2">Una semana llena de expresión artística, música y teatro protagonizada por nuestros estudiantes.</textarea>
                                        </div>
                                        <div class="ef-row">
                                            <div class="ef-group ef-col-2">
                                                <label class="ef-label">Categoría</label>
                                                <select class="ef-input ef-select cms-field">
                                                    <option>Logros</option>
                                                    <option selected>Cultura</option>
                                                    <option>Ciencia</option>
                                                </select>
                                            </div>
                                            <div class="ef-group ef-col-2">
                                                <label class="ef-label">Fecha</label>
                                                <input type="date" class="ef-input cms-field" value="2025-03-08" />
                                            </div>
                                        </div>
                                        <div class="ef-group">
                                            <div class="ef-img-upload compact">
                                                <div class="eiu-preview small">
                                                    <div class="eiu-placeholder"><i class="fas fa-image"></i><span>Sin imagen</span></div>
                                                </div>
                                                <button class="eiu-btn" onclick="triggerUpload('noticia2Img')"><i class="fas fa-upload"></i> Subir</button>
                                                <input type="file" id="noticia2Img" accept="image/*" class="ef-file-hidden" />
                                            </div>
                                        </div>
                                    </div>

                                </div><!-- /newsList -->

                                <button class="ef-add-item" id="btnAddNoticia">
                                    <i class="fas fa-plus"></i> Agregar noticia
                                </button>
                            </div>
                        </div>
                    </div><!-- /editor-noticias -->

                    <!-- ─ SECCIÓN GALERÍA ─ -->
                    <div class="section-editor" id="editor-galeria">
                        <div class="editor-block">
                            <div class="eb-header">
                                <div class="eb-icon"><i class="fas fa-images"></i></div>
                                <h3 class="eb-title">Galería de fotos</h3>
                                <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                            </div>
                            <div class="eb-body">
                                <div class="ef-group">
                                    <label class="ef-label">Título de la sección</label>
                                    <input type="text" class="ef-input cms-field" value="Nuestra Galería" />
                                </div>
                                <div class="ef-gallery-grid" id="galleryGrid">
                                    <div class="egg-item" data-pos="1">
                                        <div class="egg-preview">
                                            <div class="egg-placeholder"><i class="fas fa-image"></i>
                                                <p>Acto cívico</p>
                                            </div>
                                        </div>
                                        <div class="egg-actions">
                                            <input type="file" accept="image/*" class="ef-file-hidden egg-input" />
                                            <button class="egg-btn" onclick="triggerUpload(this.previousElementSibling)"><i class="fas fa-upload"></i></button>
                                            <button class="egg-btn red egg-del"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <input type="text" class="egg-caption ef-input" placeholder="Descripción…" value="Acto cívico" />
                                    </div>
                                    <div class="egg-item" data-pos="2">
                                        <div class="egg-preview">
                                            <div class="egg-placeholder"><i class="fas fa-image"></i>
                                                <p>Deportes</p>
                                            </div>
                                        </div>
                                        <div class="egg-actions">
                                            <input type="file" accept="image/*" class="ef-file-hidden egg-input" />
                                            <button class="egg-btn" onclick="triggerUpload(this.previousElementSibling)"><i class="fas fa-upload"></i></button>
                                            <button class="egg-btn red egg-del"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <input type="text" class="egg-caption ef-input" placeholder="Descripción…" value="Deportes" />
                                    </div>
                                    <div class="egg-item" data-pos="3">
                                        <div class="egg-preview">
                                            <div class="egg-placeholder"><i class="fas fa-image"></i>
                                                <p>Laboratorio</p>
                                            </div>
                                        </div>
                                        <div class="egg-actions">
                                            <input type="file" accept="image/*" class="ef-file-hidden egg-input" />
                                            <button class="egg-btn" onclick="triggerUpload(this.previousElementSibling)"><i class="fas fa-upload"></i></button>
                                            <button class="egg-btn red egg-del"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <input type="text" class="egg-caption ef-input" placeholder="Descripción…" value="Laboratorio" />
                                    </div>
                                    <div class="egg-item" data-pos="4">
                                        <div class="egg-preview">
                                            <div class="egg-placeholder"><i class="fas fa-image"></i>
                                                <p>Arte</p>
                                            </div>
                                        </div>
                                        <div class="egg-actions">
                                            <input type="file" accept="image/*" class="ef-file-hidden egg-input" />
                                            <button class="egg-btn" onclick="triggerUpload(this.previousElementSibling)"><i class="fas fa-upload"></i></button>
                                            <button class="egg-btn red egg-del"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <input type="text" class="egg-caption ef-input" placeholder="Descripción…" value="Arte" />
                                    </div>
                                    <div class="egg-item" data-pos="5">
                                        <div class="egg-preview">
                                            <div class="egg-placeholder"><i class="fas fa-image"></i>
                                                <p>Graduación</p>
                                            </div>
                                        </div>
                                        <div class="egg-actions">
                                            <input type="file" accept="image/*" class="ef-file-hidden egg-input" />
                                            <button class="egg-btn" onclick="triggerUpload(this.previousElementSibling)"><i class="fas fa-upload"></i></button>
                                            <button class="egg-btn red egg-del"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <input type="text" class="egg-caption ef-input" placeholder="Descripción…" value="Graduación" />
                                    </div>
                                    <div class="egg-item egg-add" id="galleryAddBtn">
                                        <i class="fas fa-plus"></i>
                                        <span>Agregar foto</span>
                                        <input type="file" accept="image/*" class="ef-file-hidden" id="galleryNewImg" multiple />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /editor-galeria -->

                    <!-- ─ SECCIÓN CONTACTO ─ -->
                    <div class="section-editor" id="editor-contacto">
                        <div class="editor-block">
                            <div class="eb-header">
                                <div class="eb-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <h3 class="eb-title">Información de contacto</h3>
                                <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                            </div>
                            <div class="eb-body">
                                <div class="ef-group">
                                    <label class="ef-label">Dirección</label>
                                    <input type="text" class="ef-input cms-field" value="Calle 45 #12-34, Barrio San Luis, Bogotá D.C." />
                                </div>
                                <div class="ef-row">
                                    <div class="ef-group ef-col-2">
                                        <label class="ef-label">Teléfono principal</label>
                                        <input type="text" class="ef-input cms-field" value="(601) 234-5678" />
                                    </div>
                                    <div class="ef-group ef-col-2">
                                        <label class="ef-label">Celular</label>
                                        <input type="text" class="ef-input cms-field" value="310 000 0000" />
                                    </div>
                                </div>
                                <div class="ef-row">
                                    <div class="ef-group ef-col-2">
                                        <label class="ef-label">Correo general</label>
                                        <input type="email" class="ef-input cms-field" value="info@sancristobal.edu.co" />
                                    </div>
                                    <div class="ef-group ef-col-2">
                                        <label class="ef-label">Correo admisiones</label>
                                        <input type="email" class="ef-input cms-field" value="admisiones@sancristobal.edu.co" />
                                    </div>
                                </div>
                                <div class="ef-row">
                                    <div class="ef-group ef-col-2">
                                        <label class="ef-label">Horario lunes–viernes</label>
                                        <input type="text" class="ef-input cms-field" value="6:30 am – 4:00 pm" />
                                    </div>
                                    <div class="ef-group ef-col-2">
                                        <label class="ef-label">Horario sábados</label>
                                        <input type="text" class="ef-input cms-field" value="8:00 am – 12:00 pm" />
                                    </div>
                                </div>
                                <div class="ef-group">
                                    <label class="ef-label">Redes sociales</label>
                                    <div class="ef-social-list">
                                        <div class="ef-social-item">
                                            <i class="fab fa-facebook-f" style="color:#1877f2"></i>
                                            <input type="url" class="ef-input cms-field" placeholder="URL de Facebook" value="https://facebook.com/sancristobal" />
                                        </div>
                                        <div class="ef-social-item">
                                            <i class="fab fa-instagram" style="color:#e1306c"></i>
                                            <input type="url" class="ef-input cms-field" placeholder="URL de Instagram" value="https://instagram.com/sancristobal" />
                                        </div>
                                        <div class="ef-social-item">
                                            <i class="fab fa-whatsapp" style="color:#25d366"></i>
                                            <input type="url" class="ef-input cms-field" placeholder="Número de WhatsApp" value="573100000000" />
                                        </div>
                                        <div class="ef-social-item">
                                            <i class="fab fa-youtube" style="color:#ff0000"></i>
                                            <input type="url" class="ef-input cms-field" placeholder="URL de YouTube" value="" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /editor-contacto -->

                </div><!-- /editor-scroll -->
            </div><!-- /etab-contenido -->

            <!-- ════════════════════════════════
           TAB DISEÑO
           ════════════════════════════════ -->
            <div class="etab-panel" id="etab-diseno">
                <div class="editor-scroll">
                    <div class="editor-block">
                        <div class="eb-header">
                            <div class="eb-icon"><i class="fas fa-palette"></i></div>
                            <h3 class="eb-title">Colores del tema</h3>
                            <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                        </div>
                        <div class="eb-body">
                            <div class="ef-color-grid">
                                <div class="ef-color-item">
                                    <label class="ef-label">Color principal</label>
                                    <div class="ef-color-wrap">
                                        <input type="color" class="ef-color" id="colorPrincipal" value="#2d7a4f" />
                                        <input type="text" class="ef-input ef-color-hex" value="#2d7a4f" maxlength="7" />
                                    </div>
                                </div>
                                <div class="ef-color-item">
                                    <label class="ef-label">Color oscuro</label>
                                    <div class="ef-color-wrap">
                                        <input type="color" class="ef-color" id="colorOscuro" value="#1e5436" />
                                        <input type="text" class="ef-input ef-color-hex" value="#1e5436" maxlength="7" />
                                    </div>
                                </div>
                                <div class="ef-color-item">
                                    <label class="ef-label">Color de acento</label>
                                    <div class="ef-color-wrap">
                                        <input type="color" class="ef-color" id="colorAcento" value="#c8a84b" />
                                        <input type="text" class="ef-input ef-color-hex" value="#c8a84b" maxlength="7" />
                                    </div>
                                </div>
                                <div class="ef-color-item">
                                    <label class="ef-label">Color de fondo</label>
                                    <div class="ef-color-wrap">
                                        <input type="color" class="ef-color" id="colorFondo" value="#f7f9f7" />
                                        <input type="text" class="ef-input ef-color-hex" value="#f7f9f7" maxlength="7" />
                                    </div>
                                </div>
                            </div>

                            <!-- Paletas rápidas -->
                            <div class="ef-group" style="margin-top:16px;">
                                <label class="ef-label">Paletas predefinidas</label>
                                <div class="ef-palettes">
                                    <button class="ef-palette active" data-colors='["#2d7a4f","#1e5436","#c8a84b","#f7f9f7"]' title="Verde institucional">
                                        <span style="background:#2d7a4f"></span>
                                        <span style="background:#1e5436"></span>
                                        <span style="background:#c8a84b"></span>
                                    </button>
                                    <button class="ef-palette" data-colors='["#1a56a0","#0e3a7a","#e8a020","#f5f8ff"]' title="Azul académico">
                                        <span style="background:#1a56a0"></span>
                                        <span style="background:#0e3a7a"></span>
                                        <span style="background:#e8a020"></span>
                                    </button>
                                    <button class="ef-palette" data-colors='["#c53030","#7b1515","#e8a020","#fff5f5"]' title="Rojo y dorado">
                                        <span style="background:#c53030"></span>
                                        <span style="background:#7b1515"></span>
                                        <span style="background:#e8a020"></span>
                                    </button>
                                    <button class="ef-palette" data-colors='["#4a0080","#2d0050","#c8a84b","#faf5ff"]' title="Púrpura elegante">
                                        <span style="background:#4a0080"></span>
                                        <span style="background:#2d0050"></span>
                                        <span style="background:#c8a84b"></span>
                                    </button>
                                    <button class="ef-palette" data-colors='["#0a5c73","#063a4a","#e8a020","#f0f9fb"]' title="Azul petróleo">
                                        <span style="background:#0a5c73"></span>
                                        <span style="background:#063a4a"></span>
                                        <span style="background:#e8a020"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tipografía -->
                    <div class="editor-block">
                        <div class="eb-header">
                            <div class="eb-icon"><i class="fas fa-font"></i></div>
                            <h3 class="eb-title">Tipografía</h3>
                            <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                        </div>
                        <div class="eb-body">
                            <div class="ef-row">
                                <div class="ef-group ef-col-2">
                                    <label class="ef-label">Fuente de títulos</label>
                                    <select class="ef-input ef-select cms-field" id="fontTitulo">
                                        <option selected>Playfair Display</option>
                                        <option>Merriweather</option>
                                        <option>Lora</option>
                                        <option>Cormorant Garamond</option>
                                        <option>DM Serif Display</option>
                                    </select>
                                </div>
                                <div class="ef-group ef-col-2">
                                    <label class="ef-label">Fuente de cuerpo</label>
                                    <select class="ef-input ef-select cms-field" id="fontCuerpo">
                                        <option selected>DM Sans</option>
                                        <option>Nunito</option>
                                        <option>Outfit</option>
                                        <option>Poppins</option>
                                        <option>Source Sans 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logo del colegio -->
                    <div class="editor-block">
                        <div class="eb-header">
                            <div class="eb-icon"><i class="fas fa-graduation-cap"></i></div>
                            <h3 class="eb-title">Logo e identidad</h3>
                            <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                        </div>
                        <div class="eb-body">
                            <div class="ef-group">
                                <label class="ef-label">Nombre del colegio</label>
                                <input type="text" class="ef-input cms-field" value="Colegio San Cristóbal" />
                            </div>
                            <div class="ef-group">
                                <label class="ef-label">Eslogan</label>
                                <input type="text" class="ef-input cms-field" value="Educando el futuro desde 1985" />
                            </div>
                            <div class="ef-group">
                                <label class="ef-label">Logo del colegio</label>
                                <div class="ef-img-upload">
                                    <div class="eiu-preview" style="height:80px;">
                                        <div class="eiu-placeholder"><i class="fas fa-graduation-cap" style="font-size:2rem;color:#2d7a4f;opacity:.4;"></i><span>Logo actual (ícono)</span></div>
                                    </div>
                                    <div class="eiu-actions">
                                        <button class="eiu-btn" onclick="triggerUpload('logoUpload')"><i class="fas fa-upload"></i> Subir logo</button>
                                    </div>
                                    <input type="file" id="logoUpload" accept="image/png,image/svg+xml,image/webp" class="ef-file-hidden" />
                                    <p class="ef-hint-text">Formatos: PNG, SVG, WEBP. Fondo transparente recomendado.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /etab-diseno -->

            <!-- ════════════════════════════════
           TAB SEO
           ════════════════════════════════ -->
            <div class="etab-panel" id="etab-seo">
                <div class="editor-scroll">
                    <div class="editor-block">
                        <div class="eb-header">
                            <div class="eb-icon" style="background:#e0f2fe;color:#0891b2;"><i class="fas fa-search"></i></div>
                            <h3 class="eb-title">SEO y metadatos</h3>
                            <button class="eb-collapse"><i class="fas fa-chevron-up"></i></button>
                        </div>
                        <div class="eb-body">
                            <div class="ef-group">
                                <label class="ef-label">Título de la página <span class="ef-hint">(aparece en la pestaña del navegador)</span></label>
                                <input type="text" class="ef-input cms-field" value="Colegio San Cristóbal | Educando el Futuro" maxlength="60" />
                                <div class="ef-seo-meter" id="seoTitleMeter">
                                    <div class="esm-fill" style="width:70%"></div><span>Óptimo</span>
                                </div>
                            </div>
                            <div class="ef-group">
                                <label class="ef-label">Descripción meta <span class="ef-hint">(aparece en resultados de búsqueda)</span></label>
                                <textarea class="ef-input ef-textarea cms-field" rows="3" maxlength="160">Colegio San Cristóbal, institución educativa líder en Bogotá. Ofrecemos educación de calidad desde preescolar hasta grado 11°.</textarea>
                                <div class="ef-seo-meter">
                                    <div class="esm-fill" style="width:85%"></div><span>Muy bueno</span>
                                </div>
                            </div>
                            <div class="ef-group">
                                <label class="ef-label">Palabras clave</label>
                                <input type="text" class="ef-input cms-field" value="colegio bogotá, educación bogotá, san cristóbal colegio" />
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /etab-seo -->

            <!-- ════════════════════════════════
           TAB HISTORIAL
           ════════════════════════════════ -->
            <div class="etab-panel" id="etab-historial">
                <div class="editor-scroll">
                    <ul class="hist-list">
                        <li class="hist-item">
                            <div class="hi-dot active"></div>
                            <div class="hi-body">
                                <p class="hi-desc">Texto del hero actualizado</p>
                                <p class="hi-meta">Rosa Cardona · Hace 2 minutos</p>
                            </div>
                            <button class="hi-restore">Restaurar</button>
                        </li>
                        <li class="hist-item">
                            <div class="hi-dot"></div>
                            <div class="hi-body">
                                <p class="hi-desc">Imagen de galería agregada (Laboratorio.jpg)</p>
                                <p class="hi-meta">Rosa Cardona · Hace 1 hora</p>
                            </div>
                            <button class="hi-restore">Restaurar</button>
                        </li>
                        <li class="hist-item">
                            <div class="hi-dot"></div>
                            <div class="hi-body">
                                <p class="hi-desc">Noticia "Festival de Arte" editada</p>
                                <p class="hi-meta">Prof. María Castaño (Coord.) · Ayer 3:20 pm</p>
                            </div>
                            <button class="hi-restore">Restaurar</button>
                        </li>
                        <li class="hist-item">
                            <div class="hi-dot"></div>
                            <div class="hi-body">
                                <p class="hi-desc">Publicación exitosa de cambios</p>
                                <p class="hi-meta">Rosa Cardona · Hace 2 días</p>
                            </div>
                            <button class="hi-restore">Restaurar</button>
                        </li>
                    </ul>
                </div>
            </div><!-- /etab-historial -->

        </div><!-- /cms-editor-col -->
    </div><!-- /cms-layout -->

    <!-- ══════════════════════════════════════
       MODAL GESTIÓN DE PERMISOS
       ══════════════════════════════════════ -->
    <div class="modal-overlay" id="modalPermisos">
        <div class="mp-box">
            <div class="mp-header">
                <div class="mp-icon"><i class="fas fa-users-cog"></i></div>
                <div>
                    <h3>Gestionar permisos de edición</h3>
                    <p>Asigna qué secciones puede editar cada rol o persona.</p>
                </div>
                <button class="mp-close" id="mpClose"><i class="fas fa-times"></i></button>
            </div>

            <div class="mp-body">
                <!-- Roles globales -->
                <div class="mp-section">
                    <h4 class="mp-section-title"><i class="fas fa-layer-group"></i> Permisos por rol</h4>
                    <div class="mp-roles-grid">

                        <div class="mp-role-card rector">
                            <div class="mpr-header">
                                <div class="mpr-icon"><i class="fas fa-crown"></i></div>
                                <div>
                                    <p class="mpr-name">Rector / Director</p>
                                    <p class="mpr-desc">Acceso completo</p>
                                </div>
                                <span class="mpr-badge total">Total</span>
                            </div>
                            <div class="mpr-perms">
                                <div class="mpr-perm-item" v-for="s in secciones">
                                    <span><i class="fas fa-check-circle" style="color:#2d7a4f"></i> Todas las secciones</span>
                                </div>
                            </div>
                        </div>

                        <div class="mp-role-card coordinador">
                            <div class="mpr-header">
                                <div class="mpr-icon" style="background:#eff6ff;color:#3182ce;"><i class="fas fa-user-tie"></i></div>
                                <div>
                                    <p class="mpr-name">Coordinador</p>
                                    <p class="mpr-desc">Noticias y comunicados</p>
                                </div>
                                <span class="mpr-badge parcial">Parcial</span>
                            </div>
                            <div class="mpr-perms">
                                <label class="mpr-check"><input type="checkbox" checked /><span></span> Noticias y eventos</label>
                                <label class="mpr-check"><input type="checkbox" checked /><span></span> Comunicados</label>
                                <label class="mpr-check"><input type="checkbox" /><span></span> Galería</label>
                                <label class="mpr-check"><input type="checkbox" /><span></span> Contacto</label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Permisos individuales por docente -->
                <div class="mp-section">
                    <h4 class="mp-section-title"><i class="fas fa-user-edit"></i> Permisos individuales a docentes</h4>
                    <p class="mp-section-desc">Asigna secciones específicas a un docente particular.</p>

                    <div class="mp-assign-form">
                        <select class="mp-select" id="mpDocente">
                            <option value="">Seleccionar docente…</option>
                            <option value="rm">Ricardo Méndez — Matemáticas</option>
                            <option value="at">Ana Torres — Ciencias</option>
                            <option value="mc">María Castaño — Español</option>
                            <option value="lv">Luis Vargas — Sociales</option>
                            <option value="no">Natalia Ospina — Tecnología</option>
                            <option value="hs">Hernán Sánchez — Ed. Física</option>
                        </select>
                        <div class="mp-perm-checks" id="mpPermChecks">
                            <label class="mpr-check"><input type="checkbox" /><span></span> Inicio</label>
                            <label class="mpr-check"><input type="checkbox" /><span></span> Nosotros</label>
                            <label class="mpr-check"><input type="checkbox" checked /><span></span> Noticias</label>
                            <label class="mpr-check"><input type="checkbox" /><span></span> Galería</label>
                            <label class="mpr-check"><input type="checkbox" /><span></span> Contacto</label>
                        </div>
                        <button class="mp-btn-assign" id="btnAssignPerm">
                            <i class="fas fa-save"></i> Guardar permisos
                        </button>
                    </div>

                    <!-- Lista de docentes con permisos activos -->
                    <div class="mp-active-perms" id="mpActivePerms">
                        <div class="mpap-item">
                            <div class="mpap-av" style="background:#faf5ff;color:#6b46c1">MC</div>
                            <div class="mpap-info">
                                <p class="mpap-name">María Castaño</p>
                                <p class="mpap-perms">Noticias, Comunicados</p>
                            </div>
                            <button class="mpap-remove" title="Revocar permisos"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="mpap-item">
                            <div class="mpap-av" style="background:#e0f2fe;color:#0891b2">NO</div>
                            <div class="mpap-info">
                                <p class="mpap-name">Natalia Ospina</p>
                                <p class="mpap-perms">Galería</p>
                            </div>
                            <button class="mpap-remove" title="Revocar permisos"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mp-footer">
                <button class="mp-btn-cancel" id="mpCancel">Cancelar</button>
                <button class="mp-btn-save" id="mpSave"><i class="fas fa-save"></i> Guardar configuración</button>
            </div>
        </div>
    </div>

    <!-- Modal agregar sección -->
    <div class="modal-overlay" id="modalAddSection">
        <div class="mas-box">
            <div class="mp-header">
                <div class="mp-icon"><i class="fas fa-plus-circle"></i></div>
                <div>
                    <h3>Agregar nueva sección</h3>
                    <p>Crea una sección personalizada para el sitio.</p>
                </div>
                <button class="mp-close" id="masClose"><i class="fas fa-times"></i></button>
            </div>
            <div class="mas-body">
                <div class="ef-group">
                    <label class="ef-label">Nombre de la sección</label>
                    <input type="text" class="ef-input" id="masSectionName" placeholder="Ej. Música, Deportes, Egresados…" />
                </div>
                <div class="ef-group">
                    <label class="ef-label">Tipo de sección</label>
                    <div class="mas-types">
                        <button class="mas-type active" data-type="texto"><i class="fas fa-align-left"></i><span>Texto + imagen</span></button>
                        <button class="mas-type" data-type="galeria"><i class="fas fa-images"></i><span>Galería</span></button>
                        <button class="mas-type" data-type="tarjetas"><i class="fas fa-th-large"></i><span>Tarjetas</span></button>
                        <button class="mas-type" data-type="hero"><i class="fas fa-star"></i><span>Banner</span></button>
                    </div>
                </div>
                <div class="ef-group">
                    <label class="ef-label">¿Quién puede editar esta sección?</label>
                    <select class="ef-input ef-select" id="masSectionPerm">
                        <option>Solo rector</option>
                        <option>Rector y coordinador</option>
                        <option>Docente específico</option>
                        <option>Todos los docentes</option>
                    </select>
                </div>
            </div>
            <div class="mp-footer">
                <button class="mp-btn-cancel" id="masCancel">Cancelar</button>
                <button class="mp-btn-save" id="masConfirm"><i class="fas fa-plus"></i> Crear sección</button>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toastContainer"></div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrativo/js/ediLanding.js"></script>
</body>

</html>