<div class="page-header page-header-actions">
    <h2><?= isset($turno) ? 'Editar Turno' : 'Nuevo Turno' ?></h2>
</div>

<div class="panel form-panel-medium">
    <form action="<?= isset($turno) ? '/mercedes/turnos/update' : '/mercedes/turnos/store' ?>" 
          method="post" enctype="multipart/form-data">
        <?php if (isset($turno)): ?>
            <input type="hidden" name="id" value="<?= (int)$turno['id'] ?>">
        <?php endif; ?>
        <input type="hidden" name="id_grupo" value="<?= isset($turno) ? (int)$turno['id_grupo'] : (int)($_GET['id_grupo'] ?? 0) ?>">

        <div class="form-group">
            <label for="turno">Nombre del Turno</label>
            <input type="text" name="turno" id="turno" class="form-control"
                   value="<?= htmlspecialchars($turno['turno'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="hora_inicio">Hora Inicio</label>
            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control"
                   value="<?= htmlspecialchars($turno['hora_inicio'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="hora_fin">Hora Fin</label>
            <input type="time" name="hora_fin" id="hora_fin" class="form-control"
                   value="<?= htmlspecialchars($turno['hora_fin'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="icono">Icono del Turno</label>
            <input type="file" name="icono" id="icono" class="form-control" accept="image/*">
            <?php if (!empty($turno['url_icono'])): ?>
                <p>Icono actual:</p>
                <img src="<?= htmlspecialchars($turno['url_icono']) ?>" alt="Icono turno" class="avatar-sm">
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="/mercedes/turnos?id=<?= isset($turno) ? $turno['id_grupo'] : (int)($_GET['id_grupo'] ?? 0) ?>" 
               class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
