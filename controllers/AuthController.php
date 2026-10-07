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

        // Usuario no encontrado
        if (!$user) {
            $_SESSION['error'] = 'Credenciales incorrectas. Verifica tu correo y contraseña.';
            $this->redirect('/mercedes/login');
        }

        // Validar contraseña
        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Credenciales incorrectas. Verifica tu correo y contraseña.';
            $this->redirect('/mercedes/login');
        }

        // Validar estado activo
        if ((int)$user['is_active'] !== 1) {
            $_SESSION['error'] = 'Tu cuenta se encuentra desactivada. Contacta al administrador.';
            $this->redirect('/mercedes/login');
        }

        // Validar activación SOLO para Riders
        if ($user['role_slug'] === 'rider' && empty($user['activated_at'])) {
            $_SESSION['error'] = 'Tu cuenta aún no ha sido activada. Revisa tu correo para el enlace de activación.';
            $this->redirect('/mercedes/login');
        }

        // Regenerar sesión
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

    public function showActivateRider(): void
    {
        $token = $_GET['token'] ?? '';
        if ($token === '') {
            $_SESSION['error'] = 'Token de activación inválido.';
            $this->redirect('/mercedes/login');
        }

        // Buscar usuario por token hash
        $tokenHash = hash('sha256', $token);
        $stmt = Database::getConnection()->prepare("
            SELECT * FROM usuarios 
            WHERE activation_token_hash = :hash 
            AND activation_expires_at > NOW()
            AND role_id = 3
            LIMIT 1
        ");
        $stmt->execute([':hash' => $tokenHash]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $_SESSION['error'] = 'El enlace de activación no es válido o ha expirado.';
            $this->redirect('/mercedes/login');
        }

        $tokenOriginal = $token;
        $view = 'auth/activar-rider';
        require __DIR__ . '/../views/layouts/public.php';
    }

    public function storeActivateRider(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($token === '' || $password === '' || $passwordConfirm === '') {
            $_SESSION['error'] = 'Debes completar todos los campos.';
            $this->redirect('/mercedes/login');
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            $this->redirect('/mercedes/activar-rider?token=' . urlencode($token));
        }

        if (strlen($password) < 12) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 12 caracteres.';
            $this->redirect('/mercedes/activar-rider?token=' . urlencode($token));
        }

        $tokenHash = hash('sha256', $token);
        $stmt = Database::getConnection()->prepare("
            SELECT id FROM usuarios 
            WHERE activation_token_hash = :hash 
            AND activation_expires_at > NOW()
            AND role_id = 3
            LIMIT 1
        ");
        $stmt->execute([':hash' => $tokenHash]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $_SESSION['error'] = 'El enlace de activación no es válido o ha expirado.';
            $this->redirect('/mercedes/login');
        }

        // Actualizar usuario con nueva contraseña y marcar activación
        $this->userModel->activateUser((int)$usuario['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'activated_at' => date('Y-m-d H:i:s'),
            'activation_token_hash' => null,
            'activation_expires_at' => null,
        ]);

        $_SESSION['success'] = 'Cuenta activada correctamente. Ya puedes iniciar sesión.';
        $this->redirect('/mercedes/login');
    }

}