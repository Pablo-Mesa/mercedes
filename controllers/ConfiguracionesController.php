<?php
/**
 * controllers/ConfiguracionesController.php
 * Gestión de configuraciones del sistema (Punto de Control).
 */
require_once __DIR__ . '/../models/PuntoControlModel.php';
require_once __DIR__ . '/BaseController.php';

class ConfiguracionesController extends BaseController {

    private PuntoControlModel $puntoModel;

    public function __construct() {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        $this->puntoModel = new PuntoControlModel();
        $this->requireRole(['admin']);
    }

    /**
     * Mostrar la vista de Punto de Control ($view = configuraciones/puntodecontrol).
     */
    public function puntodecontrol(): void {
        $punto = $this->puntoModel->get(); // devuelve el único registro
        $puntoConfigurado = PuntoControlModel::isConfigured($punto);
        $view = 'configuraciones/puntodecontrol';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Guardar cambios del Punto de Control (POST).
     */
    public function guardarPuntoControl(): void {
        $data = [
            'titulo'    => trim($_POST['titulo'] ?? ''),
            'barrio'    => trim($_POST['barrio'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'contacto'  => trim($_POST['contacto'] ?? ''),
            'latitud'   => trim((string)($_POST['latitud'] ?? '')),
            'longitud'  => trim((string)($_POST['longitud'] ?? '')),
            'radio'     => trim((string)($_POST['radio'] ?? '')),
        ];

        if (!PuntoControlModel::isConfigured($data)) {
            $this->json([
                'success' => false,
                'message' => 'Completa todos los datos con una ubicación y un radio válidos antes de guardar.'
            ], 422);
        }

        $success = $this->puntoModel->update($data);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Actualizado correctamente' : 'Error al actualizar'
        ]);
        exit;
    }

}
