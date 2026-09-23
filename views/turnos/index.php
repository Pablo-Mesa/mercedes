<div class="page-header page-header-actions">
    <h2>Turnos del Grupo: <?= htmlspecialchars($grupo['nombre']) ?></h2>
</div>

<div class="panel">
    <a href="/mercedes/turnos/create?id_grupo=<?= $grupo['id_grupo'] ?>" class="btn btn-primary mb-1">
        Nuevo Turno
    </a>

    <div class="panel table-panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>Turno</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Icono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($turnos as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['turno']) ?></td>
                    <td><?= htmlspecialchars($t['hora_inicio']) ?></td>
                    <td><?= htmlspecialchars($t['hora_fin']) ?></td>
                    <td>
                        <?php if (!empty($t['url_icono'])): ?>
                            <img src="<?= htmlspecialchars($t['url_icono']) ?>" alt="Icono turno" class="avatar-sm">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/mercedes/turnos/edit?id=<?= $t['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="/mercedes/turnos/delete?id=<?= $t['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Seguro que deseas eliminar este turno?');">
                           Eliminar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
                            
</div>
