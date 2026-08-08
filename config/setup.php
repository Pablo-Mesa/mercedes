<?php
/**
 * config/setup.php
 * Instalador automático del proyecto Mercedes.
 */

require_once __DIR__ . '/Database.php';

try {
    $server = Database::getServerConnection();
    $dbName = Database::getDbName();

    $server->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $server->exec("USE `{$dbName}`");

    $server->exec("
        CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS cargos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cargo VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            descripcion VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NULL,
            address VARCHAR(255) NULL,
            role_id INT NOT NULL,
            cargo_id INT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            documento_tipo ENUM('DNI', 'Pasaporte', 'RUC', 'CI') NOT NULL DEFAULT 'DNI',
            documento_numero VARCHAR(50) NULL,
            CONSTRAINT fk_usuarios_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT fk_usuarios_cargo FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value VARCHAR(255) NULL,
            description VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS riders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            contacto VARCHAR(150) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            foto_perfil VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS encabezado_tarifa (
            id_encabezado_tarifa INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(150) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 0,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS detalles_tarifas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_encabezado_tarifa INT NOT NULL,
            desde DECIMAL(10,2) NOT NULL,
            hasta DECIMAL(10,2) NOT NULL,
            costo DECIMAL(10,2) NOT NULL,
            disponible TINYINT(1) NOT NULL DEFAULT 1,
            CONSTRAINT fk_detalles_encabezado FOREIGN KEY (id_encabezado_tarifa) REFERENCES encabezado_tarifa(id_encabezado_tarifa) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS turnos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            turno VARCHAR(50) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS acciones_rider (
            id_accion INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            codigo VARCHAR(50) NULL,
            requiere_factura TINYINT(1) NOT NULL DEFAULT 0,
            activo TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS produccion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            id_usuario INT NOT NULL,
            id_rider INT NOT NULL,
            id_detalle_tarifa INT NOT NULL,
            id_turno INT NOT NULL,
            id_accion INT NOT NULL,
            total_factura DECIMAL(10,2) NULL,
            vuelto DECIMAL(10,2) NULL,
            rendicion TINYINT(1) NOT NULL DEFAULT 0,
            CONSTRAINT fk_produccion_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT fk_produccion_rider FOREIGN KEY (id_rider) REFERENCES riders(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT fk_produccion_detalle FOREIGN KEY (id_detalle_tarifa) REFERENCES detalles_tarifas(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT fk_produccion_turno FOREIGN KEY (id_turno) REFERENCES turnos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT fk_produccion_accion FOREIGN KEY (id_accion) REFERENCES acciones_rider(id_accion) ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("INSERT IGNORE INTO turnos (id, turno, activo) VALUES (1, 'Diurno', 1), (2, 'Nocturno', 1)");
    $server->exec("INSERT IGNORE INTO acciones_rider (id_accion, nombre, codigo, requiere_factura, activo) VALUES (1, 'Entrega', 'ENT', 0, 1), (2, 'Cobro', 'COB', 1, 1)");

    $stmt = $server->prepare("SELECT id FROM roles WHERE slug = :slug");
    $stmt->execute(['slug' => 'admin']);
    $roleId = $stmt->fetchColumn();

    if (!$roleId) {
        $server->prepare("INSERT INTO roles (name, slug, description) VALUES (:name, :slug, :description)")
            ->execute([
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Rol con acceso total al sistema.'
            ]);
        $roleId = $server->lastInsertId();
    }

    $stmt = $server->prepare("SELECT id FROM cargos WHERE slug = :slug");
    $stmt->execute(['slug' => 'admin-general']);
    $cargoId = $stmt->fetchColumn();

    if (!$cargoId) {
        $server->prepare("INSERT INTO cargos (cargo, slug, descripcion) VALUES (:cargo, :slug, :descripcion)")
            ->execute([
                'cargo' => 'Administrador General',
                'slug' => 'admin-general',
                'descripcion' => 'Cargo asignado por defecto al usuario administrador.'
            ]);
        $cargoId = $server->lastInsertId();
    }

    $stmt = $server->prepare("SELECT id FROM settings WHERE setting_key = :k");
    $stmt->execute(['k' => 'site_name']);
    if (!$stmt->fetchColumn()) {
        $server->prepare("INSERT INTO settings (setting_key, setting_value, description) VALUES (:k, :v, :d)")
            ->execute([
                'k' => 'site_name',
                'v' => 'Mercedes Admin',
                'd' => 'Nombre público del sitio.'
            ]);
    }

    $stmt = $server->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => 'admin@correo.com']);
    if (!$stmt->fetchColumn()) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $server->prepare("INSERT INTO usuarios (name, email, password, phone, address, role_id, cargo_id, is_active, documento_tipo, documento_numero) VALUES (:name, :email, :password, :phone, :address, :role_id, :cargo_id, 1, :documento_tipo, :documento_numero)")
            ->execute([
                'name' => 'Administrador Principal',
                'email' => 'admin@correo.com',
                'password' => $hashedPassword,
                'phone' => '0000-0000',
                'address' => 'Oficina Central',
                'role_id' => $roleId,
                'cargo_id' => $cargoId,
                'documento_tipo' => 'DNI',
                'documento_numero' => '00000000'
            ]);
    }

    file_put_contents(__DIR__ . '/installed.txt', 'Instalado el ' . date('Y-m-d H:i:s'));
    Database::resetConnection();
} catch (PDOException $e) {
    die('<h1 style="font-family:sans-serif;color:#b00020;text-align:center;margin-top:80px;">Error durante la instalación automática: ' . htmlspecialchars($e->getMessage()) . '</h1>');
}
