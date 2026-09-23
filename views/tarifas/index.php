<?php
/**
 * views/tarifas/index.php
 * Vista pura de listado de tarifas. Se inyecta dentro del Layout Maestro.
 * Variable disponible: $tarifas
 */ 
?>
<div class="page-header page-header-actions">
    <div>
        <p>Administra los tarifas del sistema. Los cambios en su estado son reversibles.</p>
    </div>
    <a href="/mercedes/tarifas/crear" class="btn btn-primary btn-nuevo">+ Nuevo Tarifa</a>
</div>

<div class="panel table-panel">
  <table class="data-table">
    <thead>
      <tr>
        <th>Título</th>
        <th>Fecha de creación</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($encabezados as $encabezado): ?>
        <tr>
          <td><?= htmlspecialchars($encabezado['titulo']) ?></td>
          <td><?= $encabezado['fecha_creacion'] ?></td>
          <td>            
            <?php if ((int) $encabezado['is_active'] === 1): ?>
                <span class="badge badge-success">Activo</span>
            <?php else: ?>
                <span class="badge badge-danger">Inactivo</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="/mercedes/tarifas/estado?id=<?= (int)$encabezado['id_encabezado_tarifa'] ?>"
              class="btn btn-small <?= ((int)$encabezado['is_active'] === 1) ? 'btn-danger-outline' : 'btn-success-outline' ?>">
              <?= ((int)$encabezado['is_active'] === 1) ? 'Desactivar' : 'Activar' ?>
            </a>
            <a href="/mercedes/tarifas/detalles?id=<?= (int)$encabezado['id_encabezado_tarifa'] ?>" class="btn btn-small">Ver Detalles</a>               
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
