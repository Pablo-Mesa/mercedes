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

    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect($this->getDefaultPath($_SESSION['user_role'] ?? ''));
        }

        require __DIR__ . '/../views/login.php';
    }

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

        if ((int) $user['is_active'] !== 1) {
            $_SESSION['error'] = 'Tu cuenta se encuentra desactivada. Contacta al administrador.';
            $this->redirect('/mercedes/login');
        }

        session_regenerate_id(true);

        // Sesión básica
        $_SESSION['user_id']        = (int) $user['id'];
        $_SESSION['user_name']      = $user['name'];
        $_SESSION['user_email']     = $user['email'];
        $_SESSION['user_role']      = $user['role_slug'];
        $_SESSION['user_role_name'] = $user['role_name'];
        $_SESSION['grupo_id']       = $user['grupo_id'] ?? null;

        // Rider: guardar ficha
        if ($_SESSION['user_role'] === 'rider') {
            $rider = $this->userModel->findRiderByUsuario($_SESSION['user_id']);
            if ($rider) {
                $_SESSION['rider_id']   = (int) $rider['id'];
                $_SESSION['rider_name'] = $rider['name'];
            }
        }

        unset($_SESSION['error']);

        // Fecha: GET > POST > actual
        $fecha = $_GET['fecha'] ?? ($_POST['fecha'] ?? date('Y-m-d'));

        $this->redirect($this->getDefaultPath($_SESSION['user_role'], $fecha));
    }

    private function getDefaultPath(string $role, ?string $fecha = null): string
    {
        $settings = new SettingsModel();
        $cuadernoActivo = $settings->getBoolean('tool_cuaderno_enabled');
        $asistenciasActivas = $settings->getBoolean('tool_asistencias_enabled');
        $fechaQuery = urlencode($fecha ?? date('Y-m-d'));

        if ($cuadernoActivo) {
            return match ($role) {
                'admin' => '/mercedes/dashboard',
                'operaciones' => '/mercedes/produccion?vista=tarjetas&fecha=' . $fechaQuery,
                'rider' => '/mercedes/rider_produccion?fecha=' . $fechaQuery,
                'invitado' => '/mercedes/invitados?fecha=' . $fechaQuery,
                default => '/mercedes/login',
            };
        }

        if ($asistenciasActivas) {
            return match ($role) {
                'admin', 'operaciones' => '/mercedes/asistencias',
                'rider' => '/mercedes/rider_asistencia?fecha=' . $fechaQuery,
                'invitado' => '/mercedes/invitados?fecha=' . $fechaQuery,
                default => '/mercedes/login',
            };
        }

        return match ($role) {
            'admin' => '/mercedes/herramientas',
            'operaciones' => '/mercedes/produccion?fecha=' . $fechaQuery,
            'rider' => '/mercedes/rider_produccion?fecha=' . $fechaQuery,
            'invitado' => '/mercedes/invitados?fecha=' . $fechaQuery,
            default => '/mercedes/login',
        };
    }

    public function logout(): void
    {
        $this->startSession();

        $_SESSION = [];
        session_destroy();

        $this->redirect('/mercedes/login');
    }
}