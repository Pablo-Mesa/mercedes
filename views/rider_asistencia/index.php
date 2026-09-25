<?php
/**
 * views/rider_asistencia/index.php
 * Vista para listar asistencias del rider autenticado.
 * Variables disponibles: $asistencias, $fecha
 */
?>
<!-- encabezado -->
<div class="page-header page-header-actions">
    <div>
        <p>Consulta tu historial de llegadas y salidas.</p>
    </div>
    <form method="GET" action="/mercedes/rider_asistencia" class="mes-selector">
        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" id="fecha" value="<?= htmlspecialchars($fecha) ?>" class="form-control">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
</div>
<!-- lista de llegadas -->
<div class="panel table-panel">
    <table class="data-table w-full">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Tipo</th>
                <th>Ubicación</th>
                <th>Dispositivo</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($asistencias)): ?>
                <?php foreach ($asistencias as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars(date("d-m-Y", strtotime($a['fecha']))); ?></td>
                        <td><?= htmlspecialchars($a['hora']); ?></td>
                        <td>
                            <?php if ($a['tipo'] === 'entrada'): ?>
                                <span class="badge badge-success">Entrada</span>
                            <?php else: ?>
                                <span class="badge badge-info">Salida</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= !empty($a['lat']) && !empty($a['lon'])
                                ? htmlspecialchars($a['lat'] . ', ' . $a['lon'])
                                : 'No disponible'; ?>
                        </td>
                        <td><?= htmlspecialchars($a['dispositivo']); ?></td>
                        <td><?= htmlspecialchars($a['observaciones'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No se encontraron asistencias para esta fecha.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- acciones -->
<div class="form-actions">
    <a href="/mercedes/rider_asistencia/form" class="btn btn-primary">Marcar mi llegada</a>
</div>