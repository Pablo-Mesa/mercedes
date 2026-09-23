<?php
/**
 * controllers/HerramientasController.php
 * Vista de selección de herramientas (Cuaderno / Control de Asistencias).
 * Exclusivo para rol 'admin'.
 */
require_once __DIR__ . '/BaseController.php';

class HerramientasController extends BaseController {
    private const CUADERNO_KEY = 'tool_cuaderno_enabled';
    private const ASISTENCIAS_KEY = 'tool_asistencias_enabled';
    private SettingsModel $settings;

    public function __construct() {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        // Solo admin puede acceder
        $this->requireRole(['admin']);
        $this->settings = new SettingsModel();
    }

    /**
     * Muestra la vista principal de Herramientas.
     */
    public function index(): void {
        $herramientasSeleccionadas = [
            'cuaderno' => $this->settings->getBoolean(self::CUADERNO_KEY),
            'asistencias' => $this->settings->getBoolean(self::ASISTENCIAS_KEY),
        ];
        $_SESSION['herramientas'] = $herramientasSeleccionadas;

        // Variable para que main.php sepa qué vista cargar
        $view = 'herramientas/index';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Guarda la selección de herramientas.
     */
    public function save(): void {
        $seleccion = $_POST['herramientas'] ?? [];
        $cuadernoActivo = isset($seleccion['cuaderno']) && $seleccion['cuaderno'] === '1';
        $asistenciasActivas = isset($seleccion['asistencias']) && $seleccion['asistencias'] === '1';

        $this->settings->set(self::CUADERNO_KEY, $cuadernoActivo ? '1' : '0', 'Activa o desactiva globalmente la herramienta Cuaderno.');
        $this->settings->set(self::ASISTENCIAS_KEY, $asistenciasActivas ? '1' : '0', 'Activa o desactiva globalmente el Control de Asistencias.');

        $_SESSION['herramientas'] = [
            'cuaderno' => $cuadernoActivo,
            'asistencias' => $asistenciasActivas,
        ];

        // Si es AJAX, devolvemos JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Selección de herramientas actualizada.']);
            exit;
        }

        // Flujo clásico (fallback)
        $_SESSION['success'] = 'Selección de herramientas actualizada.';
        $this->redirect('/mercedes/herramientas');
    }

}
