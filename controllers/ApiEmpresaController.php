<?php

class ApiEmpresaController
{
    public function index(): void
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

        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

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

        try {
            $apiToken = new ApiToken();
            $usuario = $apiToken->verificar($coincidencias[1]);

            if ($usuario === false) {
                http_response_code(401);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Sesión inválida o expirada.'
                ]);
                return;
            }

            $zonaHoraria = new DateTimeZone('America/Asuncion');
            $fechaActual = (new DateTimeImmutable('now', $zonaHoraria))
                ->format('Y-m-d');

            $fecha = $_GET['fecha'] ?? $fechaActual;

            if (
                !is_string($fecha) ||
                !preg_match('/\A\d{4}-\d{2}-\d{2}\z/D', $fecha)
            ) {
                http_response_code(400);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Formato de fecha inválido. Utiliza YYYY-MM-DD.'
                ]);
                return;
            }

            $fechaObjeto = DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $fecha,
                $zonaHoraria
            );

            if (
                $fechaObjeto === false ||
                $fechaObjeto->format('Y-m-d') !== $fecha
            ) {
                http_response_code(400);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Fecha inválida.'
                ]);
                return;
            }

            require_once __DIR__ . '/../models/GruposModel.php';
            $empresas = (new GruposModel())->getAll();

            http_response_code(200);

            echo json_encode([
                'ok' => true,
                'empresas' => $empresas,
            ], JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (Throwable $e) {
            error_log('[Solver API Empresas] ' . $e->getMessage());

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ocurrió un error interno.'
            ]);
        }
    }
}
