<?php
/**
 * views/riders/form.php
 * Vista pura de formulario, reutilizada para crear y editar riders.
 * Variables disponibles: $rider (null si es creación) $formAction
 */
$isEdit = !empty($rider);
?>
<div class="page-header">
    <h2><?= $isEdit ? 'Editar rider' : 'Crear rider' ?></h2>
    <p><?= $isEdit ? 'Actualiza la información del rider seleccionado.' : 'Completa los datos para registrar un nuevo rider.' ?></p>
</div>

<div class="panel form-panel">
    <form action="<?= htmlspecialchars($formAction) ?>" method="POST" enctype="multipart/form-data" class="grid-form">

        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$rider['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" class="form-control"
                   value="<?= htmlspecialchars($rider['name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="contacto">Contacto</label>
            <input type="text" name="contacto" id="contacto" class="form-control"
                   value="<?= htmlspecialchars($rider['contacto'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="foto_perfil">Foto de perfil</label>
            <input type="file" name="foto_perfil" id="foto_perfil" class="form-control" accept="image/*">

            <!-- Vista previa: si hay imagen guardada, se muestra -->
              <img id="preview" 
                  src="/mercedes/public/uploads/riders/<?= !empty($rider['foto_perfil'])
                  ? htmlspecialchars($rider['foto_perfil'])
                  : 'default.png' ?>" alt="Vista previa" 
                  class="img-preview">

    
        </div>

        <div class="form-group">
            <label>Asignar a grupos:</label><br>
            <?php foreach ($grupos as $grupo): ?>
                <label>
                    <input type="checkbox" name="grupos[]" value="<?= $grupo['id_grupo'] ?>"
                        <?= in_array($grupo['id_grupo'], $gruposAsignados ?? []) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($grupo['nombre']) ?>
                </label><br>
            <?php endforeach; ?>
        </div>

        <div class="form-group">
            <label for="is_active">Activo</label>
            <select name="is_active" id="is_active" class="form-control">
                <option value="1" <?php echo (isset($rider) && $rider && (int) $rider['is_active'] === 1) ? 'selected' : ''; ?>>Sí</option>
                <option value="0" <?php echo (isset($rider) && $rider && (int) $rider['is_active'] === 0) ? 'selected' : ''; ?>>No</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="/mercedes/riders" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Crear rider' ?></button>
            <?php if ($isEdit): ?>
                <button type="submit" name="reset_foto" value="1" class="btn btn-warning">
                    Restablecer foto por defecto
                </button>
            <?php endif; ?>
        </div>

    </form>
</div>
