<?php
/**
 * controllers/TarifasController.php
 * Controlador para gestionar encabezados y detalles de tarifas.
 */
require_once __DIR__ . '/../models/EncabezadoTarifaModel.php';
require_once __DIR__ . '/../models/DetalleTarifaModel.php';
require_once __DIR__ . '/BaseController.php';

class TarifasController extends BaseController
{
    private EncabezadoTarifaModel $encabezadoModel;
    private DetalleTarifaModel $detalleModel;

    public function __construct(){
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        $this->encabezadoModel = new EncabezadoTarifaModel();
        $this->detalleModel = new DetalleTarifaModel();
        $this->requireRole(['admin']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');
    }

    // Listar encabezados
    public function index()
    {
        $encabezados = $this->encabezadoModel->getAll();     
        $view = 'tarifas/index';
        require __DIR__ . '/../views/layouts/main.php';        
    }

    // Mostrar formulario de creación/edición
    public function create($id = null)
    {
        $encabezado = null;
        if ($id) {
            $encabezado = $this->encabezadoModel->findById($id);
        }
        $formAction = '/mercedes/tarifas/crear';
        $view = 'tarifas/form';    
        require __DIR__ . '/../views/layouts/main.php';
    }

    // Guardar nuevo encabezado
    public function store()
    {
        $data = [
            'titulo' => $_POST['titulo'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        $this->encabezadoModel->create($data);
        $_SESSION['success'] = 'Tarifa creada correctamente.';
        $this->redirect('/mercedes/tarifas');
    }

    // Actualizar encabezado existente
    public function update($id = null)
    {
        $id = (int)($id ?? ($_GET['id'] ?? $_POST['id'] ?? 0));
        if ($id > 0) {
            $data = [
                'titulo' => $_POST['titulo'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            $this->encabezadoModel->update($id, $data);
        }
        $_SESSION['success'] = 'Tarifa actualizada correctamente.';
        $this->redirect('/mercedes/tarifas');
    }

        // Toggle habilitar/deshabilitar encabezado
    public function toggleEncabezado(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->encabezadoModel->setActiveEncabezado($id);
            $_SESSION['success'] = 'Estado de tarifa actualizado correctamente.';
        }
        $this->redirect('/mercedes/tarifas');
    }

    // Eliminar encabezado
    public function delete(): void
    {
        $id_encabezado = (int)($_GET['id'] ?? 0);
        if ($id_encabezado > 0) {
            $this->encabezadoModel->delete($id_encabezado);
            $_SESSION['success'] = 'Tarifa eliminada correctamente.';
        }
        $this->redirect('/mercedes/tarifas');
    }

    // Mostrar detalles asociados a un encabezado
    public function detalles(): void
    {
        $id_encabezado = (int)($_GET['id'] ?? 0);
        $encabezado = $this->encabezadoModel->findById($id_encabezado);
        $detalles = $this->detalleModel->getByEncabezado($id_encabezado);     
        
        $formAction = '/mercedes/tarifas/detalles&id=' . $id_encabezado;
        $view = 'tarifas/detalles';   // este es el archivo físico dentro de views/tarifas
        
        require __DIR__ . '/../views/layouts/main.php';
    }

}