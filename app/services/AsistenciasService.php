<?php

require_once __DIR__ . '/../repositories/AsistenciasRepository.php';
require_once __DIR__ . '/../../helpers/TurnoHelper.php';

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
        if (!in_array($payload['tipo'], ['entrada','salida'])) {
            $errors[] = 'Tipo de marcación inválido.';
        }

        // Validar ubicación contra punto de control SOLO en entradas
        if ($payload['tipo'] === 'entrada') {
            if (empty($payload['lat']) || empty($payload['lon'])) {
                $errors[] = 'No se pudo obtener tu ubicación para validar la entrada.';
            } elseif (!empty($payload['punto_control_id'])) {
                $puntoControl = $this->repository->getPuntoControlById((int)$payload['punto_control_id']);
                if ($puntoControl) {
                    $distancia = $this->calcularDistancia(
                        (float)$payload['lat'],
                        (float)$payload['lon'],
                        (float)$puntoControl['latitud'],
                        (float)$puntoControl['longitud']
                    );

                    error_log("Validación entrada: lat={$payload['lat']}, lon={$payload['lon']}, distancia={$distancia}, radio={$puntoControl['radio_metros']}");

                    if ($distancia > (int)$puntoControl['radio_metros']) {
                        $errors[] = 'Debes estar dentro del radio permitido del punto de control para marcar entrada.';
                    }
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

            error_log("Validación salida: entrada encontrada=" . json_encode($entrada));

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
