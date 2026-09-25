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

        // Validar duplicados por grupo + turno + tipo
        if ($this->repository->existeMarcacionDuplicada(
            $payload['rider_id'],
            $payload['grupo_id'],
            $payload['turno_id'],
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
