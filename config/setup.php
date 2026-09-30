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
        CREATE TABLE IF NOT EXISTS punto_control (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(100) NOT NULL,
            barrio VARCHAR(100) NOT NULL,
            direccion VARCHAR(150) NOT NULL,
            contacto VARCHAR(50) NOT NULL,
            latitud DECIMAL(10,7) NOT NULL DEFAULT 0,
            longitud DECIMAL(10,7) NOT NULL DEFAULT 0,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            radio_metros INT NOT NULL DEFAULT 50,
            CHECK (CHAR_LENGTH(TRIM(titulo)) > 0),
            CHECK (CHAR_LENGTH(TRIM(barrio)) > 0),
            CHECK (CHAR_LENGTH(TRIM(direccion)) > 0),
            CHECK (CHAR_LENGTH(TRIM(contacto)) > 0),
            CHECK (radio_metros BETWEEN 1 AND 10000)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS grupos (
            id_grupo INT AUTO_INCREMENT PRIMARY KEY,
            id_punto_control INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            descripcion VARCHAR(255) NULL,
            icono VARCHAR(255) NOT NULL DEFAULT 'default.png',
            activo TINYINT(1) NOT NULL DEFAULT 1,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_grupos_punto_control (id_punto_control)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

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
            grupo_id INT NULL,
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
            id_usuario INT NULL,
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
            hora_inicio TIME NOT NULL,
            hora_fin TIME NULL,
            url_icono VARCHAR(255) NULL,
            id_grupo INT NULL,
            INDEX idx_turnos_grupo_hora (id_grupo, hora_inicio)
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
            id_grupo INT NOT NULL,
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

    $server->exec("
        CREATE TABLE IF NOT EXISTS rider_grupo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rider_id INT NOT NULL,
            grupo_id INT NOT NULL,
            fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_rider_grupo_rider (rider_id),
            INDEX idx_rider_grupo_grupo (grupo_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS grupo_encabezado_tarifa (
            id_grupo_encabezado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_grupo INT NOT NULL,
            id_encabezado_tarifa INT UNSIGNED NOT NULL,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_grupo_tarifa_grupo (id_grupo),
            INDEX idx_grupo_tarifa_encabezado (id_encabezado_tarifa)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS tarifas (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            desde DECIMAL(10,2) NOT NULL DEFAULT 0,
            hasta DECIMAL(10,2) NOT NULL DEFAULT 0,
            costo INT NOT NULL DEFAULT 0,
            disponible TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $server->exec("
        CREATE TABLE IF NOT EXISTS asistencias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rider_id INT NOT NULL,
            grupo_id INT NOT NULL,
            turno_id INT NOT NULL,
            fecha DATE NOT NULL,
            hora TIME NOT NULL,
            tipo ENUM('entrada', 'salida') NOT NULL,
            dispositivo VARCHAR(50) NOT NULL DEFAULT 'web',
            ip_registro VARCHAR(45) NULL,
            lat DECIMAL(10,8) NULL,
            lon DECIMAL(10,8) NULL,
            punto_control_id INT NULL,
            observaciones TEXT NULL,
            creado_el TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_el TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_asistencias_rider_fecha (rider_id, fecha),
            INDEX idx_asistencias_grupo_turno (grupo_id, turno_id),
            INDEX idx_asistencias_punto_control (punto_control_id),
            UNIQUE KEY uq_asistencia_rider_grupo_turno_fecha_tipo (rider_id, grupo_id, turno_id, fecha, tipo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $ensureColumn = static function (string $table, string $column, string $definition) use ($server, $dbName): void {
        $stmt = $server->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND COLUMN_NAME = :column'
        );
        $stmt->execute(['schema' => $dbName, 'table' => $table, 'column' => $column]);
        if ((int)$stmt->fetchColumn() === 0) {
            $server->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    };

    foreach ([
        ['usuarios', 'grupo_id', 'INT NULL'],
        ['riders', 'id_usuario', 'INT NULL'],
        ['turnos', 'hora_inicio', 'TIME NOT NULL DEFAULT \'00:00:00\''],
        ['turnos', 'hora_fin', 'TIME NULL'],
        ['turnos', 'url_icono', 'VARCHAR(255) NULL'],
        ['turnos', 'id_grupo', 'INT NULL'],
        ['produccion', 'id_grupo', 'INT NOT NULL DEFAULT 0'],
        ['punto_control', 'radio_metros', 'INT NOT NULL DEFAULT 50'],
        ['asistencias', 'grupo_id', 'INT NOT NULL DEFAULT 0'],
        ['asistencias', 'turno_id', 'INT NOT NULL DEFAULT 0'],
    ] as [$table, $column, $definition]) {
        $ensureColumn($table, $column, $definition);
    }

    $indexCheck = $server->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND INDEX_NAME = :index_name'
    );
    $indexCheck->execute([
        'schema' => $dbName,
        'table' => 'asistencias',
        'index_name' => 'uq_asistencia_rider_grupo_turno_fecha_tipo',
    ]);
    if ((int)$indexCheck->fetchColumn() === 0) {
        $duplicates = $server->query(
            'SELECT 1 FROM asistencias GROUP BY rider_id, grupo_id, turno_id, fecha, tipo HAVING COUNT(*) > 1 LIMIT 1'
        )->fetchColumn();
        if ($duplicates !== false) {
            throw new RuntimeException('Hay marcaciones duplicadas; resuélvelas antes de aplicar la clave de unicidad.');
        }
        $server->exec(
            'ALTER TABLE asistencias ADD UNIQUE KEY uq_asistencia_rider_grupo_turno_fecha_tipo (rider_id, grupo_id, turno_id, fecha, tipo)'
        );
    }

    $server->exec("INSERT IGNORE INTO punto_control (id, titulo, barrio, direccion, contacto, latitud, longitud, radio_metros) VALUES (1, 'Predeterminado', 'Predeterminado', 'Predeterminado', 'Sin configurar', 0, 0, 50)");

    $server->exec("INSERT IGNORE INTO roles (id, name, slug, description) VALUES
        (1, 'Administrador', 'admin', 'Rol con acceso total al sistema, incluyendo la gestión de usuarios.'),
        (2, 'Operaciones', 'operaciones', 'Usuario encargado de cargar producciones de un grupo específico.'),
        (3, 'Rider', 'rider', 'Rol con acceso limitado a su propia producción.'),
        (4, 'Invitado', 'invitado', 'Rol con acceso de solo lectura a la producción de todos los riders.')
    ");

    $server->exec("INSERT IGNORE INTO cargos (id, cargo, slug, descripcion) VALUES (1, 'Administrador General', 'admin-general', 'Cargo asignado por defecto al usuario administrador del sistema.')");

    $server->exec("INSERT IGNORE INTO acciones_rider (id_accion, nombre, codigo, requiere_factura, activo) VALUES
        (1, 'Solo entrega', 'entrega', 0, 1),
        (2, 'Cobro con POS', 'pos', 1, 1),
        (3, 'Cobro en efectivo', 'efectivo', 1, 1)
    ");

    $server->exec("INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
        ('site_name', 'Mercedes', 'Nombre público del sitio.'),
        ('tool_cuaderno_enabled', '0', 'Activa o desactiva globalmente la herramienta Cuaderno.'),
        ('tool_asistencias_enabled', '0', 'Activa o desactiva globalmente el Control de Asistencias.')
    ");

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
        $adminPassword = getenv('MERCEDES_ADMIN_PASSWORD');
        if (!is_string($adminPassword) || strlen($adminPassword) < 12) {
            throw new RuntimeException('Configura MERCEDES_ADMIN_PASSWORD con al menos 12 caracteres antes de completar la instalación.');
        }

        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        $server->prepare("INSERT INTO usuarios (name, email, password, phone, address, role_id, grupo_id, cargo_id, is_active, documento_tipo, documento_numero) VALUES (:name, :email, :password, :phone, :address, :role_id, NULL, :cargo_id, 1, :documento_tipo, :documento_numero)")
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
} catch (Throwable $e) {
    die('<h1 style="font-family:sans-serif;color:#b00020;text-align:center;margin-top:80px;">Error durante la instalación automática: ' . htmlspecialchars($e->getMessage()) . '</h1>');
}
