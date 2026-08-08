<?php
/**
 * views/riders/index.php
 * Vista pura de listado de riders. Se inyecta dentro del Layout Maestro.
 * Variable disponible: $riders
 */
/** @var array $riders */
/** @var array $tarifas */

  // Configurar la zona horaria de Paraguay
  date_default_timezone_set('America/Asuncion');
  // Si el usuario ya pasó fecha por GET, usamos esa.
  // Si no, usamos la fecha actual de Paraguay.
  $fechaSeleccionada = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : date('Y-m-d');
?>

<!-- Filtros de Producción -->
<div class="page-header page-header-actions w-full">    
    <form method="get" action="/mercedes/produccion" class="filtro-produccion">

      <div class="content-filter">
        
        <!-- Fecha -->
        <div class="filtro-fecha">
          <!-- <label for="fecha">Fecha:</label> -->
          <input type="date" name="fecha" id="fecha" 
                value="<?= htmlspecialchars($fechaSeleccionada) ?>">
        </div>

        <!-- Botones -->
        <div class="form-actions">
          <button type="submit" class="btn-filtrar">Filtrar</button>
          <!--<a href="/mercedes/produccion" class="btn-reset">Ver todas</a>-->
          <a href="#" class="btn btn-primary btn-nuevo" id="btnAbrirModal" >+ Agregar produccion</a>          
        </div>

        <!-- Turno 
        <div class="form-group">
          <label for="turno">Turno:</label>
          <select name="turno" id="turno">
            <option value="">-- Todos --</option>
            <option value="1" <?= (($_GET['turno'] ?? '') == '1') ? 'selected' : '' ?>>🌞 Diurno</option>
            <option value="2" <?= (($_GET['turno'] ?? '') == '2') ? 'selected' : '' ?>>🌙 Nocturno</option>
          </select>
        </div>
        -->
        <!-- Rider 
        <div class="form-group">
          <label for="rider">Rider:</label>
          <select name="rider" id="rider">
            <option value="">-- Todos --</option>
            <?php foreach ($riders as $r): ?>
              <option value="<?= $r['id'] ?>" <?= (($_GET['rider'] ?? '') == $r['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($r['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        -->      
        <!-- Acción del Rider 
        <div class="form-group">
          <label for="accion">Acción del Rider:</label>          
          <select name="accion" id="accion">
            <option value="">-- Todas --</option>
            <?php foreach ($acciones as $a): ?>
              <option value="<?= $a['id_accion'] ?>" <?= (($_GET['accion'] ?? '') == $a['id_accion']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        -->              
        <!--
        <div class="view-switch">
            <a href="/mercedes/produccion?vista=tabla" 
              class="switch-btn <?= ($_GET['vista'] ?? 'tabla') === 'tabla' ? 'active' : '' ?>">
              📊 Tabla
            </a>
            <a href="/mercedes/produccion?vista=tarjetas" 
              class="switch-btn <?= ($_GET['vista'] ?? 'tabla') === 'tarjetas' ? 'active' : '' ?>">
              🗂️ Tarjetas
            </a>
          </div>
      
        </div>
        -->
      
    </form>
</div>

<!-- grid tarjetas -->
<div class="cards-grid w-full">
  <?php foreach ($produccionesResumen as $p): ?>
    <div class="card">
      <img src="/mercedes/public/uploads/riders/<?= $p['rider_foto'] ?? 'default.png' ?>" class="card-avatar">
      <h3><?= htmlspecialchars($p['rider_nombre']) ?></h3>
      <!-- 
      <span class="badge turno"><?= $p['turno'] === 'Diurno' ? '🌞 Diurno' : '🌙 Nocturno' ?></span>
      -->
      <!--
      <span class="badge accion"><?= htmlspecialchars($p['accion_nombre']) ?></span>
      -->
      <!-- 
      <span class="badge-rendicion <?= $p['rendicion'] ? 'rendido' : 'pendiente' ?>">
        <?= $p['rendicion'] ? 'Rendido' : 'Pendiente' ?>
      </span>
      -->
      <!-- 
      <label class="switch">
        <input type="checkbox" 
              class="toggle-rendicion" 
              data-id="<?= $p['rider_id'] ?>" 
              <?= $p['rendicion'] ? 'checked' : '' ?>>
        <span class="slider"></span>
      </label>
      -->

      <p><span class="badge-hora mb-1">📊 Servicios: <?= $p['servicios'] ?></span></p>
      
      <p><span class="badge-hora">💰 Total: Gs. <strong><?= number_format((float)$p['total_tarifa'], 0, ',', '.') ?></strong></span></p>
      <div class="form-actions">        
        <button class="btn btn-success btn-agregar" data-rider="<?= $p['rider_id'] ?>">Agregar</button>
        <button class="btn btn-info btn-detalle" data-rider="<?= $p['rider_id'] ?>" data-rider-name="<?= htmlspecialchars($p['rider_nombre']) ?>" >Detalles</button>        
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- modal produccion -->
<div id="modalProduccion" class="modal">
  <div class="modal-contenido">

    <!-- titulo y accion cerrar ventana -->
    <span id="btnCerrarModal" class="cerrar">&times;</span>
    <h3>Registrar Producción</h3>

    <!-- formulario -->
    <form id="formProduccion">
      
      <!-- fecha y turno -->
      <div class="content-generic">
        <!-- fecha -->
        <div class="form-group">
          <label for="fechaHoraViaje">Fecha y hora del viaje:</label>
          <input type="datetime-local" name="fecha_creacion" id="fechaHoraViaje" required>
        </div>

        <!-- turno -->
        <div class="form-group">
          <label for="turno">Turno:</label>
          <div class="turno-wrapper">
            <select name="id_turno" id="turno" required>
              <option value="">-- Seleccionar Turno --</option>
              <?php foreach ($turnos as $t): ?>
                <option value="<?= $t['id'] ?>" data-icon="<?= $t['url_icono'] ?>"
                  <?= ($t['id'] == $turnoActualId) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($t['turno']) ?> (<?= $t['hora_inicio'] ?> - <?= $t['hora_fin'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <span id="turnoIconPreview"></span>
          </div>
        </div>
      </div>

      <!-- lista riders -->
      <div class="form-group">
        <label for="rider">Rider:</label>
        <select name="id_rider" id="rider" required>
          <option value="">-- Seleccionar Rider --</option>
          <?php foreach ($riders as $r): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
          <?php endforeach; ?>
        </select>      
      </div>
      
      <!-- tarifas -->      
      <div class="form-group">
        <label for="tarifa">Tarifas:</label>
        <select name="id_detalle_tarifa" id="tarifa" required>
          <option value="">-- Seleccionar Tarifa --</option>
          <?php foreach ($tarifas as $t): ?>
            <option value="<?= $t['id'] ?>">
              <?= number_format((float)$t['costo'], 0, ',', '.') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- accion de rider -->      
      <div class="form-group">
        <label for="accion">Acción del Rider:</label>        
        <select name="id_accion" id="frmProduccion_accion" required>
          <?php foreach ($acciones as $a): ?>
            <option value="<?= $a['id_accion'] ?>" 
                    data-requiere-factura="<?= $a['requiere_factura'] ?>"
                    <?= $a['id_accion'] == 1 ? 'selected' : '' ?>>
              <?= htmlspecialchars($a['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- campos monto factura y vuelto -->      
      <div id="efectivoFields" class="hidden">
        <div class="form-group">
          <label for="totalFactura">Total factura:</label>
          <input type="number" step="0.01" name="total_factura" id="totalFactura">
        </div>

        <div class="form-group">
          <label for="vuelto">Vuelto:</label>
          <input type="number" step="0.01" name="vuelto" id="vuelto">
        </div>
      </div>

      <!-- botones acciones en el formulario -->      
      <div class="form-actions">
        <button class="btn btn-warning" type="submit">Registrar Producción</button>              
      </div>
      
    </form>

  </div>
</div>

<!-- Detalles Producción -->
<div id="detalleModal" class="modal">
  <div class="modal-content">
    <span id="closeModal" class="close">&times;</span>
    <h3><span id="rider_name" class=""></span></h3>   
    <table id="detalleTable">  
      <thead>
        <tr>
          <th>Hora</th>          
          <th>Turno</th>
          <th>Tarifa</th>
          <th>Acción</th>
          <th>Rendición</th> <!-- 👈 nueva columna -->
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <!-- cada fila se llena vía JS -->
      </tbody>
    </table>
  </div>
</div>
