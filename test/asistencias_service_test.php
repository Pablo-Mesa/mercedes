<?php

require_once __DIR__ . '/../app/services/AsistenciasService.php';

class StubAsistenciasRepository extends AsistenciasRepository
{
    public ?array $puntoControl = null;
    public ?array $entrada = ['id' => 1];
    public bool $duplicada = false;

    public function __construct()
    {
    }

    public function getPuntoControlById(int $id): ?array
    {
        return $this->puntoControl;
    }

    public function getEntradaByRiderAndTurno(int $riderId, int $grupoId, int $turnoId, string $fecha): ?array
    {
        return $this->entrada;
    }

    public function existeMarcacionDuplicada(int $riderId, int $grupoId, int $turnoId, string $tipo, string $fecha): bool
    {
        return $this->duplicada;
    }
}

function assertMarcacionErrors(bool $expected, array $errors, string $message): void
{
    if (($errors !== []) !== $expected) {
        throw new RuntimeException($message . ' Errores: ' . implode(' ', $errors));
    }
}

$repository = new StubAsistenciasRepository();
$service = new AsistenciasService($repository);
$payload = [
    'rider_id' => 4,
    'grupo_id' => 2,
    'turno_id' => 3,
    'fecha' => '2026-09-29',
    'tipo' => 'entrada',
    'punto_control_id' => 1,
    'lat' => '-25.2637',
    'lon' => '-57.5759',
];

$repository->puntoControl = [
    'id' => 1,
    'titulo' => 'Base Central',
    'barrio' => 'Centro',
    'direccion' => 'Calle 1',
    'contacto' => '0981000000',
    'latitud' => '-25.2637',
    'longitud' => '-57.5759',
    'radio_metros' => 50,
];
assertMarcacionErrors(false, $service->validarMarcacion($payload), 'Una entrada dentro de un punto configurado debe ser válida.');

$repository->puntoControl['titulo'] = 'Predeterminado';
assertMarcacionErrors(true, $service->validarMarcacion($payload), 'El punto de reserva no debe autorizar marcaciones.');

$repository->puntoControl['titulo'] = 'Base Central';
$payload['punto_control_id'] = '';
assertMarcacionErrors(true, $service->validarMarcacion($payload), 'Una marcación sin punto de control debe rechazarse.');

$payload['punto_control_id'] = 1;
$payload['lat'] = '91';
assertMarcacionErrors(true, $service->validarMarcacion($payload), 'Una latitud fuera de rango debe rechazarse.');

echo "Asistencias: pruebas OK\n";