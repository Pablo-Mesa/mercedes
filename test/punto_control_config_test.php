<?php

require_once __DIR__ . '/../models/PuntoControlModel.php';

function assertConfigured(bool $expected, ?array $point, string $message): void
{
    if (PuntoControlModel::isConfigured($point) !== $expected) {
        throw new RuntimeException($message);
    }
}

$validPoint = [
    'titulo' => 'Base Central',
    'barrio' => 'Centro',
    'direccion' => 'Calle 1',
    'contacto' => '0981000000',
    'latitud' => '-25.2637',
    'longitud' => '-57.5759',
    'radio_metros' => '50',
];

assertConfigured(true, $validPoint, 'Una ubicación completa debe considerarse configurada.');
assertConfigured(false, null, 'Un punto inexistente no debe considerarse configurado.');
assertConfigured(false, array_merge($validPoint, ['titulo' => 'Predeterminado']), 'El título de reserva no debe considerarse configuración real.');
assertConfigured(false, array_merge($validPoint, ['contacto' => '']), 'Un campo vacío debe invalidar la configuración.');
assertConfigured(false, array_merge($validPoint, ['latitud' => '91']), 'Una latitud fuera de rango debe invalidar la configuración.');
assertConfigured(false, array_merge($validPoint, ['latitud' => '0', 'longitud' => '0']), 'Las coordenadas de reserva 0,0 no deben considerarse una ubicación configurada.');
assertConfigured(false, array_merge($validPoint, ['radio_metros' => '0']), 'Un radio no positivo debe invalidar la configuración.');

echo "Punto de Control: pruebas OK\n";