<?php
date_default_timezone_set('America/Lima');
session_start();

// Si ya tiene sesión activa, no tiene nada que hacer aquí
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Este archivo solo acepta POST; cualquier otra cosa lo manda al formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

require_once '../conexion.php';

$usuario  = trim($_POST['usuario']  ?? '');
$password =      $_POST['password'] ?? '';

// Validación básica
if (empty($usuario) || empty($password)) {
    $_SESSION['login_error'] = 'Por favor, complete todos los campos';
    header("Location: login.php");
    exit();
}

$stmt = $conexion->prepare(
    "SELECT id, usu_password, nombre, rol
     FROM 4usuario
     WHERE usu_usuario = ? AND activo = 1"
);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($fila && password_verify($password, $fila['usu_password'])) {

    // Login exitoso
    $_SESSION['usuario_id']       = $fila['id'];
    $_SESSION['usuario_nombre']   = $fila['nombre'];
    $_SESSION['usuario_rol']      = $fila['rol'];
    $_SESSION['usuario_username'] = $usuario;

    // Registrar último acceso
    $upd = $conexion->prepare("UPDATE 4usuario SET ultimo_acceso = NOW() WHERE id = ?");
    $upd->bind_param("i", $fila['id']);
    $upd->execute();
    $upd->close();

    header("Location: dashboard.php");
    exit();
}

// Credenciales incorrectas — mensaje genérico (no revelar cuál campo falló)
$_SESSION['login_error'] = 'Usuario o contraseña incorrectos';
header("Location: login.php");
exit();