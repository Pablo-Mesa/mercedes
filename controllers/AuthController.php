<?php
/**
 * controllers/AuthController.php
 * Controla el inicio y cierre de sesión del sistema.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        $this->startSession();
        $this->userModel = new User();
    }

    /**
     * Muestra el formulario de login (GET /mercedes/login).
     * Si ya hay una sesión activa, redirige directo al dashboard.
     */
    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/dashboard');
        }

        require __DIR__ . '/../views/login.php';
    }

    /**
     * Procesa las credenciales enviadas por POST.
     */
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Debes completar el correo y la contraseña.';
            $this->redirect('/mercedes/login');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Credenciales incorrectas. Verifica tu correo y contraseña.';
            $this->redirect('/mercedes/login');
        }

        if ((int)$user['is_active'] !== 1) {
            $_SESSION['error'] = 'Tu cuenta se encuentra desactivada. Contacta al administrador.';
            $this->redirect('/mercedes/login');
        }

        // Sesión completa
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role_slug'];
        $_SESSION['user_role_name'] = $user['role_name'];

        unset($_SESSION['error']);

        $this->redirect('/mercedes/dashboard');
    }

    /**
     * Destruye la sesión activa y regresa al login.
     */
    public function logout(): void
    {
        $this->startSession();

        $_SESSION = [];
        session_destroy();

        $this->redirect('/mercedes/login');
    }
}
