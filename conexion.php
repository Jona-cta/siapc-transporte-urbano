<?php
// Conexion a la BD. Toma los datos de variables de entorno (Docker) con valores locales por defecto.
$hostname = getenv('DB_HOST') ?: 'db';
$database = getenv('DB_NAME') ?: 'turbano_bd_op';
$username = getenv('DB_USER') ?: 'siapc';
$password = getenv('DB_PASS') ?: 'siapc';

$conexion = new mysqli($hostname, $username, $password, $database);

if ($conexion->connect_errno) {
    error_log("Error de conexión MySQL [" . date('Y-m-d H:i:s') . "]: " . $conexion->connect_error);
    die("El sistema está experimentando problemas técnicos. Intente más tarde.");
}

$conexion->set_charset("utf8mb4");
$conexion->query("SET time_zone = '-05:00'");
?>
