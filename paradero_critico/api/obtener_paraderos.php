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

// Configurar charset
$conexion->set_charset("utf8mb4");

// Recibir los filtros
$ruta = $_POST['ruta'] ?? null;
$sentido_texto = $_POST['sentido'] ?? null;

$response = [];

try {
    if (!$sentido_texto) {
        throw new Exception("El sentido es obligatorio.");
    }
    
    // Convertir el sentido de texto a ID
    $sql_sentido = "SELECT id FROM 2op_sentido WHERE sentido = ?";
    $stmt_sentido = $conexion->prepare($sql_sentido);
    $stmt_sentido->bind_param('s', $sentido_texto);
    $stmt_sentido->execute();
    $result_sentido = $stmt_sentido->get_result();
    
    if ($result_sentido->num_rows === 0) {
        throw new Exception("Sentido no válido.");
    }
    
    $sentido_id = $result_sentido->fetch_assoc()['id'];

    // Si se selecciona "Todas las Rutas", no filtramos por ruta específica
    if ($ruta && $ruta !== "TODAS") {
        $query = "SELECT DISTINCT paradero 
                  FROM PARADEROS 
                  WHERE ruta = ? AND sentido = ? 
                  ORDER BY orden";
        
        $stmt = $conexion->prepare($query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta de paraderos: " . $conexion->error);
        }
        
        $stmt->bind_param('si', $ruta, $sentido_id);
    } else {
        $query = "SELECT DISTINCT paradero 
                  FROM PARADEROS 
                  WHERE sentido = ? 
                  ORDER BY unificado";
        
        $stmt = $conexion->prepare($query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta de paraderos: " . $conexion->error);
        }
        
        $stmt->bind_param('i', $sentido_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $paraderos = [];
    while ($fila = $result->fetch_assoc()) {
        // Ya no usar utf8_encode, la conexión ya está en utf8mb4
        $paraderos[] = $fila['paradero'];
    }

    $response = [
        'status' => 'success',
        'paraderos' => $paraderos,
    ];
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$conexion->close();
?>