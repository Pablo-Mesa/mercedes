<!-- Filtros -->
<div class="crow">
  <!-- Formulario Filtros -->
  <form method="get" class="mes-selector">
    <input type="date" name="fecha" id="fecha" value="<?= $_GET['fecha'] ?? date('Y-m-d') ?>" onchange="this.form.submit()">
    <select name="grupo_id" id="grupo_id" onchange="this.form.submit()">
      <option value="">Todas</option>
      <?php foreach ($grupos as $g): ?>
        <option value="<?= $g['id_grupo'] ?>" <?= (($_GET['grupo_id'] ?? '') == $g['id_grupo']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($g['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>
  
  <!-- Icono del grupo seleccionado -->
  <?php if (!empty($_GET['grupo_id'])): ?>
    <?php 
      $grupoSeleccionado = $grupos[array_search($_GET['grupo_id'], array_column($grupos, 'id_grupo'))] ?? null;
    ?>
    <?php if (!empty($grupoSeleccionado['icono'])): ?>
      <img src="/mercedes/public/uploads/grupos/<?= htmlspecialchars($grupoSeleccionado['icono']) ?>" 
           alt="<?= htmlspecialchars($grupoSeleccionado['nombre']) ?>" 
           class="icono-grupo-seleccionado">
    <?php endif; ?>
  <?php endif; ?>
  
  <!-- Botón alternar vista tipo tablas -->
  <!-- <button id="btnToggleTabla" class="btn-toggle">
    🔀 <span class="hide-element-text">Cambiar vista</span>
  </button> -->
</div>

<!-- Tabla completa -->      
<div id="tablaCompleta" style="display:none;">
  <!-- Tabla completa -->
  <table class="tabla-produccion-rider">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Empresa</th>
        <th>Turno</th>
        <th>Acción</th>
        <th>Costo</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($producciones)): ?>
        <?php foreach ($producciones as $p): ?>
          <tr>
            <td data-label="Fecha"><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
            <td data-label="Empresa"><?= htmlspecialchars($p['grupo_nombre']) ?></td>
            <td data-label="Turno"><?= htmlspecialchars($p['turno']) ?></td>
            <td data-label="Acción"><?= htmlspecialchars($p['accion']) ?></td>
            <td data-label="Costo">Gs. <?= number_format((float)$p['tarifa_detalle'], 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5">No hay registros para los filtros seleccionados.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Tabla compacta -->
<div id="tablaCompacta">
  <?php if (!empty($producciones)): ?>
    <!-- Tabla compacta para móviles -->
    <table class="tabla-produccion-movil">
      <thead>
        <tr>
          <th>Empresa</th>
          <th>Turno</th>
          <th>Acción</th>
          <th>Costo</th>
        </tr>
      </thead>
      <tbody>
        <?php 
          $totalServicios = 0;
          $totalCostos = 0;
        ?>
        <?php if (!empty($producciones)): ?>
          <?php foreach ($producciones as $p): ?>
            <?php 
              $totalServicios++;
              $totalCostos += (float)$p['tarifa_detalle'];
            ?>
            <tr>
              <!-- Icono del grupo -->
              <td>
                <!-- <?= htmlspecialchars($p['grupo_nombre']) ?> -->
                <img src="/mercedes/public/uploads/grupos/<?= htmlspecialchars($p['grupo_icono']) ?>" 
                    alt="<?= htmlspecialchars($p['grupo_nombre']) ?>" 
                    class="icono-grupo">                 
              </td>
              <td><?= htmlspecialchars($p['turno']) ?></td>
              <td><?= htmlspecialchars($p['accion']) ?></td>
              <td>Gs. <?= number_format((float)$p['tarifa_detalle'], 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4">No hay registros.</td></tr>
        <?php endif; ?>
      </tbody>
      <?php if ($totalServicios > 0): ?>
        <tfoot>
          <tr>
            <td colspan="2"><strong>Total servicios:</strong> <?= $totalServicios ?></td>
            <td colspan="2"><strong>Suma costos:</strong> Gs. <?= number_format($totalCostos, 0, ',', '.') ?></td>
          </tr>
        </tfoot>
      <?php endif; ?>
    </table>
  <?php else: ?>
    <p class="badge-muted">No hay registros...</p>
  <?php endif; ?> 

</div>

<!-- Resumen tabla-resumen-modern-->
<?php if (!empty($resumenGrupos)): ?>
  <table class="tabla-produccion-movil tabla-produccion-movil-block-color">
    <thead>
        <tr>
            <th>Empresa</th>
            <th>Servicios</th>
            <th>Costos</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resumenGrupos as $data): ?>
            <tr>
                <td>
                    <div class="col-empresa">
                        <img src="/mercedes/public/uploads/grupos/<?= htmlspecialchars($data['grupo_icono']) ?>" 
                            alt="<?= htmlspecialchars($data['grupo_nombre']) ?>">
                        <span><?= htmlspecialchars($data['grupo_nombre']) ?></span>
                    </div>
                </td>
                <td>
                    <span class="col-icon"><?= $data['servicios'] ?></span>
                </td>
                <td>
                    <span class="col-icon"> Gs. <?= number_format($data['costos'], 0, ',', '.') ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
  </table>  
<?php endif; ?>