<?php
/**
 * views/tarifas/form.php
 * Vista pura de formulario, reutilizada para crear y editar tarifas.
 * Variables disponibles: $tarifas (null si es creación) $formAction
 */
$isEdit = !empty($tarifas);
?>
<div class="page-header">
    <h2><?= $isEdit ? 'Editar tarifa' : 'Crear tarifa' ?></h2>
    <p><?= $isEdit ? 'Actualiza la información del tarifa seleccionado.' : 'Completa los datos para registrar un nueva tarifa.' ?></p>
</div>

<div class="panel form-panel">
  <form action="<?= htmlspecialchars($formAction) ?>" method="POST" enctype="multipart/form-data" class="grid-form">
    
    <div class="d-flex flex-column justify-center align-center">  

    <div class="form-group">
      <label for="titulo">Título:</label>
      <input type="text" name="titulo" id="titulo" class="form-control"
             value="<?= isset($encabezado) ? htmlspecialchars($encabezado['titulo']) : '' ?>" required>
    </div>

    <div class="contenedor-activo">
      <label for="is_active">Activo:</label>
      <input type="checkbox" name="is_active" id="is_active" value="1"
             <?= isset($encabezado) && $encabezado['is_active'] ? 'checked' : '' ?>>
    </div>

    <?php if (isset($encabezado)): ?>
      <p>Fecha de creación: <?= $encabezado['fecha_creacion'] ?></p>
    <?php endif; ?>

    <div class="form-actions">
        <a href="/mercedes/tarifas" class="btn btn-outline">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Crear tarifa' ?></button>
    </div>

    </div>

  </form>
</div>
