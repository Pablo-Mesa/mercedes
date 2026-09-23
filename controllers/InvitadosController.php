<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../app/services/ProduccionService.php';

class InvitadosController extends BaseController {
    private ProduccionService $produccionService;

    public function __construct() {
        $this->startSession();

        // Validar login
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }

        // Validar rol permitido
        $this->requireRole(['invitado']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');

        // Crear el modelo y pasarlo al servicio
        $produccionModel = new ProduccionModel();
        $this->produccionService = new ProduccionService($produccionModel);
    }

    public function index(): void {
        date_default_timezone_set('America/Asuncion');
        $fechaSeleccionada = $_GET['fecha'] ?? ($_POST['fecha'] ?? date('Y-m-d'));

        $produccionesResumen = $this->produccionService->getProduccionPorFecha($fechaSeleccionada);

        $data = [
            'view' => 'invitados/index',
            'produccionesResumen' => $produccionesResumen,
            'fechaSeleccionada' => $fechaSeleccionada
        ];

        $this->renderLayout($data['view'], $data);
    }

    private function renderLayout(string $view, array $data): void {
        $data['view'] = $view;
        extract($data);
        require __DIR__ . '/../views/layouts/main.php';
    }
}
