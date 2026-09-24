<?php
/**
 * views/asistencias/index.php
 * Vista para admin/operaciones: lista de asistencias del día.
 * Variables disponibles: $asistencias (array), $fecha (string)
 */
?>
<div class="page-header page-header-actions">
    <div>
        <p>Listado de llegadas y salidas de riders.</p>
    </div>
    <form method="GET" action="/mercedes/asistencias" class="filter-form d-flex flex-row gap-2">
        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" id="fecha" value="<?= htmlspecialchars($fecha ?? '') ?>" class="form-control">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
</div>

<div class="panel table-panel w">
    <div class="panel table-panel">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rider</th>
                    <th>Contacto</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Dispositivo</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($asistencias)): ?>
                    <?php foreach ($asistencias as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['rider_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($a['rider_contacto'] ?? '') ?></td>
                            <td><?= htmlspecialchars($a['hora'] ?? '') ?></td>
                            <td>
                                <?php if (($a['tipo'] ?? '') === 'entrada'): ?>
                                    <span class="badge bg-success">Entrada</span>
                                <?php elseif (($a['tipo'] ?? '') === 'salida'): ?>
                                    <span class="badge bg-info">Salida</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">N/D</span>
                                <?php endif; ?>
                            </td>                            
                            <td><?= htmlspecialchars($a['dispositivo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($a['observaciones'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No se encontraron asistencias para la fecha seleccionada.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
