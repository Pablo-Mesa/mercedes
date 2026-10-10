
<?php
/**
 * controllers/AboutdevController.php
 * Controla la página informativa About Dev.
 */

require_once __DIR__ . '/BaseController.php';

class AboutdevController extends BaseController
{
    public function __construct()
    {
        // Permite el acceso a cualquier usuario autenticado.
        $this->requireAuth();
    }

    /**
     * Muestra About Dev dentro del Layout Maestro.
     */
    public function index(): void
    {
        $view = 'aboutdev/index';

        require __DIR__ . '/../views/layouts/main.php';
    }
}
