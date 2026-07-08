<?php
/**
 * ver_como.php - Activa/desactiva la vista previa "Ver como" (solo admin).
 *
 *   ?id=<N>  → ver el sistema como el usuario N (previsualizar sus permisos)
 *   ?id=0    → volver a mi vista (admin)
 *
 * Es SOLO previsualización del menú/accesos; no ejecuta acciones como ese
 * usuario. La identidad real (admin) nunca se pierde.
 */
date_default_timezone_set('America/Lima');
require_once 'verificar_sesion.php';   // exige sesión y da $usuario_id, $usuario_rol

// Solo un admin real puede usar la vista previa
if (($usuario_rol ?? '') !== 'admin') {
    unset($_SESSION['ver_como_id']);
    header('Location: dashboard.php');
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0 && $id !== (int) $usuario_id) {
    $_SESSION['ver_como_id'] = $id;      // activar vista previa
} else {
    unset($_SESSION['ver_como_id']);     // volver a mi vista
}

header('Location: dashboard.php');
exit();
