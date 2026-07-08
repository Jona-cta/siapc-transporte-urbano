<?php
date_default_timezone_set('America/Lima');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../perfil_usuario/login.php");
    exit();
}

// Regenerar ID de sesión periódicamente para mayor seguridad
if (!isset($_SESSION['ultimo_regeneracion'])) {
    $_SESSION['ultimo_regeneracion'] = time();
} elseif (time() - $_SESSION['ultimo_regeneracion'] > 1800) { // 30 minutos
    session_regenerate_id(true);
    $_SESSION['ultimo_regeneracion'] = time();
}

// Variables de sesión disponibles - SIN conversiones
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];
$usuario_rol = $_SESSION['usuario_rol'];
$usuario_username = $_SESSION['usuario_username'];
?>