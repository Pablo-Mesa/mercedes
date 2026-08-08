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
        $this->ensureIsAdmin();
    }

    /**
     * Corta la petición con 403 si el rol de la sesión no es 'admin'.
     */
    private function ensureIsAdmin(): void
    {
        if (($_SESSION['user_role'] ?? null) !== 'admin') {
            http_response_code(403);
            echo '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Acceso Prohibido</title>
                <link rel="icon" type="image/svg+xml" href="/mercedes/public/images/favicon.svg">
                <style>
                    body { font-family: "Segoe UI", Arial, sans-serif; background:#0f1524; color:#fff; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
                    .box { text-align:center; padding: 40px 60px; background:#171f36; border-radius:16px; border:1px solid #2a3358; }
                    .box h1 { font-size: 56px; margin: 0 0 10px; color:#ff5c7a; }
                    .box p { color:#a9b1d1; margin-bottom: 24px; }
                    .box a { color:#7f9cff; text-decoration:none; font-weight:600; }
                </style>
            </head>
            <body>
                <div class="box">
                    <h1>403</h1>
                    <p>Acceso Prohibido. No cuentas con permisos para gestionar usuarios.</p>
                    <a href="/mercedes/dashboard">Volver al Dashboard</a>
                </div>
            </body>
            </html>';
            exit;
        }
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