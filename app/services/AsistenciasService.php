<?php

require_once __DIR__ . '/../repositories/AsistenciasRepository.php';
require_once __DIR__ . '/../../helpers/TurnoHelper.php';
require_once __DIR__ . '/../../models/PuntoControlModel.php';

class AsistenciasService
{
    private AsistenciasRepository $repository;

    public function __construct(AsistenciasRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Validar reglas de negocio antes de registrar asistencia
     */
    public function validarMarcacion(array $payload): array
    {
        $errors = [];

        if (empty($payload['rider_id'])) {
            $errors[] = 'Rider inválido.';
        }
        if (empty($payload['grupo_id']) || empty($payload['turno_id'])) {
            $errors[] = 'Grupo o turno inválido.';
        }
        if (!isset($payload['tipo']) || !in_array($payload['tipo'], ['entrada', 'salida'], true)) {
            $errors[] = 'Tipo de marcación inválido.';
            return $errors;
        }

        $puntoControlId = filter_var($payload['punto_control_id'] ?? null, FILTER_VALIDATE_INT);
        $puntoControl = $puntoControlId
            ? $this->repository->getPuntoControlById($puntoControlId)
            : null;

        if (!PuntoControlModel::isConfigured($puntoControl)) {
            $errors[] = 'El Punto de Control no está configurado o no es válido.';
        }

        if ($payload['tipo'] === 'entrada') {
            $latitud = $payload['lat'] ?? null;
            $longitud = $payload['lon'] ?? null;
            if (!is_numeric($latitud) || !is_numeric($longitud)
                || (float)$latitud < -90 || (float)$latitud > 90
                || (float)$longitud < -180 || (float)$longitud > 180) {
                $errors[] = 'No se pudo obtener tu ubicación para validar la entrada.';
            } elseif (PuntoControlModel::isConfigured($puntoControl)) {
                $distancia = $this->calcularDistancia(
                    (float)$latitud,
                    (float)$longitud,
                    (float)$puntoControl['latitud'],
                    (float)$puntoControl['longitud']
                );

                if ($distancia > (int)($puntoControl['radio_metros'] ?? $puntoControl['radio'])) {
                    $errors[] = 'Debes estar dentro del radio permitido del punto de control para marcar entrada.';
                }
            }
        }

        if ($payload['tipo'] === 'salida') {
            $entrada = $this->repository->getEntradaByRiderAndTurno(
                (int)$payload['rider_id'],
                (int)$payload['grupo_id'],
                (int)$payload['turno_id'],
                $payload['fecha']
            );

            if (!$entrada) {
                $errors[] = 'No puedes marcar salida sin haber registrado una entrada previa en este turno.';
            }
        }

        // Validar duplicados: solo 1 entrada y 1 salida por turno
        if ($this->repository->existeMarcacionDuplicada(
            (int)$payload['rider_id'],
            (int)$payload['grupo_id'],
            (int)$payload['turno_id'],
            $payload['tipo'],
            $payload['fecha']
        )) {
            $errors[] = "Ya existe una marcación de tipo {$payload['tipo']} en este turno.";
        }

        return $errors;
    }

    /**
     * Registrar asistencia en la base de datos
     */
    public function registrar(array $payload): bool
    {
        return $this->repository->insert($payload);
    }

    /**
     * Calcular distancia entre dos coordenadas (Haversine)
     */
    private function calcularDistancia(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $radioTierra = 6371000; // metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $radioTierra * $c;
    }
}
