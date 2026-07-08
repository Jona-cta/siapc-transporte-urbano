<?php
header('Content-Type: application/json');
require_once '../verificar_sesion.php';
require_once '../../conexion.php';

// Solo admins pueden ver permisos
if ($usuario_rol !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

$usuario_id = intval($_GET['usuario_id'] ?? 0);

if ($usuario_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit();
}

$sql = "SELECT 
            p.id,
            p.modulo_id,
            m.nombre AS modulo_nombre,
            m.descripcion AS modulo_descripcion,
            p.puede_ver,
            p.puede_crear,
            p.puede_editar,
            p.puede_eliminar
        FROM 4usuario_permisos p
        INNER JOIN 4usuario_modulos m ON p.modulo_id = m.id
        WHERE p.usuario_id = ? AND m.activo = 1
        ORDER BY m.orden ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$permisos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $permisos[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'permisos' => $permisos
]);

$stmt->close();
$conexion->close();
?>