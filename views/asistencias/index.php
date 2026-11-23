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
    <form method="GET" action="<?= BASE_PATH ?>/asistencias" class="filter-form d-flex flex-row gap-2">
        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" id="fecha" value="<?= htmlspecialchars($fecha ?? '') ?>" class="form-control">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
</div>

<div id="alerta-audio-bloqueado" 
     class="alert alert-warning py-2 px-3 mb-3" 
     style="display: none; cursor: pointer;"
     onclick="NotificadorAudio.bip()">
    🔇 <strong>Alerta sonora en pausa:</strong> Haz clic en cualquier parte de la pantalla para activar el sonido de nuevas llegadas.
</div>
<div class="panel table-panel">
    <div class="panel table-panel" tabindex="0" role="region" aria-label="Tabla de asistencias">
        <table 
            id="tabla-asistencias" 
            data-ultimo-id="<?= $ultimoIdInicial ?? 0 ?>" 
            data-fecha="<?= htmlspecialchars($fecha ?? date('Y-m-d')) ?>"
            data-stream-url="<?= BASE_PATH ?>/asistencias/stream" 
            class="data-table"
        >
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Rider</th>
                    <th>Hora</th>                            
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody id="tbody-asistencias">
                <?php if (!empty($asistencias)): ?>
                    <?php foreach ($asistencias as $a): ?>                        
                        <tr id="asistencia-<?= (int)$a['id'] ?>">
                            <td><?= htmlspecialchars(isset($a['fecha']) && $a['fecha'] !== '' ? date('d-m-Y', strtotime($a['fecha'])) : '') ?></td>
                            <td><?= htmlspecialchars($a['rider_name'] ?? '') ?></td>
                            <td>                                
                                <?php if (($a['tipo'] ?? '') === 'entrada'): ?>
                                    <span class="badge badge-success">Entrada</span>
                                <?php elseif (($a['tipo'] ?? '') === 'salida'): ?>
                                    <span class="badge badge-info">Salida</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">N/D</span>
                                <?php endif; ?>
                                <small># <?= htmlspecialchars($a['dispositivo'] ?? '') ?></small>
                                <br>    
                                <?= htmlspecialchars($a['hora'] ?? '') ?>                                
                            </td>                            
                            <td><?= htmlspecialchars($a['observaciones'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="fila-sin-asistencias">
                        <td colspan="4" class="text-center text-muted">
                            No se encontraron asistencias para la fecha seleccionada.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="<?= BASE_PATH ?>/public/js/notificaciones-audio.js"></script>
<script src="<?= BASE_PATH ?>/public/js/asistencias-sse.js"></script>
