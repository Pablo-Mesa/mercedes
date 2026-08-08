<?php
/**
 * views/tarifas/detalles.php
 * Vista pura de listado de detalles de tarifa. Se inyecta dentro del Layout Maestro.
 * Variable disponible: $detalles
 */ 
?>
<div class="page-header page-header-actions">
    <div>
        <h2>Detalles de Tarifa:</h2>
        <p>Administra los tarifas del sistema. Los cambios en su estado son reversibles.</p>
        <p>Estado del encabezado: <?= $encabezado['is_active'] ? 'Habilitado' : 'Deshabilitado' ?></p>
        <?php if (!$encabezado['is_active']): ?>
          <p><em>No es posible agregar o modificar detalles porque el encabezado está deshabilitado.</em></p>
        <?php endif; ?>
    </div>
    <?php if ($encabezado['is_active']): ?>
      <a href="/mercedes/tarifas/nuevo_detalle&id_encabezado=<?= $encabezado['id_encabezado_tarifa'] ?>" 
        class="btn btn-primary">
        + Agregar nuevo detalle
      </a>
    <?php endif; ?>
</div>


<div class="panel table-panel">
  <table class="data-table">
    <thead>
      <tr>
        <th>Desde</th>
        <th>Hasta</th>
        <th>Costo</th>
        <th>Disponible</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($detalles as $detalle): ?>
        <tr>
          <td> Km. <?= $detalle['desde'] ?></td>
          <td> Km. <?= $detalle['hasta'] ?></td>
          <td> Gs. <?= number_format((float)$detalle['costo'], 0, ',', '.') ?> </td>
          <td>
            <?php if ((int) $detalle['disponible'] === 1): ?>
                <span class="badge badge-success">Activo</span>
            <?php else: ?>
                <span class="badge badge-danger">Inactivo</span>
            <?php endif; ?>

          </td>
          <td>
            <?php if ($encabezado['is_active']): ?>
              <a href="/mercedes/tarifas/detalle_estado?id=<?= (int)$detalle['id'] ?>&id_encabezado=<?= (int)$encabezado['id_encabezado_tarifa'] ?>"
                class="btn btn-small <?= ((int)$detalle['disponible'] === 1) ? 'btn-danger-outline' : 'btn-success-outline' ?>">
                <?= ((int)$detalle['disponible'] === 1) ? 'Desactivar' : 'Activar' ?>
              </a>
            <?php else: ?>
              <em>Acciones bloqueadas</em>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="form-actions">
    <a href="/mercedes/tarifas" class="btn btn-small">Volver</a>
</div>