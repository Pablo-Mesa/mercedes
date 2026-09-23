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
                (fecha_creacion, id_usuario, id_rider, id_detalle_tarifa, id_turno, id_grupo, id_accion, total_factura, vuelto) 
                VALUES (:fecha_creacion, :id_usuario, :id_rider, :id_detalle_tarifa, :id_turno, :id_grupo, :id_accion, :total_factura, :vuelto)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'fecha_creacion'   => $data['fecha_creacion'],
            'id_usuario'       => $data['id_usuario'],
            'id_rider'         => $data['id_rider'],
            'id_detalle_tarifa'=> $data['id_detalle_tarifa'],
            'id_turno'         => $data['id_turno'],
            'id_grupo'         => $data['id_grupo'],   // 👈 nuevo campo
            'id_accion'        => $data['id_accion'],
            'total_factura'    => $data['total_factura'],
            'vuelto'           => $data['vuelto']
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
        $sql = "SELECT id_accion, nombre, codigo, requiere_factura
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
        $sql = "SELECT 
                    p.id,
                    p.id_rider,
                    p.id_grupo,
                    p.fecha_creacion,
                    p.total_factura,
                    p.rendicion,
                    r.name AS rider_nombre, 
                    a.nombre AS accion_nombre,
                    CASE p.id_turno 
                        WHEN 1 THEN 'Diurno'
                        WHEN 2 THEN 'Nocturno'
                    END AS turno,
                    t.costo AS tarifa_detalle
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

        // 👇 filtro automático para operaciones
        if ($_SESSION['user_role'] === 'operaciones' && isset($_SESSION['grupo_id'])) {
            $sql .= " AND p.id_grupo = :grupoId";
            $params['grupoId'] = $_SESSION['grupo_id'];
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
        ?int $accion = null
    ): array {
            
        $sql = "SELECT r.id AS rider_id,
                    r.name AS rider_nombre,
                    r.foto_perfil AS rider_foto,
                    COUNT(p.id) AS servicios,
                    SUM(t.costo) AS total_tarifa,
                    MIN(p.rendicion) AS rendicion,
                    SUM(CASE WHEN a.requiere_factura = 1 AND p.rendicion = 0 THEN 1 ELSE 0 END) AS pendientes,
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

    public function getProduccionesByRider(int $riderId, ?string $fecha = null, ?int $grupoId = null): array
    {
        $sql = "SELECT 
                    p.id, 
                    p.fecha_creacion, 
                    p.rendicion, 
                    p.id_grupo,
                    t.costo AS tarifa_detalle,
                    a.nombre AS accion_nombre,
                    tu.turno AS turno, 
                    tu.url_icono AS url_icono,
                    g.nombre AS grupo_nombre,
                    g.icono AS icono,
                    p.total_factura, 
                    p.vuelto
                FROM produccion p
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                JOIN turnos tu ON p.id_turno = tu.id
                LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
                WHERE p.id_rider = :riderId";

        $params = ['riderId' => $riderId];

        if ($fecha) {
            $sql .= " AND DATE(p.fecha_creacion) = :fecha";
            $params['fecha'] = $fecha;
        }

        if ($grupoId !== null && $grupoId > 0) {
            $sql .= " AND p.id_grupo = :grupoId";
            $params['grupoId'] = $grupoId;
        } elseif ($_SESSION['user_role'] === 'operaciones' && isset($_SESSION['grupo_id'])) {
            $sql .= " AND p.id_grupo = :grupoId";
            $params['grupoId'] = (int) $_SESSION['grupo_id'];
        }

        $sql .= " ORDER BY p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRiderIdByProduccionId(int $id): ?int
    {
        $sql = "SELECT id_rider FROM produccion WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetchColumn();

        return $result !== false ? (int) $result : null;
    }

    /*, turno, a.nombre*/
    public function getTurnos()
    {
        $sql = "SELECT * FROM turnos ORDER BY hora_inicio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduccionResumenGlobal(?string $fecha = null): array {
        // Si no se pasa fecha, no hacemos nada (por ahora)
        if (!$fecha) {
            return [];
        }

        $sql = "SELECT 
                COUNT(DISTINCT p.id_rider) AS deliverys_activos,

                -- Pendientes totales (POS + efectivo)
                SUM(CASE WHEN a.requiere_factura = 1 AND p.rendicion = 0 THEN 1 ELSE 0 END) AS pendientes_totales,

                -- Cantidad de pendientes con POS
                SUM(CASE WHEN a.requiere_factura = 1 AND p.rendicion = 0 AND a.codigo = 'pos' THEN 1 ELSE 0 END) AS pendientes_pos,

                -- Cantidad de pendientes en efectivo
                SUM(CASE WHEN a.requiere_factura = 1 AND p.rendicion = 0 AND a.codigo = 'efectivo' THEN 1 ELSE 0 END) AS pendientes_efectivo,

                COUNT(*) AS total_servicios,
                SUM(CASE WHEN p.id_turno = 1 THEN 1 ELSE 0 END) AS mañana,
                SUM(CASE WHEN p.id_turno = 2 THEN 1 ELSE 0 END) AS tarde,
                SUM(CASE WHEN p.id_turno = 3 THEN 1 ELSE 0 END) AS noche,
                SUM(t.costo) AS total_produccion
            FROM produccion p
            JOIN acciones_rider a ON p.id_accion = a.id_accion
            JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
            WHERE DATE(p.fecha_creacion) = :fecha";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['fecha' => $fecha]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getProduccionPorDia(int $mes, int $anio, ?int $grupoId = null): array {
        $sql = "SELECT 
                    DATE(p.fecha_creacion) AS dia,
                    g.id_grupo,
                    g.nombre AS grupo_nombre,
                    g.icono AS grupo_icono,
                    COUNT(DISTINCT p.id_rider) AS deliverys_activos,
                    SUM(CASE WHEN p.id_turno = 1 THEN 1 ELSE 0 END) AS mañana,
                    SUM(CASE WHEN p.id_turno = 2 THEN 1 ELSE 0 END) AS tarde,
                    SUM(CASE WHEN p.id_turno = 3 THEN 1 ELSE 0 END) AS noche,
                    SUM(t.costo) AS total_costo
                FROM produccion p
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                JOIN grupos g ON p.id_grupo = g.id_grupo
                WHERE MONTH(p.fecha_creacion) = :mes 
                AND YEAR(p.fecha_creacion) = :anio";

        if ($grupoId) {
            $sql .= " AND p.id_grupo = :grupo_id";
        }

        $sql .= " GROUP BY dia, g.id_grupo, g.nombre, g.icono
                ORDER BY dia ASC, g.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        if ($grupoId) {
            $stmt->bindValue(':grupo_id', $grupoId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduccionResumenMensual(int $mes, int $anio, ?int $grupoId = null): array {
        $sql = "SELECT 
                    COUNT(DISTINCT p.id_rider) AS deliverys_activos_mes,
                    COUNT(*) AS total_envios_mes,
                    SUM(t.costo) AS total_costo_mes
                FROM produccion p
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                WHERE MONTH(p.fecha_creacion) = :mes 
                AND YEAR(p.fecha_creacion) = :anio";

        if ($grupoId) {
            $sql .= " AND p.id_grupo = :grupo_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        if ($grupoId) {
            $stmt->bindValue(':grupo_id', $grupoId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTotalesPorMes(int $mes, int $anio, ?int $grupoId = null): array {
        $sql = "SELECT 
                    COUNT(DISTINCT p.id_rider) AS deliverys_activos,
                    SUM(CASE WHEN p.id_turno = 1 THEN 1 ELSE 0 END) AS mañana,
                    SUM(CASE WHEN p.id_turno = 2 THEN 1 ELSE 0 END) AS tarde,
                    SUM(CASE WHEN p.id_turno = 3 THEN 1 ELSE 0 END) AS noche,
                    SUM(t.costo) AS total_costo
                FROM produccion p
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                WHERE MONTH(p.fecha_creacion) = :mes 
                AND YEAR(p.fecha_creacion) = :anio";

        if ($grupoId) {
            $sql .= " AND p.id_grupo = :grupo_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        if ($grupoId) {
            $stmt->bindValue(':grupo_id', $grupoId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getProduccionesConDetalle_Deprecated(
        ?string $fecha = null,
        ?int $turno = null,
        ?int $rider = null,
        ?int $accion = null
    ): array {
        $sql = "SELECT p.id, p.id_rider, r.name AS rider_nombre, r.foto_perfil AS rider_foto,
                    p.total_factura, p.rendicion, a.codigo AS accion_codigo,
                    t.costo AS tarifa, p.id_grupo
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

        // 👇 filtro automático para operaciones
        if ($_SESSION['user_role'] === 'operaciones' && isset($_SESSION['grupo_id'])) {
            $sql .= " AND p.id_grupo = :grupoId";
            $params['grupoId'] = $_SESSION['grupo_id'];
        }

        $sql .= " ORDER BY r.name ASC, p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por Rider
        $producciones = [];
        foreach ($rows as $row) {
            $riderId = $row['id_rider'];
            if (!isset($producciones[$riderId])) {
                $producciones[$riderId] = [
                    'rider_id'     => $riderId,
                    'rider_nombre' => $row['rider_nombre'],
                    'rider_foto'   => $row['rider_foto'],
                    'id_grupo'     => $row['id_grupo'],
                    'facturas'     => [],
                    'tarifas'      => [],
                    'total_tarifa' => 0
                ];
            }

            $icono = $row['accion_codigo'] === 'efectivo' ? '💵' :
                    ($row['accion_codigo'] === 'pos' ? '💳' : '📦');

            $producciones[$riderId]['facturas'][] = [
                'monto'       => $row['total_factura'],
                'accion_icono'=> $icono,
                'rendicion'   => ($row['accion_codigo'] === 'entrega') ? 1 : $row['rendicion']
            ];

            $producciones[$riderId]['tarifas'][] = [
                'costo' => $row['tarifa']
            ];

            $producciones[$riderId]['total_tarifa'] += $row['tarifa'];
        }

        return array_values($producciones);
    }

    public function getProduccionesConDetalle(
        ?string $fecha = null,
        ?int $turno = null,
        ?int $rider = null,
        ?int $accion = null
    ): array {
        $sql = "SELECT p.id, p.id_rider, r.name AS rider_nombre, r.foto_perfil AS rider_foto,
                    p.total_factura, p.rendicion, a.codigo AS accion_codigo,
                    t.costo AS tarifa, p.id_grupo, g.nombre AS grupo_nombre, g.icono AS grupo_icono
                FROM produccion p
                JOIN riders r ON p.id_rider = r.id
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                JOIN grupos g ON p.id_grupo = g.id_grupo
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

        // Filtro automático para operaciones
        if ($_SESSION['user_role'] === 'operaciones' && isset($_SESSION['grupo_id'])) {
            $sql .= " AND p.id_grupo = :grupoId";
            $params['grupoId'] = $_SESSION['grupo_id'];
        }

        $sql .= " ORDER BY g.nombre ASC, r.name ASC, p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por Grupo y Rider
        $producciones = [];
        foreach ($rows as $row) {
            $grupoId = $row['id_grupo'];
            if (!isset($producciones[$grupoId])) {
                $producciones[$grupoId] = [
                    'grupo_id'     => $grupoId,
                    'grupo_nombre' => $row['grupo_nombre'],
                    'grupo_icono'  => $row['grupo_icono'], 
                    'riders'       => []
                ];
            }

            $riderId = $row['id_rider'];
            if (!isset($producciones[$grupoId]['riders'][$riderId])) {
                $producciones[$grupoId]['riders'][$riderId] = [
                    'rider_id'     => $riderId,
                    'rider_nombre' => $row['rider_nombre'],
                    'rider_foto'   => $row['rider_foto'],
                    'facturas'     => [],
                    'tarifas'      => [],
                    'total_tarifa' => 0
                ];
            }

            $icono = $row['accion_codigo'] === 'efectivo' ? '💵' :
                    ($row['accion_codigo'] === 'pos' ? '💳' : '📦');

            $producciones[$grupoId]['riders'][$riderId]['facturas'][] = [
                'monto'       => $row['total_factura'],
                'accion_icono'=> $icono,
                'rendicion'   => ($row['accion_codigo'] === 'entrega') ? 1 : $row['rendicion']
            ];

            $producciones[$grupoId]['riders'][$riderId]['tarifas'][] = [
                'costo' => $row['tarifa']
            ];

            $producciones[$grupoId]['riders'][$riderId]['total_tarifa'] += $row['tarifa'];
        }

        return array_values($producciones);
    }

    public function getRidersByGrupo($grupoId) {
        $sql = "SELECT DISTINCT r.* 
                FROM riders r
                JOIN rider_grupo rg ON rg.rider_id = r.id
                WHERE rg.grupo_id = :grupo_id AND r.is_active = 1
                ORDER BY r.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':grupo_id', $grupoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByRider(int $riderId, ?string $fecha = null, ?int $grupoId = null): array
    {
        $sql = "SELECT p.id, p.fecha_creacion, p.rendicion, 
                    t.costo AS tarifa_detalle,
                    a.nombre AS accion_nombre,
                    tu.turno AS turno, tu.url_icono AS url_icono,
                    g.nombre AS grupo_nombre,
                    g.icono AS icono,
                    p.total_factura, p.vuelto
                FROM produccion p
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                JOIN turnos tu ON p.id_turno = tu.id
                JOIN grupos g ON p.id_grupo = g.id_grupo
                WHERE p.id_rider = :riderId";

        $params = ['riderId' => $riderId];

        if ($fecha) {
            $sql .= " AND DATE(p.fecha_creacion) = :fecha";
            $params['fecha'] = $fecha;
        }

        if ($grupoId !== null && $grupoId > 0) {
            $sql .= " AND p.id_grupo = :grupoId";
            $params['grupoId'] = $grupoId;
        } elseif ($_SESSION['user_role'] === 'operaciones' && isset($_SESSION['grupo_id'])) {
            $sql .= " AND p.id_grupo = :grupoId";
            $params['grupoId'] = (int) $_SESSION['grupo_id'];
        }

        $sql .= " ORDER BY p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduccionResumenPorGrupo(?string $fecha = null, ?int $grupoId = null): array
    {
        if (!$fecha || $grupoId === null || $grupoId <= 0) {
            return [];
        }

        $sql = "SELECT 
                    COUNT(DISTINCT p.id_rider) AS deliverys_activos,

                    SUM(CASE WHEN a.requiere_factura = 1 AND p.rendicion = 0 THEN 1 ELSE 0 END) AS pendientes_totales,
                    SUM(CASE WHEN a.requiere_factura = 1 AND p.rendicion = 0 AND a.codigo = 'pos' THEN 1 ELSE 0 END) AS pendientes_pos,
                    SUM(CASE WHEN a.requiere_factura = 1 AND p.rendicion = 0 AND a.codigo = 'efectivo' THEN 1 ELSE 0 END) AS pendientes_efectivo,

                    COUNT(*) AS total_servicios,
                    SUM(CASE WHEN p.id_turno = 1 THEN 1 ELSE 0 END) AS mañana,
                    SUM(CASE WHEN p.id_turno = 2 THEN 1 ELSE 0 END) AS tarde,
                    SUM(CASE WHEN p.id_turno = 3 THEN 1 ELSE 0 END) AS noche,
                    SUM(t.costo) AS total_produccion
                FROM produccion p
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                WHERE DATE(p.fecha_creacion) = :fecha
                AND p.id_grupo = :grupoId";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'fecha' => $fecha,
            'grupoId' => $grupoId,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTarifasByGrupo(int $grupoId): array {
        $sql = "SELECT dt.id, dt.costo
                FROM detalles_tarifas dt
                JOIN encabezado_tarifa et 
                ON dt.id_encabezado_tarifa = et.id_encabezado_tarifa
                JOIN grupo_encabezado_tarifa ge 
                ON ge.id_encabezado_tarifa = et.id_encabezado_tarifa
                WHERE ge.id_grupo = :grupo_id
                AND et.is_active = 1 
                AND dt.disponible = 1
                ORDER BY dt.costo ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':grupo_id', $grupoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve producciones de un rider, con filtros opcionales por grupo y fecha.
     */
    public function getProduccionPorRider(int $riderId, ?int $grupoId = null, ?string $fecha = null): array {
        $sql = "SELECT 
                    p.id,
                    p.fecha_creacion,
                    r.name AS rider_nombre,
                    r.foto_perfil AS rider_foto,
                    g.nombre AS grupo_nombre,
                    g.icono AS grupo_icono,
                    tu.turno AS turno,
                    a.nombre AS accion,
                    t.costo AS tarifa_detalle
                FROM produccion p
                JOIN riders r ON p.id_rider = r.id
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                JOIN usuarios u ON p.id_usuario = u.id
                JOIN turnos tu ON p.id_turno = tu.id
                JOIN grupos g ON p.id_grupo = g.id_grupo
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                WHERE p.id_rider = :riderId";

        $params = ['riderId' => $riderId];

        if ($grupoId) {
            $sql .= " AND p.id_grupo = :grupoId";
            $params['grupoId'] = $grupoId;
        }

        if ($fecha) {
            $sql .= " AND DATE(p.fecha_creacion) = :fecha";
            $params['fecha'] = $fecha;
        }

        $sql .= " ORDER BY p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResumenPorGrupo(int $riderId, ?string $fecha = null, ?string $id_grupo = null): array {
        $sql = "SELECT g.nombre AS grupo_nombre, g.icono AS grupo_icono,
                    COUNT(*) AS servicios,
                    SUM(t.costo) AS costos
                FROM produccion p
                JOIN grupos g ON p.id_grupo = g.id_grupo
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                WHERE p.id_rider = :riderId";

        if ($fecha) {
            $sql .= " AND DATE(p.fecha_creacion) = :fecha";
        }

        if ($id_grupo) {
            $sql .= " AND p.id_grupo = :id_grupo";
        }

        $sql .= " GROUP BY g.id_grupo, g.nombre, g.icono";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':riderId', $riderId, PDO::PARAM_INT);
        if ($fecha) $stmt->bindParam(':fecha', $fecha);
        if ($id_grupo) $stmt->bindParam(':id_grupo', $id_grupo);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTurnosByGrupo(int $grupoId): array {
        $turnoModel = new TurnoModel();
        return $turnoModel->getByGrupo($grupoId);
    }

    public function getAllProduccionConRiders(?string $fecha = null): array {
        $sql = "SELECT p.id, p.id_rider, r.name AS rider_nombre,
                    r.foto_perfil AS rider_foto,
                    p.total_factura, p.rendicion,
                    a.codigo AS accion_codigo,
                    t.costo AS tarifa,
                    p.id_grupo, g.nombre AS grupo_nombre, g.icono AS grupo_icono
                FROM produccion p
                JOIN riders r ON p.id_rider = r.id
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN detalles_tarifas t ON p.id_detalle_tarifa = t.id
                JOIN grupos g ON p.id_grupo = g.id_grupo
                WHERE DATE(p.fecha_creacion) = :fecha
                ORDER BY g.nombre ASC, r.name ASC, p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['fecha' => $fecha]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $producciones = [];
        foreach ($rows as $row) {
            $grupoId = $row['id_grupo'];
            if (!isset($producciones[$grupoId])) {
                $producciones[$grupoId] = [
                    'grupo_id'     => $grupoId,
                    'grupo_nombre' => $row['grupo_nombre'],
                    'grupo_icono'  => $row['grupo_icono'],
                    'riders'       => []
                ];
            }

            $riderId = $row['id_rider'];
            if (!isset($producciones[$grupoId]['riders'][$riderId])) {
                $producciones[$grupoId]['riders'][$riderId] = [
                    'rider_id'     => $riderId,
                    'rider_nombre' => $row['rider_nombre'],
                    'rider_foto'   => $row['rider_foto'],
                    'facturas'     => [],
                    'tarifas'      => [],
                    'total_tarifa' => 0
                ];
            }

            // Mapear icono según acción
            $icono = $row['accion_codigo'] === 'efectivo' ? '💵' :
                    ($row['accion_codigo'] === 'pos' ? '💳' : '📦');

            $producciones[$grupoId]['riders'][$riderId]['facturas'][] = [
                'monto'       => $row['total_factura'],
                'accion_icono'=> $icono,
                'rendicion'   => ($row['accion_codigo'] === 'entrega') ? 1 : $row['rendicion']
            ];

            $producciones[$grupoId]['riders'][$riderId]['tarifas'][] = [
                'costo' => $row['tarifa']
            ];

            $producciones[$grupoId]['riders'][$riderId]['total_tarifa'] += $row['tarifa'];
        }

        return array_values($producciones);
    }

}