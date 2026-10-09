-- Upgrade 02: profile-based Power BI security administration.
-- Run on MySQL 8+ in the `pbi` schema with an account allowed to create tables and views.
-- This script preserves existing CPY/staging tables and the direct menu permission table.

USE `pbi`;

CREATE TABLE IF NOT EXISTS `pbi_security_profiles` (
    `idperfil` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(50) NOT NULL,
    `nombre` VARCHAR(100) NOT NULL,
    `powerbi_role_name` VARCHAR(100) NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `usuario_creacion` VARCHAR(255) NULL,
    `fecha_actualizacion` DATETIME(6) NULL,
    `usuario_actualizacion` VARCHAR(255) NULL,
    `fecha_eliminacion` DATETIME(6) NULL,
    `usuario_eliminacion` VARCHAR(255) NULL,
    PRIMARY KEY (`idperfil`),
    UNIQUE KEY `pbi_security_profiles_code_unique` (`codigo`),
    UNIQUE KEY `pbi_security_profiles_name_unique` (`nombre`),
    UNIQUE KEY `pbi_security_profiles_powerbi_role_unique` (`powerbi_role_name`),
    KEY `pbi_security_profiles_active_idx` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pbi_security_users` (
    `idusuario` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NULL,
    `correo` VARCHAR(255) NOT NULL,
    `idperfil` BIGINT UNSIGNED NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `usuario_creacion` VARCHAR(255) NULL,
    `fecha_actualizacion` DATETIME(6) NULL,
    `usuario_actualizacion` VARCHAR(255) NULL,
    `fecha_eliminacion` DATETIME(6) NULL,
    `usuario_eliminacion` VARCHAR(255) NULL,
    PRIMARY KEY (`idusuario`),
    UNIQUE KEY `pbi_security_users_email_unique` (`correo`),
    KEY `pbi_security_users_profile_idx` (`idperfil`, `activo`),
    CONSTRAINT `pbi_security_users_profile_fk`
        FOREIGN KEY (`idperfil`) REFERENCES `pbi_security_profiles` (`idperfil`)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pbi_security_companies` (
    `idempresa` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `clave_pbi` VARCHAR(255) NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `usuario_creacion` VARCHAR(255) NULL,
    `fecha_actualizacion` DATETIME(6) NULL,
    `usuario_actualizacion` VARCHAR(255) NULL,
    `fecha_eliminacion` DATETIME(6) NULL,
    `usuario_eliminacion` VARCHAR(255) NULL,
    PRIMARY KEY (`idempresa`),
    UNIQUE KEY `pbi_security_companies_name_unique` (`nombre`),
    UNIQUE KEY `pbi_security_companies_key_unique` (`clave_pbi`),
    KEY `pbi_security_companies_active_idx` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pbi_security_user_companies` (
    `idusuario_empresa` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idusuario` BIGINT UNSIGNED NOT NULL,
    `idempresa` BIGINT UNSIGNED NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `usuario_creacion` VARCHAR(255) NULL,
    `fecha_actualizacion` DATETIME(6) NULL,
    `usuario_actualizacion` VARCHAR(255) NULL,
    `fecha_eliminacion` DATETIME(6) NULL,
    `usuario_eliminacion` VARCHAR(255) NULL,
    PRIMARY KEY (`idusuario_empresa`),
    UNIQUE KEY `pbi_security_user_company_unique` (`idusuario`, `idempresa`),
    KEY `pbi_security_user_company_user_idx` (`idusuario`, `activo`),
    KEY `pbi_security_user_company_company_idx` (`idempresa`, `activo`),
    CONSTRAINT `pbi_security_user_company_user_fk`
        FOREIGN KEY (`idusuario`) REFERENCES `pbi_security_users` (`idusuario`)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `pbi_security_user_company_company_fk`
        FOREIGN KEY (`idempresa`) REFERENCES `pbi_security_companies` (`idempresa`)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pbi_security_profile_menu_items` (
    `idperfil_menu` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idperfil` BIGINT UNSIGNED NOT NULL,
    `idmenu` INT UNSIGNED NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `usuario_creacion` VARCHAR(255) NULL,
    `fecha_actualizacion` DATETIME(6) NULL,
    `usuario_actualizacion` VARCHAR(255) NULL,
    `fecha_eliminacion` DATETIME(6) NULL,
    `usuario_eliminacion` VARCHAR(255) NULL,
    PRIMARY KEY (`idperfil_menu`),
    UNIQUE KEY `pbi_security_profile_menu_unique` (`idperfil`, `idmenu`),
    KEY `pbi_security_profile_menu_profile_idx` (`idperfil`, `activo`),
    CONSTRAINT `pbi_security_profile_menu_profile_fk`
        FOREIGN KEY (`idperfil`) REFERENCES `pbi_security_profiles` (`idperfil`)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `pbi_security_profile_menu_item_fk`
        FOREIGN KEY (`idmenu`) REFERENCES `pbi_menu_items` (`idmenu`)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the four physical RLS profiles. Existing rows are left unchanged.
INSERT INTO `pbi_security_profiles` (
    `codigo`, `nombre`, `powerbi_role_name`, `activo`,
    `fecha_creacion`, `usuario_creacion`, `fecha_actualizacion`, `usuario_actualizacion`
) VALUES
    ('COMPLETO', 'Completo', 'RLS_Completo', 1, NOW(6), 'initial_setup', NOW(6), 'initial_setup'),
    ('COMERCIAL', 'Comercial', 'RLS_Comercial', 1, NOW(6), 'initial_setup', NOW(6), 'initial_setup'),
    ('COMPRAS', 'Compras', 'RLS_Compras', 1, NOW(6), 'initial_setup', NOW(6), 'initial_setup'),
    ('SIN_TIENDAS1', 'Sin Tiendas1', 'RLS_SinTiendas1', 1, NOW(6), 'initial_setup', NOW(6), 'initial_setup')
ON DUPLICATE KEY UPDATE `idperfil` = `idperfil`;

-- Give the existing GENERAL menu item to the initial profiles.
-- The default item idmenu = 0 stays visible through the Menu RLS formula.
INSERT INTO `pbi_security_profile_menu_items` (
    `idperfil`, `idmenu`, `activo`,
    `fecha_creacion`, `usuario_creacion`, `fecha_actualizacion`, `usuario_actualizacion`
)
SELECT
    security_profile.`idperfil`, menu_item.`idmenu`, 1,
    NOW(6), 'initial_setup', NOW(6), 'initial_setup'
FROM `pbi_security_profiles` AS security_profile
INNER JOIN `pbi_menu_items` AS menu_item
    ON menu_item.`nombre` = 'GENERAL'
   AND menu_item.`modulo` = 'GENERAL'
   AND menu_item.`activo` = 1
   AND menu_item.`fecha_eliminacion` IS NULL
WHERE security_profile.`codigo` IN ('COMPLETO', 'COMERCIAL', 'COMPRAS', 'SIN_TIENDAS1')
ON DUPLICATE KEY UPDATE `idperfil_menu` = `idperfil_menu`;

-- Do not automatically insert companies: `clave_pbi` must match the exact stable key in Master_Dim_Empresa.
-- Create companies and user/company assignments from the new administration pages.
-- Optional template, after replacing the values with the real model key:
-- INSERT INTO `pbi_security_companies` (`nombre`, `clave_pbi`, `activo`, `fecha_creacion`, `usuario_creacion`)
-- VALUES ('Montreal', 'REEMPLAZAR_CON_CLAVE_PBIX', 1, NOW(6), 'initial_setup');

CREATE OR REPLACE VIEW `vw_pbi_security_users` AS
SELECT
    LOWER(TRIM(security_user.`correo`)) AS `Correo`,
    security_profile.`codigo` AS `PerfilCodigo`,
    security_profile.`nombre` AS `PerfilNombre`,
    security_profile.`powerbi_role_name` AS `PowerBiRole`
FROM `pbi_security_users` AS security_user
INNER JOIN `pbi_security_profiles` AS security_profile
    ON security_profile.`idperfil` = security_user.`idperfil`
WHERE security_user.`activo` = 1
  AND security_user.`fecha_eliminacion` IS NULL
  AND security_profile.`activo` = 1
  AND security_profile.`fecha_eliminacion` IS NULL;

CREATE OR REPLACE VIEW `vw_pbi_security_user_companies` AS
SELECT DISTINCT
    LOWER(TRIM(security_user.`correo`)) AS `Correo`,
    security_company.`clave_pbi` AS `EmpresaClave`
FROM `pbi_security_user_companies` AS user_company
INNER JOIN `pbi_security_users` AS security_user
    ON security_user.`idusuario` = user_company.`idusuario`
INNER JOIN `pbi_security_profiles` AS security_profile
    ON security_profile.`idperfil` = security_user.`idperfil`
INNER JOIN `pbi_security_companies` AS security_company
    ON security_company.`idempresa` = user_company.`idempresa`
WHERE user_company.`activo` = 1
  AND user_company.`fecha_eliminacion` IS NULL
  AND security_user.`activo` = 1
  AND security_user.`fecha_eliminacion` IS NULL
  AND security_profile.`activo` = 1
  AND security_profile.`fecha_eliminacion` IS NULL
  AND security_company.`activo` = 1
  AND security_company.`fecha_eliminacion` IS NULL;

-- Replaces the old direct-only view. Profile grants and direct user exceptions are additive.
CREATE OR REPLACE VIEW `vw_pbi_menu_permisos` AS
SELECT DISTINCT
    LOWER(TRIM(security_user.`correo`)) AS `Correo`,
    profile_menu_item.`idmenu`
FROM `pbi_security_users` AS security_user
INNER JOIN `pbi_security_profiles` AS security_profile
    ON security_profile.`idperfil` = security_user.`idperfil`
INNER JOIN `pbi_security_profile_menu_items` AS profile_menu_item
    ON profile_menu_item.`idperfil` = security_profile.`idperfil`
INNER JOIN `pbi_menu_items` AS menu_item
    ON menu_item.`idmenu` = profile_menu_item.`idmenu`
WHERE security_user.`activo` = 1
  AND security_user.`fecha_eliminacion` IS NULL
  AND security_profile.`activo` = 1
  AND security_profile.`fecha_eliminacion` IS NULL
  AND profile_menu_item.`activo` = 1
  AND profile_menu_item.`fecha_eliminacion` IS NULL
  AND menu_item.`activo` = 1
  AND menu_item.`fecha_eliminacion` IS NULL
  AND menu_item.`requiere_permiso` = 1
UNION
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

-- Optional least-privilege grants. Execute only with a database administrator account.
-- GRANT SELECT, INSERT, UPDATE ON `pbi`.`pbi_security_profiles` TO 'pbi_access_admin'@'192.168.4.%';
-- GRANT SELECT, INSERT, UPDATE ON `pbi`.`pbi_security_users` TO 'pbi_access_admin'@'192.168.4.%';
-- GRANT SELECT, INSERT, UPDATE ON `pbi`.`pbi_security_companies` TO 'pbi_access_admin'@'192.168.4.%';
-- GRANT SELECT, INSERT, UPDATE ON `pbi`.`pbi_security_user_companies` TO 'pbi_access_admin'@'192.168.4.%';
-- GRANT SELECT, INSERT, UPDATE ON `pbi`.`pbi_security_profile_menu_items` TO 'pbi_access_admin'@'192.168.4.%';
-- GRANT SELECT ON `pbi`.`vw_pbi_security_users` TO 'pbi_powerbi_reader'@'%';
-- GRANT SELECT ON `pbi`.`vw_pbi_security_user_companies` TO 'pbi_powerbi_reader'@'%';
-- GRANT SELECT ON `pbi`.`vw_pbi_menu` TO 'pbi_powerbi_reader'@'%';
-- GRANT SELECT ON `pbi`.`vw_pbi_menu_permisos` TO 'pbi_powerbi_reader'@'%';
