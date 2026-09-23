<?php
/**
 * controllers/DashboardController.php
 * Controla el panel principal tras iniciar sesión.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/BaseController.php';

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->startSession();
        $this->requireRole(['admin']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');
    }

    /**
     * Valida sesión activa y renderiza el dashboard dentro del Layout Maestro.
     */
    public function index(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }

        $userModel = new User();
        $totalUsuarios = count($userModel->getAll());
        $usuarioActual = $userModel->findById((int)$_SESSION['user_id']);

        $produccionModel = new ProduccionModel();
        $dashboardModel = new DashboardModel();

        // Crear objeto fecha en la zona horaria de Asunción
        $fechaParaguay = new DateTime('now', new DateTimeZone('America/Asuncion'));

        // Extraer año y mes desde esa fecha
        $anio = $fechaParaguay->format('Y');
        $mesSeleccionado = $_GET['mes'] ?? $fechaParaguay->format('n'); // mes actual por defecto

        $grupoSeleccionado = $_GET['grupo_id'] ?? null;
        if ($grupoSeleccionado === '') {
            $grupoSeleccionado = null;
        }

        $produccionesPorDia = $produccionModel->getProduccionPorDia((int)$mesSeleccionado, (int)$anio, $grupoSeleccionado);
        $resumenMensual     = $produccionModel->getProduccionResumenMensual((int)$mesSeleccionado, (int)$anio, $grupoSeleccionado);
        $totales            = $produccionModel->getTotalesPorMes((int)$mesSeleccionado, (int)$anio, $grupoSeleccionado);
        $puntoDeControl     = $dashboardModel->getNombrePuntoDeControl();

        $gruposModel = new GruposModel();
        $grupos = $gruposModel->getAll();

        $view = 'dashboard';
        require __DIR__ . '/../views/layouts/main.php';
    }


}
