<?php
/**
 * models/AsistenciaModel.php
 * Representa la entidad Asistencia y acceso directo a la tabla asistencias.
 */

require_once __DIR__ . '/../config/Database.php';

class AsistenciaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Insertar nueva asistencia
     */
    public function insert(array $data): bool
    {
        $sql = "INSERT INTO asistencias 
                (rider_id, fecha, hora, tipo, dispositivo, ip_registro, lat, lon, punto_control_id, observaciones) 
                VALUES (:rider_id, :fecha, :hora, :tipo, :dispositivo, :ip_registro, :lat, :lon, :punto_control_id, :observaciones)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':rider_id' => $data['rider_id'],
            ':fecha' => $data['fecha'],
            ':hora' => $data['hora'],
            ':tipo' => $data['tipo'],
            ':dispositivo' => $data['dispositivo'],
            ':ip_registro' => $data['ip_registro'],
            ':lat' => $data['lat'],
            ':lon' => $data['lon'],
            ':punto_control_id' => $data['punto_control_id'],
            ':observaciones' => $data['observaciones'],
        ]);
    }

    /**
     * Obtener asistencias por rider y fecha
     */
    public function getByRiderAndDate(int $riderId, string $fecha): array
    {
        $sql = "SELECT * 
                FROM asistencias 
                WHERE rider_id = :rider_id AND fecha = :fecha 
                ORDER BY hora ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':rider_id' => $riderId,
            ':fecha' => $fecha,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validar duplicados (ej. dos entradas seguidas)
     */
    public function existeMarcacionDuplicada(int $riderId, string $tipo, string $fecha): bool
    {
        $sql = "SELECT COUNT(*) as total 
                FROM asistencias 
                WHERE rider_id = :rider_id AND tipo = :tipo AND fecha = :fecha";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':rider_id' => $riderId,
            ':tipo' => $tipo,
            ':fecha' => $fecha,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Obtener datos de un punto de control
     */
    public function getPuntoControlById(int $id): ?array
    {
        $sql = "SELECT * FROM punto_control WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Obtener todas las asistencias por fecha (para admin/operaciones)
     */
    public function getByDate(string $fecha): array
    {
        $sql = "SELECT a.*, r.name AS rider_name, r.contacto AS rider_contacto
                FROM asistencias a
                JOIN riders r ON a.rider_id = r.id
                WHERE a.fecha = :fecha
                ORDER BY a.hora ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fecha' => $fecha]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
