<?php
header('Content-Type: application/json');
require_once '../verificar_sesion.php';
require_once '../../conexion.php';

// Solo admins pueden ver módulos
if ($usuario_rol !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

$sql = "SELECT id, nombre, descripcion, ruta, icono, orden 
        FROM 4usuario_modulos 
        WHERE activo = 1 
        ORDER BY orden ASC";

$result = $conexion->query($sql);

$modulos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $modulos[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'modulos' => $modulos
]);

$conexion->close();
?>