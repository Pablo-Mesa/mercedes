<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

<div class="form-grid">
    <!-- fila 1: info + mapa -->
    <div class="row">
        <div class="col">
            <!-- info -->
            <div class="rider-info">    
                <div class="punto-control">                    
                    <span class="img-nav-icon"><?= render_icon('empresas', 'nav-icon') ?></span>
                    <strong><?= htmlspecialchars($puntoControl['titulo'] ?? 'Punto de control') ?></strong>
                    <div>    
                        <span class="img-nav-icon"><?= render_icon('puntodecontrol', 'nav-icon') ?></span>
                        <span><?= htmlspecialchars($puntoControl['direccion'] ?? '') ?></span>            
                    </div>        
                    <div>
                        <span>
                            <span class="img-nav-icon"><?= render_icon('calendario', 'nav-icon') ?></span> <?= date('d/m/Y') ?>
                        </span>
                        <div id="clock">
                            <span class="img-nav-icon"><?= render_icon('reloj', 'nav-icon') ?></span>
                            <span id="clock-time"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <!-- mapa -->
            <div id="map" class="map-style"></div>
        </div>
    </div>

    <!-- fila 2: disponibilidad -->
    <div class="col">
        <h5>Disponibilidad</h5>
        <?php foreach ($grupos as $g): ?>
            <?php foreach ($g['turnos'] as $t): ?>
                <div class="turno-card" style="display:flex; flex-direction:row; 
                     align-items:center; justify-content:space-between; 
                     border:1px solid #ccc; padding:8px; margin-bottom:6px; border-radius:4px;">
                    
                    <div style="flex:1;">
                        <strong><?= htmlspecialchars($g['nombre']) ?></strong>
                        <div style="font-size:0.9em; color:#555;">
                            <?= htmlspecialchars($g['punto_titulo']) ?>
                        </div>
                    </div>

                    <div style="flex:1; text-align:center;">
                        <span><?= htmlspecialchars($t['hora_inicio']) ?> - <?= htmlspecialchars($t['hora_fin']) ?></span>
                    </div>

                    <div style="flex:1; text-align:center;">
                        <label><input type="radio" name="tipo_<?= $t['id'] ?>" value="entrada" checked> Entrada</label>
                        <label><input type="radio" name="tipo_<?= $t['id'] ?>" value="salida"> Salida</label>
                    </div>

                    <div style="flex:0;">
                        <form action="<?= htmlspecialchars($formAction) ?>" method="POST" class="inline-form">
                            <input type="hidden" name="grupo_id" value="<?= $g['id_grupo'] ?>">
                            <input type="hidden" name="turno_id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="tipo" value="entrada">
                            <input type="hidden" name="lat" id="lat">
                            <input type="hidden" name="lon" id="lon">
                            <input type="hidden" name="dispositivo" value="web">
                            <button type="submit" class="btn btn-primary">Marcar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>

    <!-- fila 3: tabla marcaciones -->
    <div class="row mt-2">
        <div class="col">
            <h5>Marcaciones del día</h5>
            <table id="tablaMarcaciones" class="w-full text-center">
                <thead>
                    <tr><th>Hora</th><th>Tipo</th><th>Dispositivo</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($asistencias as $a): ?>
                        <tr>
                            <td class=""><?= htmlspecialchars($a['hora']) ?></td>
                            <td><?= htmlspecialchars($a['tipo']) ?></td>
                            <td><?= htmlspecialchars($a['dispositivo']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    window.puntoControl = <?= json_encode($puntoControl) ?>;
</script>
<script src="/mercedes/public/js/rider_asistencia.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
