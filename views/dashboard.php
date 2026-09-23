<?php
  /**
   * views/dashboard.php
   * Vista pura del panel principal. Se inyecta dentro de views/layouts/main.php.
   */
?>

<?php
  $meses = [
      1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
      5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
      9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
  ];
?>

<!-- barra superior horizontal -->
<div class="row-bar">
  <!-- titulo punto de control -->
  <p class="p-p0">
    <span class="font-basic">
      <span class="img-nav-icon" ><?= render_icon('puntodecontrol', 'nav-icon') ?></span>
      Punto de control:
      <span class="badge-filtro">
        <?= $puntoDeControl['titulo'] ?? 'No asignado' ?>
      </span>
    </span>
  </p>
  <!-- formulario (mes/empres) -->
  <form method="get" class="mes-selector">
    <!-- lista meses -->
    <label for="mes">
      <span class="img-nav-icon" ><?= render_icon('calendario', 'nav-icon') ?></span> Seleccionar mes:
    </label>
    <select name="mes" id="mes" onchange="this.form.submit()" class="select-grupo">
        <?php for ($m = 1; $m <= date('n'); $m++): ?>
            <option value="<?= $m ?>" <?= ($m == (int)($_GET['mes'] ?? date('n'))) ? 'selected' : '' ?>>
                <?= $meses[$m] ?>
            </option>
        <?php endfor; ?>
    </select>     

    <!-- lista de empresas -->
    <label for="grupo_id">
      <span class="img-nav-icon" ><?= render_icon('empresas', 'nav-icon') ?></span> Empresa:      
    </label>
    <select name="grupo_id" id="grupo_id" onchange="this.form.submit()" class="select-grupo">
      <option value="">Todas</option>
      <?php foreach ($grupos as $g): ?>
          <option value="<?= $g['id_grupo'] ?>" 
            <?= (($_GET['grupo_id'] ?? '') == $g['id_grupo']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($g['nombre']) ?>
          </option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<!-- Tabla diaria -->
 <div class="tabla-wrapper">
  <table class="tabla-dashboard">
    <thead>
      <tr>
        <th><span class="img-nav-icon" ><?= render_icon('empresas', 'nav-icon') ?></span></th>
        <th><span class="img-nav-icon" ><?= render_icon('calendario', 'nav-icon') ?></span></th>
        <th><span class="img-nav-icon" ><?= render_icon('riders', 'nav-icon') ?></span></th>
        <th colspan="3"><span class="img-nav-icon" ><?= render_icon('turnos', 'nav-icon') ?></span> Turnos</th>
        <th><span class="img-nav-icon" ><?= render_icon('tarifas', 'nav-icon') ?></span></th>
      </tr>
      <tr>
        <th>Empresa</th>
        <th>Día</th>        
        <th>Deliverys activos</th>
        <th>Mañana</th>
        <th>Tarde</th>
        <th>Noche</th>
        <th>Total costo</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($produccionesPorDia as $dia): ?>
        <tr>
          <td>
           <img src="/mercedes/public/uploads/grupos/<?= htmlspecialchars($dia['grupo_icono']) ?>" 
                 alt="icono grupo" style="height:20px;vertical-align:middle;margin-right:5px;">            
            <?= htmlspecialchars($dia['grupo_nombre']) ?>            
          </td>
          <td><?= date('d/m/Y', strtotime($dia['dia'])) ?></td>          
          <td><?= (int)$dia['deliverys_activos'] ?></td>
          <td><?= (int)$dia['mañana'] ?></td>
          <td><?= (int)$dia['tarde'] ?></td>
          <td><?= (int)$dia['noche'] ?></td>
          <td>Gs. <?= number_format((float)$dia['total_costo'], 0, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>      
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2"><strong>Totales</strong></td>
        <td><?= $totales['deliverys_activos'] ?></td>
        <td><?= $totales['mañana'] ?></td>
        <td><?= $totales['tarde'] ?></td>
        <td><?= $totales['noche'] ?></td>
        <td>Gs. <?= number_format((float)$totales['total_costo'], 0, ',', '.') ?></td>
      </tr>
    </tfoot>
  </table>

</div>

<!-- Resumen mensual -->
<div class="resumen-produccion">
    <!-- Badge de grupo con icono -->
    <p>
      
        <span class="img-nav-icon" ><?= render_icon('empresas', 'nav-icon') ?></span> Empresa: 
        <?php if ($grupoSeleccionado): ?>
          <span class="badge-filtro">
            <?php 
              // Buscar el grupo seleccionado en el array $grupos
              $grupoData = array_filter($grupos, fn($g) => $g['id_grupo'] == $grupoSeleccionado);
              $grupoData = reset($grupoData);
            ?>
            <img src="/mercedes/public/uploads/grupos/<?= htmlspecialchars($grupoData['icono']) ?>" 
                alt="icono grupo" class="badge-icon">
            <?= htmlspecialchars($grupoData['nombre']) ?>
          </span>
        <?php else: ?>
          Todas las empresas
        <?php endif; ?>      
    </p>
    <p><span class="img-nav-icon" ><?= render_icon('riders', 'nav-icon') ?></span> Deliverys activos en el mes: <?= $resumenMensual['deliverys_activos_mes'] ?></p>
    <p><span class="img-nav-icon" ><?= render_icon('envios', 'nav-icon') ?></span> Total envíos en el mes: <?= $resumenMensual['total_envios_mes'] ?></p>
    <p"><span class="img-nav-icon" ><?= render_icon('tarifas', 'nav-icon') ?></span> Costo total del mes: Gs. <?= number_format((float)$resumenMensual['total_costo_mes'], 0, ',', '.') ?></p>
</div>