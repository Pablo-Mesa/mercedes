<?php
require_once __DIR__ . '/../models/EncabezadoTarifaModel.php';
require_once __DIR__ . '/../models/DetalleTarifaModel.php';
require_once __DIR__ . '/BaseController.php';

class DetallesController extends BaseController
{
    private DetalleTarifaModel $detalleModel;
    private EncabezadoTarifaModel $encabezadoModel;

    public function __construct()
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        $this->detalleModel = new DetalleTarifaModel();
        $this->encabezadoModel = new EncabezadoTarifaModel();
        $this->requireRole(['admin']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');
    }

    // Mostrar detalles de un encabezado
    public function index(int $id_encabezado): void
    {
        $encabezado = $this->encabezadoModel->findById($id_encabezado);
        $detalles = $this->detalleModel->getByEncabezado($id_encabezado);

        $view = 'tarifas/detalles';
        require __DIR__ . '/../views/layouts/main.php';
    }

    // Formulario para crear un nuevo detalle
    public function create(int $id_encabezado): void
    {
        $encabezado = $this->encabezadoModel->findById($id_encabezado);

        $formAction = '/mercedes/tarifas/nuevo_detalle?id_encabezado=' . $id_encabezado;
        $view = 'tarifas/nuevo_detalle';
        require __DIR__ . '/../views/layouts/main.php';
    }

    // Guardar nuevo detalle
    public function store(int $id_encabezado): void
    {
        $data = [
            'id_encabezado_tarifa' => $id_encabezado,
            'desde' => $_POST['desde'],
            'hasta' => $_POST['hasta'],
            'costo' => $_POST['costo'],
            'disponible' => isset($_POST['disponible']) ? 1 : 0
        ];
        $this->detalleModel->create($data);
        $_SESSION['success'] = 'Detalle creado correctamente.';
        $this->redirect('/mercedes/tarifas/detalles?id=' . $id_encabezado);
    }

    // Actualizar detalle existente
    public function update(int $id, int $id_encabezado): void
    {
        $data = [
            'desde' => $_POST['desde'],
            'hasta' => $_POST['hasta'],
            'costo' => $_POST['costo'],
            'disponible' => isset($_POST['disponible']) ? 1 : 0
        ];
        $this->detalleModel->update($id, $data);
        $_SESSION['success'] = 'Detalle actualizado correctamente.';
        $this->redirect('/mercedes/tarifas/detalles?id=' . $id_encabezado);
    }

    // Toggle habilitar/deshabilitar detalle
    public function toggleDetalle(int $id, int $id_encabezado): void
    {
        $this->detalleModel->toggle($id);
        $this->redirect('/mercedes/tarifas/detalles?id=' . $id_encabezado);
    }
    
    // Eliminar detalle
    public function delete(int $id, int $id_encabezado): void
    {
        $this->detalleModel->delete($id);
        $this->redirect('/mercedes/tarifas/detalles?id=' . $id_encabezado);
    }
}
