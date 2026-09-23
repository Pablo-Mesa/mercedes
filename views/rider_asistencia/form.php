<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

<!-- Rider info -->
<div class="rider-info">    
    <!-- Fecha, reloj y punto de control -->
    <div class="punto-control">
        <div>
            <span>
                <span class="img-nav-icon" ><?= render_icon('calendario', 'nav-icon') ?></span> <?= date('d/m/Y') ?>
            </span>
            <div id="clock">
                <span class="img-nav-icon"><?= render_icon('reloj', 'nav-icon') ?></span>
                <span id="clock-time"></span>
            </div>
        </div>
        
        <span class="img-nav-icon" ><?= render_icon('empresas', 'nav-icon') ?></span>
        <strong> <?= htmlspecialchars($puntoControl['titulo'] ?? 'Punto de control') ?></strong>
        <div>    
            <span class="img-nav-icon" ><?= render_icon('puntodecontrol', 'nav-icon') ?></span>
            <span> <?= htmlspecialchars($puntoControl['direccion'] ?? '') ?></span>            
        </div>        
        <div id="map" class="map-style mt-1 mb-1" ></div>
    </div>
</div>

<!-- Grupos para los que está disponible -->
<div class="grupos-disponibles">
    <h5>Disponible para:</h5>
    <div class="grupo-icons">
        <?php foreach ($grupos as $g): ?>
            <div class="grupo-item" title="<?= htmlspecialchars($g['nombre']) ?>">
                <img src="<?= BASE_PATH ?>/public/uploads/grupos/<?= htmlspecialchars($g['icono']) ?>" 
                     alt="<?= htmlspecialchars($g['nombre']) ?>" 
                     class="icon-sm border-rounded-m">
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Marcacion -->
<form action="<?= htmlspecialchars($formAction) ?>" method="POST" id="asistenciaForm" class="d-flex flex-column w-half">

    <!-- Botón inicial -->
    <div class="form-actions">
        <button type="button" id="btnComprobar" class="btn btn-primary">Comprobar mi ubicación</button>
    </div>

    <!-- Contenedor oculto -->
    <div id="accionesUbicacion" style="display:none;">
        <!-- tipo marcacion moderna -->    
        <div class="crow">
            <!-- tipo marcacion moderna --> 
            <div class="radio-input">
                <label>
                    <input type="radio" id="value-1" name="value-radio" value="value-1">
                    <span>Entrada</span>
                </label>
                <label>
                    <input type="radio" id="value-2" name="value-radio" value="value-2">
                    <span>Salida</span>
                </label>
                <span class="selection"></span>
            </div>
        </div>

        <!-- Observacion -->
        <div class="form-group">
            <label for="observaciones">Observaciones</label>
            <textarea name="observaciones" id="observaciones" class="form-control" placeholder="Opcional"></textarea>
        </div>

        <!-- Campos ocultos para lat/lon -->
        <div>
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lon" id="lon">
            <input type="hidden" name="dispositivo" value="web">
        </div>        

        <!-- Botones de accion -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Marcar mi llegada</button>
            <a href="<?= BASE_PATH ?>/rider_asistencia" class="btn btn-secondary">Volver al historial</a>
        </div>
    </div>

</form>     

<script>
    window.puntoControl = <?= json_encode($puntoControl) ?>;
</script>
<!-- Enlace al JS externo -->
<script src="/mercedes/public/js/rider_asistencia.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>