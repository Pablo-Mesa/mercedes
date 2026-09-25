<?php

date_default_timezone_set('America/Asuncion');

require_once __DIR__ . '/../models/AsistenciaModel.php';
require_once __DIR__ . '/../models/RiderModel.php';
require_once __DIR__ . '/../models/PuntoControlModel.php';
require_once __DIR__ . '/../models/GruposModel.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../app/services/AsistenciasService.php';


class AsistenciasController extends BaseController
{
    private AsistenciaModel $asistenciaModel;
    private AsistenciasService $asistenciasService;
    private RiderModel $riderModel;
    private PuntoControlModel $puntoControlModel;
    private $grupoModel;
    private TurnoModel $turnoModel; 

    public function __construct()
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }

        $this->asistenciaModel    = new AsistenciaModel();
        $this->asistenciasService = new AsistenciasService(new AsistenciasRepository());
        $this->grupoModel         = new GruposModel();
        $this->riderModel         = new RiderModel();
        $this->puntoControlModel  = new PuntoControlModel();
        $this->turnoModel         = new TurnoModel();   // 👈 inicialización faltante

        // Solo riders pueden acceder a esta vista
        $this->requireRole(['rider']);
        $this->requireToolEnabled('tool_asistencias_enabled', 'El Control de Asistencias está desactivado.');
    }

    /**
     * GET → Mostrar historial de asistencias del rider
     */
    public function index(): void
    {
        $riderId = $_SESSION['rider_id'] ?? null;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        if (!$riderId) {
            $this->redirect('/mercedes/login');
        }

        $asistencias = $this->asistenciaModel->getByRiderAndDate($riderId, $fecha);

        $this->renderLayout('rider_asistencia/index', [
            'fecha' => $fecha,
            'asistencias' => $asistencias,
            'formAction' => BASE_PATH . '/rider_asistencia/form'
        ]);
    }

    /**
     * POST → Marcar llegada/salida
     */
        public function store(): void
    {
        $payload = [
            'rider_id'       => $_SESSION['rider_id'] ?? null,
            'grupo_id'       => $_POST['grupo_id'] ?? null,
            'turno_id'       => $_POST['turno_id'] ?? null,
            'fecha'          => date('Y-m-d'),
            'hora'           => date('H:i:s'),
            'tipo'           => $_POST['tipo'] ?? 'entrada',
            'lat'            => $_POST['lat'] ?? null,
            'lon'            => $_POST['lon'] ?? null,
            'dispositivo'    => $_POST['dispositivo'] ?? 'web',
            'ip_registro'    => $_SERVER['REMOTE_ADDR'] ?? null,
            'punto_control_id' => $_POST['punto_control_id'] ?? null,
            'observaciones'  => $_POST['observaciones'] ?? null,
        ];

        $errors = $this->asistenciasService->validarMarcacion($payload);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => implode(' ', $errors),
            ], 400);
            return;
        }

        $success = $this->asistenciasService->registrar($payload);

        $this->json([
            'success' => $success,
            'message' => $success
                ? 'Marcación registrada correctamente.'
                : 'No se pudo registrar la marcación.',
            'data' => $success ? $payload : null
        ]);
    }

    private function renderLayout(string $view, array $data): void
    {
        $data['view'] = $view;
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function form(): void
    {
        $riderId = $_SESSION['rider_id'] ?? null;
        $fecha = date('Y-m-d');

        if (!$riderId) {
            $this->redirect('/mercedes/login');
        }

        $grupos = $this->grupoModel->getByRider($riderId);
        foreach ($grupos as &$g) {
            $g['turnos'] = $this->turnoModel->getByGrupo($g['id_grupo']);
        }

        $asistencias = $this->asistenciaModel->getByRiderAndDate($riderId, $fecha);

        $this->renderLayout('rider_asistencia/form', [
            'formAction'   => BASE_PATH . '/rider_asistencia/form',
            'puntoControl' => $this->puntoControlModel->get(),
            'grupos'       => $grupos,
            'asistencias'  => $asistencias
        ]);
    }

}