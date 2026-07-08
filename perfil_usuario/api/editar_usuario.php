<?php
header('Content-Type: application/json');
require_once '../verificar_sesion.php';
require_once '../../conexion.php';

// Solo admins pueden editar usuarios
if ($usuario_rol !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$rol = $_POST['rol'] ?? '';
$activo = isset($_POST['activo']) ? 1 : 0;
$cambiar_password = isset($_POST['cambiar_password']) && $_POST['cambiar_password'] === '1';

// Validaciones
if ($id <= 0 || empty($nombre) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Si se va a cambiar la contraseña
if ($cambiar_password) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'La contraseña no puede estar vacía']);
        exit();
    }
    
    if ($password !== $password_confirm) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        exit();
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit();
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conexion->prepare("UPDATE 4usuario SET nombre = ?, rol = ?, activo = ?, usu_password = ? WHERE id = ?");
    $stmt->bind_param("ssisi", $nombre, $rol, $activo, $password_hash, $id);
} else {
    // Sin cambio de contraseña
    $stmt = $conexion->prepare("UPDATE 4usuario SET nombre = ?, rol = ?, activo = ? WHERE id = ?");
    $stmt->bind_param("ssii", $nombre, $rol, $activo, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario']);
}

$stmt->close();
$conexion->close();
?>