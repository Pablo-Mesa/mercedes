<?php
/**
 * views/grupos/index.php
 * Listado de grupos.
 * Variable disponible: $grupos
 */
?>
<div class="page-header page-header-actions">
    <div>
        <p>Administra las empresas asociadas al puntos de control.</p>
    </div>
    <a href="/mercedes/grupos/crear" class="btn btn-primary btn-nuevo">+ Nueva Empresa</a>
</div>

<div class="panel table-panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>                
                <th>Punto de Control</th>                
                <th>Nombre</th>          
                <th>Icono</th>      
                <th>Descripción</th>
                <th>Estado</th>                
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($grupos)): ?>
                <?php foreach ($grupos as $g): ?>
                    <tr>
                        <td><?= $g['id_grupo']; ?></td>
                        <td><?= htmlspecialchars($g['punto_titulo']); ?></td>
                        <td><?= htmlspecialchars($g['nombre']); ?></td>
                        <td>
                            <img src="/mercedes/public/uploads/grupos/<?= !empty($g['icono']) 
                                ? htmlspecialchars($g['icono']) 
                                : 'default.png' ?>" 
                                alt="Icono grupo" class="avatar-sm">
                        </td>                                                                        
                        <td><?= htmlspecialchars($g['descripcion']);?></td>
                        <td>
                            <?= ((int)$g['activo'] === 1) 
                                ? '<span class="badge badge-success">Activo</span>' 
                                : '<span class="badge badge-danger">Inactivo</span>'; ?>
                        </td>                        
                        <td>
                            <a href="/mercedes/grupos/editar?id=<?= (int)$g['id_grupo'] ?>" class="btn btn-small">Editar</a>
                            <!--
                            <a href="/mercedes/grupos/eliminar?id=<?= (int)$g['id_grupo'] ?>" 
                               class="btn btn-small btn-danger-outline"
                               onclick="return confirm('¿Seguro que deseas eliminar este grupo?');">
                                Eliminar
                            </a>
                            -->
                            <a href="/mercedes/grupos/asignar_tarifas?id=<?= $g['id_grupo'] ?>" 
                                class="btn btn-small">
                                Tarifas
                            </a>

                            <a href="/mercedes/turnos?id=<?= $g['id_grupo'] ?>" class="btn btn-small mt-1">
                                Gestionar Turnos
                            </a>
                            
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No se encontraron registros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
