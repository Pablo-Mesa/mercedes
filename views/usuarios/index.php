<?php
/**
 * views/usuarios/index.php
 * Vista pura de listado de usuarios. Se inyecta dentro del Layout Maestro.
 * Variable disponible: $usuarios
 */
?>

<div class="page-header page-header-actions">
    <div>
        <p>Administra las cuentas del sistema. Los cambios de estado son reversibles.</p>
    </div>
    <a href="/mercedes/usuarios/crear" class="btn btn-primary btn-nuevo">+ Nuevo usuario</a>
</div>

<div class="panel table-panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Cargo</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="7" class="empty-state">Todavía no hay usuarios registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge badge-role"><?= htmlspecialchars($u['role_name'] ?? '—') ?></span></td>
                        <td><?= htmlspecialchars($u['cargo_name'] ?? '—') ?></td>
                        <!-- <td><?= htmlspecialchars($u['documento_tipo'] . ' ' . $u['documento_numero']) ?></td> -->
                        <td>
                            <?php if ((int)$u['is_active'] === 1): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <a href="/mercedes/usuarios/editar?id=<?= (int)$u['id'] ?>" class="btn btn-small">Editar</a>
                            <a href="/mercedes/usuarios/estado?id=<?= (int)$u['id'] ?>"
                               class="btn btn-small <?= ((int)$u['is_active'] === 1) ? 'btn-danger-outline' : 'btn-success-outline' ?>">
                                <?= ((int)$u['is_active'] === 1) ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
