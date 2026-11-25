<?php

require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../app/services/ProduccionService.php';

class ProduccionPopulateTestModel extends ProduccionModel
{
    public array $inserted = [];
    public bool $transactionStarted = false;
    public int $commits = 0;
    public int $rollbacks = 0;
    public ?int $failAfterInsertions = null;
    public ?float $tariffCostOverride = null;
    public array $riders = [
        ['id_rider' => 10, 'grupo_id' => 1],
        ['id_rider' => 10, 'grupo_id' => 2],
        ['id_rider' => 11, 'grupo_id' => 2],
    ];
    public array $actions = [
        ['id_accion' => 3, 'codigo' => 'efectivo'],
    ];

    public function __construct()
    {
    }

    public function getRidersActivosConGrupos(?int $grupoId = null): array
    {
        if ($grupoId !== null) {
            return array_values(array_filter(
                $this->riders,
                static fn(array $row): bool => (int) $row['grupo_id'] === $grupoId
            ));
        }

        return $this->riders;
    }

    public function getAccionesRider(): array
    {
        return $this->actions;
    }

    public function getAccionById(int $idAccion): ?array
    {
        foreach ($this->getAccionesRider() as $accion) {
            if ($accion['id_accion'] === $idAccion) {
                return $accion;
            }
        }

        return null;
    }

    public function getTarifasByGrupo(int $grupoId): array
    {
        $cost = $this->tariffCostOverride ?? ($grupoId === 1 ? 98000 : 106000);
        return [['id' => $grupoId * 100, 'costo' => $cost]];
    }

    public function getTurnosByGrupo(int $grupoId): array
    {
        return [['id' => $grupoId]];
    }

    public function startTransaction(): void
    {
        $this->transactionStarted = true;
    }

    public function commitTransaction(): void
    {
        $this->commits++;
    }

    public function rollbackTransaction(): void
    {
        $this->rollbacks++;
        $this->inserted = [];
    }

    public function insert(array $data): bool
    {
        if ($this->failAfterInsertions !== null && count($this->inserted) >= $this->failAfterInsertions) {
            return false;
        }

        $this->inserted[] = $data;
        return true;
    }
}

function assertPopulate(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$model = new ProduccionPopulateTestModel();
$service = new ProduccionService($model);
$result = $service->poblarProduccion('2026-11-24', 7);

assertPopulate($result['riders_procesados'] === 2, 'Debe procesar cada rider una sola vez.');
assertPopulate($result['registros_creados'] >= 8 && $result['registros_creados'] <= 24, 'Debe crear entre 4 y 12 registros por rider.');
assertPopulate(count($model->inserted) === $result['registros_creados'], 'El resultado debe coincidir con las inserciones.');
assertPopulate($model->commits === 1 && $model->rollbacks === 0, 'Un lote correcto debe confirmar una sola transacción.');

foreach ($model->inserted as $registro) {
    assertPopulate(in_array($registro['id_grupo'], [1, 2], true), 'Debe elegir un grupo asignado al rider.');
    assertPopulate($registro['id_detalle_tarifa'] === $registro['id_grupo'] * 100, 'La tarifa debe pertenecer al grupo elegido.');
    assertPopulate($registro['id_turno'] === $registro['id_grupo'], 'El turno debe pertenecer al grupo elegido.');
    assertPopulate(str_starts_with($registro['fecha_creacion'], '2026-11-24 '), 'La hora debe quedar dentro del día seleccionado.');

    if ($registro['id_accion'] === 3) {
        $expectedChange = ceil($registro['total_factura'] / 100000) * 100000 - $registro['total_factura'];
        assertPopulate($registro['vuelto'] === $expectedChange, 'El vuelto de efectivo debe seguir la regla del siguiente múltiplo.');
    } else {
        assertPopulate($registro['vuelto'] === null, 'Las acciones distintas de efectivo deben guardar vuelto null.');
    }
}

$scopedModel = new ProduccionPopulateTestModel();
$scopedService = new ProduccionService($scopedModel);
$scopedResult = $scopedService->poblarProduccion('2026-11-24', 7, 2);
assertPopulate($scopedResult['riders_procesados'] === 2, 'La generación limitada a un grupo debe procesar solo sus riders.');
foreach ($scopedModel->inserted as $registro) {
    assertPopulate($registro['id_grupo'] === 2, 'Operaciones debe limitar cada registro al grupo de sesión.');
}

$nonCashModel = new ProduccionPopulateTestModel();
$nonCashModel->actions = [['id_accion' => 2, 'codigo' => 'pos']];
$nonCashService = new ProduccionService($nonCashModel);
$nonCashService->poblarProduccion('2026-11-24', 7, 2);
foreach ($nonCashModel->inserted as $registro) {
    assertPopulate($registro['vuelto'] === null, 'Las acciones no efectivo deben guardar vuelto null.');
}

$failingModel = new ProduccionPopulateTestModel();
$failingModel->failAfterInsertions = 2;
$failingService = new ProduccionService($failingModel);

try {
    $failingService->poblarProduccion('2026-11-24', 7);
    throw new RuntimeException('Una inserción fallida debía cancelar el lote.');
} catch (DomainException $exception) {
    assertPopulate($failingModel->rollbacks === 1, 'Un fallo debe revertir la transacción completa.');
    assertPopulate($failingModel->commits === 0, 'Un lote fallido no debe confirmar la transacción.');
    assertPopulate($failingModel->inserted === [], 'El rollback debe descartar incluso los registros insertados antes del fallo.');
}

$exactChangeModel = new ProduccionPopulateTestModel();
$exactChangeModel->riders = [['id_rider' => 12, 'grupo_id' => 1]];
$exactChangeModel->tariffCostOverride = 100000;
$exactChangeService = new ProduccionService($exactChangeModel);
$exactChangeService->poblarProduccion('2026-11-24', 7);
foreach ($exactChangeModel->inserted as $registro) {
    assertPopulate($registro['vuelto'] === 0.0, 'Un múltiplo exacto de 100000 debe producir vuelto 0.');
}

echo "Produccion populate service tests passed.\n";
