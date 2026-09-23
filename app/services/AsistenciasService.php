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

        // Validar rider
        if (empty($payload['rider_id'])) {
            $errors[] = 'Rider inválido.';
        }

        // Validar tipo de marcación
        if (!in_array($payload['tipo'], ['entrada', 'salida'])) {
            $errors[] = 'Tipo de marcación inválido.';
        }

        // Validar ubicación (lat/lon)
        if (empty($payload['lat']) || empty($payload['lon'])) {
            $errors[] = 'Ubicación no disponible. Activa tu GPS.';
        } else {
            // Validar contra punto de control
            if (!empty($payload['punto_control_id'])) {
                $puntoControl = $this->repository->getPuntoControlById($payload['punto_control_id']);
                if ($puntoControl) {
                    $distancia = $this->calcularDistancia(
                        $payload['lat'],
                        $payload['lon'],
                        $puntoControl['latitud'],
                        $puntoControl['longitud']
                    );

                    if ($distancia > 100) { // radio permitido en metros
                        $errors[] = 'Debes estar en el punto de control para marcar llegada.';
                    }
                }
            }
        }

        // Validar turno activo
        $turnos = $this->repository->getAllTurnos(); // devuelve todos los turnos
        $turnoId = TurnoHelper::getTurnoActual($turnos, date('H:i:s'));

        if ($turnoId === null) {
            return ['success' => false, 'message' => 'No hay turno activo en este momento'];
        }


        // Validar duplicados (ej. dos entradas seguidas)
        if ($this->repository->existeMarcacionDuplicada($payload['rider_id'], $payload['tipo'], $payload['fecha'])) {
            $errors[] = 'Ya existe una marcación de este tipo para hoy.';
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
