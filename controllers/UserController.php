<?php
/**
 * controllers/UserController.php
 * CRUD completo y reversible de usuarios, exclusivo para el rol 'admin'.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class UserController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }

        $this->userModel = new User();
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

    /**
     * Listado de usuarios ($view = usuarios/index).
     */
    public function index(): void
    {
        /**/
        $usuarios = $this->userModel->getAll();
        
        $view = 'usuarios/index';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Formulario de creación ($view = usuarios/form).
     */
    public function create(): void
    {
        $usuario = null;
        $roles = $this->userModel->getAllRoles();
        $cargos = $this->userModel->getAllCargos();
        $formAction = '/mercedes/usuarios/crear';
        $view = 'usuarios/form';    
        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Procesa el alta de un nuevo usuario.
     */
    public function store(): void
    {
        $data = $this->collectFormData();

        if (empty($data['password'])) {
            $_SESSION['error'] = 'La contraseña es obligatoria al crear un usuario.';
            $this->redirect('/mercedes/usuarios/crear');
        }

        $this->userModel->create($data);
        $_SESSION['success'] = 'Usuario creado correctamente.';
        $this->redirect('/mercedes/usuarios');
    }

    /**
     * Formulario de edición, cargando los datos del usuario por ID.
     */
    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $usuario = $this->userModel->findById($id);

        if (!$usuario) {
            $_SESSION['error'] = 'El usuario solicitado no existe.';
            header('Location: /mercedes/usuarios');
            exit;
        }

        $roles = $this->userModel->getAllRoles();
        $cargos = $this->userModel->getAllCargos();
        $formAction = '/mercedes/usuarios/editar?id=' . $id;
        $view = 'usuarios/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Procesa la actualización de un usuario existente.
     */
    public function update(): void
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

        if (!$id || !$this->userModel->findById($id)) {
            $_SESSION['error'] = 'El usuario solicitado no existe.';
            header('Location: /mercedes/usuarios');
            exit;
        }

        $data = $this->collectFormData();
        $this->userModel->update($id, $data);

        $_SESSION['success'] = 'Usuario actualizado correctamente.';
        $this->redirect('/mercedes/usuarios');
    }

    /**
     * Alterna el estado activo/inactivo de un usuario (cambio reversible).
     */
    public function toggleStatus(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id) {
            $this->userModel->toggleStatus($id);
            $_SESSION['success'] = 'Estado del usuario actualizado correctamente.';
        }

        header('Location: /mercedes/usuarios');
        exit;
    }

    /**
     * Normaliza los datos recibidos por POST desde el formulario de usuario.
     */
    private function collectFormData(): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'role_id' => (int)($_POST['role_id'] ?? 0),
            'cargo_id' => !empty($_POST['cargo_id']) ? (int)$_POST['cargo_id'] : null,
            'documento_tipo' => $_POST['documento_tipo'] ?? 'DNI',
            'documento_numero' => trim($_POST['documento_numero'] ?? ''),
        ];
    }
}
