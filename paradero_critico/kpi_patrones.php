<?php
require_once __DIR__ . '/../perfil_usuario/verificar_sesion.php';
require_once __DIR__ . '/../perfil_usuario/verificar_permisos.php';
require_once __DIR__ . '/../perfil_usuario/_layout.php';

// Control de acceso por modulo: requiere permiso 'ver' en 'kpi_patrones'
if (($usuario_rol ?? '') !== 'admin'
    && !tienePermiso((int) $usuario_id, 'kpi_patrones', 'ver')) {
    header('Location: /bd_op/perfil_usuario/acceso_denegado.php');
    exit();
}

// Las paginas fuera de perfil_usuario deben indicar la ruta base del layout
$GLOBALS['cta_pu_base'] = '../perfil_usuario/';
layout_inicio('Patrones e Indicadores', 'kpi_patrones');
?>

<!-- Librerias de graficos propias de esta pagina -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
    /* ===== Paleta del login aplicada al contenido de esta pagina ===== */
    :root { --primary: #002E90; --primary-dark: #002167; }

    .kpp .text-primary { color: var(--primary) !important; }
    .kpp .btn-primary, .kpp .btn-primary:focus { background-color: var(--primary); border-color: var(--primary); }
    .kpp .btn-primary:hover, .kpp .btn-primary:active { background-color: var(--primary-dark); border-color: var(--primary-dark); }

    .page-header    { background: white; border-radius: 12px; padding: 24px 28px;
                      margin: 0 0 14px; box-shadow: 0 2px 10px rgba(0,0,0,.07); }
    .page-header h1 { color: var(--primary); font-size: 1.5rem; font-weight: 700; margin: 0 0 4px; }
    .page-header p  { color: #607D8B; margin: 0; font-size: .9rem; }
    .badge-periodo  { background: #E8F0FD; color: var(--primary); font-size: .77rem;
                      padding: 4px 12px; border-radius: 20px; font-weight: 600; }
    .filter-card    { background: white; border-radius: 12px; padding: 16px 20px;
                      margin-bottom: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .kpi-card       { background: white; border-radius: 12px; padding: 18px 16px;
                      box-shadow: 0 2px 10px rgba(0,0,0,.07); height: 100%; }
    .kpi-icon       { font-size: 2rem; margin-bottom: 5px; }
    .kpi-value      { font-size: 1.85rem; font-weight: 800; line-height: 1; }
    .kpi-label      { font-size: .77rem; color: #78909C; margin-top: 4px;
                      text-transform: uppercase; letter-spacing: .5px; }
    .kpi-sub        { font-size: .76rem; color: #90A4AE; margin-top: 4px; }
    .chart-card     { background: white; border-radius: 12px; padding: 20px;
                      box-shadow: 0 2px 10px rgba(0,0,0,.07); margin-bottom: 14px; }
    .chart-card h5  { color: var(--primary); font-weight: 700; font-size: .96rem; margin-bottom: 2px; }
    .chart-card small { color: #90A4AE; font-size: .78rem; }
    .nota           { background: #FFF8E1; border-left: 4px solid #FFB300;
                      padding: 8px 14px; border-radius: 0 8px 8px 0;
                      font-size: .8rem; color: #5D4037; margin-bottom: 14px; }
    .estado-inicial { background: white; border-radius: 12px; padding: 50px 20px;
                      text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,.06);
                      color: #90A4AE; }
    .estado-inicial i { font-size: 3rem; margin-bottom: 12px; }
    .alert-error    { background: #FFEBEE; border-left: 4px solid #E53935;
                      padding: 10px 14px; border-radius: 0 8px 8px 0;
                      font-size: .83rem; color: #B71C1C; margin-bottom: 14px; display: none; }
    .chart-loading  { text-align: center; padding: 40px 0; color: #90A4AE; font-size: .9rem; }
    .btn-analizar   { background: var(--primary); border: none; padding: 9px 26px;
                      font-weight: 600; border-radius: 8px; color: #fff; }
    .btn-analizar:hover { background: var(--primary-dark); color: #fff; }
    #kpiSpinner, #resultados { display: none; }
</style>

<div class="kpp">

    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1><i class="bi bi-graph-up-arrow me-2"></i>Patrones e Indicadores Historicos</h1>
            <p>Comportamiento de saturacion por dia de semana, franja horaria y ruta</p>
        </div>
        <span class="badge-periodo">
            <i class="bi bi-calendar-range me-1"></i>
            Datos: Abr 2025 - Feb 2026
        </span>
    </div>

    <div class="filter-card">
        <div class="row align-items-end g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Ruta</label>
                <select id="ruta" class="form-select">
                    <option value="TODAS">Todas las rutas (301, 303, 305, 336)</option>
                    <option value="301">Ruta 301</option>
                    <option value="303">Ruta 303</option>
                    <option value="305">Ruta 305</option>
                    <option value="336">Ruta 336</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Sentido</label>
                <select id="sentido" class="form-select">
                    <option value="NS">Norte-Sur (NS)</option>
                    <option value="SN">Sur-Norte (SN)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-analizar w-100" id="btnAnalizar" onclick="cargarTodo()">
                    <i class="bi bi-search me-2"></i>Analizar
                </button>
            </div>
        </div>
    </div>

    <div class="nota">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Metodologia:</strong> Un viaje se considera saturado cuando acumula 80 o mas validaciones
        en su recorrido completo. El analisis exacto por paradero critico se realiza en el modulo de analisis diario.
    </div>

    <div class="alert-error" id="errorBox">
        <i class="bi bi-exclamation-triangle me-2"></i><span id="errorMsg"></span>
    </div>

    <!-- Estado inicial antes de cualquier consulta -->
    <div id="estadoInicial" class="estado-inicial">
        <i class="bi bi-bar-chart-line d-block text-primary"></i>
        <p class="mb-1 fw-semibold" style="color:#546E7A;">Seleccione los filtros y haga clic en Analizar</p>
        <small>Las consultas sobre el periodo completo pueden tomar algunos segundos</small>
    </div>

    <!-- Spinner mientras cargan los KPIs -->
    <div id="kpiSpinner" class="text-center py-5">
        <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;" role="status"></div>
        <p class="mt-3 text-muted small">Calculando indicadores...</p>
    </div>

    <!-- Contenido principal, visible solo despues del primer analisis -->
    <div id="resultados">

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon text-primary"><i class="bi bi-bus-front"></i></div>
                    <div class="kpi-value text-primary" id="k-total">-</div>
                    <div class="kpi-label">Total de viajes</div>
                    <div class="kpi-sub">en el periodo analizado</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon text-danger"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="kpi-value text-danger" id="k-tasa">-</div>
                    <div class="kpi-label">Tasa de saturacion</div>
                    <div class="kpi-sub"><span id="k-sat">-</span> viajes saturados</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon text-warning"><i class="bi bi-people"></i></div>
                    <div class="kpi-value text-warning" id="k-pax">-</div>
                    <div class="kpi-label">Ocupacion promedio</div>
                    <div class="kpi-sub">pasajeros por viaje</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon" style="color:#607D8B"><i class="bi bi-arrow-up-circle"></i></div>
                    <div class="kpi-value" style="color:#607D8B" id="k-exceso">-</div>
                    <div class="kpi-label">Exceso promedio</div>
                    <div class="kpi-sub">pax sobre capacidad al saturar</div>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <h5><i class="bi bi-grid-3x3 me-2"></i>Patron de saturacion: dia de semana x franja horaria</h5>
            <small>Porcentaje de viajes saturados promedio historico por dia y hora de inicio.</small>
            <div id="heatmap" class="mt-3"><div class="chart-loading"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando mapa de calor...</div></div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="chart-card h-100">
                    <h5><i class="bi bi-graph-up me-2"></i>Tendencia mensual de saturacion</h5>
                    <small>Tasa de saturacion (%) y total de viajes mes a mes.</small>
                    <div style="position:relative; height:280px; margin-top:12px;">
                        <div id="tendenciaLoading" class="chart-loading pt-5"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando...</div>
                        <canvas id="chartTendencia" style="display:none;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-card h-100">
                    <h5><i class="bi bi-calendar-week me-2"></i>Por dia de semana</h5>
                    <small>Tasa de saturacion promedio historica por dia.</small>
                    <div style="position:relative; height:280px; margin-top:12px;">
                        <div id="diaLoading" class="chart-loading pt-5"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando...</div>
                        <canvas id="chartDia" style="display:none;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card" id="seccionRutas" style="display:none;">
            <h5><i class="bi bi-signpost-split me-2"></i>Comparativa por ruta</h5>
            <small>Tasa de saturacion (%) por ruta en el sentido seleccionado.</small>
            <div style="position:relative; height:200px; margin-top:12px;">
                <canvas id="chartRutas"></canvas>
            </div>
        </div>

    </div>
</div>

<script>
let heatmapChart = null, chartTendencia = null, chartDia = null, chartRutas = null;
const PRIMARY = '#002E90', DANGER = '#E53935';

function params() {
    return `ruta=${document.getElementById('ruta').value}&sentido=${document.getElementById('sentido').value}`;
}

async function get(action) {
    const r = await fetch(`api/kpi_datos.php?action=${action}&${params()}`);
    const txt = await r.text();
    let d;
    try { d = JSON.parse(txt); }
    catch(e) { throw new Error('Respuesta inesperada del servidor: ' + txt.substring(0, 300)); }
    if (d.status === 'error') throw new Error(d.message);
    return d;
}

function mostrarError(msg) {
    const b = document.getElementById('errorBox');
    document.getElementById('errorMsg').textContent = msg;
    b.style.display = 'block';
}

function ocultarError() {
    document.getElementById('errorBox').style.display = 'none';
}

function renderKPIs(d) {
    document.getElementById('k-total').textContent  = d.total.toLocaleString('es-PE');
    document.getElementById('k-tasa').textContent   = d.tasa + '%';
    document.getElementById('k-sat').textContent    = d.saturados.toLocaleString('es-PE');
    document.getElementById('k-pax').textContent    = d.avg_pax;
    document.getElementById('k-exceso').textContent = d.avg_exceso > 0 ? '+' + d.avg_exceso : '0';
}

function renderHeatmap(d) {
    if (heatmapChart) { heatmapChart.destroy(); heatmapChart = null; }
    document.getElementById('heatmap').innerHTML = '';
    heatmapChart = new ApexCharts(document.querySelector('#heatmap'), {
        series: d.series,
        chart:  { type: 'heatmap', height: 310, toolbar: { show: false } },
        dataLabels: {
            enabled: true,
            formatter: v => v > 0 ? v + '%' : '',
            style: { fontSize: '10px', fontWeight: 600 }
        },
        plotOptions: {
            heatmap: {
                shadeIntensity: 0.5,
                colorScale: {
                    ranges: [
                        { from: 0,  to: 0,   name: 'Sin datos', color: '#F5F5F5' },
                        { from: 1,  to: 10,  name: 'Normal',    color: '#C8E6C9' },
                        { from: 10, to: 25,  name: 'Moderado',  color: '#FFF176' },
                        { from: 25, to: 45,  name: 'Alto',      color: '#FFCC80' },
                        { from: 45, to: 100, name: 'Critico',   color: '#EF9A9A' }
                    ]
                }
            }
        },
        xaxis:   { type: 'category', labels: { style: { fontSize: '11px' } } },
        yaxis:   { labels: { style: { fontSize: '11px', fontWeight: 700 } } },
        tooltip: { y: { formatter: v => v + '% de viajes saturados' } },
        legend:  { position: 'top', fontSize: '11px' }
    });
    heatmapChart.render();
}

function renderTendencia(d) {
    if (chartTendencia) chartTendencia.destroy();
    document.getElementById('tendenciaLoading').style.display = 'none';
    const canvas = document.getElementById('chartTendencia');
    canvas.style.display = 'block';
    chartTendencia = new Chart(canvas, {
        type: 'line',
        data: {
            labels: d.labels,
            datasets: [
                { label: 'Tasa saturacion (%)', data: d.tasas,
                  borderColor: DANGER, backgroundColor: 'rgba(229,57,53,.1)',
                  borderWidth: 2.5, pointBackgroundColor: DANGER, pointRadius: 5,
                  tension: 0.35, fill: true, yAxisID: 'y' },
                { label: 'Total viajes', data: d.totales,
                  borderColor: PRIMARY, borderWidth: 1.5, borderDash: [5,4],
                  pointRadius: 3, tension: 0.35, yAxisID: 'y2' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
            scales: {
                y:  { beginAtZero: true, ticks: { callback: v => v + '%' },
                      title: { display: true, text: 'Saturacion (%)', font: { size: 11 } } },
                y2: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false },
                      title: { display: true, text: 'Viajes totales', font: { size: 11 } } }
            }
        }
    });
}

function renderDia(d) {
    if (chartDia) chartDia.destroy();
    document.getElementById('diaLoading').style.display = 'none';
    const canvas = document.getElementById('chartDia');
    canvas.style.display = 'block';
    const colors = d.tasas.map(t =>
        t >= 45 ? '#EF9A9A' : t >= 25 ? '#FFCC80' : t >= 10 ? '#FFF176' : '#C8E6C9'
    );
    chartDia = new Chart(canvas, {
        type: 'bar',
        data: { labels: d.labels, datasets: [{ label: 'Tasa saturacion (%)',
            data: d.tasas, backgroundColor: colors, borderWidth: 1.5, borderRadius: 6 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => v + '%' } } }
        }
    });
}

function renderRutas(d) {
    if (chartRutas) chartRutas.destroy();
    chartRutas = new Chart(document.getElementById('chartRutas'), {
        type: 'bar',
        data: { labels: d.labels, datasets: [{ label: 'Tasa saturacion (%)',
            data: d.tasas,
            backgroundColor: ['rgba(0,46,144,.75)','rgba(0,46,144,.6)',
                              'rgba(0,46,144,.45)','rgba(0,46,144,.3)'],
            borderColor: PRIMARY, borderWidth: 1.5, borderRadius: 6 }] },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { callback: v => v + '%' } } }
        }
    });
}

async function cargarTodo() {
    ocultarError();
    document.getElementById('btnAnalizar').disabled = true;
    document.getElementById('estadoInicial').style.display = 'none';
    document.getElementById('kpiSpinner').style.display    = 'block';
    document.getElementById('resultados').style.display    = 'none';

    const esTodas = document.getElementById('ruta').value === 'TODAS';

    try {
        // KPIs primero: query mas liviana, da feedback rapido al usuario
        const kpis = await get('kpis');
        renderKPIs(kpis);
        document.getElementById('kpiSpinner').style.display = 'none';
        document.getElementById('resultados').style.display = 'block';

        // El resto en paralelo: el heatmap es el mas pesado
        const [heatmap, tendencia, dia, rutas] = await Promise.all([
            get('heatmap'),
            get('tendencia'),
            get('diasemana'),
            esTodas ? get('rutas') : Promise.resolve(null)
        ]);

        renderHeatmap(heatmap);
        renderTendencia(tendencia);
        renderDia(dia);

        const secRutas = document.getElementById('seccionRutas');
        if (esTodas && rutas) {
            renderRutas(rutas);
            secRutas.style.display = 'block';
        } else {
            secRutas.style.display = 'none';
        }

    } catch (e) {
        document.getElementById('kpiSpinner').style.display = 'none';
        mostrarError(e.message);
        console.error(e);
    } finally {
        document.getElementById('btnAnalizar').disabled = false;
    }
}
</script>

<?php layout_fin(); ?>
