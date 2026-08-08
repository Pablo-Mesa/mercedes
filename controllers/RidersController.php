<?php
/**
 * controllers/RidersController.php
 * CRUD completo y reversible de riders, exclusivo para el rol 'admin'.
 */
require_once __DIR__ . '/../models/RiderModel.php';
require_once __DIR__ . '/../helpers/UploadHelper.php';
require_once __DIR__ . '/BaseController.php';

class RidersController extends BaseController {
    
    private RiderModel $riderModel;

    public function __construct() {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        $this->riderModel = new RiderModel();
        $this->ensureIsAdmin();
    }

    /** 
     * Corta la petición con 403 si el rol de la sesión no es 'admin'. 
     */
    private function ensureIsAdmin(): void {
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
                    <p>Acceso Prohibido. No cuentas con permisos para gestionar riders.</p>
                    <a href="/mercedes/dashboard">Volver al Dashboard</a>
                </div>
            </body>
            </html>';
            exit;
        }
    }

    /** 
     * Listado de riders ($view = riders/index). 
     */
    public function index(): void
     {
        $riders = $this->riderModel->getAll();
        $view = 'riders/index';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /** 
     * Formulario de creación ($view = riders/form). 
     */
    public function create() {
        $rider = null;
        $formAction = '/mercedes/riders/crear';
        $view = 'riders/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $rider = $this->riderModel->getById($id);
        $formAction = '/mercedes/riders/editar?id=' . $id;
        $view = 'riders/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function store(): void {
        $data = $this->procesarDatos();
        $this->riderModel->insert($data);
        header('Location: /mercedes/riders');
        exit;
    }

    public function update(): void {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id || !$this->riderModel->getById($id)) {
            $_SESSION['error'] = 'El rider solicitado no existe.';
            header('Location: /mercedes/riders');
            exit;
        }
        $data = $this->procesarDatos($_POST);
        $this->riderModel->update($id, $data);
        $_SESSION['success'] = 'Rider actualizado correctamente.';
        header('Location: /mercedes/riders');
        exit;
    }

    /** 
     * Alterna el estado activo/inactivo de un rider (cambio reversible). 
     */
    public function toggleStatus(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->riderModel->toggleStatus($id);
            $_SESSION['success'] = 'Estado de rider actualizado correctamente.';
        }
        header('Location: /mercedes/riders');
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $this->riderModel->delete($id);
        header('Location: /mercedes/riders');
        exit;
    }

    /** 
     * Estandariza los datos recibidos del formulario. 
     */      
    private function procesarDatos(): array {
        $fotoPerfil = null;

        if (!empty($_POST['reset_foto'])) {
            $fotoPerfil = 'default.png';
        } elseif (!empty($_FILES['foto_perfil']['name'])) {
            $fotoPerfil = UploadHelper::upload($_FILES['foto_perfil'], 'riders');
        }

        return [
            'name' => trim($_POST['name']),
            'contacto' => trim($_POST['contacto']),
            'is_active' => (int)($_POST['is_active'] ?? 0),
            'foto_perfil' => $fotoPerfil ?? 'default.png',
        ];
    }

}
