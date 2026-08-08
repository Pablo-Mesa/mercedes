<div class="tarifas-nuevo-detalle">
  <h2>Nuevo detalle para la tarifa: <?= htmlspecialchars($encabezado['titulo']) ?></h2>

  <form action="/mercedes/tarifas/nuevo_detalle?id_encabezado=<?= $encabezado['id_encabezado_tarifa'] ?>" method="POST">
    <div>
      <label for="desde">Desde:</label>
      <input type="number" step="0.01" name="desde" id="desde" required>
    </div>

    <div>
      <label for="hasta">Hasta:</label>
      <input type="number" step="0.01" name="hasta" id="hasta" required>
    </div>

    <div>
      <label for="costo">Costo:</label>
      <input type="number" name="costo" id="costo" required>
    </div>

    <div>
      <label for="disponible">Disponible:</label>
      <input type="checkbox" name="disponible" id="disponible" value="1" checked>
    </div>

    <button type="submit">Guardar</button>
    <a href="/mercedes/tarifas/detalles?id=<?= $encabezado['id_encabezado_tarifa'] ?>">Cancelar</a>
  </form>
</div>
