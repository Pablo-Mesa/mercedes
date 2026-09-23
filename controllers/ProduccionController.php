<?php

require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../models/RiderModel.php';
require_once __DIR__ . '/../models/DetalleTarifaModel.php';
require_once __DIR__ . '/../models/GruposModel.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../helpers/TurnoHelper.php';
require_once __DIR__ . '/../app/services/ProduccionService.php';

class ProduccionController extends BaseController
{
    private ProduccionModel $produccionModel;
    private ProduccionService $produccionService;
    private RiderModel $riderModel;
    private DetalleTarifaModel $detalleTarifaModel;

    public function __construct()
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }

        $this->produccionModel = new ProduccionModel();
        $this->produccionService = new ProduccionService($this->produccionModel);

        $this->riderModel = new RiderModel();
        $this->detalleTarifaModel = new DetalleTarifaModel();

        $this->requireRole(['admin', 'operaciones']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');
    }

    public function index(): void
    {
        $data = $this->buildIndexData();
        $this->renderLayout($data['view'], $data);
    }

    public function store(): void
    {
        $payload = [
            'id_usuario' => $_SESSION['user_id'] ?? null,
            'id_rider' => $_POST['id_rider'] ?? null,
            'id_detalle_tarifa' => $_POST['id_detalle_tarifa'] ?? null,
            'id_turno' => $_POST['id_turno'] ?? 1,
            'id_grupo' => $_POST['id_grupo'] ?? ($_SESSION['grupo_id'] ?? 0),
            'id_accion' => $_POST['id_accion'] ?? null,
            'total_factura' => trim($_POST['total_factura'] ?? ''),
            'vuelto' => trim($_POST['vuelto'] ?? ''),
            'fecha_creacion' => $_POST['fecha_creacion'] ?? date('Y-m-d H:i:s'),
        ];

        $errors = $this->produccionService->validarRegistro($payload);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => implode(' ', $errors),
            ], 400);
        }

        $success = $this->produccionService->registrar($payload);

        $this->json([
            'success' => $success,
            'message' => $success
                ? 'Producción registrada correctamente.'
                : 'No se pudo guardar la producción.',
        ]);
    }

    public function detalle(): void
    {
        $riderId = (int) ($_GET['rider'] ?? $_GET['id'] ?? 0);
        $fecha = $_GET['fecha'] ?? null;

        if ($riderId <= 0) {
            $this->json(['error' => 'Rider inválido'], 400);
        }

        $this->json($this->produccionModel->getProduccionesByRider($riderId, $fecha));
    }

    public function rendicionUpdate(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $estado = (int) ($_GET['estado'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'ID inválido'], 400);
        }

        $this->json($this->produccionService->actualizarRendicion($id, $estado));
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->produccionModel->delete($id);
            $_SESSION['success'] = 'Producción eliminada.';
        }

        $this->redirect('/mercedes/produccion?vista=tarjetas');
    }

    public function getRidersByGrupoAjax(): void
    {
        $grupoId = $_GET['grupo'] ?? null;

        $this->json($grupoId
            ? $this->produccionModel->getRidersByGrupo((int) $grupoId)
            : []);
    }

    public function getTarifasByGrupoAjax(): void
    {
        $grupoId = $_GET['grupo'] ?? null;

        $this->json($grupoId
            ? $this->detalleTarifaModel->getByGrupo((int) $grupoId)
            : []);
    }

    public function getTurnosByGrupoAjax(): void
    {
        $grupoId = $_GET['grupo'] ?? null;

        $this->json($grupoId
            ? $this->produccionModel->getTurnosByGrupo((int) $grupoId)
            : []);
    }

    public function byRider(): void
    {
        $riderId = (int) ($_GET['id'] ?? 0);
        $fecha = $_GET['fecha'] ?? null;

        if ($riderId <= 0) {
            $this->json(['error' => 'Rider inválido'], 400);
        }

        $grupoId = $_SESSION['user_role'] === 'operaciones' ? ($_SESSION['grupo_id'] ?? null) : null;

        $this->json($this->produccionModel->getProduccionesByRider($riderId, $fecha, $grupoId));
    }

    private function buildIndexData(): array
    {
        $fecha  = $_GET['fecha'] ?? date('Y-m-d');
        $turno  = isset($_GET['turno']) && $_GET['turno'] !== '' ? (int) $_GET['turno'] : null;
        $rider  = isset($_GET['rider']) && $_GET['rider'] !== '' ? (int) $_GET['rider'] : null;
        $accion = isset($_GET['accion']) && $_GET['accion'] !== '' ? (int) $_GET['accion'] : null;
        $vista  = $_GET['vista'] ?? 'tarjetas';

        $view = $vista === 'tarjetas' ? 'produccion/cards' : 'produccion/index';

        $data = [
            'fecha'  => $fecha,
            'turno'  => $turno,
            'rider'  => $rider,
            'accion' => $accion,
            'vista'  => $vista,
            'view'   => $view,
        ];

        if ($vista === 'tarjetas') {
            $data['produccionesResumen'] = $this->produccionModel->getProduccionesConDetalle($fecha, $turno, $rider, $accion);
        } else {
            $data['producciones'] = $this->produccionModel->getProduccionesFiltradas($fecha, $turno, $rider, $accion);
        }

        $grupoId = $_SESSION['grupo_id'] ?? null;

        if ($_SESSION['user_role'] === 'admin') {
            $grupos  = (new GruposModel())->getAll();
            $grupoId = $_POST['id_grupo'] ?? $_GET['grupo'] ?? null;

            if ($grupoId) {
                $data['riders']        = $this->produccionModel->getRidersByGrupo((int)$grupoId);
                $data['tarifas']       = $this->produccionModel->getTarifasByGrupo((int)$grupoId);
                $data['turnos']        = $this->produccionModel->getTurnosByGrupo((int)$grupoId); // ✅ turnos filtrados
                $data['resumenGlobal'] = $this->produccionModel->getProduccionResumenPorGrupo($fecha, (int)$grupoId);
            } else {
                $data['riders']        = [];
                $data['tarifas']       = [];
                $data['turnos']        = []; // vacío hasta que elija grupo
                $data['resumenGlobal'] = $this->produccionModel->getProduccionResumenGlobal($fecha);
            }
        } else {
            $grupos = [];
            $data['riders']        = $grupoId ? $this->produccionModel->getRidersByGrupo((int)$grupoId) : [];
            $data['tarifas']       = $grupoId ? $this->produccionModel->getTarifasByGrupo((int)$grupoId) : [];
            $data['turnos']        = $grupoId ? $this->produccionModel->getTurnosByGrupo((int)$grupoId) : [];
            $data['resumenGlobal'] = $this->produccionModel->getProduccionResumenPorGrupo($fecha, (int)$grupoId);
        }

        if ($_SESSION['user_role'] === 'operaciones' && $grupoId) {
            $grupoModel = new GruposModel();
            $grupo      = $grupoModel->getById($grupoId);

            if (!empty($grupo['icono'])) {
                $data['grupoIcono'] = '/mercedes/public/uploads/grupos/' . $grupo['icono'];
            }
        }

        $data['grupos']        = $grupos ?? [];
        $data['acciones']      = $this->produccionModel->getAccionesRider();
        $data['turnoActualId'] = TurnoHelper::getTurnoActual($data['turnos']);

        return $data;
    }

    private function renderLayout(string $view, array $data): void
    {
        $data['view'] = $view;
        require __DIR__ . '/../views/layouts/main.php';
    }
}