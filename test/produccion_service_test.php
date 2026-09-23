<?php

require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../app/services/ProduccionService.php';

class StubProduccionModel extends ProduccionModel
{
    public function getAccionById(int $idAccion): ?array
    {
        $acciones = [
            1 => ['id_accion' => 1, 'codigo' => 'efectivo'],
            2 => ['id_accion' => 2, 'codigo' => 'pos'],
        ];

        return $acciones[$idAccion] ?? null;
    }

    public function insert(array $data): bool
    {
        return true;
    }

    public function updateRendicion(int $id, int $estado): bool
    {
        return true;
    }

    public function getRiderIdByProduccionId(int $id): ?int
    {
        return 42;
    }
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

echo "=== TEST FINAL DE REFACTORIZACION ===\n\n";

$service = new ProduccionService(new StubProduccionModel());

echo "Caso 1: payload inválido\n";
$invalid = [
    'id_usuario' => 1,
    'id_rider' => 1,
    'id_detalle_tarifa' => 1,
    'id_accion' => 0,
    'total_factura' => 'abc',
    'vuelto' => '',
    'id_grupo' => 1,
    'id_turno' => 1,
    'fecha_creacion' => date('Y-m-d H:i:s'),
];

$errors = $service->validarRegistro($invalid);
echo 'Errores: ' . json_encode($errors) . PHP_EOL;
assertTrue(count($errors) > 0, 'El payload inválido debe devolver errores.');

echo "\nCaso 2: payload válido\n";
$valid = [
    'id_usuario' => 1,
    'id_rider' => 1,
    'id_detalle_tarifa' => 1,
    'id_accion' => 1,
    'total_factura' => 1500,
    'vuelto' => 0,
    'id_grupo' => 1,
    'id_turno' => 1,
    'fecha_creacion' => date('Y-m-d H:i:s'),
];

$validationErrors = $service->validarRegistro($valid);
echo 'Errores validación: ' . json_encode($validationErrors) . PHP_EOL;
assertTrue(count($validationErrors) === 0, 'El payload válido no debe devolver errores.');

echo "\nCaso 3: registro y actualización\n";
$registro = $service->registrar($valid);
assertTrue($registro === true, 'El servicio debe poder registrar un payload válido.');

$actualizacion = $service->actualizarRendicion(99, 1);
echo 'Resultado: ' . json_encode($actualizacion) . PHP_EOL;
assertTrue(is_array($actualizacion), 'actualizarRendicion debe devolver un array.');
assertTrue($actualizacion['success'] === true, 'La actualización debe devolver success true.');
assertTrue($actualizacion['id_rider'] === 42, 'Debe devolver el rider asociado.');

echo "\n=== PRUEBA FINAL OK ===\n";