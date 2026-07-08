<?php
require_once __DIR__ . '/../perfil_usuario/verificar_sesion.php';
require_once __DIR__ . '/../perfil_usuario/verificar_permisos.php';
require_once __DIR__ . '/../perfil_usuario/_layout.php';

// Control de acceso por modulo: requiere permiso 'ver' en 'paradero_critico'
if (($usuario_rol ?? '') !== 'admin'
    && !tienePermiso((int) $usuario_id, 'paradero_critico', 'ver')) {
    header('Location: /bd_op/perfil_usuario/acceso_denegado.php');
    exit();
}

// Token CSRF para proteger el endpoint de analisis (V-04)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Las paginas fuera de perfil_usuario deben indicar la ruta base del layout
$GLOBALS['cta_pu_base'] = '../perfil_usuario/';
layout_inicio('Análisis de Paradero Crítico', 'paradero_critico');
?>

<!-- Recursos propios de la pagina (Bootstrap e iconos ya los carga el layout) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
    /* ===== Paleta del login aplicada al contenido de esta pagina ===== */
    :root { --pc-primary: #002E90; --pc-primary-dark: #002167; }

    .pc-header h1 { color: var(--pc-primary); font-weight: 700; font-size: 1.5rem; margin: 0; }

    .pc-card .btn-primary,
    .pc-card .btn-primary:focus {
        background-color: var(--pc-primary); border-color: var(--pc-primary);
    }
    .pc-card .btn-primary:hover,
    .pc-card .btn-primary:active {
        background-color: var(--pc-primary-dark); border-color: var(--pc-primary-dark);
    }
    .pc-card .form-control:focus,
    .pc-card .form-select:focus {
        border-color: var(--pc-primary);
        box-shadow: 0 0 0 0.2rem rgba(0, 46, 144, 0.20);
    }
    .pc-card .table thead th { background: var(--pc-primary); color: #fff; border-color: var(--pc-primary); }
    .pc-card .bg-secondary { background-color: var(--pc-primary-dark) !important; }

    #loadingSpinner { display: none; text-align: center; margin-top: 20px; }
    #loadingSpinner .spinner-border { width: 3rem; height: 3rem; color: var(--pc-primary); }
    .table-responsive { margin-top: 20px; }
</style>

<div class="cta-card pc-card">
    <div class="pc-header text-center mb-4">
        <h1><i class="bi bi-bus-front me-2"></i>Análisis de Paradero Crítico</h1>
        <p class="text-muted mb-0">Detección de saturación y recomendación de unidades por franja horaria</p>
    </div>

    <!-- Filtros -->
    <form id="filterForm" class="row g-3">
        <input type="hidden" id="csrf" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="col-md-3">
            <label for="fecha" class="form-label">Fecha:</label>
            <input type="text" class="form-control" id="fecha" name="fecha" required>
        </div>
        <div class="col-md-3">
            <label for="ruta" class="form-label">Ruta:</label>
            <select class="form-select" id="ruta" name="ruta" required>
                <option value="">Seleccione la Ruta</option>
                <option value="301">301</option>
                <option value="303">303</option>
                <option value="305">305</option>
                <option value="336">336</option>
                <option value="TODAS">Todas las Rutas (301, 303, 305, 336)</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="sentido" class="form-label">Sentido:</label>
            <select class="form-select" id="sentido" name="sentido" required>
                <option value="NS">Norte-Sur (NS)</option>
                <option value="SN">Sur-Norte (SN)</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="paradero" class="form-label">Paradero:</label>
            <select class="form-select" id="paradero" name="paradero" required>
                <option value="">Selecciona una Ruta y Sentido</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Analizar</button>
        </div>
    </form>

    <!-- Spinner de carga -->
    <div id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p>Cargando datos, por favor espere...</p>
    </div>

    <!-- Resumen -->
    <div id="resumen" class="table-responsive"></div>

    <!-- Tabla detallada -->
    <div id="tablaResultados" class="table-responsive"></div>
</div>

<script>
    // Escapa texto para evitar XSS al insertar resultados en el DOM (V-01)
    function esc(v){return String(v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}

    document.addEventListener('DOMContentLoaded', function () {
        const fechaInput = document.getElementById('fecha');
        const rutaSelect = document.getElementById('ruta');
        const sentidoSelect = document.getElementById('sentido');
        const paraderoSelect = document.getElementById('paradero');
        const filterForm = document.getElementById('filterForm');
        const resumen = document.getElementById('resumen');
        const tablaResultados = document.getElementById('tablaResultados');
        const loadingSpinner = document.getElementById('loadingSpinner');

        // Configuración del calendario
        flatpickr(fechaInput, { dateFormat: "Y-m-d" });

        rutaSelect.addEventListener('change', cargarParaderos);
        sentidoSelect.addEventListener('change', cargarParaderos);

        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            analizarParadero();
        });

        function cargarParaderos() {
            const ruta = rutaSelect.value;
            const sentido = sentidoSelect.value;

            // Validar que el sentido esté seleccionado
            if (!sentido) {
                alert('Selecciona un sentido para continuar.');
                return;
            }

            // Si no se selecciona ninguna ruta, cargamos los paraderos para todas las rutas
            paraderoSelect.innerHTML = '<option value="">Cargando...</option>';

            fetch('api/obtener_paraderos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ ruta, sentido }) // Enviar la ruta vacía si se selecciona "Todas las Rutas"
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    paraderoSelect.innerHTML = data.paraderos.map(paradero =>
                        `<option value="${paradero}">${paradero}</option>`
                    ).join('');
                } else {
                    paraderoSelect.innerHTML = '<option value="">No hay paraderos disponibles</option>';
                }
            })
            .catch(error => {
                console.error('Error al cargar paraderos:', error);
                paraderoSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
        }

        function analizarParadero() {
            const fecha = fechaInput.value;
            let ruta = rutaSelect.value;
            const sentido = sentidoSelect.value;
            const paradero = paraderoSelect.value;

            if (!paradero) {
                alert('Selecciona un paradero para analizar.');
                return;
            }

            mostrarSpinner();
            fetch('api/analisis_paradero_critico.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ fecha, ruta, sentido, paradero, csrf: document.getElementById('csrf').value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderResumen(data.recomendaciones);
                    renderDetalles(data.recomendaciones);
                } else {
                    resumen.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    tablaResultados.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => ocultarSpinner());
        }


        function renderResumen(recomendaciones) {
            if (!recomendaciones.length) {
                resumen.innerHTML = `<div class="alert alert-info">No se encontraron datos para el resumen.</div>`;
                return;
            }

            let html = `
                <h3 class="text-center my-4">Resumen</h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Cantidad de Buses</th>
                            <th>Exceso de Capacidad</th>
                            <th>Recomendación</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${recomendaciones.map(rec => `
                            <tr>
                                <td>${esc(rec.inicio)}</td>
                                <td>${esc(rec.fin)}</td>
                                <td>${esc(rec.cantidad_buses)}</td>
                                <td>${esc(rec.exceso)}</td>
                                <td>${rec.buses_adicionales > 0
                                    ? `${esc(rec.buses_adicionales)} Bus(es) Adicional(es) Recomendado(s)`
                                    : 'No Requerido'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            resumen.innerHTML = html;
        }


        function renderDetalles(recomendaciones) {
            if (!recomendaciones.length) {
                tablaResultados.innerHTML = `<div class="alert alert-info">No se encontraron datos detallados.</div>`;
                return;
            }

            let html = `
                <h3 class="text-center my-4">Detalle</h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            <th>Ruta</th>
                            <th>Inicio</th>
                            <th>Paradero Lleno</th>
                            <th>Validaciones al Crítico</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${recomendaciones.map(rec => `
                            <tr>
                                <td colspan="5" class="text-center bg-secondary text-white">Entre ${esc(rec.inicio)} y ${esc(rec.fin)}</td>
                            </tr>
                            ${rec.detalle.map(bus => `
                                ${bus.paradero_lleno && bus.validaciones_hasta_paradero_critico >= 80 ? `
                                    <tr>
                                        <td>${esc(bus.id_carrera)}</td>
                                        <td>${esc(bus.ruta)}</td>
                                        <td>${esc(bus.hora_inicio)}</td>
                                        <td>${esc(bus.paradero_lleno)}</td>
                                        <td>${esc(bus.validaciones_hasta_paradero_critico)}</td>
                                    </tr>
                                ` : ''}
                            `).join('')}
                        `).join('')}
                    </tbody>
                </table>
            `;
            tablaResultados.innerHTML = html;
        }

        function mostrarSpinner() {
            loadingSpinner.style.display = 'block';
        }

        function ocultarSpinner() {
            loadingSpinner.style.display = 'none';
        }
    });
</script>

<?php layout_fin(); ?>
