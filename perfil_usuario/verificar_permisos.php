<?php
/**
 * HELPER DE PERMISOS
 * Funciones para verificar permisos granulares de usuarios
 * Requiere: verificar_sesion.php (para tener $usuario_id disponible)
 */

require_once __DIR__ . '/../conexion.php';

/**
 * Verifica si un usuario tiene un permiso específico en un módulo
 * 
 * @param int $usuario_id ID del usuario
 * @param string $modulo_nombre Nombre del módulo (ej: 'peticiones', 'conductores')
 * @param string $accion Acción a verificar: 'ver', 'crear', 'editar', 'eliminar'
 * @return bool true si tiene permiso, false si no
 */
function tienePermiso($usuario_id, $modulo_nombre, $accion) {
    global $conexion;
    
    // Validar acción
    $acciones_validas = ['ver', 'crear', 'editar', 'eliminar'];
    if (!in_array($accion, $acciones_validas)) {
        return false;
    }
    
    // Mapear acción a columna de BD
    $columna_permiso = 'puede_' . $accion;
    
    $sql = "SELECT p.$columna_permiso 
            FROM 4usuario_permisos p
            INNER JOIN 4usuario_modulos m ON p.modulo_id = m.id
            WHERE p.usuario_id = ? 
            AND m.nombre = ? 
            AND m.activo = 1
            LIMIT 1";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("is", $usuario_id, $modulo_nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $tiene_permiso = (bool) $row[$columna_permiso];
        $stmt->close();
        return $tiene_permiso;
    }
    
    $stmt->close();
    return false;
}

/**
 * Obtiene todos los módulos visibles para un usuario
 * 
 * @param int $usuario_id ID del usuario
 * @return array Array de módulos con sus datos y permisos
 */
function obtenerModulosVisibles($usuario_id) {
    global $conexion;
    
    $sql = "SELECT 
                m.id,
                m.nombre,
                m.descripcion,
                m.ruta,
                m.icono,
                m.orden,
                m.grupo,
                p.puede_ver,
                p.puede_crear,
                p.puede_editar,
                p.puede_eliminar
            FROM 4usuario_modulos m
            INNER JOIN 4usuario_permisos p ON m.id = p.modulo_id
            WHERE p.usuario_id = ? 
            AND m.activo = 1
            AND p.puede_ver = 1
            ORDER BY m.orden ASC";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $modulos = [];
    while ($row = $result->fetch_assoc()) {
        $modulos[] = $row;
    }
    
    $stmt->close();
    return $modulos;
}

/**
 * Obtiene todos los permisos de un usuario en un módulo específico
 * 
 * @param int $usuario_id ID del usuario
 * @param string $modulo_nombre Nombre del módulo
 * @return array|null Array con los permisos o null si no tiene acceso
 */
function obtenerPermisosModulo($usuario_id, $modulo_nombre) {
    global $conexion;
    
    $sql = "SELECT 
                p.puede_ver,
                p.puede_crear,
                p.puede_editar,
                p.puede_eliminar
            FROM 4usuario_permisos p
            INNER JOIN 4usuario_modulos m ON p.modulo_id = m.id
            WHERE p.usuario_id = ? 
            AND m.nombre = ? 
            AND m.activo = 1
            LIMIT 1";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("is", $usuario_id, $modulo_nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $permisos = [
            'puede_ver' => (bool) $row['puede_ver'],
            'puede_crear' => (bool) $row['puede_crear'],
            'puede_editar' => (bool) $row['puede_editar'],
            'puede_eliminar' => (bool) $row['puede_eliminar']
        ];
        $stmt->close();
        return $permisos;
    }
    
    $stmt->close();
    return null;
}

/**
 * Verifica si un usuario puede acceder a una página (módulo)
 * Redirige a acceso_denegado.php si no tiene permiso
 * 
 * @param int $usuario_id ID del usuario
 * @param string $modulo_nombre Nombre del módulo
 * @return void (redirige si no tiene acceso)
 */
function requierePermiso($usuario_id, $modulo_nombre, $accion = 'ver') {
    if (!tienePermiso($usuario_id, $modulo_nombre, $accion)) {
        header("Location: ../perfil_usuario/acceso_denegado.php");
        exit();
    }
}

/**
 * Verifica si el usuario es administrador
 * Útil para páginas que solo admins pueden acceder
 * 
 * @param string $usuario_rol Rol del usuario desde la sesión
 * @return bool true si es admin
 */
function esAdmin($usuario_rol) {
    return $usuario_rol === 'admin';
}

/**
 * Protege una página solo para administradores
 * Redirige si no es admin
 * 
 * @param string $usuario_rol Rol del usuario desde la sesión
 * @return void (redirige si no es admin)
 */
function soloAdmin($usuario_rol) {
    if (!esAdmin($usuario_rol)) {
        header("Location: acceso_denegado.php");
        exit();
    }
}
?>