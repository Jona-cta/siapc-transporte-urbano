<?php
header('Content-Type: application/json');
require_once '../verificar_sesion.php';
require_once '../../conexion.php';

// Solo admins pueden eliminar usuarios
if ($usuario_rol !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

// Verificar que no se esté eliminando a sí mismo
if ($id == $usuario_id) {
    echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propio usuario']);
    exit();
}

$stmt = $conexion->prepare("DELETE FROM 4usuario WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario']);
}

$stmt->close();
$conexion->close();
?>