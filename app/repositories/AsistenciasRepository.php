<?php

require_once __DIR__ . '/../../config/Database.php';

class AsistenciasRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Insertar nueva asistencia
     */
    public function insert(array $payload): bool
    {
        $sql = "INSERT INTO asistencias 
                (rider_id, fecha, hora, tipo, dispositivo, ip_registro, lat, lon, punto_control_id, observaciones) 
                VALUES (:rider_id, :fecha, :hora, :tipo, :dispositivo, :ip_registro, :lat, :lon, :punto_control_id, :observaciones)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':rider_id' => $payload['rider_id'],
            ':fecha' => $payload['fecha'],
            ':hora' => $payload['hora'],
            ':tipo' => $payload['tipo'],
            ':dispositivo' => $payload['dispositivo'],
            ':ip_registro' => $payload['ip_registro'],
            ':lat' => $payload['lat'],
            ':lon' => $payload['lon'],
            ':punto_control_id' => $payload['punto_control_id'],
            ':observaciones' => $payload['observaciones'],
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
     * Validar si existe marcación duplicada (ej. dos entradas seguidas)
     */
    public function existeMarcacionDuplicada(int $riderId, int $grupoId, int $turnoId, string $tipo, string $fecha): bool
    {
        $sql = "SELECT COUNT(*) as total
                FROM asistencias
                WHERE rider_id = :rider_id
                AND grupo_id = :grupo_id
                AND turno_id = :turno_id
                AND tipo = :tipo
                AND fecha = :fecha";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':rider_id' => $riderId,
            ':grupo_id' => $grupoId,
            ':turno_id' => $turnoId,
            ':tipo'     => $tipo,
            ':fecha'    => $fecha,
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

    public function getTurnosActivos(): array
    {
        $sql = "SELECT * FROM turnos WHERE 1 = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTurnos(): array {
        $sql = "SELECT * FROM turnos";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}