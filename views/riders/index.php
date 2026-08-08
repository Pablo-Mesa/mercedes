<?php
/**
 * views/riders/index.php
 * Vista pura de listado de riders. Se inyecta dentro del Layout Maestro.
 * Variable disponible: $riders
 */ 
?>
<div class="page-header page-header-actions">
    <div>
        <h2>Riders</h2>
        <p>Administra los riders del sistema. Los cambios en su estado son reversibles.</p>
    </div>
    <a href="/mercedes/riders/crear" class="btn btn-primary btn-nuevo">+ Nuevo rider</a>
</div>

<div class="panel table-panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th> 
                <th>Nombre</th> 
                <th>Contacto</th> 
                <th>Foto de perfil</th> 
                <th>Estado</th> 
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($riders)): ?>
                <?php foreach ($riders as $r): ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo htmlspecialchars($r['contacto']); ?></td>

                        <td>
                            <img src="/mercedes/public/uploads/riders/<?= !empty($r['foto_perfil']) 
                                ? htmlspecialchars($r['foto_perfil']) 
                                : 'default.png' ?>" 
                                alt="Foto de <?= htmlspecialchars($r['name']); ?>" 
                                class="avatar-sm">
                        </td>


                        <td>
                            <?php if ((int) $r['is_active'] === 1): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/mercedes/riders/editar?id=<?= (int)$r['id'] ?>" class="btn btn-small">Editar</a>
                            <a href="/mercedes/riders/estado?id=<?= (int)$r['id'] ?>"
                               class="btn btn-small <?= ((int)$r['is_active'] === 1) ? 'btn-danger-outline' : 'btn-success-outline' ?>">
                                <?= ((int)$r['is_active'] === 1) ? 'Desactivar' : 'Activar' ?>
                            </a>
                            <!-- 
                            <a href="/mercedes/riders/eliminar?id=<?= (int)$r['id'] ?>" 
                               class="btn btn-small btn-danger-outline"
                               onclick="return confirm('¿Seguro que deseas eliminar este rider?');">
                                Eliminar
                            </a>
                            -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No se encontraron registros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
