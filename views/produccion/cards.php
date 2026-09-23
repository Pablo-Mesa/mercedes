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

$produccionesResumen = $produccionesResumen ?? [];
$riders = $riders ?? [];
$tarifas = $tarifas ?? [];
$grupos = $grupos ?? [];
$acciones = $acciones ?? [];
$turnos = $turnos ?? [];
$resumenGlobal = $resumenGlobal ?? [];
$turnoActualId = $turnoActualId ?? null;
$grupoIcono = $grupoIcono ?? null;
$detalleProduccion = $detalleProduccion ?? [];
?>

<!-- encabezdo de la vista -->
<div class="page-header page-header-actions w-full">    
  <form method="get" action="/mercedes/produccion" class="filtro-produccion">
    <!-- barra para aplicar filtros -->  
    <div class="filter-bar">      

      <!-- Fecha -->
      <div class="filter-item">
        <input type="date" name="fecha" id="fecha" 
              value="<?= htmlspecialchars($fechaSeleccionada) ?>">
      </div>

      <!-- Botón Filtrar -->
      <div class="filter-item">
        <button type="submit" class="btn-filtrar">
          <span class="btn-text">Filtrar</span>
          <span class="btn-icon">🔍</span>
        </button>
      </div>
      <!-- Botón Agregar Producción -->
      <div class="filter-item">
        <a href="#" class="btn btn-nuevo" id="btnAbrirModal">
          <span class="btn-text">+ Agregar producción</span>
          <span class="btn-icon">➕</span>
        </a>
      </div>
      <!-- Switch de vista -->
      <div class="filter-item">
        <button id="toggleVista" class="btn-switch">
          <span class="btn-text">Ver en tabla</span>
          <span class="btn-icon">📊</span>
        </button>
      </div>
    </div>
  </form>
</div>

<!-- grid tarjetas -->
<div class="cards-grid w-full">
  <?php foreach ($produccionesResumen as $grupo): ?>    
    <?php if ($_SESSION['user_role'] === 'admin' || $grupo['grupo_id'] == $_SESSION['grupo_id']): ?>
      <?php foreach ($grupo['riders'] as $p): ?>
        <!-- tarjeta -->
        <div class="card">
        
          <!-- avatar -->
          <img src="/mercedes/public/uploads/riders/<?= $p['rider_foto'] ?? 'default.png' ?>" class="card-avatar">
          
          <!-- nombre rider -->
          <h3><?= htmlspecialchars($p['rider_nombre']) ?></h3>
          
          <!-- servicios -->
          <div class="crow">
            <p>
              <span class="badge-hora mb-1">
                📊 Servicios: <?= count($p['facturas']) ?>
              </span>
            </p>
            <button class="btn-transparent btn-detalle" 
                    data-rider="<?= $p['rider_id'] ?>" 
                    data-rider-name="<?= htmlspecialchars($p['rider_nombre']) ?>" >
              🔍
            </button>
          </div>
          
          <!-- pendientes -->
          <div class="crow mb-1">
            <p>
              <?php 
                $pendientes = 0;
                foreach ($p['facturas'] as $f) {
                  if ((int)$f['rendicion'] === 0) {
                    $pendientes++;
                  }
                }
              ?>
              <?php if ($pendientes === 0): ?>
                <span class="badge-hora " style="background: linear-gradient(135deg, #C8E6C9, #A5D6A7);">
                  ✅ Pendientes: 0
                </span>
              <?php else: ?>
                <span class="badge-hora " style="background: linear-gradient(135deg, #FFCC80, #FFF59D);">
                  ⚠️ <strong>Pendientes: <?= $pendientes ?></strong>
                </span>
              <?php endif; ?>
            </p>
            <?php if ($pendientes > 0): ?>            
              <button class="btn-transparent" data-rider="">⚠️</button>
            <?php endif; ?>          
          </div>
          
          <!-- monto total -->
          <div class="crow">
            <p>
              <span class="badge-hora">
                <span class="img-nav-icon" >
                    <?= render_icon('totales', 'nav-icon') ?>
                </span>
                Total: Gs. <strong><?= number_format((float)$p['total_tarifa'], 0, ',', '.') ?></strong>
              </span>
            </p>
            <button class="btn-transparent btn-agregar" data-rider="<?= $p['rider_id'] ?>">➕</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endforeach; ?>
</div>

<!-- tabla resumen y detalle -->
<div id="tablaProduccion" class="tabla-produccion" style="display:none;">
  <?php foreach ($produccionesResumen as $grupo): ?>
    <?php if ($_SESSION['user_role'] === 'admin' || $grupo['grupo_id'] == $_SESSION['grupo_id']): ?>
      <div class="content-wrap">
        <div class="modal-header-icon">
          <?php if (!empty($grupo['grupo_icono'])): ?>
            <img src="/mercedes/public/uploads/grupos/<?= htmlspecialchars($grupo['grupo_icono']) ?>" 
                alt="Icono <?= htmlspecialchars($grupo['grupo_nombre']) ?>" 
                class="grupo-icono">
          <?php endif; ?>
          <h2 class="grupo-titulo"><?= htmlspecialchars($grupo['grupo_nombre']) ?></h2>
        </div>
        <div class="wrapper">
          <?php foreach ($grupo['riders'] as $p): ?>
            <div class="rider-col">
              <h3><?= htmlspecialchars($p['rider_nombre']) ?></h3>
              
              <strong>Facturas</strong><br>
              <table class="facturas-table">
                <thead>
                  <tr>
                    <th>Montos</th>
                    <th>A</th>
                    <th>R</th>
                  </tr>
                </thead>
                <?php foreach ($p['facturas'] as $f): ?>
                  <tr>
                    <td class="monto"><?= number_format($f['monto'], 0, ',', '.') ?></td>
                    <td class="icono"><?= $f['accion_icono'] ?></td>
                    <td class="estado"><?= $f['rendicion'] ? '✅' : '⚠️' ?></td>
                  </tr>
                <?php endforeach; ?>
              </table>
              <hr class="nav-divider-dark"> 
              
              <strong>Tarifas</strong><br>
              <table class="tarifas-table">
                <?php $i = 1; ?>
                <?php foreach ($p['tarifas'] as $t): ?>
                  <tr>
                    <td class="tarifa-num"><?= $i++ ?>)</td>
                    <td class="tarifa-costo"><?= number_format($t['costo'], 0, ',', '.') ?></td>
                  </tr>
                <?php endforeach; ?>
              </table>
              <hr class="nav-divider-dark">
              
              <strong>Producción:</strong><br>
              <span class="span-monto"><?= number_format($p['total_tarifa'], 0, ',', '.') ?></span>
            </div>
            <!-- <div class="separator-vertical"></div> -->            
          <?php endforeach; ?>          
        </div>                
      </div>                
    <?php endif; ?>
  <?php endforeach; ?>
</div>

<!-- resumen produccion -->
<?php if (!empty($resumenGlobal)): ?>
  <div class="resumen-produccion">    
    <p>
      <span class="img-nav-icon" ><?= render_icon('riders', 'nav-icon') ?></span> 
      Deliverys activos: <?= $resumenGlobal['deliverys_activos'] ?>
    </p>
    <div class="rowCol">
      <p>
        <span class="img-nav-icon" ><?= render_icon('warning', 'nav-icon') ?></span>
        Pendientes totales: <?= $resumenGlobal['pendientes_totales'] ?> 
      </p>
      <p>
        <span class="img-nav-icon" ><?= render_icon('pos', 'nav-icon') ?></span>
        Cobros con POS: <?= $resumenGlobal['pendientes_pos'] ?> 
      <p>
        <span class="img-nav-icon" ><?= render_icon('tarifas', 'nav-icon') ?></span>
        Cobros en efectivo: <?= $resumenGlobal['pendientes_efectivo'] ?>
      </p>
    </div>
    <p>
      <span class="img-nav-icon" ><?= render_icon('produccion', 'nav-icon') ?></span>
      Servicios totales: <?= $resumenGlobal['total_servicios'] ?> 
      (🌞 <?= $resumenGlobal['mañana'] ?> | 🌙 <?= $resumenGlobal['tarde'] ?> | 🌃 <?= $resumenGlobal['noche'] ?>)</p>
    <p>
      <span class="img-nav-icon" ><?= render_icon('totales', 'nav-icon') ?></span>
      Total producción: Gs. <?= number_format((float)$resumenGlobal['total_produccion'], 0, ',', '.') ?>
    </p>
  </div>
<?php endif; ?>

<!-- modal produccion -->
<div id="modalProduccion" class="modal">
  <div class="modal-contenido">

    <!-- header del modal -->
    <div class="modal-header">  
        <div class="">
          <?php if ($_SESSION['user_role'] === 'operaciones' && !empty($grupoIcono)): ?>
              <div class="modal-header-icon">
                  <img src="<?= htmlspecialchars($grupoIcono) ?>" 
                      alt="Grupo" 
                      class="grupo-icon">
              </div>
          <?php endif ?>
          <h3 class="modal-title">Registrar Producción</h3>
        </div>  
        <span id="btnCerrarModal" class="cerrar">&times;</span>
    </div>

    <!-- formulario -->
    <form id="formProduccion" action="/mercedes/produccion/store" method="post">
      
      <!-- fecha y turno -->
      <div class="content-generic">
        <!-- fecha -->
        <div class="w-48 border-radius">
          <label for="fechaHoraViaje" class="form-group-label">Fecha y hora del viaje:</label>
           <div> 
            <input type="datetime-local" name="fecha_creacion" id="fechaHoraViaje" required class="style-select">
          </div>
        </div>

        <!-- turno -->
        <div class="w-half border-radius">
          <label for="turno" class="form-group-label">Turno:</label>
          <!-- turno-wrapper -->
          <div class="turno-wrapper"> 
            <select name="id_turno" id="turno" required class="style-select">
              <option value="">-- Seleccionar Turno --</option>
              <?php foreach ($turnos as $t): ?>
                <option value="<?= $t['id'] ?>" data-icon="<?= $t['url_icono'] ?>"
                  <?= ($t['id'] == $turnoActualId) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($t['turno']) ?> <!-- (<?= $t['hora_inicio'] ?> - <?= $t['hora_fin'] ?>) --> 
                </option>
              <?php endforeach; ?>
            </select>
            <span id="turnoIconPreview" class="icon"></span>
          </div>
        </div>
      </div>

      <!-- lista grupos -->
      <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <div class="form-group">
          <label for="grupo">Grupo:</label>
          <select name="id_grupo" id="grupo" required>
            <option value="">-- Seleccionar Grupo --</option>
            <?php foreach ($grupos as $g): ?>
              <option value="<?= $g['id_grupo'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php else: ?>
        <input type="hidden" name="id_grupo" value="<?= $_SESSION['grupo_id'] ?>">
      <?php endif; ?>

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

      <!-- total factura y vuelto-->
      <div>            
        <!-- total factura -->
        <div class="form-group">
          <label for="totalFactura">Total factura:</label>
          <input type="number" step="0.01" min="0" name="total_factura" id="totalFactura" required>
        </div>

        <!-- campos vuelto -->      
        <div id="efectivoFields" class="hidden">
          <div class="w-45">
            <label for="vuelto">Vuelto:</label>
            <input type="number" step="0.01" min="0" name="vuelto" id="vuelto">
          </div>
        </div>
      </div>

      <!-- accion de rider -->      
      <div class="form-group">
        <label for="accion">Acción del Rider:</label>        
        <select name="id_accion" id="frmProduccion_accion" required>
          <?php foreach ($acciones as $a): ?>
            <option value="<?= $a['id_accion'] ?>" 
                    data-codigo="<?= $a['codigo'] ?>"
                    data-requiere-factura="<?= $a['requiere_factura'] ?>"
                    <?= $a['id_accion'] == 1 ? 'selected' : '' ?>>
              <?= htmlspecialchars($a['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
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
    <!-- encabezado modal -->
    <div class="content-b">
      <div class="disposicion_a0">
        <h3><span id="rider_name" class=""></span></h3>
        <small><span id="rider_production_date" class="badge-hora"></span></small>
        <!-- Botón para activar edición -->
        <button id="toggleEdit" class="btn-toggle">✏️ Editar tabla</button>
      </div>
      <div>
        <span id="closeModal" class="close">&times;</span>
      </div>       
    </div>    
    <!-- tabla -->
    <table id="detalleTable">  
      <thead>
        <tr>
          <th>Empresa</th> <!-- 👈 mostrar grupo solo si es admin -->
          <th>Turno</th>
          <th>Hora</th>          
          <th>Tarifa</th>         
          <th>Acción</th>
          <th>Rendición</th>
          <th class="acciones-col hidden">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($detalleProduccion)): ?>
          <?php foreach ($detalleProduccion as $fila): ?>
            <?php if ($_SESSION['user_role'] === 'admin' || $fila['id_grupo'] == $_SESSION['grupo_id']): ?>
              <tr>
                <td><?= htmlspecialchars($fila['grupo_nombre']) ?></td>
                <td><?= htmlspecialchars($fila['turno']) ?></td>
                <td><?= date('H:i', strtotime($fila['fecha_creacion'])) ?></td>
                <td><?= number_format($fila['tarifa_detalle'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($fila['accion_nombre']) ?></td>
                <td><?= $fila['rendicion'] ? '✅' : '⚠️' ?></td>
                <td class="acciones-col hidden">
                  <button class="btn-editar" data-id="<?= $fila['id'] ?>">✏️</button>
                  <button class="btn-eliminar" data-id="<?= $fila['id'] ?>">🗑️</button>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>