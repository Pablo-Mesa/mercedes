<?php
/**
 * controllers/UserController.php
 * CRUD completo y reversible de usuarios, exclusivo para el rol 'admin'.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/GruposModel.php';
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
        $this->requireRole(['admin']);
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

        // 👇 nuevo: cargar grupos
        $grupoModel = new GruposModel();
        $grupos = $grupoModel->getAll();

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

        if ($data['role_id'] == $operacionesRoleId && empty($data['grupo_id'])) {
            $_SESSION['error'] = 'Debe asignar un grupo al usuario de operaciones.';
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

        // 👇 nuevo: cargar grupos
        $grupoModel = new GruposModel();
        $grupos = $grupoModel->getAll();

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

        if ($data['role_id'] == $operacionesRoleId && empty($data['grupo_id'])) {
            $_SESSION['error'] = 'Debe asignar un grupo al usuario de operaciones.';
            $this->redirect('/mercedes/usuarios/crear');
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
            'grupo_id' => !empty($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : null,            
            'cargo_id' => !empty($_POST['cargo_id']) ? (int)$_POST['cargo_id'] : null,
            'documento_tipo' => $_POST['documento_tipo'] ?? 'DNI',
            'documento_numero' => trim($_POST['documento_numero'] ?? ''),
        ];
    }
}