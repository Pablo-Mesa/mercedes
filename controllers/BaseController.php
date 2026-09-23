<?php
require_once __DIR__ . '/../models/SettingsModel.php';

/**
 * controllers/BaseController.php
 * Base compartida para todos los controladores.
 * Mantiene la API actual del proyecto y centraliza sesión, permisos y redirecciones.
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
            $this->forbidden('No tienes permisos para acceder a esta sección.');
        }
    }

    protected function requireRole(array $allowedRoles): void
    {
        $this->requireAuth();

        $currentRole = $_SESSION['user_role'] ?? null;

        if (!in_array($currentRole, $allowedRoles, true)) {
            $this->forbidden('No tienes permisos para acceder a esta sección.');
        }
    }

    protected function requireToolEnabled(string $settingKey, string $message = 'Esta herramienta está desactivada.'): void
    {
        $settings = new SettingsModel();

        if (!$settings->getBoolean($settingKey)) {
            $this->forbidden($message);
        }
    }

    protected function requireAnyToolEnabled(): void
    {
        $settings = new SettingsModel();

        if (!$settings->getBoolean('tool_cuaderno_enabled')
            && !$settings->getBoolean('tool_asistencias_enabled')) {
            $this->forbidden('No hay ninguna herramienta activa.');
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

    protected function forbidden(string $message = 'Acceso prohibido'): void
    {
        http_response_code(403);

        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Acceso Prohibido</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #0f172a;
                    color: #ffffff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                }
                .box {
                    background: #111827;
                    border: 1px solid #374151;
                    border-radius: 12px;
                    padding: 40px 60px;
                    text-align: center;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
                }
                h1 {
                    margin: 0 0 12px;
                    color: #f87171;
                    font-size: 52px;
                }
                p {
                    margin: 0 0 18px;
                    color: #d1d5db;
                }
                a {
                    color: #93c5fd;
                    text-decoration: none;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="box">
                <h1>403</h1>
                <p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>
                <a href="/mercedes/login">Volver al login</a>
            </div>
        </body>
        </html>';

        exit;
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}