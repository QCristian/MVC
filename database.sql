-- Base de datos para la aplicación MVC
-- Ejecutar en XAMPP (phpMyAdmin o mysql CLI)

CREATE DATABASE IF NOT EXISTS `mvc_app`
  DEFAULT CHARACTER SET = 'utf8mb4'
  DEFAULT COLLATE = 'utf8mb4_general_ci';

USE `mvc_app`;

-- Tabla de usuarios utilizada por RegisterController y LoginController
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin','superadmin','moderator') NOT NULL DEFAULT 'user',
  `force_password_change` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_users_username` (`username`),
  UNIQUE KEY `ux_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mensajes de contacto enviados por usuarios/visitantes
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contacts_user_id` (`user_id`),
  CONSTRAINT `fk_contacts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Drafts (notas privadas/guardadas por usuarios)
CREATE TABLE IF NOT EXISTS `drafts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_drafts_user_id` (`user_id`),
  CONSTRAINT `fk_drafts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Secciones del dashboard / sidebar, creadas y gestionadas por admin o por moderadores con aprobación
CREATE TABLE IF NOT EXISTS `dashboard_sections` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `content` TEXT NOT NULL,
  `created_by` INT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_dashboard_sections_slug` (`slug`),
  KEY `idx_dashboard_sections_created_by` (`created_by`),
  CONSTRAINT `fk_dashboard_sections_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `dashboard_section_views` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `section_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `content` TEXT NOT NULL DEFAULT '',
  `kind` ENUM('summary','details','table','form','custom') NOT NULL DEFAULT 'custom',
  `visible_to` VARCHAR(255) NOT NULL DEFAULT 'all',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dashboard_section_views_section_id` (`section_id`),
  KEY `idx_dashboard_section_views_created_by` (`created_by`),
  CONSTRAINT `fk_dashboard_section_views_section` FOREIGN KEY (`section_id`) REFERENCES `dashboard_sections`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dashboard_section_views_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `dashboard_products` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `view_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `weight` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `photo_path` VARCHAR(255) DEFAULT NULL,
  `photo_url` VARCHAR(255) DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dashboard_products_view_id` (`view_id`),
  KEY `idx_dashboard_products_created_by` (`created_by`),
  CONSTRAINT `fk_dashboard_products_view` FOREIGN KEY (`view_id`) REFERENCES `dashboard_section_views`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dashboard_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Solicitudes de cambio para moderación de contenidos
CREATE TABLE IF NOT EXISTS `section_change_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `section_id` INT NOT NULL,
  `requested_title` VARCHAR(150) NOT NULL,
  `requested_content` TEXT NOT NULL,
  `requested_by` INT NOT NULL,
  `status` ENUM('pending','in_progress','approved','rejected','deleted') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_section_change_requests_section_id` (`section_id`),
  KEY `idx_section_change_requests_requested_by` (`requested_by`),
  CONSTRAINT `fk_section_change_requests_section` FOREIGN KEY (`section_id`) REFERENCES `dashboard_sections`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_section_change_requests_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_section_change_requests_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Servicios ofertados (contenido mostrado en la web)
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_services_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ejemplo de usuario administrador (contraseña: cambiar antes de producción)
-- INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES ('admin', 'admin@example.com', '$2y$10$EXAMPLEHASHREPLACE', 'admin');

-- Auditoría de cambios de rol
CREATE TABLE IF NOT EXISTS `role_audit` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `changed_by` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `old_role` VARCHAR(50) DEFAULT NULL,
  `new_role` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_role_audit_changed_by` (`changed_by`),
  CONSTRAINT `fk_role_audit_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fin del esquema
