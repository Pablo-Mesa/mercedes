<?php
/**
 * controllers/BaseController.php
 * Base compartida para controladores del proyecto.
 */
abstract class BaseController
{
    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function requireAuth(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? null) !== 'admin') {
            http_response_code(403);
            echo '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Acceso Prohibido</title>
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
                    <p>Acceso Prohibido. No cuentas con permisos para gestionar esta sección.</p>
                    <a href="/mercedes/dashboard">Volver al Dashboard</a>
                </div>
            </body>
            </html>';
            exit;
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function setFlash(string $type, string $message): void
    {
        $this->startSession();
        $_SESSION[$type] = $message;
    }
}
