-- Aplicar solamente sobre una copia de la base existente y después de un backup.
-- Este archivo no es el volcado de estructura: no contiene DROP TABLE.
-- Primero revisar el resultado de la consulta de duplicados; debe ser vacío.
-- En la base local mercedes, el índice único ya se instaló directamente; no ejecutar este archivo allí.

SELECT rider_id, grupo_id, turno_id, fecha, tipo, COUNT(*) AS cantidad
FROM asistencias
GROUP BY rider_id, grupo_id, turno_id, fecha, tipo
HAVING COUNT(*) > 1;

-- Añadir defaults solo donde faltan; conservar toda configuración existente.
INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
    ('site_name', 'Mercedes', 'Nombre público del sitio.'),
    ('tool_cuaderno_enabled', '0', 'Activa o desactiva globalmente la herramienta Cuaderno.'),
    ('tool_asistencias_enabled', '0', 'Activa o desactiva globalmente el Control de Asistencias.');

UPDATE settings
SET setting_value = '0'
WHERE setting_key IN ('tool_cuaderno_enabled', 'tool_asistencias_enabled')
  AND (setting_value IS NULL OR TRIM(setting_value) = '');

-- Restaurar únicamente campos vacíos; no sobrescribir datos reales.
INSERT INTO punto_control (titulo, barrio, direccion, contacto, latitud, longitud, radio_metros)
SELECT 'Predeterminado', 'Predeterminado', 'Predeterminado', 'Sin configurar', 0, 0, 50
WHERE NOT EXISTS (SELECT 1 FROM punto_control);

UPDATE punto_control SET titulo = 'Predeterminado' WHERE titulo IS NULL OR TRIM(titulo) = '';
UPDATE punto_control SET barrio = 'Predeterminado' WHERE barrio IS NULL OR TRIM(barrio) = '';
UPDATE punto_control SET direccion = 'Predeterminado' WHERE direccion IS NULL OR TRIM(direccion) = '';
UPDATE punto_control SET contacto = 'Sin configurar' WHERE contacto IS NULL OR TRIM(contacto) = '';
UPDATE punto_control SET latitud = 0 WHERE latitud IS NULL;
UPDATE punto_control SET longitud = 0 WHERE longitud IS NULL;
UPDATE punto_control SET radio_metros = 50 WHERE radio_metros IS NULL OR radio_metros < 1;

ALTER TABLE punto_control
    MODIFY titulo VARCHAR(100) NOT NULL DEFAULT 'Predeterminado',
    MODIFY barrio VARCHAR(100) NOT NULL DEFAULT 'Predeterminado',
    MODIFY direccion VARCHAR(150) NOT NULL DEFAULT 'Predeterminado',
    MODIFY contacto VARCHAR(50) NOT NULL DEFAULT 'Sin configurar',
    MODIFY latitud DECIMAL(10,7) NOT NULL DEFAULT 0,
    MODIFY longitud DECIMAL(10,7) NOT NULL DEFAULT 0,
    MODIFY radio_metros INT NOT NULL DEFAULT 50;

ALTER TABLE punto_control
    ADD CONSTRAINT chk_punto_titulo_no_vacio CHECK (CHAR_LENGTH(TRIM(titulo)) > 0),
    ADD CONSTRAINT chk_punto_barrio_no_vacio CHECK (CHAR_LENGTH(TRIM(barrio)) > 0),
    ADD CONSTRAINT chk_punto_direccion_no_vacia CHECK (CHAR_LENGTH(TRIM(direccion)) > 0),
    ADD CONSTRAINT chk_punto_contacto_no_vacio CHECK (CHAR_LENGTH(TRIM(contacto)) > 0),
    ADD CONSTRAINT chk_punto_radio_valido CHECK (radio_metros BETWEEN 1 AND 10000);

-- Ejecutar solo si el preflight de duplicados anterior no devolvió filas.
ALTER TABLE asistencias
    ADD UNIQUE KEY uq_asistencia_rider_grupo_turno_fecha_tipo
        (rider_id, grupo_id, turno_id, fecha, tipo);