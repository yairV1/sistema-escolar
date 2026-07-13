/* =============================================
   COLEGIO SAN CRISTÓBAL — MATRÍCULAS JS
   Tabla, filtros, paginación, modal detalle,
   aprobar/rechazar, bulk actions, exportar
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─── Datos de muestra ─── */
  const MATRICULAS = [
    { id:1,  codigo:'2025-MAT-0001', nombre:'Emilio Serna Ruiz',       iniciales:'ES', colorBg:'#e8f5ee', colorTxt:'#2d7a4f', grado:'6',   tipo:'traslado', fecha:'2025-03-01', estado:'pendiente',  acudiente:'Laura Ruiz',      acuTel:'3101234567', acuEmail:'laura@mail.com', acuParent:'Madre', acuDoc:'CC 52001234', acuOcup:'Docente',       dir:'Cra. 10 #45-23', tel:'3001234567', email:'e.serna@mail.com', eps:'Sura',    fechaNac:'2013-05-14', genero:'Masculino', doc:'TI 1023456789', colAnterior:'Colegio Andino', jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:2,  codigo:'2025-MAT-0002', nombre:'Valentina López Mora',    iniciales:'VL', colorBg:'#eff6ff', colorTxt:'#3182ce', grado:'PRE', tipo:'nueva',    fecha:'2025-03-02', estado:'pendiente',  acudiente:'Carlos López',    acuTel:'3119876543', acuEmail:'carlos@mail.com', acuParent:'Padre', acuDoc:'CC 80234567', acuOcup:'Ingeniero',     dir:'Cl. 22 #8-45',   tel:'3109876543', email:'',                   eps:'Nueva EPS', fechaNac:'2019-08-20', genero:'Femenino',  doc:'RC 2019-456', colAnterior:'—', jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:3,  codigo:'2025-MAT-0003', nombre:'Santiago García B.',      iniciales:'SG', colorBg:'#fef3c7', colorTxt:'#d97706', grado:'9',   tipo:'reingreso', fecha:'2025-03-03', estado:'revision',   acudiente:'Rosa García',     acuTel:'3201234567', acuEmail:'rosa@mail.com',   acuParent:'Madre', acuDoc:'CC 41234567', acuOcup:'Independiente', dir:'Tv. 45 #12-09',  tel:'3201234567', email:'s.garcia@mail.com',  eps:'Compensar', fechaNac:'2009-11-03', genero:'Masculino', doc:'TI 1098765432', colAnterior:'IED Sur',       jornada:'Tarde',  anio:'2025', nee:'No presenta' },
    { id:4,  codigo:'2025-MAT-0004', nombre:'Natalia Reyes Cano',      iniciales:'NR', colorBg:'#f0fff4', colorTxt:'#2d7a4f', grado:'11',  tipo:'traslado', fecha:'2025-03-04', estado:'aprobada',   acudiente:'Marta Cano',      acuTel:'3154321098', acuEmail:'marta@mail.com',  acuParent:'Madre', acuDoc:'CC 45678901', acuOcup:'Contadora',     dir:'Cra. 7 #90-11',  tel:'3154321098', email:'n.reyes@mail.com',   eps:'Famisanar', fechaNac:'2007-02-18', genero:'Femenino',  doc:'TI 1012345678', colAnterior:'IED Sur',       jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:5,  codigo:'2025-MAT-0005', nombre:'Juan Pablo Arango',       iniciales:'JP', colorBg:'#fff5f5', colorTxt:'#e53e3e', grado:'7',   tipo:'nueva',    fecha:'2025-03-05', estado:'pendiente',  acudiente:'Pedro Arango',    acuTel:'3165432109', acuEmail:'pedro@mail.com',  acuParent:'Padre', acuDoc:'CC 71234567', acuOcup:'Abogado',       dir:'Cl. 50 #20-30',  tel:'3165432109', email:'',                   eps:'Salud Total', fechaNac:'2011-07-22', genero:'Masculino', doc:'TI 1034567890', colAnterior:'—', jornada:'Tarde',  anio:'2025', nee:'No presenta' },
    { id:6,  codigo:'2025-MAT-0006', nombre:'Isabella Moreno Vega',    iniciales:'IM', colorBg:'#faf5ff', colorTxt:'#6b46c1', grado:'8',   tipo:'traslado', fecha:'2025-03-06', estado:'revision',   acudiente:'Ana Vega',        acuTel:'3176543210', acuEmail:'ana@mail.com',    acuParent:'Madre', acuDoc:'CC 43210987', acuOcup:'Médica',        dir:'Av. 68 #30-15',  tel:'3176543210', email:'i.moreno@mail.com',  eps:'Colmédica', fechaNac:'2010-04-05', genero:'Femenino',  doc:'TI 1045678901', colAnterior:'Col. Los Andes', jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:7,  codigo:'2025-MAT-0007', nombre:'Sebastián Díaz Torres',   iniciales:'SD', colorBg:'#e0f2fe', colorTxt:'#0891b2', grado:'10',  tipo:'nueva',    fecha:'2025-03-07', estado:'aprobada',   acudiente:'Jorge Díaz',      acuTel:'3187654321', acuEmail:'jorge@mail.com',  acuParent:'Padre', acuDoc:'CC 80345678', acuOcup:'Docente',       dir:'Cra. 30 #5-20',  tel:'3187654321', email:'s.diaz@mail.com',    eps:'Sanitas',   fechaNac:'2008-09-12', genero:'Masculino', doc:'TI 1056789012', colAnterior:'—', jornada:'Tarde',  anio:'2025', nee:'No presenta' },
    { id:8,  codigo:'2025-MAT-0008', nombre:'Mariana Castro Peña',     iniciales:'MC', colorBg:'#fef3c7', colorTxt:'#d97706', grado:'5',   tipo:'reingreso', fecha:'2025-03-08', estado:'rechazada',  acudiente:'Lina Peña',       acuTel:'3198765432', acuEmail:'lina@mail.com',   acuParent:'Madre', acuDoc:'CC 47890123', acuOcup:'Comerciante',   dir:'Cl. 13 #55-40',  tel:'3198765432', email:'m.castro@mail.com',  eps:'Coomeva',   fechaNac:'2013-12-01', genero:'Femenino',  doc:'TI 1067890123', colAnterior:'IED Simón B.',  jornada:'Mañana', anio:'2025', nee:'Dislexia' },
    { id:9,  codigo:'2025-MAT-0009', nombre:'Felipe Rojas Méndez',     iniciales:'FR', colorBg:'#e8f5ee', colorTxt:'#2d7a4f', grado:'3',   tipo:'nueva',    fecha:'2025-03-09', estado:'aprobada',   acudiente:'Carmen Méndez',   acuTel:'3209876543', acuEmail:'carmen@mail.com', acuParent:'Madre', acuDoc:'CC 39876543', acuOcup:'Enfermera',     dir:'Cra. 14 #60-18', tel:'3209876543', email:'',                   eps:'Nueva EPS', fechaNac:'2015-03-28', genero:'Masculino', doc:'TI 1078901234', colAnterior:'—', jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:10, codigo:'2025-MAT-0010', nombre:'Luciana Vargas Ortiz',    iniciales:'LV', colorBg:'#eff6ff', colorTxt:'#3182ce', grado:'2',   tipo:'traslado', fecha:'2025-03-10', estado:'pendiente',  acudiente:'Tomás Vargas',    acuTel:'3210987654', acuEmail:'tomas@mail.com',  acuParent:'Padre', acuDoc:'CC 72345678', acuOcup:'Arquitecto',    dir:'Cl. 80 #7-14',   tel:'3210987654', email:'l.vargas@mail.com',  eps:'Sura',      fechaNac:'2016-06-15', genero:'Femenino',  doc:'RC 2016-789', colAnterior:'Col. San Pedro',jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:11, codigo:'2025-MAT-0011', nombre:'Andrés Herrera Luna',     iniciales:'AH', colorBg:'#faf5ff', colorTxt:'#6b46c1', grado:'4',   tipo:'nueva',    fecha:'2025-03-11', estado:'pendiente',  acudiente:'Gloria Luna',     acuTel:'3221098765', acuEmail:'gloria@mail.com', acuParent:'Madre', acuDoc:'CC 41234890', acuOcup:'Psicóloga',     dir:'Tv. 60 #25-08',  tel:'3221098765', email:'',                   eps:'Compensar', fechaNac:'2014-10-07', genero:'Masculino', doc:'TI 1089012345', colAnterior:'—', jornada:'Tarde',  anio:'2025', nee:'No presenta' },
    { id:12, codigo:'2025-MAT-0012', nombre:'Sofía Bermúdez Ríos',     iniciales:'SB', colorBg:'#e0f2fe', colorTxt:'#0891b2', grado:'1',   tipo:'nueva',    fecha:'2025-03-12', estado:'revision',   acudiente:'Hugo Bermúdez',   acuTel:'3232109876', acuEmail:'hugo@mail.com',   acuParent:'Padre', acuDoc:'CC 80456789', acuOcup:'Contador',      dir:'Cra. 21 #44-33', tel:'3232109876', email:'s.bermudez@mail.com', eps:'Famisanar', fechaNac:'2017-01-19', genero:'Femenino',  doc:'RC 2017-321', colAnterior:'—', jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:13, codigo:'2025-MAT-0013', nombre:'Tomás Quintero Salas',    iniciales:'TQ', colorBg:'#fff7ed', colorTxt:'#dd6b20', grado:'6',   tipo:'reingreso', fecha:'2025-03-13', estado:'aprobada',   acudiente:'Patricia Salas',  acuTel:'3243210987', acuEmail:'patricia@mail.com', acuParent:'Madre', acuDoc:'CC 45601234', acuOcup:'Veterinaria',  dir:'Cl. 35 #15-22',  tel:'3243210987', email:'t.quintero@mail.com',eps:'Colmédica', fechaNac:'2012-08-30', genero:'Masculino', doc:'TI 1090123456', colAnterior:'IED Kennedy', jornada:'Tarde',  anio:'2025', nee:'No presenta' },
    { id:14, codigo:'2025-MAT-0014', nombre:'Gabriela Suárez Pineda',  iniciales:'GS', colorBg:'#e8f5ee', colorTxt:'#2d7a4f', grado:'7',   tipo:'traslado', fecha:'2025-03-14', estado:'rechazada',  acudiente:'Álvaro Suárez',   acuTel:'3254321098', acuEmail:'alvaro@mail.com', acuParent:'Padre', acuDoc:'CC 73456789', acuOcup:'Economista',    dir:'Av. 19 #90-55',  tel:'3254321098', email:'g.suarez@mail.com',  eps:'Salud Total', fechaNac:'2011-11-25', genero:'Femenino',  doc:'TI 1001234567', colAnterior:'Col. Mayor',    jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:15, codigo:'2025-MAT-0015', nombre:'David Lozano Pedraza',    iniciales:'DL', colorBg:'#fef3c7', colorTxt:'#d97706', grado:'9',   tipo:'nueva',    fecha:'2025-03-15', estado:'pendiente',  acudiente:'Martha Pedraza',  acuTel:'3265432109', acuEmail:'martha@mail.com', acuParent:'Madre', acuDoc:'CC 40789012', acuOcup:'Diseñadora',    dir:'Cra. 50 #30-10', tel:'3265432109', email:'',                   eps:'Coomeva',   fechaNac:'2009-04-14', genero:'Masculino', doc:'TI 1012356789', colAnterior:'—', jornada:'Tarde',  anio:'2025', nee:'No presenta' },
    { id:16, codigo:'2025-MAT-0016', nombre:'Camila Ospina Torres',    iniciales:'CO', colorBg:'#faf5ff', colorTxt:'#6b46c1', grado:'10',  tipo:'traslado', fecha:'2025-03-16', estado:'revision',   acudiente:'Luis Ospina',     acuTel:'3276543210', acuEmail:'luis@mail.com',   acuParent:'Padre', acuDoc:'CC 80567890', acuOcup:'Piloto',        dir:'Cl. 100 #15-40', tel:'3276543210', email:'c.ospina@mail.com',  eps:'Sanitas',   fechaNac:'2008-07-09', genero:'Femenino',  doc:'TI 1023467890', colAnterior:'Col. Bilingüe', jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:17, codigo:'2025-MAT-0017', nombre:'Nicolás Pardo Arce',      iniciales:'NP', colorBg:'#e0f2fe', colorTxt:'#0891b2', grado:'8',   tipo:'nueva',    fecha:'2025-03-17', estado:'aprobada',   acudiente:'Diana Arce',      acuTel:'3287654321', acuEmail:'diana@mail.com',  acuParent:'Madre', acuDoc:'CC 43890123', acuOcup:'Docente',       dir:'Cra. 68 #45-22', tel:'3287654321', email:'n.pardo@mail.com',   eps:'Nueva EPS', fechaNac:'2010-02-27', genero:'Masculino', doc:'TI 1034578901', colAnterior:'—', jornada:'Tarde',  anio:'2025', nee:'No presenta' },
    { id:18, codigo:'2025-MAT-0018', nombre:'Valeria Ríos Castillo',   iniciales:'VR', colorBg:'#e8f5ee', colorTxt:'#2d7a4f', grado:'11',  tipo:'nueva',    fecha:'2025-03-18', estado:'pendiente',  acudiente:'Jorge Ríos',      acuTel:'3298765432', acuEmail:'jorge2@mail.com', acuParent:'Padre', acuDoc:'CC 72678901', acuOcup:'Gerente',       dir:'Cl. 72 #20-15',  tel:'3298765432', email:'v.rios@mail.com',    eps:'Sura',      fechaNac:'2007-09-03', genero:'Femenino',  doc:'TI 1045689012', colAnterior:'—', jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:19, codigo:'2025-MAT-0019', nombre:'Samuel Acosta Niño',      iniciales:'SA', colorBg:'#fff5f5', colorTxt:'#e53e3e', grado:'PRE', tipo:'nueva',    fecha:'2025-03-19', estado:'aprobada',   acudiente:'Elena Niño',      acuTel:'3309876543', acuEmail:'elena@mail.com',  acuParent:'Madre', acuDoc:'CC 39012345', acuOcup:'Farmacéutica',  dir:'Tv. 38 #55-12',  tel:'3309876543', email:'',                   eps:'Compensar', fechaNac:'2019-12-10', genero:'Masculino', doc:'RC 2019-654', colAnterior:'—', jornada:'Mañana', anio:'2025', nee:'No presenta' },
    { id:20, codigo:'2025-MAT-0020', nombre:'Daniela Mejía Forero',    iniciales:'DM', colorBg:'#fef3c7', colorTxt:'#d97706', grado:'5',   tipo:'traslado', fecha:'2025-03-20', estado:'revision',   acudiente:'Raúl Mejía',      acuTel:'3320987654', acuEmail:'raul@mail.com',   acuParent:'Padre', acuDoc:'CC 80789012', acuOcup:'Músico',        dir:'Cra. 24 #10-35', tel:'3320987654', email:'d.mejia@mail.com',   eps:'Colmédica', fechaNac:'2013-06-21', genero:'Femenino',  doc:'TI 1056790123', colAnterior:'Col. Musical',  jornada:'Tarde',  anio:'2025', nee:'No presenta' },
    // 27 registros más para completar 47
    ...Array.from({length:27}, (_,i) => {
      const estados   = ['pendiente','revision','aprobada','rechazada'];
      const tipos     = ['nueva','reingreso','traslado'];
      const grados    = ['PRE','1','2','3','4','5','6','7','8','9','10','11'];
      const nombres   = ['Alejandro','Valentina','Carlos','María','José','Ana','Luis','Laura','Diego','Sofía'];
      const apellidos = ['González','Martínez','Rodríguez','López','García','Hernández','Torres','Ramírez','Flores','Cruz'];
      const n = nombres[i % nombres.length] + ' ' + apellidos[i % apellidos.length];
      const ini = n.split(' ').map(x=>x[0]).join('').slice(0,2).toUpperCase();
      const colors = [['#e8f5ee','#2d7a4f'],['#eff6ff','#3182ce'],['#fef3c7','#d97706'],['#faf5ff','#6b46c1'],['#e0f2fe','#0891b2']];
      const c = colors[i % colors.length];
      const d = new Date(2025,2,21+i); const fecha = d.toISOString().split('T')[0];
      return {
        id: 21+i, codigo:`2025-MAT-${String(21+i).padStart(4,'0')}`,
        nombre:n, iniciales:ini, colorBg:c[0], colorTxt:c[1],
        grado:grados[i % grados.length], tipo:tipos[i % tipos.length],
        fecha, estado:estados[i % estados.length],
        acudiente:apellidos[(i+1)%apellidos.length]+' '+apellidos[(i+2)%apellidos.length],
        acuTel:'310'+String(1000000+i), acuEmail:`acu${i}@mail.com`,
        acuParent:'Madre', acuDoc:`CC ${40000000+i}`, acuOcup:'Empleado',
        dir:`Cra. ${i+1} #${i+10}-${i+5}`, tel:'320'+String(1000000+i), email:'',
        eps:'Sura', fechaNac:'2010-01-01', genero: i%2===0?'Masculino':'Femenino',
        doc:`TI 10${String(10000000+i)}`, colAnterior:'—',
        jornada: i%2===0?'Mañana':'Tarde', anio:'2025', nee:'No presenta'
      };
    })
  ];

  /* ─── Estado ─── */
  let filtered    = [...MATRICULAS];
  let currentPage = 1;
  let perPage     = 10;
  let sortCol     = 'fecha';
  let sortDir     = 'desc';
  let currentView = 'table'; // 'table' | 'cards'
  let selected    = new Set();
  let currentDetalle = null;
  let pendingAction  = null;

  /* ─── Referencias DOM ─── */
  const matSearch      = document.getElementById('matSearch');
  const matSearchClear = document.getElementById('matSearchClear');
  const filtroEstado   = document.getElementById('filtroEstado');
  const filtroTipo     = document.getElementById('filtroTipo');
  const filtroGrado    = document.getElementById('filtroGrado');
  const btnClearFilters= document.getElementById('btnClearFilters');
  const matCount       = document.getElementById('matCount');
  const matTbody       = document.getElementById('matTbody');
  const matEmpty       = document.getElementById('matEmpty');
  const matCardsGrid   = document.getElementById('matCardsGrid');
  const viewTable      = document.getElementById('viewTable');
  const viewCards      = document.getElementById('viewCards');
  const btnViewTable   = document.getElementById('btnViewTable');
  const btnViewCards   = document.getElementById('btnViewCards');
  const selectAll      = document.getElementById('selectAll');
  const matBulkBar     = document.getElementById('matBulkBar');
  const mbbCount       = document.getElementById('mbbCount');
  const mpInfo         = document.getElementById('mpInfo');
  const mpPages        = document.getElementById('mpPages');
  const mpPrev         = document.getElementById('mpPrev');
  const mpNext         = document.getElementById('mpNext');
  const mpPerPage      = document.getElementById('mpPerPage');
  const modalDetalle   = document.getElementById('modalDetalle');
  const modalConfirm   = document.getElementById('modalConfirm');
  const mkpiCards      = document.querySelectorAll('.mkpi-card');

  /* ─── Helpers ─── */
  const estadoConfig = {
    pendiente: { label:'Pendiente',  icon:'fa-clock',        cls:'pendiente' },
    revision:  { label:'En revisión',icon:'fa-search',       cls:'revision'  },
    aprobada:  { label:'Aprobada',   icon:'fa-check-circle', cls:'aprobada'  },
    rechazada: { label:'Rechazada',  icon:'fa-times-circle', cls:'rechazada' },
  };
  const tipoLabel = { nueva:'Nueva', reingreso:'Reingreso', traslado:'Traslado' };
  const gradoLabel = g => g === 'PRE' ? 'Preescolar' : `Grado ${g}°`;

  function badgeHTML(estado) {
    const c = estadoConfig[estado] || estadoConfig.pendiente;
    return `<span class="td-badge ${c.cls}"><i class="fas ${c.icon}"></i>${c.label}</span>`;
  }
  function tipoBadge(tipo) {
    return `<span class="td-badge-tipo ${tipo}">${tipoLabel[tipo] || tipo}</span>`;
  }
  function formatDate(str) {
    if (!str) return '—';
    const [y,m,d] = str.split('-');
    const ms=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return `${parseInt(d)} ${ms[parseInt(m)-1]} ${y}`;
  }
  function showToast(msg, type='success') {
    const colors = {
      success:{ bg:'#e8f5ee', border:'#2d7a4f', text:'#1e5436', icon:'fas fa-check-circle' },
      info:   { bg:'#eff6ff', border:'#3182ce', text:'#2c5282', icon:'fas fa-info-circle'  },
      error:  { bg:'#fff5f5', border:'#e53e3e', text:'#c53030', icon:'fas fa-exclamation-circle' },
    };
    const c = colors[type]||colors.info;
    const t = document.createElement('div');
    t.style.cssText=`position:fixed;bottom:24px;right:24px;z-index:9999;background:${c.bg};border:1.5px solid ${c.border};color:${c.text};padding:12px 18px;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 28px rgba(0,0,0,.12);transform:translateY(16px);opacity:0;transition:all .3s ease;max-width:320px;`;
    t.innerHTML=`<i class="${c.icon}" style="font-size:1rem;flex-shrink:0"></i>${msg}`;
    document.body.appendChild(t);
    requestAnimationFrame(()=>{ t.style.transform='translateY(0)'; t.style.opacity='1'; });
    setTimeout(()=>{ t.style.transform='translateY(16px)'; t.style.opacity='0'; setTimeout(()=>t.remove(),300); },3000);
  }

  /* ─── Filtrar y ordenar ─── */
  function applyFilters() {
    const q     = matSearch.value.trim().toLowerCase();
    const est   = filtroEstado.value;
    const tipo  = filtroTipo.value;
    const grado = filtroGrado.value;

    filtered = MATRICULAS.filter(m => {
      const matchQ     = !q || m.nombre.toLowerCase().includes(q) || m.codigo.toLowerCase().includes(q) || m.doc.toLowerCase().includes(q);
      const matchEst   = !est   || m.estado === est;
      const matchTipo  = !tipo  || m.tipo   === tipo;
      const matchGrado = !grado || m.grado  === grado;
      return matchQ && matchEst && matchTipo && matchGrado;
    });

    filtered.sort((a,b) => {
      let va = a[sortCol] || '', vb = b[sortCol] || '';
      if (sortCol === 'nombre') { va=va.toLowerCase(); vb=vb.toLowerCase(); }
      if (va < vb) return sortDir==='asc'? -1:1;
      if (va > vb) return sortDir==='asc'?  1:-1;
      return 0;
    });

    currentPage = 1;
    selected.clear();
    renderAll();
  }

  /* ─── Render tabla ─── */
  function renderAll() {
    const total  = filtered.length;
    const start  = (currentPage-1)*perPage;
    const page   = filtered.slice(start, start+perPage);

    matCount.innerHTML = `Mostrando <strong>${total}</strong> solicitudes`;

    // Tabla
    if (total === 0) {
      matTbody.innerHTML = '';
      matEmpty.style.display = 'flex';
    } else {
      matEmpty.style.display = 'none';
      matTbody.innerHTML = page.map(m => `
        <tr data-id="${m.id}" class="${selected.has(m.id)?'selected':''}">
          <td class="th-check">
            <label class="mat-cb-wrap">
              <input type="checkbox" class="row-cb" data-id="${m.id}" ${selected.has(m.id)?'checked':''} />
              <span class="mat-cb"></span>
            </label>
          </td>
          <td><span style="font-size:.75rem;font-weight:700;color:var(--gris-texto)">${m.codigo}</span></td>
          <td>
            <div class="td-est">
              <div class="td-avatar" style="background:${m.colorBg};color:${m.colorTxt}">${m.iniciales}</div>
              <div>
                <div class="td-est-name">${m.nombre}</div>
                <div class="td-est-code">${m.doc}</div>
              </div>
            </div>
          </td>
          <td><span style="font-weight:600">${gradoLabel(m.grado)}</span></td>
          <td>${tipoBadge(m.tipo)}</td>
          <td style="font-size:.8rem;color:var(--gris-texto)">${formatDate(m.fecha)}</td>
          <td>${badgeHTML(m.estado)}</td>
          <td>
            <div style="font-size:.82rem;font-weight:600">${m.acudiente}</div>
            <div style="font-size:.7rem;color:var(--gris-texto)">${m.acuTel}</div>
          </td>
          <td>
            <div class="td-actions">
              <button class="tda-btn ver"      data-id="${m.id}" title="Ver detalle"><i class="fas fa-eye"></i></button>
              <button class="tda-btn aprobar"  data-id="${m.id}" title="Aprobar"
                ${['aprobada','rechazada'].includes(m.estado)?'disabled':''}><i class="fas fa-check"></i></button>
              <button class="tda-btn rechazar" data-id="${m.id}" title="Rechazar"
                ${['aprobada','rechazada'].includes(m.estado)?'disabled':''}><i class="fas fa-times"></i></button>
            </div>
          </td>
        </tr>
      `).join('');
    }

    // Cards
    matCardsGrid.innerHTML = page.map(m => `
      <div class="mcard" data-id="${m.id}">
        <div class="mcard-top">
          <div class="mcard-avatar" style="background:${m.colorBg};color:${m.colorTxt}">${m.iniciales}</div>
          <div>
            <div class="mcard-name">${m.nombre}</div>
            <div class="mcard-code">${m.codigo}</div>
          </div>
          <div class="mcard-badge">${badgeHTML(m.estado)}</div>
        </div>
        <div class="mcard-rows">
          <div class="mcard-row"><i class="fas fa-graduation-cap"></i>${gradoLabel(m.grado)} · ${tipoLabel[m.tipo]}</div>
          <div class="mcard-row"><i class="fas fa-calendar"></i>${formatDate(m.fecha)}</div>
          <div class="mcard-row"><i class="fas fa-user"></i>${m.acudiente}</div>
        </div>
        <div class="mcard-actions">
          <button class="mca-btn ver"      data-id="${m.id}"><i class="fas fa-eye"></i> Ver</button>
          <button class="mca-btn aprobar"  data-id="${m.id}" ${['aprobada','rechazada'].includes(m.estado)?'disabled':''}><i class="fas fa-check"></i> Aprobar</button>
          <button class="mca-btn rechazar" data-id="${m.id}" ${['aprobada','rechazada'].includes(m.estado)?'disabled':''}><i class="fas fa-times"></i> Rechazar</button>
        </div>
      </div>
    `).join('');

    renderPagination(total);
    updateBulkBar();
    attachRowEvents();
  }

  /* ─── Paginación ─── */
  function renderPagination(total) {
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    mpInfo.textContent = `Página ${currentPage} de ${totalPages}`;
    mpPrev.disabled = currentPage === 1;
    mpNext.disabled = currentPage === totalPages;

    mpPages.innerHTML = '';
    for (let i=1; i<=totalPages; i++) {
      const btn = document.createElement('button');
      btn.className = 'mp-page' + (i===currentPage?' active':'');
      btn.textContent = i;
      btn.addEventListener('click', ()=>{ currentPage=i; renderAll(); });
      mpPages.appendChild(btn);
    }
  }

  mpPrev.addEventListener('click', ()=>{ if(currentPage>1){ currentPage--; renderAll(); } });
  mpNext.addEventListener('click', ()=>{
    const tp=Math.ceil(filtered.length/perPage);
    if(currentPage<tp){ currentPage++; renderAll(); }
  });
  mpPerPage.addEventListener('change', ()=>{ perPage=parseInt(mpPerPage.value); currentPage=1; renderAll(); });

  /* ─── Eventos de fila ─── */
  function attachRowEvents() {
    // Ver detalle (fila tabla)
    document.querySelectorAll('.tda-btn.ver, .mca-btn.ver').forEach(btn => {
      btn.addEventListener('click', e => { e.stopPropagation(); openDetalle(parseInt(btn.dataset.id)); });
    });
    // Click en fila de tabla
    document.querySelectorAll('#matTbody tr').forEach(tr => {
      tr.addEventListener('click', e => {
        if (e.target.closest('.tda-btn') || e.target.closest('.mat-cb-wrap')) return;
        openDetalle(parseInt(tr.dataset.id));
      });
    });
    // Click en card
    document.querySelectorAll('.mcard').forEach(card => {
      card.addEventListener('click', e => {
        if (e.target.closest('.mca-btn')) return;
        openDetalle(parseInt(card.dataset.id));
      });
    });
    // Aprobar rápido
    document.querySelectorAll('.tda-btn.aprobar, .mca-btn.aprobar').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        const id = parseInt(btn.dataset.id);
        confirmAction('approve', [id], '¿Aprobar esta matrícula?', 'La matrícula pasará a estado Aprobada.');
      });
    });
    // Rechazar rápido
    document.querySelectorAll('.tda-btn.rechazar, .mca-btn.rechazar').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        const id = parseInt(btn.dataset.id);
        confirmAction('reject', [id], '¿Rechazar esta matrícula?', 'Esta acción no se puede deshacer fácilmente.');
      });
    });
    // Checkbox filas
    document.querySelectorAll('.row-cb').forEach(cb => {
      cb.addEventListener('change', () => {
        const id = parseInt(cb.dataset.id);
        cb.checked ? selected.add(id) : selected.delete(id);
        const tr = cb.closest('tr');
        if (tr) tr.classList.toggle('selected', cb.checked);
        updateSelectAll();
        updateBulkBar();
      });
    });
  }

  /* ─── Seleccionar todos ─── */
  selectAll.addEventListener('change', () => {
    const page = filtered.slice((currentPage-1)*perPage, currentPage*perPage);
    page.forEach(m => selectAll.checked ? selected.add(m.id) : selected.delete(m.id));
    renderAll();
  });
  function updateSelectAll() {
    const page = filtered.slice((currentPage-1)*perPage, currentPage*perPage);
    selectAll.checked = page.length > 0 && page.every(m => selected.has(m.id));
  }

  /* ─── Bulk bar ─── */
  function updateBulkBar() {
    const cnt = selected.size;
    matBulkBar.style.display = cnt > 0 ? 'flex' : 'none';
    mbbCount.textContent = cnt;
  }
  document.getElementById('btnBulkApprove').addEventListener('click', () => {
    confirmAction('approve', [...selected], `¿Aprobar ${selected.size} matrículas?`, 'Todas pasarán a estado Aprobada.');
  });
  document.getElementById('btnBulkReject').addEventListener('click', () => {
    confirmAction('reject', [...selected], `¿Rechazar ${selected.size} matrículas?`, 'Esta acción afectará varias solicitudes.');
  });
  document.getElementById('btnBulkCancel').addEventListener('click', () => {
    selected.clear(); renderAll();
  });

  /* ─── Ordenar columnas ─── */
  document.querySelectorAll('.sortable').forEach(th => {
    th.addEventListener('click', () => {
      const col = th.dataset.col;
      if (sortCol === col) { sortDir = sortDir==='asc'?'desc':'asc'; }
      else { sortCol=col; sortDir='asc'; }
      document.querySelectorAll('.sortable').forEach(t => t.classList.remove('asc','desc'));
      th.classList.add(sortDir);
      applyFilters();
    });
  });

  /* ─── Filtros ─── */
  matSearch.addEventListener('input', () => {
    matSearchClear.style.display = matSearch.value ? 'flex' : 'none';
    applyFilters();
  });
  matSearchClear.addEventListener('click', () => { matSearch.value=''; matSearchClear.style.display='none'; applyFilters(); });
  filtroEstado.addEventListener('change', applyFilters);
  filtroTipo.addEventListener('change',   applyFilters);
  filtroGrado.addEventListener('change',  applyFilters);
  btnClearFilters.addEventListener('click', clearAllFilters);
  document.getElementById('btnEmptyReset').addEventListener('click', clearAllFilters);
  function clearAllFilters() {
    matSearch.value=''; matSearchClear.style.display='none';
    filtroEstado.value=''; filtroTipo.value=''; filtroGrado.value='';
    applyFilters();
  }

  /* ─── KPI clic → filtrar por estado ─── */
  mkpiCards.forEach(card => {
    card.addEventListener('click', () => {
      const f = card.dataset.filter;
      mkpiCards.forEach(c => c.classList.remove('active-filter'));
      if (filtroEstado.value === f) {
        filtroEstado.value='';
        applyFilters();
      } else {
        card.classList.add('active-filter');
        filtroEstado.value = f;
        applyFilters();
      }
    });
  });

  /* ─── Vista tabla / cards ─── */
  btnViewTable.addEventListener('click', () => {
    currentView='table';
    viewTable.style.display=''; viewCards.style.display='none';
    btnViewTable.classList.add('active'); btnViewCards.classList.remove('active');
  });
  btnViewCards.addEventListener('click', () => {
    currentView='cards';
    viewTable.style.display='none'; viewCards.style.display='';
    btnViewCards.classList.add('active'); btnViewTable.classList.remove('active');
  });

  /* ─── Modal detalle ─── */
  function openDetalle(id) {
    const m = MATRICULAS.find(x => x.id===id);
    if (!m) return;
    currentDetalle = m;

    // Header
    document.getElementById('mdAvatar').textContent  = m.iniciales;
    document.getElementById('mdAvatar').style.background = m.colorBg;
    document.getElementById('mdAvatar').style.color      = m.colorTxt;
    document.getElementById('mdNombre').textContent = m.nombre;
    document.getElementById('mdSub').textContent    = `${gradoLabel(m.grado)} · ${tipoLabel[m.tipo]}`;
    document.getElementById('mdBadge').outerHTML    = badgeHTML(m.estado).replace('td-badge','td-badge md-badge');

    // Tab estudiante
    document.getElementById('det-nombre').textContent  = m.nombre;
    document.getElementById('det-doc').textContent     = m.doc;
    document.getElementById('det-fechaNac').textContent= formatDate(m.fechaNac);
    document.getElementById('det-genero').textContent  = m.genero;
    document.getElementById('det-dir').textContent     = m.dir;
    document.getElementById('det-tel').textContent     = m.tel;
    document.getElementById('det-email').textContent   = m.email || '—';
    document.getElementById('det-eps').textContent     = m.eps;

    // Tab acudiente
    document.getElementById('det-acuNombre').textContent = m.acudiente;
    document.getElementById('det-acuParent').textContent  = m.acuParent;
    document.getElementById('det-acuDoc').textContent     = m.acuDoc;
    document.getElementById('det-acuTel').textContent     = m.acuTel;
    document.getElementById('det-acuEmail').textContent   = m.acuEmail;
    document.getElementById('det-acuOcup').textContent    = m.acuOcup;

    // Tab académico
    document.getElementById('det-grado').textContent      = gradoLabel(m.grado);
    document.getElementById('det-jornada').textContent    = m.jornada;
    document.getElementById('det-tipo').textContent       = tipoLabel[m.tipo];
    document.getElementById('det-colAnterior').textContent= m.colAnterior;
    document.getElementById('det-anio').textContent       = m.anio;
    document.getElementById('det-nee').textContent        = m.nee;

    // Timeline historial
    const tlEl = document.getElementById('det-timeline');
    tlEl.innerHTML = buildTimeline(m);

    // Limpiar obs
    document.getElementById('detObs').value = '';

    // Botones acción según estado
    const canAct = !['aprobada','rechazada'].includes(m.estado);
    document.getElementById('btnDetalleAprobar').style.display  = canAct ? 'flex' : 'none';
    document.getElementById('btnDetalleRechazar').style.display = canAct ? 'flex' : 'none';
    document.getElementById('btnDetalleRevision').style.display = (canAct && m.estado!=='revision') ? 'flex' : 'none';

    // Activar tab inicial
    document.querySelectorAll('.md-tab').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.md-tab-panel').forEach(p=>p.classList.remove('active'));
    document.querySelector('.md-tab[data-tab="estudiante"]').classList.add('active');
    document.getElementById('tab-estudiante').classList.add('active');

    modalDetalle.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function buildTimeline(m) {
    const events = [
      { type:'created',  icon:'fa-plus',   title:'Solicitud creada', date:formatDate(m.fecha), note:'Solicitud recibida en el sistema.' },
    ];
    if (m.estado === 'revision')  events.push({ type:'revision', icon:'fa-search', title:'Enviada a revisión', date:'Reciente', note:'' });
    if (m.estado === 'aprobada')  events.push({ type:'approved', icon:'fa-check',  title:'Matrícula aprobada', date:'Reciente', note:'Aprobada por la rectoría.' });
    if (m.estado === 'rechazada') events.push({ type:'rejected', icon:'fa-times',  title:'Matrícula rechazada', date:'Reciente', note:'Rechazada por la rectoría.' });
    return events.map(e=>`
      <li class="md-tl-item">
        <div class="md-tl-dot ${e.type}"><i class="fas ${e.icon}"></i></div>
        <div class="md-tl-body">
          <div class="md-tl-title">${e.title}</div>
          <div class="md-tl-meta">${e.date}</div>
          ${e.note?`<div class="md-tl-note">${e.note}</div>`:''}
        </div>
      </li>
    `).join('');
  }

  // Tabs del modal
  document.querySelectorAll('.md-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.md-tab').forEach(t=>t.classList.remove('active'));
      document.querySelectorAll('.md-tab-panel').forEach(p=>p.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById('tab-'+tab.dataset.tab).classList.add('active');
    });
  });

  // Cerrar modal detalle
  const closeDetalle = () => { modalDetalle.classList.remove('open'); document.body.style.overflow=''; };
  document.getElementById('modalDetalleClose').addEventListener('click', closeDetalle);
  document.getElementById('btnDetalleCerrar').addEventListener('click', closeDetalle);
  modalDetalle.addEventListener('click', e=>{ if(e.target===modalDetalle) closeDetalle(); });

  // Acciones desde el detalle
  document.getElementById('btnDetalleAprobar').addEventListener('click', ()=>{
    if (!currentDetalle) return;
    confirmAction('approve',[currentDetalle.id],'¿Aprobar esta matrícula?','La matrícula pasará a estado Aprobada.');
  });
  document.getElementById('btnDetalleRechazar').addEventListener('click', ()=>{
    if (!currentDetalle) return;
    confirmAction('reject',[currentDetalle.id],'¿Rechazar esta matrícula?','Esta acción cambiará el estado de la solicitud.');
  });
  document.getElementById('btnDetalleRevision').addEventListener('click', ()=>{
    if (!currentDetalle) return;
    confirmAction('revision',[currentDetalle.id],'¿Enviar a revisión?','La matrícula quedará en estado En revisión.');
  });

  /* ─── Modal confirmación ─── */
  function confirmAction(type, ids, title, msg) {
    pendingAction = { type, ids };
    const mcIcon  = document.getElementById('mcIcon');
    const mcTitle = document.getElementById('mcTitle');
    const mcMsg   = document.getElementById('mcMsg');
    const btnOk   = document.getElementById('btnConfirmOk');

    mcTitle.textContent = title;
    mcMsg.textContent   = msg;
    mcIcon.className    = 'mc-icon ' + (type==='approve'?'approve':type==='reject'?'reject':'info');
    mcIcon.innerHTML    = type==='approve'?'<i class="fas fa-check-circle"></i>':type==='reject'?'<i class="fas fa-times-circle"></i>':'<i class="fas fa-search"></i>';
    btnOk.className     = 'mc-btn-ok' + (type==='reject'?' danger':'');

    modalConfirm.classList.add('open');
  }

  document.getElementById('btnConfirmCancel').addEventListener('click', ()=>{ modalConfirm.classList.remove('open'); pendingAction=null; });
  modalConfirm.addEventListener('click', e=>{ if(e.target===modalConfirm){ modalConfirm.classList.remove('open'); pendingAction=null; } });

  document.getElementById('btnConfirmOk').addEventListener('click', ()=>{
    if (!pendingAction) return;
    const { type, ids } = pendingAction;
    const newEstado = type==='approve'?'aprobada':type==='reject'?'rechazada':'revision';
    ids.forEach(id => {
      const m = MATRICULAS.find(x=>x.id===id);
      if (m) m.estado = newEstado;
    });
    selected.clear();
    modalConfirm.classList.remove('open');
    if (currentDetalle && ids.includes(currentDetalle.id)) closeDetalle();
    pendingAction = null;
    applyFilters();
    updateKPIs();
    const msgs = { approve:'✓ Matrícula(s) aprobada(s)', reject:'Matrícula(s) rechazada(s)', revision:'Enviada(s) a revisión' };
    showToast(msgs[type] || 'Acción completada', type==='reject'?'error':type==='revision'?'info':'success');
  });

  /* ─── Actualizar KPIs ─── */
  function updateKPIs() {
    const total    = MATRICULAS.length;
    const pend     = MATRICULAS.filter(m=>m.estado==='pendiente').length;
    const rev      = MATRICULAS.filter(m=>m.estado==='revision').length;
    const apro     = MATRICULAS.filter(m=>m.estado==='aprobada').length;
    const rech     = MATRICULAS.filter(m=>m.estado==='rechazada').length;
    const vals     = [total, pend, rev, apro, rech];
    document.querySelectorAll('.mkpi-val').forEach((el,i) => { el.textContent=vals[i]; });
    const fills = document.querySelectorAll('.mkpi-bar-fill');
    if (fills[0]) fills[0].style.width='100%';
    if (fills[1]) fills[1].style.width=(pend/total*100)+'%';
    if (fills[2]) fills[2].style.width=(rev/total*100)+'%';
    if (fills[3]) fills[3].style.width=(apro/total*100)+'%';
    if (fills[4]) fills[4].style.width=(rech/total*100)+'%';
  }

  /* ─── Exportar ─── */
  document.getElementById('btnExportExcel').addEventListener('click', ()=>showToast('Exportando a Excel…','info'));
  document.getElementById('btnExportPdf').addEventListener('click',   ()=>showToast('Generando PDF…','info'));

  /* ─── Animación barras KPI al cargar ─── */
  const barFills = document.querySelectorAll('.mkpi-bar-fill');
  barFills.forEach(b => { const w=b.style.width; b.style.width='0'; setTimeout(()=>{ b.style.transition='width 1.2s cubic-bezier(.4,0,.2,1)'; b.style.width=w; },300); });

  /* ─── Init ─── */
  applyFilters();

});