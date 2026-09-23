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

}