<?php
/**
 * models/PuntoControlModel.php
 * Modelo para la tabla punto_control con fallback automático.
 */
require_once __DIR__ . '/../config/Database.php';

class PuntoControlModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene el único registro de punto de control.
     * Si no existe, crea uno por defecto y lo devuelve.
     */
    public function get(): ?array {
        $sql = "SELECT * FROM punto_control LIMIT 1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $insert = $this->db->prepare(
                "INSERT INTO punto_control
                    (titulo, barrio, direccion, contacto, latitud, longitud, radio_metros)
                 VALUES
                    (:titulo, :barrio, :direccion, :contacto, :latitud, :longitud, :radio)"
            );
            $insert->execute([
                'titulo' => 'Predeterminado',
                'barrio' => 'Predeterminado',
                'direccion' => 'Predeterminado',
                'contacto' => 'Sin configurar',
                'latitud' => 0,
                'longitud' => 0,
                'radio' => 50,
            ]);

            // Volver a consultar
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    public static function isConfigured(?array $point): bool
    {
        if ($point === null) {
            return false;
        }

        $placeholderValues = ['predeterminado', 'por definir', 'sin configurar'];
        foreach (['titulo', 'barrio', 'direccion', 'contacto'] as $field) {
            $value = strtolower(trim((string)($point[$field] ?? '')));
            if ($value === '' || in_array($value, $placeholderValues, true)) {
                return false;
            }
        }

        $latitude = $point['latitud'] ?? null;
        $longitude = $point['longitud'] ?? null;
        $radius = $point['radio_metros'] ?? $point['radio'] ?? null;

        return is_numeric($latitude)
            && is_numeric($longitude)
            && (float)$latitude >= -90
            && (float)$latitude <= 90
            && (float)$longitude >= -180
            && (float)$longitude <= 180
            && !((float)$latitude === 0.0 && (float)$longitude === 0.0)
            && is_numeric($radius)
            && (int)$radius > 0
            && (int)$radius <= 10000;
    }

    public function getAll(): array {
        $sql = "SELECT id, titulo FROM punto_control ORDER BY titulo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el registro de punto de control.
     */
    public function update(array $data): bool {
        if (!self::isConfigured($data)) {
            return false;
        }

        $sql = "UPDATE punto_control 
                SET titulo = :titulo, 
                    barrio = :barrio, 
                    direccion = :direccion, 
                    contacto = :contacto, 
                    latitud = :latitud, 
                    longitud = :longitud,
                    radio_metros = :radio,
                    fecha_actualizacion = NOW()
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

}
