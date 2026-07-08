-- ============================================================
-- SIAPC - Semilla DEMO para clonar y levantar el proyecto
-- ------------------------------------------------------------
-- Contiene SOLO la estructura de las tablas + datos MINIMOS
-- (catalogos, 1 admin demo y el registro de modulos).
-- NO incluye datos reales (validaciones, paraderos, usuarios).
--
-- Al levantar con Docker:
--   Usuario: admin
--   Clave:   admin123
--
-- Las pantallas de analisis apareceran vacias hasta que cargues
-- un volcado real de VALIDACIONES / PARADEROS.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ------------------------- Estructura -------------------------

DROP TABLE IF EXISTS `2op_sentido`;
CREATE TABLE `2op_sentido` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sentido` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sentido` (`sentido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `2op_ruta`;
CREATE TABLE `2op_ruta` (
  `id` int(10) NOT NULL,
  `ruta` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `nombre` (`ruta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `PARADEROS`;
CREATE TABLE `PARADEROS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ruta` int(11) DEFAULT NULL,
  `sentido` int(11) DEFAULT NULL,
  `paradero` varchar(50) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `unificado` int(11) DEFAULT NULL,
  `zona` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

DROP TABLE IF EXISTS `VALIDACIONES`;
CREATE TABLE `VALIDACIONES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `placa` varchar(50) DEFAULT NULL,
  `id_conductor` int(11) DEFAULT NULL,
  `servicio` int(11) DEFAULT NULL,
  `sentido` varchar(10) NOT NULL,
  `paradero` varchar(50) NOT NULL,
  `orden` int(11) DEFAULT NULL,
  `zona` varchar(50) DEFAULT NULL,
  `id carrera` bigint(20) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `id_ruta` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fecha_sentido_paradero` (`fecha`,`sentido`,`paradero`),
  KEY `idx_ruta_sentido` (`id_ruta`,`sentido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `4usuario`;
CREATE TABLE `4usuario` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `dni` varchar(20) DEFAULT NULL,
  `usu_usuario` varchar(20) NOT NULL,
  `usu_password` varchar(255) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `rol` varchar(20) DEFAULT 'encargado',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `ultimo_acceso` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`),
  KEY `idx_usuario_activo` (`activo`),
  KEY `idx_usuario_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `4usuario_modulos`;
CREATE TABLE `4usuario_modulos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `ruta` varchar(255) NOT NULL,
  `icono` varchar(50) DEFAULT 'bi-app',
  `orden` int(11) DEFAULT 0,
  `grupo` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `idx_modulos_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `4usuario_permisos`;
CREATE TABLE `4usuario_permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `puede_ver` tinyint(1) DEFAULT 0,
  `puede_crear` tinyint(1) DEFAULT 0,
  `puede_editar` tinyint(1) DEFAULT 0,
  `puede_eliminar` tinyint(1) DEFAULT 0,
  `restricciones_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_module` (`usuario_id`,`modulo_id`),
  KEY `idx_permisos_usuario` (`usuario_id`),
  KEY `idx_permisos_modulo` (`modulo_id`),
  CONSTRAINT `fk_demo_permisos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `4usuario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_demo_permisos_modulo`  FOREIGN KEY (`modulo_id`)  REFERENCES `4usuario_modulos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------- Datos minimos -------------------------

-- Catalogos (no son datos sensibles)
INSERT INTO `2op_sentido` (`id`, `sentido`) VALUES (1, 'NS'), (2, 'SN');
INSERT INTO `2op_ruta` (`id`, `ruta`) VALUES (1, '301'), (2, '303'), (3, '305'), (4, '336');

-- Usuario administrador DEMO  ->  admin / admin123
INSERT INTO `4usuario` (`id`, `usu_usuario`, `usu_password`, `nombre`, `rol`, `activo`) VALUES
(1, 'admin', '$2y$10$DAS6QnXc5VtoLNAurSwHxunDL6R77w4WyqX/L0xktL6Guc7gHbpxq', 'Administrador Demo', 'admin', 1);

-- Modulos del sistema de Paradero Critico
INSERT INTO `4usuario_modulos` (`id`, `nombre`, `descripcion`, `ruta`, `icono`, `orden`, `grupo`, `activo`) VALUES
(1, 'paradero_critico', 'Paradero Critico',       '/bd_op/paradero_critico/paradero_critico.php', 'bi-bus-front',      1, 'operaciones', 1),
(2, 'kpi_patrones',     'Patrones e Indicadores', '/bd_op/paradero_critico/kpi_patrones.php',     'bi-graph-up-arrow', 2, 'operaciones', 1);

-- Permiso completo del admin demo sobre ambos modulos
INSERT INTO `4usuario_permisos` (`usuario_id`, `modulo_id`, `puede_ver`, `puede_crear`, `puede_editar`, `puede_eliminar`) VALUES
(1, 1, 1, 1, 1, 1),
(1, 2, 1, 1, 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
