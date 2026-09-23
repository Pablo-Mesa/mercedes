<div class="page-header page-header-actions">
    <h2>Asignar Tarifas al Grupo: <?= htmlspecialchars($grupo['nombre']) ?></h2>
</div>

<div class="panel form-panel-medium">
    <form action="/mercedes/grupos/guardar_tarifas?id=<?= $grupo['id_grupo'] ?>" method="post">
        <?php foreach ($encabezados as $enc): ?>
            <div class="form-check custom-check">
                <input type="checkbox" name="encabezados[]" value="<?= $enc['id_encabezado_tarifa'] ?>"
                    <?= in_array($enc['id_encabezado_tarifa'], $encabezadosAsignados) ? 'checked' : '' ?>>
                <div class="check-info">
                    <span class="titulo"><?= htmlspecialchars($enc['titulo']) ?></span>
                    <span class="fecha">Creado: <?= htmlspecialchars($enc['fecha_creacion']) ?></span>
                    <span class="estado <?= $enc['is_active'] ? 'activo' : 'inactivo' ?>">
                        <?= $enc['is_active'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="/mercedes/grupos" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
