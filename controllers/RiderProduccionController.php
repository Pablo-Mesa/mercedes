<?php
/**
 * controllers/RiderProduccionController.php
 * Producción visible para rol 'rider'.
 */
require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../models/GruposModel.php';
require_once __DIR__ . '/BaseController.php';

class RiderProduccionController extends BaseController {

    private ProduccionModel $produccionModel;
    private GruposModel $gruposModel;

    public function __construct() {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }

        // Solo rol rider
        $this->requireRole(['rider']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');

        $this->produccionModel = new ProduccionModel();
        $this->gruposModel     = new GruposModel();
    }

    /**
     * Vista principal: producción del rider logueado.
     */
    public function index(): void {
        $riderId = $_SESSION['rider_id'] ?? null;
        if (!$riderId) {
            echo "No se encontró el rider vinculado a este usuario.";
            exit;
        }

        $grupoId = $_GET['grupo_id'] ?? null;
        if ($grupoId === '') $grupoId = null;

        $fecha = $_GET['fecha'] ?? null;

        // Producciones del rider
        $producciones = $this->produccionModel->getProduccionPorRider($riderId, $grupoId, $fecha);

        // Grupos asociados al rider
        $grupos = $this->gruposModel->getByRider($riderId);

        // Resumen por grupo
        $resumenGrupos = $this->produccionModel->getResumenPorGrupo($riderId, $fecha, $grupoId);

        $view = 'rider_produccion/index';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Detalle de un día específico.
     */
    public function detalle(): void {
        $riderId = $_SESSION['rider_id'] ?? null;
        $fecha   = $_GET['fecha'] ?? null;

        if ($riderId && $fecha) {
            $producciones = $this->produccionModel->getProduccionPorRider($riderId, null, $fecha);
            $view = 'rider_produccion/detalle';
            require __DIR__ . '/../views/layouts/main.php';
        } else {
            echo "Parámetros inválidos.";
        }
    }

    public function getResumenPorGrupo(int $riderId, ?string $fecha = null): array {
        $sql = "SELECT g.nombre AS grupo_nombre, g.icono AS grupo_icono,
                    COUNT(*) AS servicios,
                    SUM(p.tarifa_detalle) AS costos
                FROM produccion p
                JOIN grupos g ON p.id_grupo = g.id_grupo
                WHERE p.rider_id = :riderId";

        if ($fecha) {
            $sql .= " AND DATE(p.fecha_creacion) = :fecha";
        }

        $sql .= " GROUP BY g.id_grupo, g.nombre, g.icono";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':riderId', $riderId, PDO::PARAM_INT);
        if ($fecha) $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}