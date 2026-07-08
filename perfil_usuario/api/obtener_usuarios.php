<?php
header('Content-Type: application/json');
require_once '../verificar_sesion.php';
require_once '../../conexion.php';

// Solo admins pueden ver usuarios
if ($usuario_rol !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

$sql = "SELECT id, usu_usuario, nombre, rol, activo, fecha_creacion, ultimo_acceso 
        FROM 4usuario 
        ORDER BY nombre ASC";

$result = $conexion->query($sql);

$usuarios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'usuarios' => $usuarios
]);

$conexion->close();
?>