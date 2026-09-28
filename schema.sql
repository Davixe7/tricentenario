CREATE DATABASE IF NOT EXISTS management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE management_system;

CREATE TABLE IF NOT EXISTS areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_number INT NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name_paternal VARCHAR(100) NOT NULL,
    last_name_maternal VARCHAR(100) NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150) NULL,
    role ENUM('General Admin', 'Administrative Reporter', 'Operational Reporter', 'Collaborator') NOT NULL DEFAULT 'Collaborator',
    area_id INT NOT NULL,
    position_id INT NOT NULL,
    supervisor_id INT NULL,
    counterpart_reporter_id INT NULL,
    phone VARCHAR(20) NULL,
    nss VARCHAR(20) NULL,
    rfc VARCHAR(20) NULL,
    curp VARCHAR(20) NULL,
    birth_date DATE NULL,
    address TEXT NULL,
    avatar VARCHAR(255) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE RESTRICT,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE RESTRICT,
    FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (counterpart_reporter_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data (Admin user password: password123)
INSERT INTO areas (name, status) VALUES ('Dirección General', 'active'), ('Recursos Humanos', 'active');
INSERT INTO positions (name, status) VALUES ('Director General', 'active'), ('Coordinador RH', 'active');

INSERT INTO users (employee_number, first_name, last_name_paternal, username, password, role, area_id, position_id, status)
VALUES (1001, 'Administrador', 'Sistema', 'admin', '$2y$10$eE.wWvXG8S1hG1Uo9aQG/.7pXwG7uR0H0eL3J.b7W8n7B1eR5Q4i6', 'General Admin', 1, 1, 'active');