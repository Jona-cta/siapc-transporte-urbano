<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesion no activa.']);
    exit();
}

require_once __DIR__ . '/../../conexion.php';
$conexion->set_charset('utf8mb4');

// Control de acceso por modulo: requiere permiso 'ver' en 'kpi_patrones'
require_once __DIR__ . '/../../perfil_usuario/verificar_permisos.php';
if (($_SESSION['usuario_rol'] ?? '') !== 'admin'
    && !tienePermiso((int) $_SESSION['usuario_id'], 'kpi_patrones', 'ver')) {
    echo json_encode(['status' => 'error', 'message' => 'No tiene permisos para este modulo.']);
    exit();
}

$ruta    = $_GET['ruta']    ?? 'TODAS';
$sentido = $_GET['sentido'] ?? 'NS';
$action  = $_GET['action']  ?? '';

function baseSQL(string $ruta): string {
    $filtroRuta = ($ruta === 'TODAS') ? '' : 'AND r.ruta = ?';
    return "SELECT
                v.fecha,
                r.ruta                                             AS ruta,
                DAYOFWEEK(v.fecha)                                 AS dia,
                YEAR(v.fecha)                                      AS anio,
                MONTH(v.fecha)                                     AS mes,
                HOUR(MIN(v.hora))                                  AS hora_inicio,
                COUNT(*)                                           AS pax,
                CASE WHEN COUNT(*) >= 80 THEN 1 ELSE 0 END        AS saturado
            FROM VALIDACIONES v
            JOIN 2op_ruta r ON r.id = v.id_ruta
            WHERE v.sentido = ? $filtroRuta
            GROUP BY v.fecha, r.ruta, v.`id carrera`";
}

function ejecutar($conexion, string $q, string $sentido, string $ruta): array {
    $stmt = $conexion->prepare($q);
    if (!$stmt) {
        error_log('KPI prepare error: ' . $conexion->error);
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor [E002].']);
        exit();
    }
    if ($ruta === 'TODAS') {
        $stmt->bind_param('s', $sentido);
    } else {
        $stmt->bind_param('ss', $sentido, $ruta);
    }
    if (!$stmt->execute()) {
        error_log('KPI execute error: ' . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor [E003].']);
        exit();
    }
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$response = ['status' => 'error', 'message' => 'Accion no reconocida.'];

try {

    switch ($action) {

        case 'kpis':
            $q = "SELECT
                    COUNT(*)                                                           AS total,
                    SUM(saturado)                                                      AS sat,
                    ROUND(AVG(pax), 1)                                                 AS avg_pax,
                    ROUND(AVG(CASE WHEN saturado = 1 THEN pax - 80 ELSE NULL END), 1)  AS avg_exceso
                  FROM (" . baseSQL($ruta) . ") t";
            $rows = ejecutar($conexion, $q, $sentido, $ruta);
            $r    = $rows[0];
            $tasa = ($r['total'] > 0) ? round($r['sat'] * 100.0 / $r['total'], 1) : 0;
            $response = [
                'status'     => 'success',
                'total'      => (int)$r['total'],
                'saturados'  => (int)$r['sat'],
                'tasa'       => $tasa,
                'avg_pax'    => (float)$r['avg_pax'],
                'avg_exceso' => (float)($r['avg_exceso'] ?? 0)
            ];
            break;

        case 'heatmap':
            $q = "SELECT dia, hora_inicio, COUNT(*) AS total, SUM(saturado) AS sat
                  FROM (" . baseSQL($ruta) . ") t
                  WHERE dia BETWEEN 2 AND 7
                    AND hora_inicio BETWEEN 5 AND 22
                  GROUP BY dia, hora_inicio
                  ORDER BY dia, hora_inicio";
            $rows  = ejecutar($conexion, $q, $sentido, $ruta);
            $dias  = [2=>'Lun', 3=>'Mar', 4=>'Mie', 5=>'Jue', 6=>'Vie', 7=>'Sab'];
            $horas = range(5, 22);
            $matrix = [];
            foreach ($rows as $row) {
                $matrix[(int)$row['dia']][(int)$row['hora_inicio']] =
                    $row['total'] > 0 ? round($row['sat'] * 100.0 / $row['total'], 1) : 0;
            }
            $series = [];
            foreach ($dias as $num => $nombre) {
                $data = [];
                foreach ($horas as $h) {
                    $data[] = ['x' => sprintf('%02d:00', $h), 'y' => $matrix[$num][$h] ?? 0];
                }
                $series[] = ['name' => $nombre, 'data' => $data];
            }
            $response = ['status' => 'success', 'series' => $series];
            break;

        case 'tendencia':
            $q = "SELECT anio, mes, COUNT(*) AS total, SUM(saturado) AS sat
                  FROM (" . baseSQL($ruta) . ") t
                  GROUP BY anio, mes
                  ORDER BY anio, mes";
            $rows  = ejecutar($conexion, $q, $sentido, $ruta);
            $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            $labels = []; $tasas = []; $totales = [];
            foreach ($rows as $r) {
                $labels[]  = $meses[(int)$r['mes']] . ' ' . $r['anio'];
                $tasas[]   = $r['total'] > 0 ? round($r['sat'] * 100.0 / $r['total'], 1) : 0;
                $totales[] = (int)$r['total'];
            }
            $response = ['status' => 'success', 'labels' => $labels,
                         'tasas' => $tasas, 'totales' => $totales];
            break;

        case 'diasemana':
            $q = "SELECT dia, COUNT(*) AS total, SUM(saturado) AS sat
                  FROM (" . baseSQL($ruta) . ") t
                  WHERE dia BETWEEN 2 AND 7
                  GROUP BY dia
                  ORDER BY dia";
            $rows    = ejecutar($conexion, $q, $sentido, $ruta);
            $nombres = [2=>'Lunes',3=>'Martes',4=>'Miercoles',
                        5=>'Jueves',6=>'Viernes',7=>'Sabado'];
            $map = [];
            foreach ($rows as $r) { $map[(int)$r['dia']] = $r; }
            $labels = []; $tasas = [];
            for ($d = 2; $d <= 7; $d++) {
                $labels[] = $nombres[$d];
                $r = $map[$d] ?? null;
                $tasas[] = ($r && $r['total'] > 0)
                    ? round($r['sat'] * 100.0 / $r['total'], 1) : 0;
            }
            $response = ['status' => 'success', 'labels' => $labels, 'tasas' => $tasas];
            break;

        case 'rutas':
            $q = "SELECT ruta, COUNT(*) AS total, SUM(saturado) AS sat
                  FROM (
                      SELECT v.fecha, r.ruta AS ruta, v.`id carrera`,
                             COUNT(*) AS pax,
                             CASE WHEN COUNT(*) >= 80 THEN 1 ELSE 0 END AS saturado
                      FROM VALIDACIONES v
                      JOIN 2op_ruta r ON r.id = v.id_ruta
                      WHERE v.sentido = ?
                      GROUP BY v.fecha, r.ruta, v.`id carrera`
                  ) t
                  GROUP BY ruta
                  ORDER BY ruta";
            $stmt = $conexion->prepare($q);
            $stmt->bind_param('s', $sentido);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $labels = []; $tasas = []; $totales = [];
            foreach ($rows as $r) {
                $labels[]  = 'Ruta ' . $r['ruta'];
                $tasas[]   = $r['total'] > 0 ? round($r['sat'] * 100.0 / $r['total'], 1) : 0;
                $totales[] = (int)$r['total'];
            }
            $response = ['status' => 'success', 'labels' => $labels,
                         'tasas' => $tasas, 'totales' => $totales];
            break;
    }

} catch (Exception $e) {
    // Detalle al log; al cliente mensaje generico (V-02)
    error_log('Error en kpi_datos: ' . $e->getMessage());
    $response = ['status' => 'error', 'message' => 'Error interno del servidor [E004].'];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$conexion->close();
?>