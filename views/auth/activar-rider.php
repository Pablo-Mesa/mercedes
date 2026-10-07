<?php
/**
 * views/auth/activar-rider.php
 * Vista para que el Rider defina su contraseña al activar la cuenta.
 * Variables disponibles:
 * - $usuario (array con datos del usuario)
 * - $tokenOriginal (string con el token de activación)
 */
?>
<div class="page-header page-header-actions">
    <div>
        <h2>Activar cuenta de Rider</h2>
        <p>Por favor define tu contraseña para completar la activación.</p>
    </div>
</div>

<div class="panel form-panel" tabindex="0" role="region" aria-label="Formulario de activación">
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/mercedes/activar-rider/store">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" value="<?= htmlspecialchars($usuario['name'] ?? ''); ?>" disabled class="form-control">
        </div>

        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="text" value="<?= htmlspecialchars($usuario['email'] ?? ''); ?>" disabled class="form-control">
        </div>

        <div class="form-group">
            <label>Nueva contraseña</label>
            <input type="password" name="password" required class="form-control"
                   minlength="12" autocomplete="new-password"
                   placeholder="Mínimo 12 caracteres">
        </div>

        <div class="form-group">
            <label>Confirmar contraseña</label>
            <input type="password" name="password_confirm" required class="form-control"
                   minlength="12" autocomplete="new-password">
        </div>

        <!-- Token oculto -->
        <input type="hidden" name="token" value="<?= htmlspecialchars($tokenOriginal ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <!-- CSRF -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Activar cuenta</button>
        </div>
    </form>
</div>
