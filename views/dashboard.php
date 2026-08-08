<?php
/**
 * views/dashboard.php
 * Vista pura del panel principal. Se inyecta dentro de views/layouts/main.php.
 * Variables disponibles: $totalUsuarios, $usuarioActual
 */
?>
<div class="page-header">
    <h2>Bienvenido, <?= htmlspecialchars($usuarioActual['name'] ?? 'Usuario') ?></h2>
    <p>Este es el resumen general de tu template administrativo.</p>
</div>

<div class="cards-grid">
    <div class="stat-card">
        <div class="stat-icon">&#9782;</div>
        <div class="stat-info">
            <span class="stat-value"><?= (int)$totalUsuarios ?></span>
            <span class="stat-label">Usuarios registrados</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">&#9998;</div>
        <div class="stat-info">
            <span class="stat-value"><?= htmlspecialchars($usuarioActual['role_name'] ?? '—') ?></span>
            <span class="stat-label">Tu rol actual</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">&#9989;</div>
        <div class="stat-info">
            <span class="stat-value"><?= ((int)($usuarioActual['is_active'] ?? 0) === 1) ? 'Activo' : 'Inactivo' ?></span>
            <span class="stat-label">Estado de tu cuenta</span>
        </div>
    </div>
</div>

<div class="panel">
    <h3>Sobre este template</h3>
    <p>
        Estructura MVC en PHP nativo lista para producción local: rutas absolutas,
        sesiones seguras, layout maestro único y un CRUD reversible de usuarios
        protegido por rol. Usa el menú lateral para explorar la gestión de usuarios
        si tu cuenta cuenta con permisos de administrador.
    </p>
</div>
