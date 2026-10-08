<?php
/**
 * controllers/RidersController.php
 * CRUD completo y reversible de riders, exclusivo para roles 'admin' y 'operaciones'.
 */
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/UploadHelper.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/GruposModel.php';
require_once __DIR__ . '/../app/services/MailerService.php';
class RidersController extends BaseController {
    
    private User $userModel;

    public function __construct() {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        $this->userModel = new User();
        $this->requireRole(['admin', 'operaciones']);
        $this->requireAnyToolEnabled();
    }

    /** Listado de Riders */
    public function index(): void {
        $riders = $this->userModel->getAllRiders();
        $view = 'riders/index';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /** Formulario de creación */
    public function create(): void {
        $rider = null;
        $formAction = '/mercedes/riders/crear';
        $grupos = (new GruposModel())->getAll();
        $gruposAsignados = [];
        $view = 'riders/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /** Formulario de edición */
    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $rider = $this->userModel->getRiderById($id);
        if (!$rider) {
            $_SESSION['error'] = 'El rider solicitado no existe.';
            header('Location: /mercedes/riders');
            exit;
        }
        $formAction = '/mercedes/riders/editar?id=' . $id;
        $grupos = (new GruposModel())->getAll();
        $gruposAsignados = $this->userModel->getGroupsByRider($id);
        $view = 'riders/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /** Guardar nuevo Rider */
    public function store(): void {
        try {
            $data = $this->procesarDatos();
            $rider = $this->userModel->insertRider($data);

            if (!empty($_POST['grupos'])) {
                $this->userModel->assignRiderToGroups($rider['id'], $_POST['grupos']);
            }

            $_SESSION['success'] = 'Rider creado correctamente.';
            $this->redirect('/mercedes/riders');
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/mercedes/riders/crear');
        }
    }

    /** Actualizar Rider existente */
    public function update(): void {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id || !$this->userModel->getRiderById($id)) {
            $_SESSION['error'] = 'El rider solicitado no existe.';
            header('Location: /mercedes/riders');
            exit;
        }
        $data = $this->procesarDatos();
        $this->userModel->updateRider($id, $data);
        if (!empty($_POST['grupos'])) {
            $this->userModel->assignRiderToGroups($id, $_POST['grupos']);
        }
        $_SESSION['success'] = 'Rider actualizado correctamente.';
        header('Location: /mercedes/riders');
        exit;
    }

    /** Activar/Desactivar Rider */
    public function toggleStatus(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->userModel->toggleRiderStatus($id);
            $_SESSION['success'] = 'Estado de rider actualizado correctamente.';
        }
        header('Location: /mercedes/riders');
        exit;
    }

    /** Eliminar Rider */
    public function delete(): void {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $this->userModel->deleteRider($id);
        $_SESSION['success'] = 'Rider eliminado correctamente.';
        header('Location: /mercedes/riders');
        exit;
    }

    /** Procesar datos del formulario */
    private function procesarDatos(): array {
        $fotoPerfil = null;

        if (!empty($_POST['reset_foto'])) {
            $fotoPerfil = 'default.png';
        } elseif (!empty($_FILES['foto_perfil']['name'])) {
            $fotoPerfil = UploadHelper::upload($_FILES['foto_perfil'], 'riders');
        }

        return [
            'name'        => trim($_POST['name']),
            'email'       => trim($_POST['email']),
            'password'    => $_POST['password'] ?? bin2hex(random_bytes(8)), // genera temporal si no se pasa
            'phone'       => trim($_POST['phone'] ?? ''),   // ✅ ahora incluido
            'is_active'   => (int)($_POST['is_active'] ?? 0),
            'grupo_id'    => $_POST['grupo_id'] ?? null,
            'foto_perfil' => $fotoPerfil ?? 'default.png',
        ];
    }

    public function sendActivation(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $rider = $this->userModel->getRiderById($id);

        if (!$rider) {
            $_SESSION['error'] = 'Rider no encontrado.';
            $this->redirect('/mercedes/riders');
            return;
        }

        if (empty($rider['phone'])) {
            $_SESSION['error'] = 'El rider no tiene teléfono registrado.';
            $this->redirect('/mercedes/riders');
            return;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->userModel->setActivationToken($id, $tokenHash, $expiresAt);

        $activationLink = 'http://192.168.100.108/mercedes/activar-rider?token=' . urlencode($token);

        // Define el mensaje con saltos de línea normales sin concatenar con puntos innecesarios
        $mensaje = "Hola {$rider['name']}.\n\n"
                . "Tu cuenta de Rider fue creada correctamente.\n\n"
                . "Creado {$rider['created_at']}.\n\n"
                . "Usuario {$rider['email']}.\n\n"
                . "Activa tu acceso desde el siguiente enlace:\n\n"
                . $activationLink . "\n\n"
                . "Este enlace expira en 24 horas.";

        $telefono = preg_replace('/\D/', '', $rider['phone']);
        if (str_starts_with($telefono, '0')) {
            $telefono = '595' . substr($telefono, 1);
        }

        // Codifica TODO el parámetro text al final para evitar rupturas de URL
        $whatsappUrl = "https://wa.me/" . $telefono . "?text=" . urlencode($mensaje);

        header('Location: ' . $whatsappUrl);
        exit;
    }

}
