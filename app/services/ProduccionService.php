<?php

class ProduccionService
{
    private ProduccionModel $produccionModel;

    public function __construct(ProduccionModel $produccionModel)
    {
        $this->produccionModel = $produccionModel;
    }

    public function validarRegistro(array $payload): array
    {
        $errors = [];
        $payload = $this->normalizarPayload($payload);

        if (!isset($payload['id_usuario']) || (int) $payload['id_usuario'] <= 0) {
            $errors[] = 'Usuario no válido.';
        }

        if (!isset($payload['id_rider']) || (int) $payload['id_rider'] <= 0) {
            $errors[] = 'Debe seleccionar un rider.';
        }

        if (!isset($payload['id_detalle_tarifa']) || (int) $payload['id_detalle_tarifa'] <= 0) {
            $errors[] = 'Debe seleccionar una tarifa.';
        }

        $idAccion = isset($payload['id_accion']) ? (int) $payload['id_accion'] : 0;

        if ($idAccion <= 0) {
            $errors[] = 'Debe seleccionar una acción.';
        }

        if (
            !isset($payload['total_factura']) ||
            $payload['total_factura'] === '' ||
            !is_numeric($payload['total_factura']) ||
            (float) $payload['total_factura'] < 0
        ) {
            $errors[] = 'Total factura es obligatorio y debe ser un número válido.';
        }

        if ($idAccion > 0) {
            $accionData = $this->produccionModel->getAccionById($idAccion);

            if (!$accionData) {
                $errors[] = 'Acción de rider inválida.';
                return $errors;
            }

            $esEfectivo = $accionData['codigo'] === 'efectivo';

            if ($esEfectivo) {
                if (
                    !isset($payload['vuelto']) ||
                    $payload['vuelto'] === '' ||
                    !is_numeric($payload['vuelto']) ||
                    (float) $payload['vuelto'] < 0
                ) {
                    $errors[] = 'Vuelto es obligatorio para cobro en efectivo.';
                }
            }
        }

        return $errors;
    }

    public function registrar(array $payload): bool
    {
        if (empty($payload)) {
            return false;
        }

        return $this->produccionModel->insert($payload);
    }

    public function poblarProduccion(string $fecha, int $usuarioId, ?int $grupoId = null): array
    {
        $fechaBase = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        $fechaErrores = DateTimeImmutable::getLastErrors();

        if (
            $fechaBase === false ||
            ($fechaErrores !== false && ($fechaErrores['warning_count'] > 0 || $fechaErrores['error_count'] > 0)) ||
            $fechaBase->format('Y-m-d') !== $fecha
        ) {
            throw new DomainException('Selecciona una fecha válida.');
        }

        if ($usuarioId <= 0) {
            throw new DomainException('El usuario de la sesión no es válido.');
        }

        if ($grupoId !== null && $grupoId <= 0) {
            throw new DomainException('El grupo de la sesión no es válido.');
        }

        $ridersPorId = [];
        foreach ($this->produccionModel->getRidersActivosConGrupos($grupoId) as $fila) {
            $riderId = (int) ($fila['id_rider'] ?? 0);
            $idGrupo = (int) ($fila['grupo_id'] ?? 0);

            if ($riderId <= 0) {
                throw new DomainException('Se encontró un rider no válido.');
            }

            $ridersPorId[$riderId]['id_rider'] = $riderId;
            $ridersPorId[$riderId]['grupos'] = $ridersPorId[$riderId]['grupos'] ?? [];
            if ($idGrupo > 0) {
                $ridersPorId[$riderId]['grupos'][] = $idGrupo;
            }
        }

        if ($ridersPorId === []) {
            throw new DomainException('No hay riders activos asignados a grupos disponibles.');
        }

        foreach ($ridersPorId as $rider) {
            if ($rider['grupos'] === []) {
                throw new DomainException(
                    'El rider ' . $rider['id_rider'] . ' no tiene un grupo activo asignado.'
                );
            }
        }

        $acciones = $this->produccionModel->getAccionesRider();
        if ($acciones === []) {
            throw new DomainException('No hay acciones de rider activas para crear registros.');
        }

        $catalogosPorGrupo = [];
        foreach ($ridersPorId as $rider) {
            foreach (array_unique($rider['grupos']) as $idGrupo) {
                if (!isset($catalogosPorGrupo[$idGrupo])) {
                    $tarifas = $this->produccionModel->getTarifasByGrupo($idGrupo);
                    $turnos = $this->produccionModel->getTurnosByGrupo($idGrupo);

                    if ($tarifas === [] || $turnos === []) {
                        throw new DomainException(
                            'El grupo ' . $idGrupo . ' no tiene tarifas activas o turnos asociados.'
                        );
                    }

                    $catalogosPorGrupo[$idGrupo] = [
                        'tarifas' => $tarifas,
                        'turnos' => $turnos,
                    ];
                }
            }
        }

        $registrosCreados = 0;
        $ridersProcesados = 0;
        $this->produccionModel->startTransaction();

        try {
            foreach ($ridersPorId as $rider) {
                $grupos = array_values(array_unique($rider['grupos']));
                $cantidad = random_int(4, 12);

                for ($i = 0; $i < $cantidad; $i++) {
                    $idGrupo = $grupoId ?? $grupos[random_int(0, count($grupos) - 1)];
                    $catalogo = $catalogosPorGrupo[$idGrupo];
                    $tarifa = $catalogo['tarifas'][random_int(0, count($catalogo['tarifas']) - 1)];
                    $turno = $catalogo['turnos'][random_int(0, count($catalogo['turnos']) - 1)];
                    $accion = $acciones[random_int(0, count($acciones) - 1)];
                    $totalFactura = (float) $tarifa['costo'];
                    $esEfectivo = ($accion['codigo'] ?? null) === 'efectivo';
                    $vuelto = $esEfectivo
                        ? round((ceil($totalFactura / 100000) * 100000) - $totalFactura, 2)
                        : null;
                    $fechaCreacion = $fechaBase
                        ->modify('+' . random_int(0, 86399) . ' seconds')
                        ->format('Y-m-d H:i:s');

                    $payload = [
                        'id_usuario' => $usuarioId,
                        'id_rider' => $rider['id_rider'],
                        'id_detalle_tarifa' => (int) $tarifa['id'],
                        'id_turno' => (int) $turno['id'],
                        'id_grupo' => $idGrupo,
                        'id_accion' => (int) $accion['id_accion'],
                        'total_factura' => $totalFactura,
                        'vuelto' => $vuelto,
                        'fecha_creacion' => $fechaCreacion,
                    ];

                    $errors = $this->validarRegistro($payload);
                    if ($errors !== []) {
                        throw new DomainException(
                            'No se pudo validar la producción del rider ' . $rider['id_rider'] . ': ' .
                            implode(' ', $errors)
                        );
                    }

                    if (!$this->registrar($payload)) {
                        throw new DomainException(
                            'No se pudo guardar la producción del rider ' . $rider['id_rider'] . '.'
                        );
                    }

                    $registrosCreados++;
                }

                $ridersProcesados++;
            }

            $this->produccionModel->commitTransaction();
        } catch (Throwable $exception) {
            $this->produccionModel->rollbackTransaction();
            throw $exception;
        }

        return [
            'riders_procesados' => $ridersProcesados,
            'registros_creados' => $registrosCreados,
        ];
    }

    public function actualizarRendicion(int $id, int $estado): array
    {
        if ($id <= 0) {
            return [
                'success' => false,
                'id' => $id,
                'estado' => $estado,
                'id_rider' => null,
                'message' => 'ID inválido.',
            ];
        }

        if (!in_array($estado, [0, 1], true)) {
            return [
                'success' => false,
                'id' => $id,
                'estado' => $estado,
                'id_rider' => null,
                'message' => 'Estado inválido.',
            ];
        }

        $updated = $this->produccionModel->updateRendicion($id, $estado);

        return [
            'success' => $updated,
            'id' => $id,
            'estado' => $estado,
            'id_rider' => $updated
                ? $this->produccionModel->getRiderIdByProduccionId($id)
                : null,
        ];
    }

    private function normalizarPayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = trim($value);
            }
        }

        return $payload;
    }

    public function getProduccionPorFecha(string $fecha): array {
        $produccionModel = new ProduccionModel();
        return $produccionModel->getAllProduccionConRiders($fecha);
    }

    public function getResumenPorGrupo(array $produccionesResumen): array
    {
        $resumen = [];

        foreach ($produccionesResumen as $grupo) {

            $serviciosGrupo = 0;
            $importeGrupo = 0;

            $riders = [];

            foreach ($grupo['riders'] as $rider) {

                $cantidadServicios = count($rider['tarifas']);
                $importe = (float)$rider['total_tarifa'];

                $serviciosGrupo += $cantidadServicios;
                $importeGrupo += $importe;

                $riders[] = [
                    'nombre' => $rider['rider_nombre'],
                    'servicios' => $cantidadServicios,
                    'importe' => $importe
                ];
            }

            $resumen[] = [
                'grupo_nombre' => $grupo['grupo_nombre'],
                'grupo_icono' => $grupo['grupo_icono'],
                'servicios' => $serviciosGrupo,
                'importe' => $importeGrupo,
                'riders' => $riders
            ];
        }

        return $resumen;
    }

}