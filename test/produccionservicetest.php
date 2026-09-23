<?php

require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../app/services/ProduccionService.php';

class ProduccionServiceTest
{
    public static function run(): void
    {
        $model = new ProduccionModel();
        $service = new ProduccionService($model);

        echo "Caso 1: payload inválido\n";
        $invalid = [
            'id_usuario' => 1,
            'id_rider' => 1,
            'id_detalle_tarifa' => 1,
            'id_accion' => 0,
            'total_factura' => 'abc',
            'vuelto' => '',
        ];

        $errors = $service->validarRegistro($invalid);
        echo "Errores: " . json_encode($errors) . "\n";

        echo "Caso 2: payload válido (solo validación)\n";
        $valid = [
            'id_usuario' => 1,
            'id_rider' => 1,
            'id_detalle_tarifa' => 1,
            'id_accion' => 1,
            'total_factura' => 1500,
            'vuelto' => 0,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'id_grupo' => 1,
            'id_turno' => 1,
        ];

        $validationErrors = $service->validarRegistro($valid);
        echo "Errores validación: " . json_encode($validationErrors) . "\n";

        echo "Caso 3: actualización de rendición\n";
        $result = $service->actualizarRendicion(1, 1);
        echo "Resultado: " . json_encode($result) . "\n";
    }
}

ProduccionServiceTest::run();