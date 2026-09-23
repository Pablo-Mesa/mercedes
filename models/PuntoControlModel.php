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
            // Crear registro por defecto
            $insert = "INSERT INTO punto_control 
                       (titulo, barrio, direccion, contacto, latitud, longitud) 
                       VALUES ('Por definir', '', '', '', 0, 0)";
            $this->db->exec($insert);

            // Volver a consultar
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $result;
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
