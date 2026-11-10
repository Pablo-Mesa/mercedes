
<?php

class ApiAsistenciasController
{
    private const MENSAJE_MODULO_DESACTIVADO = 'El Control de Asistencias está desactivado.';

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

            $settings = new SettingsModel();

            if (!$settings->getBoolean('tool_asistencias_enabled')) {
                http_response_code(403);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => self::MENSAJE_MODULO_DESACTIVADO
                ]);
                return;
            }

            // Identidad obtenida exclusivamente del token.
            $riderId = (int) $usuario['id'];

            $asistenciaModel = new AsistenciaModel();

            $asistencias = $asistenciaModel->getByRiderAndDate($riderId, $fecha);
            //solo cargamos los datos necesarios
            $asistencias = array_map(
                static function (array $asistencia): array {
                    return [
                        'id'       => $asistencia['id'],
                        'fecha'    => $asistencia['fecha'],
                        'empresa'  => $asistencia['empresa'],
                        'tipo'     => $asistencia['tipo'],
                        'hora'     => $asistencia['hora'], 
                        'grupo_icono'     => $asistencia['grupo_icono']
                    ];
                },
                $asistencias
            );

            http_response_code(200);
            
            echo json_encode([
                'ok' => true,
                'fecha' => $fecha,
                'asistencias' => $asistencias
            ], JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (Throwable $e) {
            error_log('[Solver API Asistencias] ' . $e->getMessage());

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ocurrió un error interno.'
            ]);
        }
    }

    /**
     * Opciones de marcación para Solver Mobile.
     *
     * GET /mercedes/api/asistencias/opciones
     */
    public function opciones(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            header('Allow: GET');
            http_response_code(405);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        // 1. Autenticación mediante token Bearer.
        $authorization = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (
            !is_string($authorization)
            || !preg_match('/\ABearer ([a-f0-9]{64})\z/iD', $authorization, $coincidencias)
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

            if (!$usuario) {
                http_response_code(401);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Sesión inválida o expirada.'
                ]);
                return;
            }

            // 2. Comprobar que el módulo esté habilitado.
            $settings = new SettingsModel();

            if (!$settings->getBoolean('tool_asistencias_enabled')) {
                http_response_code(403);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => self::MENSAJE_MODULO_DESACTIVADO
                ]);
                return;
            }

            // La identidad siempre proviene del token.
            $riderId = (int) $usuario['id'];

            $zonaHoraria = new DateTimeZone('America/Asuncion');
            $ahora = new DateTimeImmutable('now', $zonaHoraria);

            $fechaHoy = $ahora->format('Y-m-d');
            $horaActual = $ahora->format('H:i:s');

            // 3. Preparar modelos y repositorio.
            $gruposModel = new GruposModel();
            $turnoModel = new TurnoModel();
            $repository = new AsistenciasRepository();
            $asistenciaModel = new AsistenciaModel();

            $grupos = $gruposModel->getByRider($riderId);

            // 4. Buscar todas las entradas pendientes.
            // Pueden pertenecer a jornadas anteriores.
            $pendientes = $repository->getEntradasPendientes($riderId);

            $pendientesPorTurno = [];

            foreach ($pendientes as $pendiente) {
                $clave = (int) $pendiente['grupo_id']
                    . ':'
                    . (int) $pendiente['turno_id'];

                $pendientesPorTurno[$clave][] = [
                    'fecha' => $pendiente['fecha'],
                    'hora_entrada' => $pendiente['hora_entrada']
                ];
            }

            // 5. Preparar las entradas existentes para las fechas
            // que podrían corresponder a la jornada actual.
            $fechaAyer = $ahora
                ->modify('-1 day')
                ->format('Y-m-d');

            $fechasAConsultar = [
                $fechaHoy,
                $fechaAyer
            ];

            $entradasRegistradas = [];

            foreach ($fechasAConsultar as $fechaConsulta) {
                $marcaciones = $asistenciaModel->getByRiderAndDate(
                    $riderId,
                    $fechaConsulta
                );

                foreach ($marcaciones as $marcacion) {
                    if ($marcacion['tipo'] !== 'entrada') {
                        continue;
                    }

                    $clave = (int) $marcacion['grupo_id']
                        . ':'
                        . (int) $marcacion['turno_id']
                        . ':'
                        . $fechaConsulta;

                    $entradasRegistradas[$clave] = true;
                }
            }

            // 6. Construir opciones disponibles.
            $empresas = [];

            foreach ($grupos as $grupo) {

                $grupoId = (int) $grupo['id_grupo'];

                $grupoActual = $gruposModel->getById($grupoId);

                if (!$grupoActual) {
                    continue;
                }

                $empresaActiva = (
                    (int) $grupoActual['activo'] === 1
                );

                $turnos = $turnoModel->getByGrupo($grupoId);

                $turnosDisponibles = [];

                foreach ($turnos as $turno) {

                    $turnoId = (int) $turno['id'];

                    $claveTurno = $grupoId . ':' . $turnoId;

                    /*
                    * 7. OFRECER SALIDAS PENDIENTES.
                    *
                    * No dependen del horario.
                    * No requieren GPS.
                    * Pueden pertenecer a jornadas anteriores.
                    * También se permiten en empresas inactivas.
                    */
                    if (!empty($pendientesPorTurno[$claveTurno])) {

                        foreach ($pendientesPorTurno[$claveTurno] as $pendiente) {

                            $turnosDisponibles[] = [
                                'turno_id' => $turnoId,
                                'turno' => $turno['turno'],
                                'hora_inicio' => $turno['hora_inicio'],
                                'hora_fin' => $turno['hora_fin'],
                                'fecha_jornada' => $pendiente['fecha'],
                                'hora_entrada' => $pendiente['hora_entrada'],
                                'accion' => 'marcar_salida'
                            ];
                        }
                    }

                    /*
                    * 8. OFRECER NUEVAS LLEGADAS.
                    *
                    * Solamente en empresas activas.
                    */
                    if (!$empresaActiva) {
                        continue;
                    }

                    $horaInicio = $turno['hora_inicio'];
                    $horaFin = $turno['hora_fin'];

                    $fechaJornada = $fechaHoy;
                    $dentroHorario = false;

                    if ($horaFin === null) {

                        // Turno sin hora de finalización.
                        // Misma regla provisional que marcar().
                        $dentroHorario = (
                            $horaActual >= $horaInicio
                        );

                    } elseif ($horaFin > $horaInicio) {

                        // Turno que comienza y termina el mismo día.
                        $dentroHorario = (
                            $horaActual >= $horaInicio
                            && $horaActual <= $horaFin
                        );

                    } elseif ($horaFin < $horaInicio) {

                        // Turno nocturno que cruza medianoche.
                        $dentroHorario = (
                            $horaActual >= $horaInicio
                            || $horaActual <= $horaFin
                        );

                        // Después de medianoche corresponde
                        // a la jornada del día anterior.
                        if ($horaActual <= $horaFin) {
                            $fechaJornada = $fechaAyer;
                        }

                    } else {

                        // Inicio y fin iguales: configuración ambigua.
                        $dentroHorario = false;
                    }

                    if (!$dentroHorario) {
                        continue;
                    }

                    // 9. Impedir una segunda llegada en la misma jornada.
                    $claveEntrada = $grupoId
                        . ':'
                        . $turnoId
                        . ':'
                        . $fechaJornada;

                    if (isset($entradasRegistradas[$claveEntrada])) {
                        continue;
                    }

                    // 10. Agregar opción de llegada.
                    $turnosDisponibles[] = [
                        'turno_id' => $turnoId,
                        'turno' => $turno['turno'],
                        'hora_inicio' => $turno['hora_inicio'],
                        'hora_fin' => $turno['hora_fin'],
                        'fecha_jornada' => $fechaJornada,
                        'hora_entrada' => null,
                        'accion' => 'marcar_llegada'
                    ];
                }

                if (empty($turnosDisponibles)) {
                    continue;
                }

                $empresas[] = [
                    'grupo_id' => $grupoId,
                    'nombre' => $grupo['nombre'],
                    'grupo_icono' => $grupo['grupo_icono'],
                    'turnos' => $turnosDisponibles
                ];
            }

            // 11. Respuesta para Solver Mobile.
            http_response_code(200);

            echo json_encode([
                'ok' => true,
                'fecha' => $fechaHoy,
                'empresas' => $empresas
            ], JSON_INVALID_UTF8_SUBSTITUTE);

        } catch (Throwable $e) {

            error_log(
                '[Solver API Asistencias Opciones] ' . $e->getMessage()
            );

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ocurrió un error interno.'
            ]);
        }
    }

    /**
     * Registrar marcaciones desde Solver Mobile.
     *
     * POST /mercedes/api/asistencias/marcar
     *
     * Etapa inicial: validar autenticación y solicitud.
     * Todavía no registra asistencias.
     */
    public function marcar(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        // 1. Autenticación mediante token Bearer.
        $authorization = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (
            !is_string($authorization)
            || !preg_match('/\ABearer ([a-f0-9]{64})\z/iD', $authorization, $coincidencias)
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

            if (!$usuario) {
                http_response_code(401);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Sesión inválida o expirada.'
                ]);
                return;
            }

            // 2. Comprobar que el módulo esté habilitado.
            $settings = new SettingsModel();

            if (!$settings->getBoolean('tool_asistencias_enabled')) {
                http_response_code(403);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => self::MENSAJE_MODULO_DESACTIVADO
                ]);
                return;
            }

            // 3. Leer y validar JSON.
            $contenido = file_get_contents('php://input');
            $datos = json_decode($contenido, true);

            if (!is_array($datos) || json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'El cuerpo de la solicitud debe ser JSON válido.'
                ]);
                return;
            }

            $accion = $datos['accion'] ?? null;

            if (!in_array($accion, ['marcar_llegada', 'marcar_salida'], true)) {
                http_response_code(422);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Acción de marcación no válida.'
                ]);
                return;
            }

            // 4. Validar empresa y turno.
            $grupoId = filter_var(
                $datos['grupo_id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $turnoId = filter_var(
                $datos['turno_id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if (
                $grupoId === false || $grupoId === null
                || $turnoId === false || $turnoId === null
            ) {
                http_response_code(422);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Empresa o turno no válido.'
                ]);
                return;
            }

            // La identidad del Rider procede exclusivamente del token.
            $riderId = (int) $usuario['id'];

            // 5. Verificar que la empresa esté asignada al Rider.
            $gruposModel = new GruposModel();
            $gruposAsignados = $gruposModel->getByRider($riderId);

            $empresaAsignada = false;

            foreach ($gruposAsignados as $grupo) {
                if ((int) $grupo['id_grupo'] === (int) $grupoId) {
                    $empresaAsignada = true;
                    break;
                }
            }

            if (!$empresaAsignada) {
                http_response_code(403);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'No tienes permiso para marcar asistencia en esta empresa.'
                ]);
                return;
            }

            // 6. Comprobar que el turno pertenece a la empresa.
            $turnoModel = new TurnoModel();
            $turno = $turnoModel->getById((int) $turnoId);

            if (!$turno || (int) $turno['id_grupo'] !== (int) $grupoId) {
                http_response_code(422);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'El turno no corresponde a la empresa seleccionada.'
                ]);
                return;
            }

            // Recursos comunes.
            $repository = new AsistenciasRepository();

            $zonaHoraria = new DateTimeZone('America/Asuncion');
            $ahora = new DateTimeImmutable('now', $zonaHoraria);

            /*
            * 7. MARCAR LLEGADA
            *
            * Requiere:
            * - Empresa activa.
            * - Horario válido.
            * - Jornada correcta.
            * - Ausencia de llegada duplicada.
            * - Punto de control configurado.
            * - GPS dentro del radio permitido.
            */
            if ($accion === 'marcar_llegada') {

                // 7.1. Comprobar que la empresa esté activa.
                $empresa = $gruposModel->getById((int) $grupoId);

                if (!$empresa || (int) $empresa['activo'] !== 1) {
                    http_response_code(403);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'La empresa no está habilitada para marcar asistencia.'
                    ]);
                    return;
                }

                // 7.2. Determinar horario y fecha de jornada.
                $horaActual = $ahora->format('H:i:s');

                $horaInicio = $turno['hora_inicio'];
                $horaFin = $turno['hora_fin'];

                $fechaJornada = $ahora->format('Y-m-d');
                $dentroHorario = false;

                if ($horaFin === null) {

                    /*
                    * Turno sin hora de finalización.
                    * Regla provisional: permitir llegada desde la
                    * hora de inicio hasta el final del día.
                    */
                    $dentroHorario = ($horaActual >= $horaInicio);

                } elseif ($horaFin > $horaInicio) {

                    // Turno que comienza y termina el mismo día.
                    $dentroHorario = (
                        $horaActual >= $horaInicio
                        && $horaActual <= $horaFin
                    );

                } elseif ($horaFin < $horaInicio) {

                    // Turno nocturno que cruza medianoche.
                    $dentroHorario = (
                        $horaActual >= $horaInicio
                        || $horaActual <= $horaFin
                    );

                    // Después de medianoche corresponde a la jornada anterior.
                    if ($horaActual <= $horaFin) {
                        $fechaJornada = $ahora
                            ->modify('-1 day')
                            ->format('Y-m-d');
                    }

                } else {

                    // Inicio y fin iguales: configuración ambigua.
                    $dentroHorario = false;
                }

                if (!$dentroHorario) {
                    http_response_code(422);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'El turno no está dentro de su horario de marcación.'
                    ]);
                    return;
                }

                // 7.3. Evitar llegadas duplicadas en la misma jornada.
                $entradaDuplicada = $repository->existeMarcacionDuplicada(
                    $riderId,
                    (int) $grupoId,
                    (int) $turnoId,
                    'entrada',
                    $fechaJornada
                );

                if ($entradaDuplicada) {
                    http_response_code(409);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'Ya registraste una llegada para esta empresa y turno en esta jornada.'
                    ]);
                    return;
                }

                // 7.4. Obtener el punto de control de la empresa.
                $puntoControlId = (int) ($empresa['id_punto_control'] ?? 0);

                if ($puntoControlId <= 0) {
                    http_response_code(422);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'La empresa no tiene un punto de control configurado.'
                    ]);
                    return;
                }

                $puntoControl = $repository->getPuntoControlById($puntoControlId);

                if (
                    !$puntoControl
                    || !isset(
                        $puntoControl['latitud'],
                        $puntoControl['longitud'],
                        $puntoControl['radio_metros']
                    )
                    || !is_numeric($puntoControl['latitud'])
                    || !is_numeric($puntoControl['longitud'])
                    || !is_numeric($puntoControl['radio_metros'])
                    || (float) $puntoControl['latitud'] < -90
                    || (float) $puntoControl['latitud'] > 90
                    || (float) $puntoControl['longitud'] < -180
                    || (float) $puntoControl['longitud'] > 180
                    || (float) $puntoControl['radio_metros'] <= 0
                ) {
                    http_response_code(422);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'El punto de control no tiene una configuración válida.'
                    ]);
                    return;
                }

                // 7.5. Validar coordenadas GPS recibidas.
                $latitud = filter_var(
                    $datos['lat'] ?? null,
                    FILTER_VALIDATE_FLOAT
                );

                $longitud = filter_var(
                    $datos['lon'] ?? null,
                    FILTER_VALIDATE_FLOAT
                );

                if (
                    $latitud === false || $latitud === null
                    || $longitud === false || $longitud === null
                    || !is_finite((float) $latitud)
                    || !is_finite((float) $longitud)
                    || $latitud < -90
                    || $latitud > 90
                    || $longitud < -180
                    || $longitud > 180
                ) {
                    http_response_code(422);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'No se pudo obtener una ubicación GPS válida.'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                // 7.6. Calcular distancia mediante Haversine.
                $latitudControl = (float) $puntoControl['latitud'];
                $longitudControl = (float) $puntoControl['longitud'];
                $radioPermitido = (float) $puntoControl['radio_metros'];

                $radioTierra = 6371000;

                $diferenciaLatitud = deg2rad(
                    $latitudControl - $latitud
                );

                $diferenciaLongitud = deg2rad(
                    $longitudControl - $longitud
                );

                $a = sin($diferenciaLatitud / 2) ** 2
                    + cos(deg2rad($latitud))
                    * cos(deg2rad($latitudControl))
                    * sin($diferenciaLongitud / 2) ** 2;

                $a = max(0.0, min(1.0, $a));

                $distanciaMetros = 2
                    * $radioTierra
                    * asin(sqrt($a));

                if ($distanciaMetros > $radioPermitido) {
                    http_response_code(422);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'Estás fuera del radio permitido del punto de control, acércate más'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                // Todas las validaciones de llegada fueron superadas.
                // La escritura en MySQL sigue deshabilitada.
            }

            /*
            * 8. MARCAR SALIDA
            *
            * No requiere GPS.
            * No requiere estar dentro del horario.
            * Debe existir una entrada pendiente.
            */
            if ($accion === 'marcar_salida') {

                $fechaJornada = $datos['fecha_jornada'] ?? null;

                $fechaValida = is_string($fechaJornada)
                    && preg_match(
                        '/^\d{4}-\d{2}-\d{2}$/',
                        $fechaJornada
                    );

                if ($fechaValida) {
                    $fecha = DateTimeImmutable::createFromFormat(
                        '!Y-m-d',
                        $fechaJornada,
                        $zonaHoraria
                    );

                    $fechaValida = (
                        $fecha !== false
                        && $fecha->format('Y-m-d') === $fechaJornada
                        && $fechaJornada <= $ahora->format('Y-m-d')
                    );
                }

                if (!$fechaValida) {
                    http_response_code(422);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'Fecha de jornada no válida.'
                    ]);
                    return;
                }

                // Buscar entradas abiertas del Rider autenticado.
                $pendientes = $repository->getEntradasPendientes($riderId);

                $entradaPendiente = null;

                foreach ($pendientes as $pendiente) {
                    if (
                        (int) $pendiente['grupo_id'] === (int) $grupoId
                        && (int) $pendiente['turno_id'] === (int) $turnoId
                        && $pendiente['fecha'] === $fechaJornada
                    ) {
                        $entradaPendiente = $pendiente;
                        break;
                    }
                }

                if ($entradaPendiente === null) {
                    http_response_code(409);

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'No existe una entrada pendiente para esta jornada.'
                    ]);
                    return;
                }

                // La fecha de salida será la fecha original de jornada.
                // La hora de salida será la hora actual del servidor.
                // No se requiere GPS para salir.
            }

            /*
            * 9. REGISTRAR ASISTENCIA EN MYSQL.
            *
            * Todas las validaciones anteriores fueron superadas.
            * La fecha corresponde a la jornada.
            * La hora corresponde al momento real de marcación.
            */

            $tipo = ($accion === 'marcar_llegada')
                ? 'entrada'
                : 'salida';

            // La fechaJornada fue determinada y validada
            // en el bloque correspondiente a cada acción.
            $fechaRegistro = $fechaJornada;

            // La hora siempre proviene del servidor.
            $horaRegistro = $ahora->format('H:i:s');

            // Solo las llegadas requieren GPS.
            $latRegistro = ($accion === 'marcar_llegada')
                ? (float) $latitud
                : null;

            $lonRegistro = ($accion === 'marcar_llegada')
                ? (float) $longitud
                : null;

            // El punto de control se registra únicamente
            // cuando se ha validado una llegada.
            $puntoControlRegistro = ($accion === 'marcar_llegada')
                ? (int) $puntoControlId
                : null;

            $payload = [
                'rider_id' => $riderId,
                'grupo_id' => (int) $grupoId,
                'turno_id' => (int) $turnoId,
                'fecha' => $fechaRegistro,
                'hora' => $horaRegistro,
                'tipo' => $tipo,
                'dispositivo' => 'Solver Mobile',
                'ip_registro' => $_SERVER['REMOTE_ADDR'] ?? null,
                'lat' => $latRegistro,
                'lon' => $lonRegistro,
                'punto_control_id' => $puntoControlRegistro,
                'observaciones' => null
            ];

            // Insertar utilizando el repositorio existente.
            $registrado = $repository->insert($payload);

            if (!$registrado) {
                http_response_code(409);

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Esta marcación ya fue registrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Registro realizado correctamente.
            http_response_code(201);

            echo json_encode([
                'ok' => true,
                'mensaje' => ($tipo === 'entrada')
                    ? 'Llegada registrada correctamente.'
                    : 'Salida registrada correctamente.',
                'asistencia' => [
                    'rider_id' => $riderId,
                    'grupo_id' => (int) $grupoId,
                    'turno_id' => (int) $turnoId,
                    'fecha_jornada' => $fechaRegistro,
                    'hora' => $horaRegistro,
                    'tipo' => $tipo
                ]
            ], JSON_UNESCAPED_UNICODE);

            return;

        } catch (Throwable $e) {

            error_log('[Solver API Marcar] ' . $e->getMessage());

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Ocurrió un error interno.'
            ]);
        }
    }

}
