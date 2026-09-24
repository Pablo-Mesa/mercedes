<?php
/**
 * Control de asistencias para administración y operaciones.
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AsistenciaModel.php';

class AdminAsistenciasController extends BaseController
{
    private AsistenciaModel $asistenciaModel;

    public function __construct()
    {
        $this->startSession();
        $this->requireRole(['admin', 'operaciones']);
        $this->requireToolEnabled(
            'tool_asistencias_enabled',
            'El Control de Asistencias está desactivado.'
        );

        // Inicializar modelo
        $this->asistenciaModel = new AsistenciaModel();
    }

    /**
     * GET → Mostrar asistencias del día (admin/operaciones)
     */
    public function index(): void
    {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        // Consultar todas las asistencias de la fecha
        $asistencias = $this->asistenciaModel->getByDate($fecha);

        // Renderizar vista admin
        $this->renderLayout('asistencias/index', [
            'fecha' => $fecha,
            'asistencias' => $asistencias
        ]);
    }

    private function renderLayout(string $view, array $data): void
    {
        $data['view'] = $view;
        require __DIR__ . '/../views/layouts/main.php';
    }

}
