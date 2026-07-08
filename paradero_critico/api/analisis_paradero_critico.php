<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'message' => 'Sesion no activa. Por favor inicie sesion.'
    ]);
    exit();
}
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);   // no mostrar errores al cliente; se registran en error_log
require_once __DIR__ . '/../../conexion.php';

// Control de acceso por modulo: requiere permiso 'ver' en 'paradero_critico'
require_once __DIR__ . '/../../perfil_usuario/verificar_permisos.php';
if (($_SESSION['usuario_rol'] ?? '') !== 'admin'
    && !tienePermiso((int) $_SESSION['usuario_id'], 'paradero_critico', 'ver')) {
    echo json_encode(['status' => 'error', 'message' => 'No tiene permisos para este modulo.']);
    exit();
}

// Proteccion CSRF (V-04): el token enviado debe coincidir con el de la sesion
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Token de seguridad invalido. Recargue la pagina.']);
    exit();
}

// Configurar charset
if (!$conexion->set_charset("utf8mb4")) {
    error_log('Error set_charset: ' . $conexion->error);
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor [E001].']);
    exit();
}

// Recibir los filtros
$fecha = $_POST['fecha'] ?? null;
$ruta = $_POST['ruta'] ?? null;
$sentido_texto = $_POST['sentido'] ?? null;
$paradero_filtro = isset($_POST['paradero']) ? $_POST['paradero'] : null;

// Validacion de formato de fecha en el servidor (V-03): debe ser AAAA-MM-DD real
if ($fecha !== null && $fecha !== '') {
    $fobj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fobj || $fobj->format('Y-m-d') !== $fecha) {
        echo json_encode(['status' => 'error', 'message' => 'Formato de fecha invalido. Use AAAA-MM-DD.']);
        exit();
    }
}

error_log("Paradero recibido: " . $paradero_filtro);
error_log("Sentido recibido: " . $sentido_texto);
error_log("Ruta recibida: " . $ruta);

$response = [];

if ($fecha && $sentido_texto && $paradero_filtro) {
    try {
        // Convertir el sentido de texto a ID
        $sql_sentido = "SELECT id FROM 2op_sentido WHERE sentido = ?";
        $stmt_sentido = $conexion->prepare($sql_sentido);
        $stmt_sentido->bind_param('s', $sentido_texto);
        $stmt_sentido->execute();
        $result_sentido = $stmt_sentido->get_result();
        
        if ($result_sentido->num_rows === 0) {
            throw new Exception("Sentido no válido: " . $sentido_texto);
        }
        
        $sentido_id = $result_sentido->fetch_assoc()['id'];
        error_log("Sentido ID: " . $sentido_id);

        // Si se selecciona "Todas las Rutas", asignar las rutas disponibles
        if ($ruta === "TODAS" || empty($ruta)) {
            $rutas = ["301", "303", "305", "336"];
        } else {
            $rutas = [$ruta];
        }

        // Obtener los paraderos anteriores al paradero crítico
        $queryParaderos = "
            SELECT DISTINCT paradero
            FROM PARADEROS
            WHERE sentido = ? AND unificado <= (
                SELECT unificado 
                FROM PARADEROS 
                WHERE paradero = ? AND sentido = ? LIMIT 1
            )";

        // Añadir el filtro de rutas si es necesario
        if (!empty($rutas)) {
            $placeholders = implode(',', array_fill(0, count($rutas), '?'));
            $queryParaderos .= " AND ruta IN ($placeholders)";
        }

        // Parámetros para la consulta
        $params = [$sentido_id, $paradero_filtro, $sentido_id];
        $types = "isi";

        // Añadir los valores de las rutas
        if (!empty($rutas)) {
            $types .= str_repeat('s', count($rutas));
            $params = array_merge($params, $rutas);
        }

        $queryParaderos .= " ORDER BY unificado";
        $stmtParaderos = $conexion->prepare($queryParaderos);
        if (!$stmtParaderos) {
            throw new Exception("Error preparando consulta de paraderos: " . $conexion->error);
        }

        if (!$stmtParaderos->bind_param($types, ...$params)) {
            throw new Exception("Error en bind_param: " . $stmtParaderos->error);
        }

        if (!$stmtParaderos->execute()) {
            throw new Exception("Error ejecutando consulta de paraderos: " . $stmtParaderos->error);
        }

        $resultParaderos = $stmtParaderos->get_result();
        if (!$resultParaderos) {
            throw new Exception("Error obteniendo resultados de paraderos: " . $stmtParaderos->error);
        }

        $paraderosPrevios = [];
        while ($fila = $resultParaderos->fetch_assoc()) {
            $paraderosPrevios[] = $fila['paradero'];
        }

        error_log("Paraderos previos encontrados: " . count($paraderosPrevios));

        if (empty($paraderosPrevios)) {
            throw new Exception("No se encontraron paraderos previos al seleccionado.");
        }

        // Obtener las validaciones acumuladas para los paraderos previos
        $queryValidaciones = "
            SELECT v.`id carrera`, r.ruta AS ruta, v.paradero, MIN(v.hora) AS hora_min, COUNT(*) AS validaciones
            FROM VALIDACIONES v
            JOIN 2op_ruta r ON r.id = v.id_ruta
            WHERE v.paradero IN ('" . implode("','", array_map([$conexion, 'real_escape_string'], $paraderosPrevios)) . "')
            AND v.fecha = ?
            AND v.sentido = ?";

        // Parámetros de fecha y sentido
        $params = [$fecha, $sentido_texto];
        $types = "ss";

        // Añadir el filtro de ruta si es necesario
        if (!empty($rutas)) {
            $placeholders = implode(',', array_fill(0, count($rutas), '?'));
            $queryValidaciones .= " AND r.ruta IN ($placeholders)";
            $params = array_merge($params, $rutas);
            $types .= str_repeat('s', count($rutas));
        }

        $queryValidaciones .= " GROUP BY v.`id carrera`, r.ruta, v.paradero
                                ORDER BY v.`id carrera`, MIN(v.hora)";
        
        error_log("Query validaciones: " . $queryValidaciones);
        
        $stmtValidaciones = $conexion->prepare($queryValidaciones);
        if (!$stmtValidaciones) {
            throw new Exception("Error preparando consulta de validaciones: " . $conexion->error);
        }
        if (!$stmtValidaciones->bind_param($types, ...$params)) {
            throw new Exception("Error en bind_param para validaciones: " . $stmtValidaciones->error);
        }
        if (!$stmtValidaciones->execute()) {
            throw new Exception("Error ejecutando consulta de validaciones: " . $stmtValidaciones->error);
        }

        $resultValidaciones = $stmtValidaciones->get_result();
        if (!$resultValidaciones) {
            throw new Exception("Error obteniendo resultados de validaciones: " . $stmtValidaciones->error);
        }

        error_log("Validaciones encontradas: " . $resultValidaciones->num_rows);

        // Procesamiento de resultados
        $busesPorCarrera = [];
        while ($row = $resultValidaciones->fetch_assoc()) {
            $idCarrera = $row['id carrera'];
            if (!isset($busesPorCarrera[$idCarrera])) {
                $busesPorCarrera[$idCarrera] = [
                    'id_carrera' => $idCarrera,
                    'ruta' => $row['ruta'],
                    'hora_inicio' => $row['hora_min'],
                    'validaciones_hasta_paradero_critico' => 0,
                    'paradero_lleno' => null,
                ];
            }

            $busesPorCarrera[$idCarrera]['validaciones_hasta_paradero_critico'] += $row['validaciones'];
            if (
                $busesPorCarrera[$idCarrera]['validaciones_hasta_paradero_critico'] >= 80 &&
                !$busesPorCarrera[$idCarrera]['paradero_lleno']
            ) {
                $busesPorCarrera[$idCarrera]['paradero_lleno'] = $row['paradero'];
            }
        }

        // Ordenar las carreras por hora de inicio
        usort($busesPorCarrera, function ($a, $b) {
            return strtotime($a['hora_inicio']) - strtotime($b['hora_inicio']);
        });

        // Detectar cortes lógicos
        $recomendaciones = [];
        $carrerasConsecutivas = [];
        $ultimaHora = null;
        
        foreach ($busesPorCarrera as $bus) {
            $esInvalida = $bus['validaciones_hasta_paradero_critico'] < 80;
        
            if ($ultimaHora !== null) {
                $diferencia = strtotime($bus['hora_inicio']) - strtotime($ultimaHora);
                if ($esInvalida || $diferencia >= 900) {
                    if (count($carrerasConsecutivas) > 1) {
                        $totalValidaciones = array_sum(array_column($carrerasConsecutivas, 'validaciones_hasta_paradero_critico'));
                        $totalBuses = count($carrerasConsecutivas);
                        $capacidadTotal = 80 * $totalBuses;
                        $exceso = $totalValidaciones - $capacidadTotal;
        
                        $capacidadBus = 80;
                        $capacidadBus60 = $capacidadBus * 0.60;
                        $busesAdicionales = ceil($exceso / $capacidadBus60);
        
                        $recomendaciones[] = [
                            'inicio' => $carrerasConsecutivas[0]['hora_inicio'],
                            'fin' => end($carrerasConsecutivas)['hora_inicio'],
                            'cantidad_buses' => count($carrerasConsecutivas),
                            'exceso' => $exceso,
                            'buses_adicionales' => $busesAdicionales,
                            'detalle' => $carrerasConsecutivas
                        ];
                    }
                    $carrerasConsecutivas = [];
                }
            }
        
            if (!$esInvalida) {
                $carrerasConsecutivas[] = $bus;
            }
        
            $ultimaHora = $bus['hora_inicio'];
        }
        
        // Agregar el último grupo de carreras si es necesario
        if (count($carrerasConsecutivas) > 1) {
            $totalValidaciones = array_sum(array_column($carrerasConsecutivas, 'validaciones_hasta_paradero_critico'));
            $totalBuses = count($carrerasConsecutivas);
            $capacidadTotal = 80 * $totalBuses;
            $exceso = $totalValidaciones - $capacidadTotal;
        
            $capacidadBus = 80;
            $capacidadBus60 = $capacidadBus * 0.60;
            $busesAdicionales = ceil($exceso / $capacidadBus60);
        
            $recomendaciones[] = [
                'inicio' => $carrerasConsecutivas[0]['hora_inicio'],
                'fin' => end($carrerasConsecutivas)['hora_inicio'],
                'cantidad_buses' => count($carrerasConsecutivas),
                'exceso' => $exceso,
                'buses_adicionales' => $busesAdicionales,
                'detalle' => $carrerasConsecutivas
            ];
        }
        
        $response = [
            'status' => 'success',
            'paradero' => $paradero_filtro,
            'recomendaciones' => $recomendaciones
        ];

    } catch (Exception $e) {
        // Se registra el detalle real en el log; al cliente solo mensajes de negocio seguros (V-02)
        error_log("Error en analisis paradero critico: " . $e->getMessage());
        $mensajes_seguros = ['Sentido no', 'No se encontraron paraderos previos'];
        $mostrar = 'No se pudo completar el analisis. Intente nuevamente.';
        foreach ($mensajes_seguros as $seg) {
            if (stripos($e->getMessage(), $seg) !== false) { $mostrar = $e->getMessage(); break; }
        }
        $response = [
            'status' => 'error',
            'message' => $mostrar
        ];
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Todos los filtros (fecha, sentido y paradero) son obligatorios.'
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$conexion->close();
?>