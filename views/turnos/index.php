<?php
$grupoNombre = $grupo['nombre'] ?? 'Sin grupo';
$grupoId = $grupo['id_grupo'] ?? null;
$turnos = $turnos ?? [];
?>

<div class="page-header page-header-actions">
    <h2>Turnos del Grupo: <?= htmlspecialchars($grupoNombre) ?></h2>
</div>

<div class="panel">
    <a href="/mercedes/turnos/create?id_grupo=<?= htmlspecialchars((string) ($grupoId ?? '')) ?>" class="btn btn-primary mb-1">
        Nuevo Turno
    </a>

    <section class="panel table-panel" aria-label="Tabla de turnos">
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
                            <a href="/mercedes/turnos/toggleStatus?id=<?= (int)$t['id'] ?>"
                               class="btn btn-small <?= ((int)$t['is_active'] === 1) ? 'btn-danger-outline' : 'btn-success-outline' ?>">
                                <?= ((int)$t['is_active'] === 1) ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
                                
</div>
