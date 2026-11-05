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
                (rider_id, grupo_id, turno_id, fecha, hora, tipo, dispositivo, ip_registro, lat, lon, punto_control_id, observaciones) 
                VALUES (:rider_id, :grupo_id, :turno_id, :fecha, :hora, :tipo, :dispositivo, :ip_registro, :lat, :lon, :punto_control_id, :observaciones)";

        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute([
                ':rider_id'        => $payload['rider_id'],
                ':grupo_id'        => $payload['grupo_id'],
                ':turno_id'        => $payload['turno_id'],
                ':fecha'           => $payload['fecha'],
                ':hora'            => $payload['hora'],
                ':tipo'            => $payload['tipo'],
                ':dispositivo'     => $payload['dispositivo'],
                ':ip_registro'     => $payload['ip_registro'],
                ':lat'             => $payload['lat'],
                ':lon'             => $payload['lon'],
                ':punto_control_id'=> $payload['punto_control_id'],
                ':observaciones'   => $payload['observaciones'],
            ]);
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                return false;
            }

            throw $exception;
        }
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

    public function getEntradaByRiderAndTurno(int $riderId, int $grupoId, int $turnoId, string $fecha): ?array
    {
        $sql = "SELECT * FROM asistencias 
                WHERE rider_id = :rider_id 
                AND grupo_id = :grupo_id 
                AND turno_id = :turno_id 
                AND fecha = :fecha 
                AND tipo = 'entrada'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':rider_id' => $riderId,
            ':grupo_id' => $grupoId,
            ':turno_id' => $turnoId,
            ':fecha' => $fecha,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Obtener todas las entradas pendientes de salida de un Rider.
     *
     * Cada jornada se identifica por:
     * rider_id + grupo_id + turno_id + fecha.
     *
     * Permite varias jornadas abiertas en empresas diferentes
     * y entradas correspondientes a días anteriores.
     */
    public function getEntradasPendientes(int $riderId): array
    {
        $sql = "
            SELECT
                entrada.id,
                entrada.rider_id,
                entrada.grupo_id,
                entrada.turno_id,
                entrada.fecha,
                entrada.hora AS hora_entrada,
                entrada.creado_el AS registrada_el,
                g.nombre AS empresa,
                t.turno,
                t.hora_inicio,
                t.hora_fin
            FROM asistencias entrada

            INNER JOIN grupos g
                ON g.id_grupo = entrada.grupo_id

            INNER JOIN turnos t
                ON t.id = entrada.turno_id

            WHERE entrada.rider_id = :rider_id
            AND entrada.tipo = 'entrada'

            AND NOT EXISTS (
                SELECT 1
                FROM asistencias salida
                WHERE salida.rider_id = entrada.rider_id
                    AND salida.grupo_id = entrada.grupo_id
                    AND salida.turno_id = entrada.turno_id
                    AND salida.fecha = entrada.fecha
                    AND salida.tipo = 'salida'
            )

            ORDER BY entrada.fecha DESC, entrada.hora DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':rider_id' => $riderId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}