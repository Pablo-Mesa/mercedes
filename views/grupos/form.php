<?php
/**
 * views/grupos/form.php
 * Formulario de creación/edición de grupos.
 * Variables disponibles: $grupo, $formAction
 */
?>
<div class="page-header">
    <h2><?= $grupo ? 'Editar Empresa' : 'Nueva Empresa' ?></h2>
</div>


<div class="panel form-panel-medium">

    <form action="<?= $formAction ?>" method="post" enctype="multipart/form-data" class="form-panel-medium">
    
        <?php if ($grupo): ?>
            <input type="hidden" name="id" value="<?= (int)$grupo['id_grupo'] ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="id_punto_control">Punto de Control</label>
            <select name="id_punto_control" id="id_punto_control" required>
                <?php foreach ($puntosControl as $pc): ?>
                    <option value="<?= $pc['id'] ?>" 
                        <?= ($grupo && $grupo['id_punto_control'] == $pc['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pc['titulo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre del Grupo</label>
            <input type="text" name="nombre" id="nombre" 
                value="<?= htmlspecialchars($grupo['nombre'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion"><?= htmlspecialchars($grupo['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="activo">Estado</label>
            <select name="activo" id="activo">
                <option value="1" <?= ($grupo && (int)$grupo['activo'] === 1) ? 'selected' : '' ?>>Activo</option>
                <option value="0" <?= ($grupo && (int)$grupo['activo'] === 0) ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>

        <div class="form-group">
            <label for="icono">Icono</label>
            <input type="file" name="icono" id="icono" accept="image/*">
            <?php if (!empty($grupo['icono'])): ?>
                <div class="preview">
                    <img src="/mercedes/public/uploads/grupos/<?= ($grupo['icono'] === 'default.png') ? 'default.png' : htmlspecialchars($grupo['icono']) ?>" alt="Icono grupo" class="avatar-sm">
                    <label>
                        <input type="checkbox" name="reset_icono" value="1"> Restablecer a default
                    </label>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="/mercedes/grupos" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>

</div>