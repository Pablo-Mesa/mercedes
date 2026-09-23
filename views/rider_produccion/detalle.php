<div class="detalle-produccion">
  <h3>📅 Producción del <?= date('d/m/Y', strtotime($fecha)) ?></h3>

  <?php if (!empty($producciones)): ?>
    <ul>
      <?php foreach ($producciones as $p): ?>
        <li>
          <?= htmlspecialchars($p['accion']) ?> 
          (Grupo: <?= htmlspecialchars($p['grupo_nombre']) ?>, 
          Turno: <?= htmlspecialchars($p['turno']) ?>, 
          Costo: Gs. <?= number_format((float)$p['tarifa_detalle'], 0, ',', '.') ?>)
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No se encontraron registros para esta fecha.</p>
  <?php endif; ?>
</div>
