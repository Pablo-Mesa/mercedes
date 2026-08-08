<?php
/**
 * views/riders/index.php
 * Vista pura de listado de riders. Se inyecta dentro del Layout Maestro.
 * Variable disponible: $riders
 */
/** @var array $riders */
/** @var array $tarifas */ 
?>

<!-- header -->
<div class="page-header page-header-actions">
    <!-- Filtros de Producción -->
    <form method="get" action="/mercedes/produccion" class="filtro-produccion">
      <div class="content-filter">
        
        <!-- Fecha -->
        <div class="form-group">
          <label for="fecha">Fecha:</label>
          <input type="date" name="fecha" id="fecha" 
                value="<?= htmlspecialchars($_GET['fecha'] ?? date('Y-m-d')) ?>">
        </div>

        <!-- Turno -->
        <div class="form-group">
          <label for="turno">Turno:</label>
          <select name="turno" id="turno">
            <option value="">-- Todos --</option>
            <option value="1" <?= (($_GET['turno'] ?? '') == '1') ? 'selected' : '' ?>>🌞 Diurno</option>
            <option value="2" <?= (($_GET['turno'] ?? '') == '2') ? 'selected' : '' ?>>🌙 Nocturno</option>
          </select>
        </div>

        <!-- Rider -->
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

        <!-- Acción del Rider -->
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

        <!-- Botones -->
        <div class="form-actions">
          <button type="submit" class="btn-filtrar">Filtrar</button>
          <a href="/mercedes/produccion" class="btn-reset">Ver todas</a>
          <a href="#" class="btn btn-primary btn-nuevo" id="btnAbrirModal" >+ Agregar produccion</a>          
        </div>

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
    </form>

</div>

<!-- tabla -->
<div class="panel table-panel">
    <!-- Listado de producciones -->
    <table class="data-table">
      <thead>
          <tr>            
            <th>Fecha</th>
            <th>Rider</th>
            <th>Turno</th>                    
            <th>Tarifa</th>          
            <th>Acciones</th>
          </tr>
      </thead>
      <tbody>
          <?php if (!empty($producciones)): ?>
          <?php foreach ($producciones as $p): ?>
              <tr>

                <td>
                  <span class="fecha">
                    📅 <?= date('d-m-Y', strtotime($p['fecha_creacion'])) ?>
                  </span>
                  <span class="hora-badge">
                    ⏰ <?= date('H:i', strtotime($p['fecha_creacion'])) ?>
                  </span>
                </td>
                <td class="d-flex align-center gap-8 text-center">
                  <img src="/mercedes/public/uploads/riders/<?= $p['rider_foto'] ?? 'default.png' ?>" 
                        alt="<?= htmlspecialchars($p['rider_nombre']) ?>" class="avatar-sm">
                  <span><?= htmlspecialchars($p['rider_nombre']) ?></span>
                </td>
                <td>
                  <?php if ($p['turno'] === 'Diurno'): ?>
                    🌞 <?= htmlspecialchars($p['turno']) ?>
                  <?php else: ?>
                    🌙 <?= htmlspecialchars($p['turno']) ?>
                  <?php endif; ?>
                </td>                            
                <td> Gs. <?= number_format((float)$p['tarifa_detalle'], 0, ',', '.') ?> </td>              
                <!-- <td><?= htmlspecialchars($p['usuario_nombre']) ?></td> -->              
                <td>
                    <a href="/mercedes/produccion/delete?id=<?= $p['id'] ?>" 
                      onclick="return confirm('¿Eliminar producción?')"
                      class="btn btn-small btn-danger-outline">
                      Eliminar                   
                    </a>
                </td>
              </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr><td colspan="5">No hay producciones registradas.</td></tr>
          <?php endif; ?>
      </tbody>
    </table>
</div>

<!-- modal produccion -->
<div id="modalProduccion" class="modal">
  <div class="modal-contenido">

    <span id="btnCerrarModal" class="cerrar">&times;</span>
    <h3>Registrar Producción</h3>

    <!-- Aquí va tu formulario -->
    <form method="post" action="/mercedes/produccion/store">

      <div class="form-group">
        <label for="rider">Rider:</label>                
        <select name="id_rider" id="rider" required>
          <option value="">-- Seleccionar Rider --</option>
          <?php foreach ($riders as $r): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>            
          <?php endforeach; ?>
        </select>        
      </div>

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

      <div class="form-group">
        <label for="turnoSwitch">Turno:</label>
        <input type="hidden" name="id_turno" id="turnoHidden" value="1"> <!-- valor por defecto Diurno -->
        <label class="switch">
          <input type="checkbox" id="turnoSwitch">
          <span class="slider"></span>
        </label>
        <span id="turnoLabel">Diurno</span>
      </div>

      <div class="form-actions">
        <button class="btn btn-warning" type="submit">Registrar Producción</button>              
      </div>
      
    </form>

  </div>
</div>