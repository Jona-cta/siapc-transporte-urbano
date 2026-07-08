<?php
/**
 * _layout.php - Marco visual compartido del Sistema TransporteUrbano (perfil_usuario)
 * --------------------------------------------------------------------------
 * Provee el "molde" reutilizable: topbar azul + botón "Mi cuenta" + sidebar
 * agrupado (data-driven desde 4usuario_modulos + permisos).
 *
 * Paleta tomada del diseño de sistema_ope (azul marino #1e3a8a).
 * El sidebar se arma con obtenerModulosVisibles(), agrupado por la columna
 * `grupo`, mostrando SOLO lo que el permiso del usuario permite.
 *
 * USO en cualquier página:
 *     require_once 'verificar_sesion.php';     // da $usuario_id, $usuario_nombre...
 *     require_once 'verificar_permisos.php';   // da obtenerModulosVisibles()
 *     require_once '_layout.php';
 *     layout_inicio('Título de la página', 'nombre_modulo_activo');
 *         // ...aquí va SOLO el contenido propio de la página...
 *     layout_fin();
 *
 * Nota: las páginas que NO incluyan este layout siguen funcionando igual
 * que antes (no rompe nada de lo existente).
 */

if (!function_exists('layout_inicio')):

/**
 * Color de acento según el grupo del módulo (para los íconos del sidebar/tiles).
 * Los grupos conocidos tienen color curado; los nuevos reciben uno estable
 * derivado de su nombre (así un grupo nuevo como RRHH ya sale con color solo).
 *
 * @param string $grupo  Nombre del grupo (ej. 'OPERACIÓN', 'RRHH').
 * @return string Color hex.
 */
function cta_color_grupo($grupo) {
    static $mapa = [
        'OPERACIÓN'     => '#2563eb', // azul
        'RRHH'          => '#059669', // verde
        'ANÁLISIS'      => '#7c3aed', // morado
        'MANTENIMIENTO' => '#ea580c', // naranja
        'ALMACÉN'       => '#0d9488', // teal
        'REPORTES'      => '#db2777', // rosa
        'ADMIN'         => '#475569', // pizarra
    ];
    if (isset($mapa[$grupo])) return $mapa[$grupo];
    $palette = ['#2563eb','#059669','#7c3aed','#ea580c','#0d9488','#db2777','#475569','#ca8a04'];
    $h = 0;
    for ($i = 0, $n = strlen($grupo); $i < $n; $i++) { $h += ord($grupo[$i]); }
    return $palette[$h % count($palette)];
}

/**
 * Devuelve el usuario EFECTIVO para pintar el menú.
 * Si el admin activó "Ver como" ($_SESSION['ver_como_id']), devuelve ESE usuario
 * (para previsualizar lo que vería); si no, devuelve el usuario real.
 *
 * @return array ['id','nombre','rol','es_preview'(bool),'real_nombre']
 */
function cta_usuario_efectivo() {
    global $usuario_id, $usuario_nombre, $usuario_rol, $conexion;
    $real = ['id' => $usuario_id, 'nombre' => $usuario_nombre, 'rol' => $usuario_rol, 'es_preview' => false];

    // Solo un admin real puede usar la vista previa
    if (($usuario_rol ?? '') !== 'admin' || empty($_SESSION['ver_como_id'])) return $real;

    $id = (int) $_SESSION['ver_como_id'];
    if ($id === (int) $usuario_id || !isset($conexion)) return $real;

    $stmt = $conexion->prepare("SELECT id, nombre, rol FROM `4usuario` WHERE id = ? AND activo = 1");
    if (!$stmt) return $real;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$u) return $real;

    return ['id' => (int) $u['id'], 'nombre' => $u['nombre'], 'rol' => $u['rol'],
            'es_preview' => true, 'real_nombre' => $usuario_nombre];
}

/**
 * Abre el documento: <head> + topbar + sidebar + <main>.
 *
 * @param string $titulo         Título de la pestaña del navegador.
 * @param string $modulo_activo  'nombre' del módulo a resaltar en el sidebar.
 */
function layout_inicio($titulo = 'Sistema TransporteUrbano', $modulo_activo = '') {
    global $usuario_id, $usuario_nombre, $usuario_username, $usuario_rol, $conexion;

    // Usuario EFECTIVO: si el admin activó "Ver como", se previsualiza ese usuario
    $ef = cta_usuario_efectivo();

    // Prefijo hacia perfil_usuario. Vacío si la página vive en perfil_usuario;
    // las páginas en otras carpetas (ej. rrhh_descansos/) definen
    // $GLOBALS['cta_pu_base'] = '../perfil_usuario/' antes de llamar al layout.
    $pu = $GLOBALS['cta_pu_base'] ?? '';

    // Módulos visibles para el usuario EFECTIVO, agrupados por `grupo`
    $modulos = function_exists('obtenerModulosVisibles')
        ? obtenerModulosVisibles($ef['id']) : [];

    // Seguridad adicional: ocultar "usuarios" a quien no es admin (según rol efectivo)
    if (($ef['rol'] ?? '') !== 'admin') {
        $modulos = array_filter($modulos, fn($m) => $m['nombre'] !== 'usuarios');
    }

    $grupos = [];
    foreach ($modulos as $m) {
        $g = $m['grupo'] ?? 'GENERAL';
        $grupos[$g][] = $m;
    }
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> · Sistema TransporteUrbano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --cta-navy:   #1e3a8a;   /* topbar / activos */
            --cta-blue:   #2563eb;   /* acento / botones */
            --cta-bg:     #f3f4f6;   /* fondo */
            --cta-ink:    #1f2937;   /* texto */
            --cta-muted:  #6b7280;   /* texto tenue */
            --cta-amber:  #fef3c7;   /* badge fondo */
            --cta-amberink:#854f0b;  /* badge texto */
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; background: var(--cta-bg); color: var(--cta-ink);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ---- Topbar ---- */
        .cta-topbar {
            background: var(--cta-navy); color: #fff; padding: 10px 20px;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 1030;
        }
        .cta-brand { font-size: 15px; font-weight: 700; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .cta-brand:hover { color: #dbeafe; }
        /* El botón "Mi cuenta" conserva el estilo original (btn-light + dropdown Bootstrap) */
        .cta-topbar .btn-light { background: #fff; color: var(--cta-navy); font-weight: 600; border: none; }
        .cta-topbar .btn-light:hover { background: #eef2ff; color: var(--cta-blue); }
        .dropdown-menu { border-radius: 10px; box-shadow: 0 6px 22px rgba(0,0,0,.15); border: none; }
        .cta-topbar-right { display: flex; align-items: center; gap: 10px; }
        .cta-reloj { color: #dbeafe; font-size: 12.5px; font-weight: 600; display: flex; align-items: center; white-space: nowrap; }
        @media (max-width: 640px) { .cta-reloj { display: none; } }

        /* ---- Botones de accion: color del encabezado (azul marino) ---- */
        .btn-primary { background-color: var(--cta-navy); border-color: var(--cta-navy); }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active,
        .btn-primary:not(:disabled):not(.disabled):active { background-color: #162d6e; border-color: #162d6e; box-shadow: none; }
        .btn-outline-primary { color: var(--cta-navy); border-color: var(--cta-navy); }
        .btn-outline-primary:hover { background-color: var(--cta-navy); border-color: var(--cta-navy); color: #fff; }

        /* ---- Botón Excel (verde de Excel) ---- */
        .btn-excel { background-color: #107C41 !important; border-color: #107C41 !important; color: #fff !important; }
        .btn-excel:hover, .btn-excel:focus, .btn-excel:active { background-color: #0d6a37 !important; border-color: #0d6a37 !important; color: #fff !important; box-shadow: none; }

        /* ---- Barra de vista previa "Ver como" ---- */
        .cta-preview-bar {
            background: var(--cta-amber); color: var(--cta-amberink);
            padding: 8px 18px; display: flex; justify-content: space-between; align-items: center;
            font-size: 13px; font-weight: 600; border-bottom: 1px solid #fde68a;
        }
        .cta-preview-volver {
            color: #fff; background: var(--cta-amberink); padding: 4px 12px; border-radius: 5px;
            text-decoration: none; font-size: 12px;
        }
        .cta-preview-volver:hover { background: #6b3f08; color: #fff; }

        /* ---- Layout ---- */
        .cta-app { display: grid; grid-template-columns: 230px 1fr; gap: 14px; padding: 14px; }

        /* ---- Sidebar ---- */
        .cta-sidebar { background: #fff; border-radius: 10px; padding: 12px 8px; position: sticky; top: 64px; max-height: calc(100vh - 80px); overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none; }
        .cta-sidebar::-webkit-scrollbar { width: 0; height: 0; display: none; }
        .cta-group { font-size: 9.5px; color: var(--cta-muted); margin: 4px 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }
        .cta-group-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 5px; vertical-align: middle; }
        .cta-group:not(:first-child) { margin-top: 14px; }
        .cta-item { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: 6px; color: #374151; text-decoration: none; margin-bottom: 1px; transition: background .1s; font-size: 13px; }
        .cta-item:hover { background: var(--cta-bg); color: #374151; }
        .cta-item.active { background: #eff6ff; color: var(--cta-navy); font-weight: 700; }
        .cta-item i { font-size: 16px; }
        .cta-sidebar-empty { color: var(--cta-muted); font-size: 11px; padding: 10px; text-align: center; }

        /* ---- Main ---- */
        .cta-main { display: flex; flex-direction: column; gap: 12px; min-width: 0; }

        /* ---- Piezas reutilizables para los módulos ---- */
        .cta-welcome { background: #fff; border-radius: 10px; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .cta-welcome h1 { font-size: 20px; margin: 0 0 2px; color: var(--cta-navy); }
        .cta-welcome p { margin: 0; color: var(--cta-muted); font-size: 13px; }
        .cta-badge-rol { background: var(--cta-amber); color: var(--cta-amberink); padding: 5px 14px; border-radius: 20px; font-weight: 700; font-size: 12px; }
        .cta-card { background: #fff; border-radius: 10px; padding: 18px; box-shadow: 0 2px 8px rgba(0,0,0,.05); }

        /* Tarjetas de acceso rápido (estilo módulo) */
        .cta-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px; }
        .cta-tile { background: #fff; border-radius: 10px; padding: 20px; text-decoration: none; color: inherit; border: 2px solid transparent; box-shadow: 0 2px 8px rgba(0,0,0,.05); transition: transform .1s, box-shadow .1s, border-color .1s; display: block; }
        .cta-tile:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(0,0,0,.10); border-color: var(--cta-blue); color: inherit; }
        .cta-tile .tile-icon { font-size: 34px; color: var(--cta-navy); }
        .cta-tile .tile-title { font-weight: 700; font-size: 15px; margin: 8px 0 4px; }
        .cta-tile .tile-desc { color: var(--cta-muted); font-size: 12px; line-height: 1.5; }

        /* ---- Responsive ---- */
        @media (max-width: 900px) {
            .cta-app { grid-template-columns: 1fr; }
            .cta-sidebar { position: static; }
        }
    </style>
</head>
<body>
    <!-- ===== Topbar (azul marino) ===== -->
    <header class="cta-topbar">
        <a class="cta-brand" href="<?= $pu ?>dashboard.php">
            <i class="bi bi-house-door"></i> Sistema TransporteUrbano
        </a>

        <div class="cta-topbar-right">

        <span class="cta-reloj" title="Fecha y hora de la sesión"><i class="bi bi-clock me-1"></i><span id="ctaRelojTxt">--:--:--</span></span>

        <?php if (($usuario_rol ?? '') === 'admin'):
            // Lista de usuarios activos para previsualizar (solo admin la ve)
            $usuarios_lista = [];
            if (isset($conexion) && ($rs = $conexion->query("SELECT id, nombre, rol FROM `4usuario` WHERE activo = 1 ORDER BY nombre"))) {
                while ($r = $rs->fetch_assoc()) $usuarios_lista[] = $r;
            }
        ?>
        <!-- ===== Selector "Ver como" (solo admin) ===== -->
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" id="dropdownVerComo" data-bs-toggle="dropdown">
                <i class="bi bi-eye me-1"></i> Ver como
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="max-height: 340px; overflow:auto;">
                <li><a class="dropdown-item" href="<?= $pu ?>ver_como.php?id=0">
                    <i class="bi bi-arrow-counterclockwise me-2"></i> Mi vista (admin)
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <?php foreach ($usuarios_lista as $u): if ((int)$u['id'] === (int)$usuario_id) continue; ?>
                    <li><a class="dropdown-item" href="<?= $pu ?>ver_como.php?id=<?= (int)$u['id'] ?>">
                        <?= htmlspecialchars($u['nombre']) ?>
                        <span class="text-muted">· <?= strtoupper(htmlspecialchars($u['rol'])) ?></span>
                    </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- ===== Botón "Mi cuenta" (preservado de tu encabezado original) ===== -->
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" id="dropdownUser" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-2"></i>
                <?= htmlspecialchars($usuario_nombre ?? '') ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                <li>
                    <span class="dropdown-item-text">
                        <i class="bi bi-person me-2"></i>
                        <strong><?= htmlspecialchars($usuario_username ?? '') ?></strong>
                    </span>
                </li>
                <li>
                    <span class="dropdown-item-text">
                        <i class="bi bi-shield-check me-1"></i>
                        <?= strtoupper(htmlspecialchars($usuario_rol ?? '')) ?>
                    </span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?= $pu ?>cambiar_password.php">
                        <i class="bi bi-key me-2"></i> Cambiar Contraseña
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= $pu ?>logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
        </div><!-- /cta-topbar-right -->
    </header>

    <?php if (!empty($ef['es_preview'])): ?>
    <!-- Aviso de vista previa activa -->
    <div class="cta-preview-bar">
        <span>
            <i class="bi bi-eye-fill me-1"></i>
            Estás viendo como <strong><?= htmlspecialchars($ef['nombre']) ?></strong>
            (<?= strtoupper(htmlspecialchars($ef['rol'])) ?>) - vista previa de permisos
        </span>
        <a href="<?= $pu ?>ver_como.php?id=0" class="cta-preview-volver">
            <i class="bi bi-arrow-left me-1"></i> Volver a mi vista
        </a>
    </div>
    <?php endif; ?>

    <div class="cta-app">
        <!-- ===== Sidebar (agrupado, data-driven) ===== -->
        <aside class="cta-sidebar">
            <?php if (empty($grupos)): ?>
                <div class="cta-sidebar-empty">
                    <i class="bi bi-inbox"></i><br>Sin módulos asignados
                </div>
            <?php else: ?>
                <?php foreach ($grupos as $nombre_grupo => $items): $cg = cta_color_grupo($nombre_grupo); ?>
                    <p class="cta-group">
                        <span class="cta-group-dot" style="background: <?= $cg ?>"></span>
                        <?= htmlspecialchars($nombre_grupo) ?>
                    </p>
                    <?php foreach ($items as $m): ?>
                        <a class="cta-item <?= ($m['nombre'] === $modulo_activo ? 'active' : '') ?>"
                           href="<?= htmlspecialchars($m['ruta']) ?>">
                            <i class="bi <?= htmlspecialchars($m['icono'] ?: 'bi-app') ?>" style="color: <?= $cg ?>"></i>
                            <span><?= htmlspecialchars($m['descripcion'] ?: $m['nombre']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>

        <!-- ===== Contenido propio de cada página ===== -->
        <main class="cta-main">
    <?php
} // fin layout_inicio

/**
 * Cierra el documento: </main> + </div> + scripts.
 */
function layout_fin() {
    ?>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        var el = document.getElementById('ctaRelojTxt');
        if (!el) return;
        function dos(n) { return (n < 10 ? '0' : '') + n; }
        var dias = ['dom','lun','mar','mié','jue','vie','sáb'];
        function tick() {
            var d = new Date(), h = d.getHours(), ap = h >= 12 ? 'p.m.' : 'a.m.', h12 = h % 12; if (h12 === 0) h12 = 12;
            el.textContent = dias[d.getDay()] + ' ' + dos(d.getDate()) + '/' + dos(d.getMonth() + 1) + '/' + d.getFullYear()
                           + '  ' + dos(h12) + ':' + dos(d.getMinutes()) + ':' + dos(d.getSeconds()) + ' ' + ap;
        }
        tick(); setInterval(tick, 1000);
    })();
    </script>
</body>
</html>
    <?php
} // fin layout_fin

endif;
