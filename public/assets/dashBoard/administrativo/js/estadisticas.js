/* =============================================
   COLEGIO SAN CRISTÓBAL — ESTADÍSTICAS JS
   Barras horizontales + Donas canvas corregidas
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─── Datos ─── */
  const BASE = {
    promInst:4.1, asistencia:94, riesgoCount:8, destacados:187,
    grados:[
      { grado:'Preescolar',est:42,  prom:4.6,asist:97,aprobados:42, riesgo:0,mejorArea:'Artística',    areaC:'—',          trend:'up'   },
      { grado:'1°',        est:98,  prom:4.5,asist:96,aprobados:97, riesgo:1,mejorArea:'Matemáticas',  areaC:'—',          trend:'up'   },
      { grado:'2°',        est:95,  prom:4.4,asist:95,aprobados:94, riesgo:1,mejorArea:'Español',      areaC:'—',          trend:'eq'   },
      { grado:'3°',        est:102, prom:4.3,asist:94,aprobados:100,riesgo:2,mejorArea:'Ciencias',     areaC:'Matemáticas',trend:'up'   },
      { grado:'4°',        est:99,  prom:4.2,asist:93,aprobados:97, riesgo:2,mejorArea:'Español',      areaC:'Matemáticas',trend:'eq'   },
      { grado:'5°',        est:105, prom:4.1,asist:92,aprobados:103,riesgo:2,mejorArea:'Ciencias',     areaC:'Inglés',     trend:'down' },
      { grado:'6°',        est:110, prom:4.0,asist:91,aprobados:107,riesgo:3,mejorArea:'Español',      areaC:'Matemáticas',trend:'eq'   },
      { grado:'7°',        est:108, prom:3.9,asist:90,aprobados:104,riesgo:4,mejorArea:'Historia',     areaC:'Física',     trend:'down' },
      { grado:'8°',        est:112, prom:3.8,asist:89,aprobados:106,riesgo:6,mejorArea:'Inglés',       areaC:'Química',    trend:'down' },
      { grado:'9°',        est:106, prom:4.0,asist:91,aprobados:103,riesgo:3,mejorArea:'Ciencias',     areaC:'Matemáticas',trend:'up'   },
      { grado:'10°',       est:104, prom:4.2,asist:93,aprobados:102,riesgo:2,mejorArea:'Matemáticas',  areaC:'Química',    trend:'up'   },
      { grado:'11°',       est:159, prom:4.3,asist:95,aprobados:157,riesgo:2,mejorArea:'Filosofía',    areaC:'Física',     trend:'up'   },
    ],
    areas:[
      { nombre:'Matemáticas',        prom:3.9 },
      { nombre:'Español',            prom:4.3 },
      { nombre:'Ciencias Naturales', prom:4.1 },
      { nombre:'Inglés',             prom:3.8 },
      { nombre:'Historia',           prom:4.2 },
      { nombre:'Física',             prom:3.7 },
      { nombre:'Química',            prom:3.6 },
      { nombre:'Educación Física',   prom:4.5 },
      { nombre:'Artística',          prom:4.4 },
      { nombre:'Filosofía',          prom:4.0 },
    ],
    rendimiento:[
      { label:'Excelente (≥ 4.5)', val:312, color:'#2d7a4f' },
      { label:'Bueno (4.0–4.4)',   val:589, color:'#38a169' },
      { label:'Aceptable (3.5–3.9)',val:331,color:'#d97706' },
      { label:'En riesgo (< 3.5)', val:8,   color:'#e53e3e' },
    ],
    riesgoDetalle:[
      { nombre:'Diego González',  ini:'DG', grado:'8°A', prom:2.9, asist:65, areas:[{n:'Matemáticas',v:2.1},{n:'Química',v:2.5},{n:'Física',v:2.8}] },
      { nombre:'Carlos Pérez',    ini:'CP', grado:'10°B',prom:3.1, asist:78, areas:[{n:'Química',v:2.7},{n:'Física',v:3.0},{n:'Inglés',v:3.2}] },
      { nombre:'María Rodríguez', ini:'MR', grado:'7°C', prom:3.2, asist:72, areas:[{n:'Matemáticas',v:2.9},{n:'Física',v:3.1},{n:'Inglés',v:3.3}] },
      { nombre:'Julián Herrera',  ini:'JH', grado:'8°B', prom:3.0, asist:68, areas:[{n:'Química',v:2.5},{n:'Matemáticas',v:2.8},{n:'Física',v:3.0}] },
      { nombre:'Valentina Cruz',  ini:'VC', grado:'6°A', prom:3.3, asist:75, areas:[{n:'Matemáticas',v:3.0},{n:'Inglés',v:3.2},{n:'Física',v:3.4}] },
      { nombre:'Andrés Morales',  ini:'AM', grado:'9°B', prom:3.2, asist:71, areas:[{n:'Física',v:2.8},{n:'Química',v:3.0},{n:'Matemáticas',v:3.3}] },
      { nombre:'Sofía Jiménez',   ini:'SJ', grado:'7°A', prom:3.1, asist:70, areas:[{n:'Inglés',v:2.7},{n:'Matemáticas',v:2.9},{n:'Química',v:3.2}] },
      { nombre:'Tomás Vargas',    ini:'TV', grado:'8°C', prom:3.3, asist:74, areas:[{n:'Química',v:2.9},{n:'Física',v:3.1},{n:'Matemáticas',v:3.3}] },
    ],
    genero:[
      { label:'Femenino',  val:634, color:'#6b46c1' },
      { label:'Masculino', val:606, color:'#3182ce' },
    ],
  };

  // Generar variantes para otros períodos
  const DATOS = {};
  ['2025-1','2025-2','2025-3','2025-4','2024-1','2024-2','2024-3','2024-4'].forEach(key => {
    const d = JSON.parse(JSON.stringify(BASE));
    if (key !== '2025-1') {
      const delta = (Math.random()-.5)*.4;
      d.promInst = Math.max(3.5, Math.min(4.8, parseFloat((d.promInst+delta).toFixed(1))));
      d.asistencia = Math.max(80, Math.min(99, d.asistencia+Math.round(delta*5)));
    }
    DATOS[key] = d;
  });

  let actual = DATOS['2025-1'];

  /* ─── Helpers ─── */
  function colorProm(p) {
    if (p >= 4.3) return { cls:'green', hex:'#2d7a4f' };
    if (p >= 4.0) return { cls:'lime',  hex:'#38a169' };
    if (p >= 3.5) return { cls:'gold',  hex:'#d97706' };
    return               { cls:'red',   hex:'#e53e3e' };
  }

  function toast(msg, type='success') {
    const c = type==='success'
      ? {bg:'#e8f5ee',border:'#2d7a4f',text:'#1e5436',icon:'fas fa-check-circle'}
      : {bg:'#eff6ff',border:'#3182ce',text:'#2c5282',icon:'fas fa-info-circle'};
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:${c.bg};border:1.5px solid ${c.border};color:${c.text};padding:12px 18px;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 28px rgba(0,0,0,.12);transform:translateY(16px);opacity:0;transition:all .3s ease;max-width:320px;`;
    t.innerHTML = `<i class="${c.icon}" style="font-size:1rem;flex-shrink:0"></i>${msg}`;
    document.body.appendChild(t);
    requestAnimationFrame(() => { t.style.transform='translateY(0)'; t.style.opacity='1'; });
    setTimeout(() => { t.style.transform='translateY(16px)'; t.style.opacity='0'; setTimeout(()=>t.remove(),300); }, 3000);
  }

  /* ─── KPIs ─── */
  function renderKPIs(d) {
    document.getElementById('kpiPromInst').textContent   = d.promInst;
    document.getElementById('kpiAsistencia').textContent = d.asistencia + '%';
    document.getElementById('kpiRiesgo').textContent     = d.riesgoCount;
    document.getElementById('kpiDestacados').textContent = d.destacados;
  }

  /* ─── Barras horizontales ─── */
  function renderBarras(grados, modo) {
    const contenedor = document.getElementById('barrasContainer');
    contenedor.innerHTML = '';

    const maxVal = modo === 'asistencia' ? 100 : 5;

    grados.forEach(g => {
      const val  = modo === 'asistencia' ? g.asist : g.prom;
      const pct  = (val / maxVal) * 100;
      const etiq = modo === 'asistencia' ? val + '%' : val.toFixed(1);
      const col  = modo === 'asistencia'
        ? (val >= 95 ? colorProm(4.5) : val >= 90 ? colorProm(4.1) : val >= 80 ? colorProm(3.7) : colorProm(3.0))
        : colorProm(val);

      const row = document.createElement('div');
      row.className = 'est-bar-row';
      row.innerHTML = `
        <div class="est-bar-label">${g.grado}</div>
        <div class="est-bar-track">
          <div class="est-bar-fill ${col.cls}" style="width:0%">
            <span>${etiq}</span>
          </div>
        </div>
      `;
      contenedor.appendChild(row);
    });

    // Animar después de insertar en el DOM
    requestAnimationFrame(() => {
      setTimeout(() => {
        const fills = contenedor.querySelectorAll('.est-bar-fill');
        grados.forEach((g, i) => {
          const val = modo === 'asistencia' ? g.asist : g.prom;
          const pct = (val / maxVal) * 100;
          fills[i].style.width = pct + '%';
        });
      }, 80);
    });
  }

  /* ─── Dona canvas ─── */
  function drawDona(canvasId, segmentos) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    // Esperar a que el canvas tenga tamaño real
    const draw = () => {
      const W   = canvas.width;
      const H   = canvas.height;
      const ctx = canvas.getContext('2d');
      const cx  = W / 2;
      const cy  = H / 2;
      const r   = Math.min(W, H) / 2 - 6;
      const hole= 0.60; // 60% de hueco

      const total = segmentos.reduce((s, x) => s + x.val, 0);
      ctx.clearRect(0, 0, W, H);

      let angulo = -Math.PI / 2;

      segmentos.forEach(seg => {
        const porcion = (seg.val / total) * Math.PI * 2;

        // Sector exterior
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, angulo, angulo + porcion);
        ctx.closePath();
        ctx.fillStyle = seg.color;
        ctx.fill();

        angulo += porcion;
      });

      // Hueco central blanco
      ctx.beginPath();
      ctx.arc(cx, cy, r * hole, 0, Math.PI * 2);
      ctx.fillStyle = '#ffffff';
      ctx.fill();
    };

    // Dibujar inmediatamente y también tras un pequeño delay
    draw();
    setTimeout(draw, 100);
  }

  function renderLeyendaDona(containerId, segmentos) {
    const el = document.getElementById(containerId);
    if (!el) return;
    const total = segmentos.reduce((s, x) => s + x.val, 0);
    el.innerHTML = segmentos.map(s => `
      <li class="edl-item">
        <div class="edl-left">
          <span class="edl-dot" style="background:${s.color}"></span>
          <span class="edl-label">${s.label}</span>
        </div>
        <div class="edl-vals">
          <span class="edl-val">${s.val.toLocaleString()}</span>
          <span class="edl-pct">${Math.round(s.val / total * 100)}%</span>
        </div>
      </li>
    `).join('');
  }

  /* ─── Rendimiento por área ─── */
  function renderAreas(areas) {
    const el = document.getElementById('areasList');
    el.innerHTML = areas.map(a => {
      const col = colorProm(a.prom);
      const pct = (a.prom / 5) * 100;
      return `
        <div class="est-area-row">
          <div class="est-area-top">
            <span class="est-area-name">${a.nombre}</span>
            <span class="est-area-val" style="color:${col.hex}">${a.prom.toFixed(1)}</span>
          </div>
          <div class="est-area-bar-track">
            <div class="est-area-bar-fill" data-pct="${pct}"
              style="width:0%;background:${col.hex}"></div>
          </div>
        </div>
      `;
    }).join('');

    // Animar
    requestAnimationFrame(() => {
      setTimeout(() => {
        el.querySelectorAll('.est-area-bar-fill').forEach(b => {
          b.style.transition = 'width 1.1s cubic-bezier(.4,0,.2,1)';
          b.style.width = b.dataset.pct + '%';
        });
      }, 80);
    });
  }

  /* ─── Tabla grados ─── */
  function renderTabla(grados) {
    const tbody = document.getElementById('tablaGradosTbody');
    tbody.innerHTML = grados.map(g => {
      const col   = colorProm(g.prom);
      const rCls  = g.riesgo === 0 ? 'none' : g.riesgo <= 2 ? 'low' : 'high';
      const rIcon = g.riesgo === 0 ? 'fa-check' : 'fa-exclamation-circle';
      const trend = g.trend === 'up'
        ? `<span class="td-trend-up"><i class="fas fa-arrow-up"></i> Subió</span>`
        : g.trend === 'down'
          ? `<span class="td-trend-down"><i class="fas fa-arrow-down"></i> Bajó</span>`
          : `<span class="td-trend-eq"><i class="fas fa-minus"></i> Estable</span>`;
      return `
        <tr>
          <td class="td-grado">${g.grado}</td>
          <td>${g.est}</td>
          <td><span class="td-prom ${col.cls}">${g.prom.toFixed(1)}</span></td>
          <td>${g.asist}%</td>
          <td>${g.aprobados} <small style="color:var(--gris-texto)">(${Math.round(g.aprobados/g.est*100)}%)</small></td>
          <td><span class="td-riesgo-badge ${rCls}"><i class="fas ${rIcon}"></i> ${g.riesgo}</span></td>
          <td style="color:var(--verde-principal);font-weight:600;font-size:.8rem">${g.mejorArea}</td>
          <td style="color:#e53e3e;font-weight:600;font-size:.8rem">${g.areaC}</td>
          <td>${trend}</td>
        </tr>
      `;
    }).join('');
  }

  /* ─── Estudiantes en riesgo ─── */
  function renderRiesgo(lista) {
    document.getElementById('riesgoBadge').textContent = lista.length + ' estudiantes';
    document.getElementById('riesgoList').innerHTML = lista.map(e => `
      <div class="eriesgo-card">
        <div class="erc-top">
          <div class="erc-avatar">${e.ini}</div>
          <div>
            <div class="erc-name">${e.nombre}</div>
            <div class="erc-sub">Grado ${e.grado}</div>
          </div>
          <div class="erc-prom">${e.prom.toFixed(1)}</div>
        </div>
        <div class="erc-areas">
          ${e.areas.map(a => `
            <div class="erc-area-row">
              <span class="erc-area-name">${a.n}</span>
              <div class="erc-area-bar">
                <div class="erc-area-fill" style="width:${(a.v/5)*100}%"></div>
              </div>
              <span class="erc-area-val">${a.v.toFixed(1)}</span>
            </div>
          `).join('')}
        </div>
        <div class="erc-footer">
          <span class="erc-asist">Asistencia: <strong>${e.asist}%</strong></span>
          <button class="erc-btn"><i class="fas fa-eye"></i> Ver perfil</button>
        </div>
      </div>
    `).join('');
  }

  /* ─── Búsqueda tabla ─── */
  document.getElementById('tablaSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tablaGradosTbody tr').forEach(tr => {
      tr.style.display = tr.cells[0].textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });

  /* ─── Selector modo barras ─── */
  document.getElementById('selectBarras').addEventListener('change', function() {
    renderBarras(actual.grados, this.value);
  });

  /* ─── Selector grado áreas ─── */
  document.getElementById('selectGradoArea').addEventListener('change', function() {
    renderAreas(actual.areas); // En producción filtrarías por grado real
  });

  /* ─── Aplicar período ─── */
  document.getElementById('btnAplicar').addEventListener('click', () => {
    const anio    = document.getElementById('filtroAnio').value;
    const periodo = document.getElementById('filtroPeriodo').value;
    const key     = `${anio}-${periodo}`;
    actual = DATOS[key] || DATOS['2025-1'];
    renderAll(actual);
    toast(`Período ${periodo} · ${anio} cargado`, 'success');
  });

  /* ─── Exportar ─── */
  document.getElementById('btnExportPdf').addEventListener('click',   () => toast('Generando reporte PDF…','info'));
  document.getElementById('btnExportTabla').addEventListener('click', () => toast('Exportando a Excel…','info'));

  /* ─── Render completo ─── */
  function renderAll(d) {
    renderKPIs(d);
    renderBarras(d.grados, 'promedio');
    document.getElementById('selectBarras').value = 'promedio';

    // Donas
    drawDona('donaRendimiento', d.rendimiento);
    renderLeyendaDona('donaLegend', d.rendimiento);

    const riesgoSegs = [
      { label:'En riesgo',  val:d.riesgoCount,       color:'#e53e3e' },
      { label:'Sin riesgo', val:1240-d.riesgoCount,  color:'#2d7a4f' },
    ];
    drawDona('donaRiesgo', riesgoSegs);
    renderLeyendaDona('riesgoLegend', riesgoSegs);

    drawDona('donaGenero', d.genero);
    renderLeyendaDona('generoLegend', d.genero);

    // Textos centro donas
    document.getElementById('donaCenterVal').textContent   = '1,240';
    document.getElementById('riesgoCenterVal').textContent = d.riesgoCount;
    document.getElementById('generoCenterVal').textContent = '1,240';

    renderAreas(d.areas);
    renderTabla(d.grados);
    renderRiesgo(d.riesgoDetalle);
  }

  /* ─── Init ─── */
  renderAll(actual);

  // Redibujar donas si el layout cambia (sidebar colapsa, etc.)
  document.getElementById('sidebarCollapse')?.addEventListener('click', () => {
    setTimeout(() => {
      drawDona('donaRendimiento', actual.rendimiento);
      drawDona('donaRiesgo', [
        { label:'En riesgo',  val:actual.riesgoCount,      color:'#e53e3e' },
        { label:'Sin riesgo', val:1240-actual.riesgoCount, color:'#2d7a4f' },
      ]);
      drawDona('donaGenero', actual.genero);
    }, 320);
  });

});