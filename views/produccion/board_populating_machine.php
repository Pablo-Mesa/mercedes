<?php
$fechaMaquina = $fechaMaquina ?? date('Y-m-d');
$resultadoMaquina = $resultadoMaquina ?? null;
$errorMaquina = $errorMaquina ?? null;
?>

<section class="content-wrap" aria-labelledby="board-populating-title">
  <h1 id="board-populating-title">Máquina Pobladora</h1>
  <p>Genera registros de producción de prueba para los riders y grupos disponibles.</p>

  <form method="post" action="<?= BASE_PATH ?>/produccion/board-populating-machine" class="form-group">
    <label for="fecha-poblado">Día a poblar</label>
    <input
      type="date"
      id="fecha-poblado"
      name="fecha"
      value="<?= htmlspecialchars($fechaMaquina, ENT_QUOTES, 'UTF-8') ?>"
      required
    >
    <button class="btn btn-warning" type="submit">Poblar Producción</button>
  </form>

  <?php if ($errorMaquina !== null): ?>
    <div role="alert" class="content-wrap" style="border-left: 4px solid #dc2626;">
      <strong>No se completó el poblado.</strong>
      <p><?= htmlspecialchars($errorMaquina, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php elseif (is_array($resultadoMaquina)): ?>
    <div role="status" class="content-wrap" style="border-left: 4px solid #16a34a;">
      <strong>Producción poblada correctamente.</strong>
      <p>Riders procesados: <?= (int) $resultadoMaquina['riders_procesados'] ?></p>
      <p>Registros creados: <?= (int) $resultadoMaquina['registros_creados'] ?></p>
    </div>
  <?php endif; ?>
</section>
