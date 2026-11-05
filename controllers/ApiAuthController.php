<?php

class ApiAuthController
{
    public function login(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            http_response_code(405);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        // IP de la conexión recibida por Apache.
        // No confiar en cabeceras X-Forwarded-For del cliente.
        $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!filter_var($ipCliente, FILTER_VALIDATE_IP)) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'No se pudo identificar la conexión.'
            ]);
            return;
        }

        $datos = json_decode(file_get_contents('php://input'), true);

        if (!is_array($datos)) {
            http_response_code(400);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Debes enviar un JSON válido.'
            ]);
            return;
        }

        $email = $datos['email'] ?? '';
        $password = $datos['password'] ?? '';

        if (!is_string($email) || !is_string($password)) {
            http_response_code(400);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'El correo y la contraseña deben ser textos.'
            ]);
            return;
        }

        $email = trim($email);

        // Comprobar el límite de intentos antes de buscar al usuario.
        $controlIntentos = new ApiLoginIntento();

        if ($controlIntentos->estaBloqueado($email, $ipCliente)) {
            header('Retry-After: 900');
            http_response_code(429);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Demasiados intentos. Inténtalo nuevamente más tarde.'
            ]);

            return;
        }

        // Buscar usuario solo si no está bloqueado.
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        // Verificar credenciales sin revelar si el correo existe
        if (!$user || !password_verify($password, $user['password'])) {
            // Registrar el intento fallido.
            $controlIntentos->registrarFallo($email, $ipCliente);
            http_response_code(401);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Credenciales incorrectas.'
            ]);
            return;
        }

        // Solo Riders pueden acceder a esta API
        if ($user['role_slug'] !== 'rider') {
            http_response_code(403);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Acceso disponible únicamente para Riders.'
            ]);
            return;
        }

        // Verificar cuenta activa
        if ((int) $user['is_active'] !== 1) {
            http_response_code(403);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Tu cuenta está desactivada.'
            ]);
            return;
        }

        // Verificar activación del Rider
        if (empty($user['activated_at'])) {
            http_response_code(403);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Debes activar tu cuenta antes de ingresar.'
            ]);
            return;
        }

        try {
            // Crear una sesión móvil para el Rider autenticado.
            $apiToken = new ApiToken();

            $token = $apiToken->crear((int) $user['id']);

            // Evitar que el navegador o intermediarios
            // almacenen la respuesta con el token.
            header('Cache-Control: no-store');
            header('Pragma: no-cache');

            http_response_code(200);

            echo json_encode([
                'ok' => true,
                'mensaje' => 'Inicio de sesión correcto.',
                'token' => $token,
                'token_type' => 'Bearer',
                'usuario' => [
                    'id' => (int) $user['id'],
                    'nombre' => $user['name'],
                    'rol' => 'rider'
                ]
            ]);

        } catch (Throwable $e) {

            error_log(
                '[Solver API Login] ' . $e->getMessage()
            );

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ocurrió un error interno. Inténtalo nuevamente.'
            ]);
        }
        
    }

    public function me(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Allow: GET');
            http_response_code(405);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        // Obtener el token Bearer.
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if (!is_string($authorization) ||
            !preg_match('/\ABearer ([a-f0-9]{64})\z/iD', $authorization, $coincidencias)) {


            http_response_code(401);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Token de autenticación no válido.'
            ]);
            return;
        }

        $token = $coincidencias[1];

        try {
            $apiToken = new ApiToken();
            $usuario = $apiToken->verificar($token);

            if ($usuario === false) {
                http_response_code(401);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Sesión inválida o expirada.'
                ]);
                return;
            }

            http_response_code(200);

            echo json_encode([
                'ok' => true,
                'usuario' => [
                    'id' => (int) $usuario['id'],
                    'nombre' => $usuario['name'],
                    'rol' => 'rider'
                ]
            ]);

        } catch (Throwable $e) {
            error_log('[Solver API Me] ' . $e->getMessage());

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ocurrió un error interno.'
            ]);
        }
    }
    
    public function logout(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            http_response_code(405);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        // Obtener el token Bearer.
        $authorization = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (
            !is_string($authorization) ||
            !preg_match('/\ABearer ([a-f0-9]{64})\z/iD', $authorization, $coincidencias)
        ) {
            http_response_code(401);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Token de autenticación no válido.'
            ]);
            return;
        }

        $token = $coincidencias[1];

        try {
            $apiToken = new ApiToken();

            // Revocar únicamente el token recibido.
            $revocado = $apiToken->revocar($token);

            if (!$revocado) {
                http_response_code(401);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Sesión inválida o expirada.'
                ]);
                return;
            }

            http_response_code(200);

            echo json_encode([
                'ok' => true,
                'mensaje' => 'Sesión cerrada correctamente.'
            ]);

        } catch (Throwable $e) {
            error_log('[Solver API Logout] ' . $e->getMessage());

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ocurrió un error interno.'
            ]);
        }
    }

}