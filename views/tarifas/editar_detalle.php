<div class="tarifas-editar-detalle">
  <h2>Editar detalle de la tarifa: <?= htmlspecialchars($encabezado['titulo']) ?></h2>

  <form action="update_detalle.php?id=<?= $detalle['id'] ?>&id_encabezado=<?= $encabezado['id_encabezado_tarifa'] ?>" method="POST">
    <div>
      <label for="desde">Desde:</label>
      <input type="number" step="0.01" name="desde" id="desde" value="<?= $detalle['desde'] ?>" required>
    </div>

    <div>
      <label for="hasta">Hasta:</label>
      <input type="number" step="0.01" name="hasta" id="hasta" value="<?= $detalle['hasta'] ?>" required>
    </div>

    <div>
      <label for="costo">Costo:</label>
      <input type="number" name="costo" id="costo" value="<?= $detalle['costo'] ?>" required>
    </div>

    <div>
      <label for="disponible">Disponible:</label>
      <input type="checkbox" name="disponible" id="disponible" value="1" <?= $detalle['disponible'] ? 'checked' : '' ?>>
    </div>

    <button type="submit">Actualizar</button>
    <a href="detalles.php?id=<?= $encabezado['id_encabezado_tarifa'] ?>">Cancelar</a>
  </form>
</div>
