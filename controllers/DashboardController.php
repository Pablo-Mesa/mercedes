<?php
/**
 * controllers/DashboardController.php
 * Controla el panel principal tras iniciar sesión.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->startSession();
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

        $view = 'dashboard';
        require __DIR__ . '/../views/layouts/main.php';
    }
}
