<?php
/**
 * views/usuarios/form.php
 * Vista pura de formulario, reutilizada para crear y editar usuarios.
 * Variables disponibles: $usuario (null si es creación), $roles, $cargos, $formAction
 */
$isEdit = !empty($usuario);
?>
<div class="page-header">
    <h2><?= $isEdit ? 'Editar usuario' : 'Nuevo usuario' ?></h2>
    <p><?= $isEdit ? 'Actualiza la información del usuario seleccionado.' : 'Completa los datos para registrar un nuevo usuario.' ?></p>
</div>

<div class="panel form-panel">
    <form action="<?= htmlspecialchars($formAction) ?>" method="POST" class="grid-form">

        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="name">Nombre completo</label>
            <input type="text" id="name" name="name" required 
                   value="<?= htmlspecialchars($usuario['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Contraseña <?= $isEdit ? '(dejar en blanco para mantener la actual)' : '' ?></label>
            <input type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?> placeholder="••••••••">
        </div>

        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($usuario['phone'] ?? '') ?>">
        </div>

        <div class="form-group form-group-wide">
            <label for="address">Dirección</label>
            <input type="text" id="address" name="address" value="<?= htmlspecialchars($usuario['address'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="role_id">Rol</label>
            <select id="role_id" name="role_id" required>
                <option value="">Selecciona un rol</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int)$r['id'] ?>" <?= (isset($usuario['role_id']) && (int)$usuario['role_id'] === (int)$r['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="cargo_id">Cargo</label>
            <select id="cargo_id" name="cargo_id">
                <option value="">Sin cargo asignado</option>
                <?php foreach ($cargos as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (isset($usuario['cargo_id']) && (int)$usuario['cargo_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['cargo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="documento_tipo">Tipo de documento</label>
            <select id="documento_tipo" name="documento_tipo">
                <?php foreach (['DNI', 'Pasaporte', 'RUC', 'CI'] as $tipo): ?>
                    <option value="<?= $tipo ?>" <?= (($usuario['documento_tipo'] ?? 'DNI') === $tipo) ? 'selected' : '' ?>>
                        <?= $tipo ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="documento_numero">Número de documento</label>
            <input type="text" id="documento_numero" name="documento_numero" value="<?= htmlspecialchars($usuario['documento_numero'] ?? '') ?>">
        </div>

        <div class="form-actions">
            <a href="/mercedes/usuarios" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Crear usuario' ?></button>
        </div>
    </form>
</div>
