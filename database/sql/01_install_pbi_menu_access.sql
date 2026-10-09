-- Run this script in the MySQL schema `pbi` with a deployment account that can create tables and views.
-- It does not drop or alter existing CPY / staging objects.

USE `pbi`;

CREATE TABLE IF NOT EXISTS `pbi_menu_items` (
    `idmenu` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `pagina_destino` VARCHAR(255) NOT NULL,
    `modulo` VARCHAR(50) NOT NULL DEFAULT 'GENERAL',
    `orden` INT UNSIGNED NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `requiere_permiso` TINYINT(1) NOT NULL DEFAULT 1,
    `es_predeterminado` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_creacion` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `usuario_creacion` VARCHAR(255) NULL,
    `fecha_actualizacion` DATETIME(6) NULL,
    `usuario_actualizacion` VARCHAR(255) NULL,
    `fecha_eliminacion` DATETIME(6) NULL,
    `usuario_eliminacion` VARCHAR(255) NULL,
    PRIMARY KEY (`idmenu`),
    KEY `pbi_menu_items_listing_idx` (`modulo`, `activo`, `orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pbi_menu_user_permissions` (
    `idmenu_permiso` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `correo` VARCHAR(255) NOT NULL,
    `idmenu` INT UNSIGNED NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `usuario_creacion` VARCHAR(255) NULL,
    `fecha_actualizacion` DATETIME(6) NULL,
    `usuario_actualizacion` VARCHAR(255) NULL,
    `fecha_eliminacion` DATETIME(6) NULL,
    `usuario_eliminacion` VARCHAR(255) NULL,
    PRIMARY KEY (`idmenu_permiso`),
    UNIQUE KEY `pbi_menu_user_permissions_unique` (`correo`, `idmenu`),
    KEY `pbi_menu_user_permissions_email_idx` (`correo`, `activo`),
    KEY `pbi_menu_user_permissions_menu_idx` (`idmenu`, `activo`),
    CONSTRAINT `pbi_menu_user_permissions_menu_fk`
        FOREIGN KEY (`idmenu`) REFERENCES `pbi_menu_items` (`idmenu`)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pbi_access_audit_logs` (
    `id_auditoria` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entidad` VARCHAR(100) NOT NULL,
    `entidad_id` VARCHAR(100) NOT NULL,
    `accion` VARCHAR(50) NOT NULL,
    `estado_anterior` JSON NULL,
    `estado_posterior` JSON NULL,
    `usuario_admin` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `agente_usuario` VARCHAR(255) NULL,
    `fecha_evento` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id_auditoria`),
    KEY `pbi_access_audit_logs_entity_idx` (`entidad`, `entidad_id`),
    KEY `pbi_access_audit_logs_date_idx` (`fecha_evento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preserve the DAX-compatible idmenu = 0 for the public selector item.
SET @pbi_access_original_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, IF(@@SESSION.sql_mode = '', '', ','), 'NO_AUTO_VALUE_ON_ZERO');

INSERT INTO `pbi_menu_items` (
    `idmenu`, `nombre`, `pagina_destino`, `modulo`, `orden`, `activo`,
    `requiere_permiso`, `es_predeterminado`, `fecha_creacion`, `usuario_creacion`,
    `fecha_actualizacion`, `usuario_actualizacion`
) VALUES (
    0, 'SELECCIONAR', 'Menu', 'GENERAL', 0, 1,
    0, 1, NOW(6), 'initial_setup', NOW(6), 'initial_setup'
)
ON DUPLICATE KEY UPDATE `idmenu` = `idmenu`;

SET SESSION sql_mode = @pbi_access_original_sql_mode;

INSERT INTO `pbi_menu_items` (
    `nombre`, `pagina_destino`, `modulo`, `orden`, `activo`,
    `requiere_permiso`, `es_predeterminado`, `fecha_creacion`, `usuario_creacion`,
    `fecha_actualizacion`, `usuario_actualizacion`
)
SELECT
    'GENERAL', 'Principal', 'GENERAL', 1, 1,
    1, 0, NOW(6), 'initial_setup', NOW(6), 'initial_setup'
WHERE NOT EXISTS (
    SELECT 1
    FROM `pbi_menu_items`
    WHERE `modulo` = 'GENERAL'
      AND `nombre` = 'GENERAL'
      AND `fecha_eliminacion` IS NULL
);

CREATE OR REPLACE VIEW `vw_pbi_menu` AS
SELECT
    `idmenu`,
    `nombre`,
    `pagina_destino`,
    `orden`,
    `modulo`,
    `es_predeterminado`
FROM `pbi_menu_items`
WHERE `activo` = 1
  AND `fecha_eliminacion` IS NULL;

CREATE OR REPLACE VIEW `vw_pbi_menu_permisos` AS
SELECT DISTINCT
    LOWER(TRIM(permission.`correo`)) AS `Correo`,
    permission.`idmenu`
FROM `pbi_menu_user_permissions` AS permission
INNER JOIN `pbi_menu_items` AS menu_item
    ON menu_item.`idmenu` = permission.`idmenu`
WHERE permission.`activo` = 1
  AND permission.`fecha_eliminacion` IS NULL
  AND menu_item.`activo` = 1
  AND menu_item.`fecha_eliminacion` IS NULL
  AND menu_item.`requiere_permiso` = 1;

-- Optional principle-of-least-privilege accounts. Replace placeholders before use.
-- CREATE USER 'pbi_access_admin'@'192.168.4.%' IDENTIFIED BY 'replace-with-a-unique-secret';
-- GRANT SELECT, INSERT, UPDATE ON `pbi`.`pbi_menu_items` TO 'pbi_access_admin'@'192.168.4.%';
-- GRANT SELECT, INSERT, UPDATE ON `pbi`.`pbi_menu_user_permissions` TO 'pbi_access_admin'@'192.168.4.%';
-- GRANT SELECT, INSERT ON `pbi`.`pbi_access_audit_logs` TO 'pbi_access_admin'@'192.168.4.%';
-- CREATE USER 'pbi_powerbi_reader'@'%' IDENTIFIED BY 'replace-with-a-unique-secret';
-- GRANT SELECT ON `pbi`.`vw_pbi_menu` TO 'pbi_powerbi_reader'@'%';
-- GRANT SELECT ON `pbi`.`vw_pbi_menu_permisos` TO 'pbi_powerbi_reader'@'%';
