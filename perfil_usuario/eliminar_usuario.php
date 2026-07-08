<?php
require_once 'verificar_sesion.php';
require_once 'verificar_permisos.php';
require_once '../conexion.php';

// Módulo usuarios es SOLO para admins
soloAdmin($usuario_rol);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: gestion_usuarios.php?accion=error");
    exit();
}

// No permitir que el usuario se elimine a sí mismo
if ($id === $usuario_id) {
    header("Location: gestion_usuarios.php?accion=error");
    exit();
}

// Eliminar el usuario
$stmt = $conexion->prepare("DELETE FROM 4usuario WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: gestion_usuarios.php?accion=eliminado");
} else {
    header("Location: gestion_usuarios.php?accion=error");
}

$stmt->close();
exit();
?>