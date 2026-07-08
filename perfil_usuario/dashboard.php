<?php
date_default_timezone_set('America/Lima');

require_once 'verificar_sesion.php';
require_once 'verificar_permisos.php';
require_once '_layout.php';

// Usuario efectivo (respeta la vista previa "Ver como" del admin)
$ef = cta_usuario_efectivo();

// Módulos que el usuario EFECTIVO puede ver (accesos rápidos)
$modulos_disponibles = obtenerModulosVisibles($ef['id']);

// Seguridad adicional: ocultar "usuarios" si el rol efectivo no es admin
if (($ef['rol'] ?? '') !== 'admin') {
    $modulos_disponibles = array_filter($modulos_disponibles, function ($m) {
        return $m['nombre'] !== 'usuarios';
    });
}

// Descripciones amigables por módulo (las mismas de antes)
$descripciones = [
    'peticiones'    => 'Revisar, aprobar o rechazar solicitudes de los conductores.',
    'conductores'   => 'Datos personales, documentos y asignaciones de conductores.',
    'usuarios'      => 'Usuarios del sistema, roles y permisos de acceso.',
    'reportes'      => 'Reportes y análisis del sistema operativo.',
    'rutas'         => 'Rutas de transporte y sus configuraciones.',
    'configuracion' => 'Parámetros generales del sistema.',
    'paradero_critico' => 'Detecta saturación de buses y recomienda unidades por franja horaria (rutas 301, 303, 305, 336).',
    'kpi_patrones'     => 'KPIs históricos, mapa de calor de saturación y tendencia mensual por ruta.',
];

layout_inicio('Inicio', 'dashboard');
?>

<!-- Bienvenida -->
<div class="cta-welcome">
    <div>
        <h1><i class="bi bi-briefcase me-2"></i>Bienvenido al Sistema Operativo</h1>
        <p>Hola <strong><?= htmlspecialchars($ef['nombre']) ?></strong>, elige un módulo del menú o de los accesos rápidos.</p>
    </div>
    <span class="cta-badge-rol">
        <i class="bi bi-shield-check me-1"></i> <?= strtoupper(htmlspecialchars($ef['rol'])) ?>
    </span>
</div>

<!-- Accesos rápidos -->
<?php if (empty($modulos_disponibles)): ?>
    <div class="cta-card text-center text-muted">
        <i class="bi bi-inbox" style="font-size:48px;color:#ddd;"></i>
        <h5 class="mt-2">No tienes módulos asignados</h5>
        <p class="mb-0">Contacta al administrador para obtener acceso.</p>
    </div>
<?php else: ?>
    <div class="cta-tiles">
        <?php foreach ($modulos_disponibles as $modulo): ?>
            <a href="<?= htmlspecialchars($modulo['ruta']) ?>" class="cta-tile">
                <i class="bi <?= htmlspecialchars($modulo['icono'] ?: 'bi-app') ?> tile-icon" style="color: <?= cta_color_grupo($modulo['grupo'] ?? '') ?>"></i>
                <div class="tile-title"><?= htmlspecialchars($modulo['descripcion']) ?></div>
                <div class="tile-desc"><?= htmlspecialchars($descripciones[$modulo['nombre']] ?? 'Módulo del sistema') ?></div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php layout_fin(); ?>
