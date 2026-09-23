<!-- encabezdo de la vista -->
<div class="page-header page-header-actions w-full">    
  <form method="get" action="/mercedes/invitados" class="filtro-produccion">
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
    </div>
  </form>
</div>

<!-- tabla produccion -->
<div class="invitados-produccion">
  <?php if (!empty($produccionesResumen)): ?>
    <?php foreach ($produccionesResumen as $grupo): ?>
      <div class="content-wrap">
        <div class="modal-header-icon">
          <?php if (!empty($grupo['grupo_icono'])): ?>
            <img src="/mercedes/public/uploads/grupos/<?= htmlspecialchars($grupo['grupo_icono']) ?>" 
                alt="Icono <?= htmlspecialchars($grupo['grupo_nombre']) ?>" 
                class="icono-grupo-seleccionado">
          <?php endif; ?>
          <h2 class="grupo-titulo"><?= htmlspecialchars($grupo['grupo_nombre']) ?></h2>
        </div>
        <div class="wrapper">
          <?php foreach ($grupo['riders'] as $p): ?>      
            <div class="rider-col">
              <h3 class="span-monto"><?= htmlspecialchars($p['rider_nombre']) ?></h3>
              <table class="produccion-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Tarifa</th>
                    <th>R</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; ?>
                  <?php foreach ($p['tarifas'] as $index => $t): ?>
                    <tr>
                      <td class="tarifa-num"><?= $i++ ?>.</td>
                      <td class="tarifa-costo-invitado"><?= number_format($t['costo'], 0, ',', '.') ?></td>
                      <td class="estado">
                        <?php 
                          // Tomar rendición de la factura correspondiente
                          $rendicion = $p['facturas'][$index]['rendicion'] ?? 0;
                          echo $rendicion ? '✅' : '⚠️';
                        ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <hr class="nav-divider-dark">
              <strong>Total Gs.: </strong>
              <span class="span-monto"><?= number_format($p['total_tarifa'], 0, ',', '.') ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="alert alert-info">
      No se encontraron producciones para la fecha seleccionada.
    </div>
  <?php endif; ?>
</div>
