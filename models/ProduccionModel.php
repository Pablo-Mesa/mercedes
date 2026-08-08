<?php
/**
 * models/ProduccionModel.php
 * Manejo de la tabla produccion.
 */
require_once __DIR__ . '/../config/Database.php';

class ProduccionModel {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /** 
     * Devuelve todas las producciones con join a rider, tarifa y usuario.
     */
    public function getAll(): array {
        $sql = "SELECT 
                    r.foto_perfil AS rider_foto,
                    r.name AS rider_nombre,
                    COUNT(CASE WHEN p.rendicion = 'pendiente' THEN 1 END) AS rendiciones_pendientes,
                    COUNT(p.id) AS servicios_realizados,
                    SUM(t.costo) AS total_tarifas
                FROM produccion p
                JOIN riders r ON p.id_rider = r.id
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                JOIN usuarios u ON p.id_usuario = u.id
                JOIN turnos tu ON p.id_turno = tu.id
                WHERE DATE(p.fecha_creacion) = CURDATE()
                GROUP BY r.id, r.name, r.foto_perfil
                ORDER BY r.name ASC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** 
     * Filtra producciones por fecha exacta.
     */
    public function getByFecha(string $fecha): array {
        $sql = "SELECT p.id AS id,
                    p.fecha_creacion AS fecha_creacion,
                    t.costo AS tarifa_detalle,
                    r.name AS rider_nombre,
                    r.foto_perfil AS rider_foto,       
                    u.name AS usuario_nombre,
                    tu.turno AS turno
                FROM produccion p
                JOIN riders r ON p.id_rider = r.id
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                JOIN usuarios u ON p.id_usuario = u.id
                JOIN turnos tu ON p.id_turno = tu.id
                WHERE DATE(p.fecha_creacion) = :fecha
                ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['fecha' => $fecha]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** 
     * Inserta nueva producción.
     */
    public function insert(array $data): bool
    {
        $sql = "INSERT INTO produccion 
                (fecha_creacion, id_usuario, id_rider, id_detalle_tarifa, id_turno, id_accion, total_factura, vuelto) 
                VALUES (:fecha_creacion, :id_usuario, :id_rider, :id_detalle_tarifa, :id_turno, :id_accion, :total_factura, :vuelto)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'fecha_creacion' => $data['fecha_creacion'],
            'id_usuario'     => $data['id_usuario'],
            'id_rider'       => $data['id_rider'],
            'id_detalle_tarifa' => $data['id_detalle_tarifa'],
            'id_turno'       => $data['id_turno'],
            'id_accion'      => $data['id_accion'],
            'total_factura'  => $data['total_factura'],
            'vuelto'         => $data['vuelto']
        ]);
    }

    /** 
     * Elimina producción por id.
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM produccion WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /** 
     * Helpers para poblar selects.
     */
    public function getAllRiders(): array {
        $sql = "SELECT id, name FROM riders WHERE is_active = 1 ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTarifas(): array {
        $sql = "SELECT id, costo FROM detalles_tarifas ORDER BY costo ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRendicion(int $id, int $estado): bool {
        $stmt = $this->db->prepare("UPDATE produccion SET rendicion = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public function getTarifasActivas(): array {
        $sql = "SELECT dt.id, dt.costo
                FROM detalles_tarifas dt
                JOIN encabezado_tarifa et ON dt.id_encabezado_tarifa = et.id_encabezado_tarifa
                WHERE et.is_active = 1 AND dt.disponible = 1
                ORDER BY dt.costo ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccionesRider(): array {
        $sql = "SELECT id_accion, nombre, codigo, requiere_factura
                FROM acciones_rider
                WHERE activo = 1
                ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccionById(int $idAccion): ?array {
        $sql = "SELECT id_accion, nombre, requiere_factura
                FROM acciones_rider
                WHERE id_accion = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idAccion]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function getProduccionesFiltradas(
        ?string $fecha = null,
        ?int $turno = null,
        ?int $rider = null,
        ?int $accion = null
    ): array {
        $sql = "SELECT p.*, 
                    r.name AS rider_nombre, 
                    a.nombre AS accion_nombre,
                    CASE p.id_turno 
                        WHEN 1 THEN 'Diurno'
                        WHEN 2 THEN 'Nocturno'
                    END AS turno,
                    t.costo AS tarifa_detalle,
                    p.rendicion
                FROM produccion p
                JOIN riders r ON p.id_rider = r.id
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                WHERE 1=1";

        $params = [];

        if ($fecha) {
            $sql .= " AND DATE(p.fecha_creacion) = :fecha";
            $params['fecha'] = $fecha;
        }

        if ($turno) {
            $sql .= " AND p.id_turno = :turno";
            $params['turno'] = $turno;
        }

        if ($rider) {
            $sql .= " AND p.id_rider = :rider";
            $params['rider'] = $rider;
        }

        if ($accion) {
            $sql .= " AND p.id_accion = :accion";
            $params['accion'] = $accion;
        }

        $sql .= " ORDER BY p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduccionesResumen(
        ?string $fecha = null,
        ?int $turno = null,
        ?int $rider = null,
        ?int $accion = null): array {
            
        $sql = "SELECT r.id AS rider_id,
                    r.name AS rider_nombre,
                    r.foto_perfil AS rider_foto,
                    COUNT(p.id) AS servicios,
                    SUM(t.costo) AS total_tarifa,
                    MIN(p.rendicion) AS rendicion,
                    CASE p.id_turno 
                        WHEN 1 THEN 'Diurno'
                        WHEN 2 THEN 'Nocturno'
                    END AS turno,
                    a.nombre AS accion_nombre
                FROM produccion p
                JOIN riders r ON p.id_rider = r.id
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                WHERE 1=1";

        $params = [];

        if ($fecha) {
            $sql .= " AND DATE(p.fecha_creacion) = :fecha";
            $params['fecha'] = $fecha;
        }
        if ($turno) {
            $sql .= " AND p.id_turno = :turno";
            $params['turno'] = $turno;
        }
        if ($rider) {
            $sql .= " AND p.id_rider = :rider";
            $params['rider'] = $rider;
        }
        if ($accion) {
            $sql .= " AND p.id_accion = :accion";
            $params['accion'] = $accion;
        }

        $sql .= " GROUP BY r.name
                ORDER BY r.name DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduccionesByRider(int $riderId, ?string $fecha = null): array {
        $sql = "SELECT p.id, p.fecha_creacion, p.rendicion, t.costo AS tarifa_detalle,
                    a.nombre AS accion_nombre,
                    CASE p.id_turno WHEN 1 THEN 'Diurno' WHEN 2 THEN 'Nocturno' END AS turno,
                    total_factura AS total_factura, vuelto AS vuelto
                FROM produccion p
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                WHERE p.id_rider = :riderId";
        $params = ['riderId' => $riderId];

        if ($fecha) {
            $sql .= " AND DATE(p.fecha_creacion) = :fecha";
            $params['fecha'] = $fecha;
        }

        $sql .= " ORDER BY p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /*, turno, a.nombre*/

    public function getTurnos()
    {
        $sql = "SELECT * FROM turnos ORDER BY hora_inicio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
