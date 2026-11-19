<?php
/**
 * Control de asistencias para administración y operaciones.
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AsistenciaModel.php';
require_once __DIR__ . '/../app/repositories/AsistenciasRepository.php';
require_once __DIR__ . '/../app/services/AsistenciaStreamService.php';

class AdminAsistenciasController extends BaseController
{
    private AsistenciaModel $asistenciaModel;
    private AsistenciaStreamService $asistenciaStreamService;

    public function __construct()
    {
        $this->startSession();
        $this->requireRole(['admin', 'operaciones']);
        $this->requireToolEnabled(
            'tool_asistencias_enabled',
            'El Control de Asistencias está desactivado.'
        );

        // Inicializar modelo y servicio SSE
        $this->asistenciaModel = new AsistenciaModel();
        $repository = new AsistenciasRepository();
        $this->asistenciaStreamService = new AsistenciaStreamService($repository);
    }

    /**
     * GET → Mostrar asistencias del día (admin/operaciones)
     */
    public function index(): void
    {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        // Consultar todas las asistencias de la fecha
        $asistencias = $this->asistenciaModel->getByDate($fecha);

        // Calcular el ID más alto para que SSE escuche solo los nuevos registros
        $ultimoIdInicial = !empty($asistencias) 
            ? max(array_column($asistencias, 'id')) 
            : 0;

        // Renderizar vista admin
        $this->renderLayout('asistencias/index', [
            'fecha' => $fecha,
            'asistencias' => $asistencias,
            'ultimoIdInicial' => $ultimoIdInicial
        ]);
    }

    /**
     * GET → Stream SSE para nuevas llegadas en tiempo real
     */
    public function stream(): void
    {
        // Liberar el archivo de sesión para no congelar el resto de la web
        session_write_close();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        set_time_limit(0);

        $ultimoId = isset($_SERVER['HTTP_LAST_EVENT_ID']) 
            ? (int)$_SERVER['HTTP_LAST_EVENT_ID'] 
            : (int)($_GET['ultimo_id'] ?? 0);

        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $this->asistenciaStreamService->escucharNuevasLlegadas($ultimoId, $fecha);

        exit;
    }

    private function renderLayout(string $view, array $data): void
    {
        $data['view'] = $view;
        require __DIR__ . '/../views/layouts/main.php';
    }
}
