<?php
// Configuración de conexión a la base de datos de ESTA plataforma (entorno Docker).
// Los valores llegan por variables de entorno del contenedor (docker-compose.yml);
// si no estuvieran definidas, usan los valores locales por defecto.
$hostname = getenv('DB_HOST') ?: 'db';
$database = getenv('DB_NAME') ?: 'turbano_bd_op';
$username = getenv('DB_USER') ?: 'siapc';
$password = getenv('DB_PASS') ?: 'siapc';

// Crear conexión
$conexion = new mysqli($hostname, $username, $password, $database);

// Verificar conexión
if ($conexion->connect_errno) {
    // Registrar error en logs del servidor (no visible para usuarios)
    error_log("Error de conexión MySQL [" . date('Y-m-d H:i:s') . "]: " . $conexion->connect_error);
    
    // Mensaje genérico sin exponer información sensible
    die("El sistema está experimentando problemas técnicos. Por favor, intente más tarde o contacte al administrador.");
}

// Configurar charset UTF-8 para prevenir problemas de codificación
// y algunos tipos de inyección SQL
$conexion->set_charset("utf8mb4");

// Opcional: Configurar zona horaria (ajusta según tu región)
$conexion->query("SET time_zone = '-05:00'"); // Hora de Perú
?>