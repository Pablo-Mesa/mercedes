<?php
require_once __DIR__ . '/app/services/AsistenciasService.php';
require_once __DIR__ . '/app/repositories/AsistenciasRepository.php';

// Coordenadas de Asunción (ejemplo: tu punto de control)
$latPunto = -25.2620458;
$lonPunto = -57.5896454;

// Coordenadas de Limpio (aprox)
$latRider = -25.166111;
$lonRider = -57.485833;

// Instanciamos el service con un repositorio dummy (no usaremos DB en este test)
$repository = new AsistenciasRepository(); // si requiere conexión, podés mockearlo
$service = new AsistenciasService($repository);

// Usamos reflexión para acceder al método privado
$refClass = new ReflectionClass($service);
$method = $refClass->getMethod('calcularDistancia');
$method->setAccessible(true);

$distancia = $method->invoke($service, $latRider, $lonRider, $latPunto, $lonPunto);

echo "Distancia entre Limpio y Asunción: {$distancia} metros\n";
