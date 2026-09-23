<div class="page-header">
  <h2>Nuevo detalle para la tarifa: <?= htmlspecialchars($encabezado['titulo']) ?></h2>
</div>

<div class="tarifas-nuevo-detalle"></div>

  
<div class="panel form-panel-medium">
  <form class="" action="/mercedes/tarifas/nuevo_detalle?id_encabezado=<?= $encabezado['id_encabezado_tarifa'] ?>" method="POST">
    
    <div class="form-group">
      <label for="desde">Desde:</label>
      <input type="number" step="0.01" name="desde" id="desde" required>
    </div>

    <div class="form-group">
      <label for="hasta">Hasta:</label>
      <input type="number" step="0.01" name="hasta" id="hasta" required>
    </div>

    <div class="form-group">
      <label for="costo">Costo:</label>
      <input type="number" name="costo" id="costo" required>
    </div>

    <div class="disposicion_a0 mt-1">
      <label for="disponible" class="formato-base-label">Disponible:</label>
      <input type="checkbox" name="disponible" id="disponible" value="1" checked>
    </div>

    <div class="form-actions">
      <button type="submit">Guardar</button>
      <a href="/mercedes/tarifas/detalles?id=<?= $encabezado['id_encabezado_tarifa'] ?>">Cancelar</a>
    </div>    
    
  </form>
</div>
