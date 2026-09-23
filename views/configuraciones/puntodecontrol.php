<?php
/**
 * views/configuraciones/puntodecontrol.php
 * Vista de Punto de Control. Se inyecta dentro del Layout Maestro.
 * Variable disponible: $punto (array con datos del punto de control o null)
 */
?>
<div class="page-header page-header-actions">
    <div>
        <p>Define el lugar físico desde donde parte la flota de deliverys.</p>
    </div>

    <?php if ($punto): ?>
        <button id="btnEditar" class="btn btn-primary">Editar</button>
    <?php endif; ?>
</div>

<div class="panel form-panel-medium">
    <div class="panel-body">

        <?php if ($punto): ?>
            <form id="formPuntoControl" class="form-panel-medium">

                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" 
                           value="<?= htmlspecialchars($punto['titulo']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="barrio">Barrio</label>
                    <input type="text" id="barrio" name="barrio" 
                           value="<?= htmlspecialchars($punto['barrio']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" 
                           value="<?= htmlspecialchars($punto['direccion']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="contacto">Contacto</label>
                    <input type="text" id="contacto" name="contacto" 
                           value="<?= htmlspecialchars($punto['contacto']) ?>" disabled>
                </div>

                <!-- Mapa -->
                <div id="map" class="map"></div>
                <input type="hidden" id="latitud" name="latitud" value="<?= $punto['latitud'] ?>">
                <input type="hidden" id="longitud" name="longitud" value="<?= $punto['longitud'] ?>">

                <div class="form-group">
                    <label for="radio">Radio de cobertura</label>
                    <select id="radio" name="radio" disabled>
                        <option value="50"  <?= ($punto['radio_metros']==50 ? 'selected' : '') ?>>50 m</option>
                        <option value="100" <?= ($punto['radio_metros']==100 ? 'selected' : '') ?>>100 m</option>
                        <option value="200" <?= ($punto['radio_metros']==200 ? 'selected' : '') ?>>200 m</option>
                        <option value="500" <?= ($punto['radio_metros']==500 ? 'selected' : '') ?>>500 m</option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="d-flex flex-row justify-between align-center">
                    <button type="submit" id="btnGuardar" class="btn btn-success" disabled>Guardar</button>

                    <?php if (!empty($punto['fecha_creacion'])): ?>
                        <?php 
                            $fechaObj = new DateTime($punto['fecha_creacion']);
                            $fecha = $fechaObj->format('d-m-Y');   // dd-mm-yyyy
                            $hora  = $fechaObj->format('H:i:s');   // hh:mm:ss

                            $fechaObjAct = new DateTime($punto['fecha_actualizacion']);
                            $fechaAct = $fechaObjAct->format('d-m-Y');   // dd-mm-yyyy
                            $horaAct  = $fechaObjAct->format('H:i:s');   // hh:mm:ss
                        ?>
                        <div class="meta-box">
                            <p>📅 Fecha creación: <span><?= $fecha ?></span> # ⏰ Hora creación: <span><?= $hora ?></span></p>
                            <p>📅 Utlima actualizacion: <span><?= $fechaAct ?></span> # ⏰ Utlima actualizacion: <span><?= $horaAct ?></span></p>
                        </div>
                    <?php endif; ?>                
                </div>

            </form>
        <?php else: ?>
            <p class="text-center text-muted">
                No se ha configurado aún el Punto de Control.<br>
                Inserta un registro inicial en la base de datos para comenzar.
            </p>
        <?php endif; ?>

    </div>
</div>
