<?php
header('Content-Type: application/json');
require_once '../verificar_sesion.php';
require_once '../../conexion.php';

// Solo admins pueden gestionar permisos
if ($usuario_rol !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$usuario_id = intval($_POST['usuario_id'] ?? 0);
$permisos = json_decode($_POST['permisos'] ?? '[]', true);

if ($usuario_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit();
}

if (!is_array($permisos) || empty($permisos)) {
    echo json_encode(['success' => false, 'message' => 'Datos de permisos inválidos']);
    exit();
}

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Primero eliminar todos los permisos existentes del usuario
    $stmt_delete = $conexion->prepare("DELETE FROM 4usuario_permisos WHERE usuario_id = ?");
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    
    // Insertar los nuevos permisos
    $stmt_insert = $conexion->prepare(
        "INSERT INTO 4usuario_permisos (usuario_id, modulo_id, puede_ver, puede_crear, puede_editar, puede_eliminar) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    
    foreach ($permisos as $permiso) {
        $modulo_id = intval($permiso['modulo_id']);
        $puede_ver = intval($permiso['puede_ver'] ?? 0);
        $puede_crear = intval($permiso['puede_crear'] ?? 0);
        $puede_editar = intval($permiso['puede_editar'] ?? 0);
        $puede_eliminar = intval($permiso['puede_eliminar'] ?? 0);
        
        // Solo insertar si al menos tiene un permiso activo
        if ($puede_ver || $puede_crear || $puede_editar || $puede_eliminar) {
            $stmt_insert->bind_param("iiiiii", 
                $usuario_id, 
                $modulo_id, 
                $puede_ver, 
                $puede_crear, 
                $puede_editar, 
                $puede_eliminar
            );
            $stmt_insert->execute();
        }
    }
    
    $stmt_insert->close();
    
    // Confirmar transacción
    $conexion->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Permisos actualizados exitosamente'
    ]);
    
} catch (Exception $e) {
    // Revertir cambios si hay error
    $conexion->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Error al guardar permisos: ' . $e->getMessage()
    ]);
}

$conexion->close();
?>