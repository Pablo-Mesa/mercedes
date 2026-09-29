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
            <h5>Disponibilidad de turnos:</h5>
            <!-- Contenedor con scroll -->
            <div class="content-card">
                <?php foreach (($grupos ?? []) as $g): ?>
                    <?php foreach ($g['turnos'] as $t): ?>
                        <!-- tarjeta -->
                        <div class="turno-card">                            
                            <!-- Columna 1: Grupo/Empresa -->
                            <div class="flex-1">
                                <strong><?= htmlspecialchars($g['nombre']) ?></strong>
                                <div class="font-color">
                                    <small><?= htmlspecialchars($g['punto_titulo']) ?></small>
                                </div>
                            </div>

                            <!-- Columna 2: Horario -->
                            <div class="flex-1 text-center">
                                <span><?= htmlspecialchars($t['hora_inicio']) ?> - <?= htmlspecialchars($t['hora_fin']) ?></span>
                            </div>

                            <!-- Columna 3: Tipo de marcación -->
                            <div class="flex-1 text-center">
                                <label><input type="radio" name="tipo" value="entrada" checked> Entrada</label>
                                <label><input type="radio" name="tipo" value="salida"> Salida</label>
                            </div>

                            <!-- Columna 4: Botón de acción -->
                            <div class="flex-0">
                                <form action="<?= htmlspecialchars($formAction ?? '', ENT_QUOTES, 'UTF-8') ?>" method="POST" class="inline-form">
                                    <input type="hidden" name="punto_control_id" value="<?= $puntoControl['id'] ?>">
                                    <input type="hidden" name="grupo_id" value="<?= $g['id_grupo'] ?>">
                                    <input type="hidden" name="turno_id" value="<?= $t['id'] ?>">
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

    </div>

    <!-- fila 3: tabla marcaciones -->
    <div class="row mt-2">
        <div class="col">
            <h5>Marcaciones del día:</h5>
            <!-- Contenedor con altura fija y scroll -->
            <div class="tabla-marcaciones-container">
                <table id="tablaMarcaciones" class="w-full text-center">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Dispositivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($asistencias ?? []) as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['empresa'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($a['hora']) ?></td>
                                <td><?= htmlspecialchars($a['tipo']) ?></td>
                                <td><?= htmlspecialchars($a['dispositivo']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    window.puntoControl = <?= json_encode($puntoControl ?? []) ?>;
</script>
<script src="/mercedes/public/js/rider_asistencia.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>