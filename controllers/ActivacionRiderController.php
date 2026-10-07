<?php
/**
 * controllers/ActivacionRiderController.php
 * Controlador para activar cuenta de Rider mediante token.
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class ActivacionRiderController extends BaseController {

    private User $userModel;

    public function __construct() {
        $this->startSession();
        $this->userModel = new User();
        // 🚫 No requerimos rol ni sesión previa, porque es acceso público vía enlace
    }

    /**
     * GET: Mostrar formulario de activación
     */
    public function index(): void {
        $token = $_GET['token'] ?? '';

        // Validar formato del token
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            $_SESSION['error'] = 'Enlace de activación inválido.';
            $this->redirect('/mercedes/login');
        }

        $tokenHash = hash('sha256', $token);
        $usuario = $this->userModel->getByActivationTokenHash($tokenHash);

        if (!$usuario) {
            $_SESSION['error'] = 'Token inválido o usuario no encontrado.';
            $this->redirect('/mercedes/login');
        }

        if (!empty($usuario['activated_at'])) {
            $_SESSION['error'] = 'La cuenta ya fue activada.';
            $this->redirect('/mercedes/login');
        }

        if (
            empty($usuario['activation_expires_at']) ||
            strtotime($usuario['activation_expires_at']) < time()
        ) {
            $_SESSION['error'] = 'El enlace de activación ha expirado.';
            $this->redirect('/mercedes/login');
        }

        // Mostrar vista de activación
        $view = 'auth/activar-rider';
        $tokenOriginal = $token; // se pasa a la vista
        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * POST: Guardar contraseña y activar cuenta
     */
    public function store(): void {
        $token = $_POST['token'] ?? '';

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            $_SESSION['error'] = 'Petición inválida.';
            $this->redirect('/mercedes/login');
        }

        $tokenHash = hash('sha256', $token);
        $usuario = $this->userModel->getByActivationTokenHash($tokenHash);

        if (!$usuario) {
            $_SESSION['error'] = 'Token inválido.';
            $this->redirect('/mercedes/login');
        }

        if (!empty($usuario['activated_at'])) {
            $_SESSION['error'] = 'La cuenta ya fue activada.';
            $this->redirect('/mercedes/login');
        }

        if (
            empty($usuario['activation_expires_at']) ||
            strtotime($usuario['activation_expires_at']) < time()
        ) {
            $_SESSION['error'] = 'El enlace de activación ha expirado.';
            $this->redirect('/mercedes/login');
        }

        // Validar contraseñas
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $errors = [];

        if (strlen($password) < 12) {
            $errors[] = 'La contraseña debe tener al menos 12 caracteres.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/mercedes/activar-rider?token=' . $token);
        }

        // Guardar contraseña segura y activar
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $this->userModel->activateUser($usuario['id'], [
            'password' => $passwordHash,
            'activated_at' => date('Y-m-d H:i:s'),
            'activation_token_hash' => null,
            'activation_expires_at' => null,
        ]);

        $_SESSION['success'] = 'Cuenta activada correctamente. Ya puedes iniciar sesión.';
        $this->redirect('/mercedes/login');
    }
}
