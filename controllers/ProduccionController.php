<?php
/**
 * controllers/ProduccionController.php
 * CRUD de producción, exclusivo para rol 'admin'.
 */
require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../models/RiderModel.php';
require_once __DIR__ . '/../models/DetalleTarifaModel.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../helpers/TurnoHelper.php';

class ProduccionController extends BaseController {

    private $db;
    private ProduccionModel $produccionModel;
    private RiderModel $riderModel;
    private DetalleTarifaModel $detalleTarifaModel;

    public function __construct() {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        
        // Si ya tienes una clase Database, úsala aquí
        $this->db = Database::getConnection();

        $this->produccionModel = new ProduccionModel();
        $this->riderModel = new RiderModel();
        $this->detalleTarifaModel = new DetalleTarifaModel();
        
        $this->ensureIsAdmin();
    }

    private function ensureIsAdmin(): void {
        if (($_SESSION['user_role'] ?? null) !== 'admin') {
            http_response_code(403);
            echo 'Acceso prohibido';
            exit;
        }
    }

    /** 
     * Listado de producciones con filtro por fecha.
     */    
    public function index(): void {
        $fecha  = $_GET['fecha'] ?? null;
        $turno  = isset($_GET['turno']) && $_GET['turno'] !== '' ? (int)$_GET['turno'] : null;
        $rider  = isset($_GET['rider']) && $_GET['rider'] !== '' ? (int)$_GET['rider'] : null;
        $accion = isset($_GET['accion']) && $_GET['accion'] !== '' ? (int)$_GET['accion'] : null;

        $vista = $_GET['vista'] ?? 'tarjetas';

        if ($vista === 'tarjetas') {
            $produccionesResumen = $this->produccionModel->getProduccionesResumen($fecha, $turno, $rider, $accion);
            $view = 'produccion/cards';
        } else {            
            $producciones = $this->produccionModel->getProduccionesFiltradas($fecha, $turno, $rider, $accion);
            $view = 'produccion/index';
        }

        // Datos para el formulario
        $riders   = $this->produccionModel->getAllRiders();
        $tarifas  = $this->produccionModel->getTarifasActivas();
        $acciones = $this->produccionModel->getAccionesRider();
        $turnos   = $this->produccionModel->getTurnos();

        // Determinar turno actual
        $turnoActualId = TurnoHelper::getTurnoActual($turnos);

        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Guarda nueva producción.
     */
    public function store()
    {
        $idUsuario = $_SESSION['user_id'];
        $idRider = $_POST['id_rider'] ?? null;
        $idTarifa = $_POST['id_detalle_tarifa'] ?? null;
        $idTurno = $_POST['id_turno'] ?? 1;
        $idAccion = $_POST['id_accion'] ?? null;
        $totalFactura = $_POST['total_factura'] ?? null;
        $vuelto = $_POST['vuelto'] ?? null;
        $fecha = $_POST['fecha_creacion'] ?? date('Y-m-d H:i:s');

        // Validación: si la acción requiere factura, obligar a completar
        $accionData = $this->produccionModel->getAccionById((int)$idAccion);
        if ($accionData && (int)$accionData['requiere_factura'] === 1) {
            if (empty($totalFactura) || empty($vuelto)) {
                $_SESSION['error'] = "Debe ingresar total factura y vuelto para cobro en efectivo.";
                header("Location: /mercedes/produccion");
                exit;
            }
        }

        $data = [
            'id_usuario' => $idUsuario,
            'id_rider' => $idRider,
            'id_detalle_tarifa' => $idTarifa,
            'id_turno' => $idTurno,
            'id_accion' => $idAccion,
            'total_factura' => $totalFactura,
            'vuelto' => $vuelto,
            'fecha_creacion' => $fecha
        ];

        $this->produccionModel->insert($data);        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Producción registrada correctamente.']);       
        exit;
    }

    public function detalle(): void {
        $riderId = (int)($_GET['rider'] ?? $_GET['id'] ?? 0);
        $fecha = DATE($_GET['fecha']) ?? null;

        if ($riderId > 0) {
            $producciones = $this->produccionModel->getProduccionesByRider($riderId, $fecha);
            header('Content-Type: application/json');
            echo json_encode($producciones);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Rider inválido']);
        }
    }

    public function rendicionUpdate(): void {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        $estado = (int)($_GET['estado'] ?? 0);

        if ($id > 0) {
            $sql = "UPDATE produccion SET rendicion = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estado, $id]);

            // obtener el rider_id para actualizar el badge en la tarjeta
            $stmt2 = $this->db->prepare("SELECT id_rider FROM produccion WHERE id = ?");
            $stmt2->execute([$id]);
            $riderId = $stmt2->fetchColumn();

            echo json_encode([
                'success' => true,
                'id' => $id,
                'estado' => $estado,
                'id_rider' => $riderId
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
        }
        exit;
    }


    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->produccionModel->delete($id);
            $_SESSION['success'] = 'Producción eliminada.';
        }
        header('Location: /mercedes/produccion?vista=tarjetas');
        exit;
    }

}
