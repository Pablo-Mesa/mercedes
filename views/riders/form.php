<?php
/**
 * views/riders/form.php
 * Formulario para crear y editar Riders (usuarios con role_id = 3).
 * Variables disponibles: $rider (null si es creación), $formAction, $grupos, $gruposAsignados
 */
$isEdit = !empty($rider);
?>
<div class="page-header">
    <h2><?= $isEdit ? 'Editar Rider' : 'Nuevo Rider' ?></h2>
    <p><?= $isEdit ? 'Actualiza la información del rider seleccionado.' : 'Completa los datos para registrar un nuevo rider.' ?></p>
</div>

<div class="panel form-panel">
    <form action="<?= htmlspecialchars($formAction ?? '') ?>" method="POST" enctype="multipart/form-data" class="grid-form">

        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$rider['id'] ?>">
        <?php endif; ?>

        <!-- nombre -->
        <div class="form-group">
            <label for="name">Nombre completo</label>
            <input type="text" id="name" name="name" required 
                   value="<?= htmlspecialchars($rider['name'] ?? '') ?>">
        </div>

        <!-- correo -->
        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? ($rider['email'] ?? '')); ?>" 
                required class="form-control">

            <?php if (!empty($_SESSION['error']) && str_contains($_SESSION['error'], 'correo')): ?>
                <div class="alert alert-danger mt-1">
                    <?= htmlspecialchars($_SESSION['error']); ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        </div>

        <!-- clave -->
        <div class="form-group">
            <label for="password">Contraseña <?= $isEdit ? '(dejar en blanco para mantener la actual)' : '' ?></label>
            <input type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?> placeholder="••••••••">
        </div>

        <!-- teléfono -->
        <div class="form-group">
            <label for="phone">Teléfono de contacto</label>
            <input type="text" id="phone" name="phone" required 
                   value="<?= htmlspecialchars($rider['phone'] ?? '') ?>">
        </div>

        <!-- foto perfil -->
        <div class="form-group">
            <label for="foto_perfil">Foto de perfil</label>
            <input type="file" name="foto_perfil" id="foto_perfil" class="form-control" accept="image/*">
            <img id="preview" 
                 src="/mercedes/public/uploads/riders/<?= !empty($rider['foto_perfil'])
                     ? htmlspecialchars($rider['foto_perfil'])
                     : 'default.png' ?>" 
                 alt="Vista previa" 
                 class="img-preview">
        </div>

        <!-- grupos -->
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

        <!-- estado -->
        <div class="form-group">
            <label for="is_active">Activo</label>
            <select name="is_active" id="is_active" class="form-control">
                <option value="1" <?= (isset($rider) && $rider && (int)$rider['is_active'] === 1) ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= (isset($rider) && $rider && (int)$rider['is_active'] === 0) ? 'selected' : '' ?>>No</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="/mercedes/riders" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Crear Rider' ?></button>
            <?php if ($isEdit): ?>
                <button type="submit" name="reset_foto" value="1" class="btn btn-warning">
                    Restablecer foto por defecto
                </button>
            <?php endif; ?>
        </div>

    </form>
</div>
