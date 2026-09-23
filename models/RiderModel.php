<?php
/**
 * models/RiderModel.php
 * Capa de acceso a datos para la entidad riders.
 */
require_once __DIR__ . '/../config/Database.php';

class RiderModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function index(): void {
        $fecha  = $_GET['fecha'] ?? date('Y-m-d');
        $turno  = isset($_GET['turno']) && $_GET['turno'] !== '' ? (int)$_GET['turno'] : null;
        $rider  = isset($_GET['rider']) && $_GET['rider'] !== '' ? (int)$_GET['rider'] : null;
        $accion = isset($_GET['accion']) && $_GET['accion'] !== '' ? (int)$_GET['accion'] : null;

        $vista = $_GET['vista'] ?? 'tarjetas';

        if ($vista === 'tarjetas') {
            $produccionesResumen = $this->produccionModel->getProduccionesConDetalle($fecha, $turno, $rider, $accion);
            $view = 'produccion/cards';
        } else {
            $producciones = $this->produccionModel->getProduccionesFiltradas($fecha, $turno, $rider, $accion);
            $view = 'produccion/index';
        }

        // 🔑 Grupo del usuario logueado
        $grupoId = $_SESSION['grupo_id'] ?? null;

        // 🔑 Grupo según rol
        if ($_SESSION['user_role'] === 'admin') {
            $grupos = (new GruposModel())->getAll(); // lista completa de grupos
            $grupoId = $_POST['id_grupo'] ?? $_GET['grupo'] ?? null;

            if ($grupoId) {
                // Admin seleccionó un grupo → riders de ese grupo
                $riders = $this->produccionModel->getRidersByGrupo($grupoId);
            } else {
                // Admin sin grupo seleccionado → todos los riders
                $riders = $this->riderModel->getAll();
            }
        } else {
            // Operaciones: grupo fijo en sesión
            $grupoId = $_SESSION['grupo_id'] ?? null;
            $grupos = [];
            $riders = $grupoId ? $this->produccionModel->getRidersByGrupo($grupoId) : [];
        }

        // 🔑 Nuevo: cargar icono del grupo para operaciones
        $grupoIcono = null;
        if ($_SESSION['user_role'] === 'operaciones' && $grupoId) {
            $grupoModel = new GruposModel();
            $grupo = $grupoModel->getById($grupoId);
            if (!empty($grupo['icono'])) {
                $grupoIcono = '/mercedes/public/uploads/grupos/' . $grupo['icono'];
            }
        }

        $tarifas  = $this->produccionModel->getTarifasActivas();
        $acciones = $this->produccionModel->getAccionesRider();
        $turnos   = $this->produccionModel->getTurnos();

        // 🔑 Resumen global adaptado por rol
        if ($_SESSION['user_role'] === 'admin') {
            $resumenGlobal = $this->produccionModel->getProduccionResumenGlobal($fecha);
        } else {
            $resumenGlobal = $this->produccionModel->getProduccionResumenPorGrupo($fecha, $grupoId);
        }

        $turnoActualId = TurnoHelper::getTurnoActual($turnos);

        require __DIR__ . '/../views/layouts/main.php';
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM riders WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data)
    {
        $sql = "INSERT INTO riders (name, contacto, is_active, foto_perfil) 
                VALUES (:name, :contacto, :is_active, :foto_perfil)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':contacto', $data['contacto']);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);
        $stmt->bindParam(':foto_perfil', $data['foto_perfil']);
        $stmt->execute();

        return $this->db->lastInsertId(); // 🔑 devolvemos el ID
    }

    public function update($id, $data)
    {
        $sql = "UPDATE riders 
                SET name = :name, contacto = :contacto, is_active = :is_active, foto_perfil = :foto_perfil 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':contacto', $data['contacto']);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);
        $stmt->bindParam(':foto_perfil', $data['foto_perfil']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Alterna el estado is_active entre 0 y 1 (activación/desactivación reversible).
     */
    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE riders SET is_active = NOT is_active WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM riders WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function assignToGroups($riderId, array $groupIds)
    {
        // Primero eliminamos asignaciones previas
        $stmt = $this->db->prepare("DELETE FROM rider_grupo WHERE rider_id = ?");
        $stmt->execute([$riderId]);

        // Insertamos nuevas asignaciones
        $sql = "INSERT INTO rider_grupo (rider_id, grupo_id) VALUES (:rider_id, :grupo_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($groupIds as $groupId) {
            $stmt->execute([
                ':rider_id' => $riderId,
                ':grupo_id' => $groupId
            ]);
        }
    }

    public function getGroups($riderId)
    {
        $sql = "SELECT grupo_id FROM rider_grupo WHERE rider_id = :rider_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':rider_id', $riderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN); // devuelve array de IDs de grupos
    }

    public function getByRider(int $riderId): array {
        $sql = "SELECT p.id, p.fecha_creacion, 
                       tu.turno, 
                       dt.costo AS tarifa_detalle, 
                       a.nombre AS accion_nombre, 
                       p.rendicion,
                       g.nombre AS grupo_nombre
                FROM produccion p
                JOIN turnos tu ON p.id_turno = tu.id
                JOIN detalles_tarifas dt ON p.id_detalle_tarifa = dt.id
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN grupos g ON p.id_grupo = g.id
                WHERE p.id_rider = :riderId
                ORDER BY p.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['riderId' => $riderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
{
    $sql = "SELECT *
            FROM riders
            ORDER BY name ASC";

    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

}