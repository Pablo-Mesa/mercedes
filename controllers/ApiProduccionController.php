<?php

class ApiProduccionController
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
            $empresa = $_GET['empresa'] ?? null;
            if($empresa === ''){ 
                $empresa = null;
            }

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

            // Verificar que la herramienta Cuaderno esté habilitada.
            $settings = new SettingsModel();

            if (!$settings->getBoolean('tool_cuaderno_enabled')) {
                http_response_code(403);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'El Cuaderno está desactivado.'
                ]);
                return;
            }

            // El Rider se identifica exclusivamente mediante su token.
            $riderId = (int) $usuario['id'];

            $produccionModel = new ProduccionModel();

            $producciones = $produccionModel->getProduccionPorRider(
                $riderId,
                $empresa,
                $fecha
            );

            $resumenGrupos = $produccionModel->getResumenPorGrupo(
                $riderId,
                $fecha,
                $empresa
            );

            http_response_code(200);

            echo json_encode([
                'ok' => true,
                'fecha' => $fecha,
                'producciones' => $producciones,
                'resumen_grupos' => $resumenGrupos
            ], JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (Throwable $e) {
            error_log('[Solver API Producciones] ' . $e->getMessage());

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ocurrió un error interno.'
            ]);
        }
    }
}