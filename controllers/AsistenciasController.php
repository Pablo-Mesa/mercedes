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
        // Forzar zona horaria local de Asunción
        date_default_timezone_set('America/Asuncion');

        $payload = [
            'rider_id'        => $_SESSION['rider_id'] ?? null,
            'grupo_id'        => $_POST['grupo_id'] ?? null,
            'turno_id'        => $_POST['turno_id'] ?? null,
            'fecha'           => date('Y-m-d'),
            'hora'            => date('H:i:s'),
            'tipo'            => $_POST['tipo'] ?? 'entrada',
            'lat'             => $_POST['lat'] ?? null,
            'lon'             => $_POST['lon'] ?? null,
            'dispositivo'     => $_POST['dispositivo'] ?? 'web',
            'ip_registro'     => $_SERVER['REMOTE_ADDR'] ?? null,
            'punto_control_id'=> $_POST['punto_control_id'] ?? null,
            'observaciones'   => $_POST['observaciones'] ?? null,
        ];

        $errors = $this->asistenciasService->validarMarcacion($payload);

        if (!empty($errors)) {
            $this->json([
                'success'    => false,
                'error_code' => 400,
                'message'    => implode(' ', $errors),
            ], 400);
            return;
        }

        $success = $this->asistenciasService->registrar($payload);

        // Obtener nombre de empresa desde grupoModel
        $empresaNombre = null;
        if (!empty($payload['grupo_id'])) {
            $grupo = $this->grupoModel->getById($payload['grupo_id']);
            $empresaNombre = $grupo['nombre'] ?? null;
        }

        // Obtener horas acumuladas del turno
        $horaEntrada = $this->asistenciaModel->getHoraEntrada(
            (int)$payload['rider_id'],
            (int)$payload['grupo_id'],
            (int)$payload['turno_id'],
            $payload['fecha']
        );

        $horaSalida = $this->asistenciaModel->getHoraSalida(
            (int)$payload['rider_id'],
            (int)$payload['grupo_id'],
            (int)$payload['turno_id'],
            $payload['fecha']
        );

        $this->json([
            'success' => $success,
            'message' => $success
                ? 'Marcación registrada correctamente.'
                : 'No se pudo registrar la marcación.',
            'data'    => $success ? array_merge($payload, [
                'empresa'      => $empresaNombre,
                'hora_entrada' => $horaEntrada,
                'hora_salida'  => $horaSalida
            ]) : null
        ]);
    }

    private function renderLayout(string $view, array $data): void
    {
        $data['view'] = $view;
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function form(): void
    {
        // Forzar zona horaria local de Asunción
        date_default_timezone_set('America/Asuncion');

        $riderId = $_SESSION['rider_id'] ?? null;
        $fecha   = date('Y-m-d');
        $horaActual = date('H:i:s');

        if (!$riderId) {
            $this->redirect('/mercedes/login');
        }

        $grupos = $this->grupoModel->getByRider($riderId);

        foreach ($grupos as &$g) {
            $turnos = $this->turnoModel->getByGrupo($g['id_grupo']);
            // Filtrar solo turnos activos según hora actual
            $g['turnos'] = array_filter($turnos, function($t) use ($horaActual) {
                return $horaActual >= $t['hora_inicio'] && $horaActual <= $t['hora_fin'];
            });
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