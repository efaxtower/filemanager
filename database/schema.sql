-- ============================================================
-- Gestor de archivos - Schema de base de datos
-- Motor: MySQL 5.7+ / MariaDB 10.4+
-- Charset: utf8mb4 / utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS filemanager
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE filemanager;

-- ------------------------------------------------------------
-- Tabla: departments
-- Departamentos de la empresa
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: users
-- Credenciales, cuota, rol y departamento
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    department_id INT UNSIGNED NULL,
    quota_bytes BIGINT UNSIGNED NOT NULL DEFAULT 16106127360,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: nodes
-- Árbol de archivos y carpetas personales por usuario
-- parent_id NULL = nodo de primer nivel (raíz conceptual)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS nodes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('file', 'folder') NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_nodes_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_nodes_parent
        FOREIGN KEY (parent_id) REFERENCES nodes(id)
        ON DELETE CASCADE,

    UNIQUE KEY uniq_name_per_user_parent (user_id, parent_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: account_requests
-- Solicitudes de creación de cuenta
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS account_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    department_id INT UNSIGNED NULL,
    message TEXT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    reviewed_by INT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    rejection_reason VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_requests_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_requests_reviewer
        FOREIGN KEY (reviewed_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: reports
-- Reportes de usuarios (bugs, sugerencias, quejas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subject VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('bug', 'suggestion', 'complaint', 'other') NOT NULL DEFAULT 'other',
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('pending', 'in_review', 'resolved', 'closed') NOT NULL DEFAULT 'pending',
    admin_response TEXT NULL,
    reviewed_by INT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reports_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_reports_reviewer
        FOREIGN KEY (reviewed_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: shared_folders
-- Carpetas compartidas (jerárquicas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS shared_folders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT UNSIGNED NULL,
    owner_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_shared_parent
        FOREIGN KEY (parent_id) REFERENCES shared_folders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_shared_owner
        FOREIGN KEY (owner_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_shared_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE SET NULL,
    UNIQUE KEY uniq_shared_name_parent (parent_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: shared_permissions
-- Permisos individuales sobre carpetas compartidas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS shared_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    folder_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    can_read TINYINT(1) NOT NULL DEFAULT 1,
    can_write TINYINT(1) NOT NULL DEFAULT 0,
    can_delete TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_perm_folder
        FOREIGN KEY (folder_id) REFERENCES shared_folders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_perm_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    UNIQUE KEY uniq_perm_folder_user (folder_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: shared_files
-- Archivos dentro de carpetas compartidas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS shared_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    folder_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sfiles_folder
        FOREIGN KEY (folder_id) REFERENCES shared_folders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_sfiles_user
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
        ON DELETE CASCADE,
    UNIQUE KEY uniq_sfiles_folder_name (folder_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Datos iniciales: departamentos
-- ------------------------------------------------------------
INSERT IGNORE INTO departments (name, description) VALUES
('Técnicos', 'Equipo de desarrollo y soporte técnico'),
('Oficinistas', 'Personal administrativo'),
('RRHH', 'Recursos Humanos'),
('Gerencia', 'Dirección y gerencia');