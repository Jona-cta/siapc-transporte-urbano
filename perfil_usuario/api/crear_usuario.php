<?php
header('Content-Type: application/json');
require_once '../verificar_sesion.php';
require_once '../../conexion.php';

// Solo admins pueden crear usuarios
if ($usuario_rol !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para crear usuarios']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$usu_usuario = trim($_POST['usu_usuario'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$rol = $_POST['rol'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$activo = isset($_POST['activo']) ? 1 : 0;

// Validaciones
if (empty($usu_usuario) || empty($nombre) || empty($rol) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
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

// Verificar si el usuario ya existe
$stmt_check = $conexion->prepare("SELECT id FROM 4usuario WHERE usu_usuario = ?");
$stmt_check->bind_param("s", $usu_usuario);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

// Crear el usuario
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conexion->prepare("INSERT INTO 4usuario (usu_usuario, usu_password, nombre, rol, activo, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssi", $usu_usuario, $password_hash, $nombre, $rol, $activo);

if ($stmt->execute()) {
    $nuevo_id = $conexion->insert_id;
    echo json_encode([
        'success' => true, 
        'message' => 'Usuario creado exitosamente',
        'usuario_id' => $nuevo_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear el usuario']);
}

$stmt->close();
$conexion->close();
?>