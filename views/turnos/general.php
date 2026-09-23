<?php foreach ($grupos as $g): ?>
    <div class="panel mb-1">
        
        <div class="panel-header d-flex align-bottom">
            <?php if (!empty($g['icono'])): ?>
                <img src="/mercedes/public/uploads/grupos/<?= htmlspecialchars($g['icono']) ?>" 
                     alt="Icono grupo" class="avatar-sm mr-2">
            <?php endif; ?>
            <h3><?= htmlspecialchars($g['nombre']) ?></h3>
        </div>

        <?php if (!empty($turnosPorGrupo[$g['id_grupo']])): ?>
            <div class="panel table-panel">        
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Turno</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Icono</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($turnosPorGrupo[$g['id_grupo']] as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['turno']) ?></td>
                                <td><?= htmlspecialchars($t['hora_inicio']) ?></td>
                                <td><?= htmlspecialchars($t['hora_fin']) ?></td>
                                <td>
                                    <?php if (!empty($t['url_icono'])): ?>
                                        <img src="<?= htmlspecialchars($t['url_icono']) ?>" alt="Icono turno" class="avatar-sm">
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Este grupo no tiene turnos registrados.
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
