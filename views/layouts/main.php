<?php
/**
 * views/layouts/main.php
 * Plantilla Maestra Centralizada.
 * Recibe la variable $view (definida por el controlador) e inyecta
 * dinámicamente la sub-vista correspondiente dentro de .content-body.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentRole = $_SESSION['user_role'] ?? '';
$currentRoleName = $_SESSION['user_role_name'] ?? '';
$currentUserName = $_SESSION['user_name'] ?? 'Usuario';
// Configurar la zona horaria de Paraguay
date_default_timezone_set('America/Asuncion');
//
$hoy = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Panel Administrativo</title>
        <link rel="icon" type="image/svg+xml" href="/mercedes/public/images/icono_solver_nobg.png">
        <link rel="stylesheet" href="/mercedes/public/css/style.css">
        <!-- Copia esto dentro de tu <head> -->
        <link rel="stylesheet" href="https://cloudflare.com">
    </head>
    <body>        
        <div class="dashboard-layout">

            <aside class="sidebar">
                <div class="sidebar-brand">
                    <img src="/mercedes/public/images/icono_solver_nobg.png" alt="Logo" class="sidebar-logo">
                    <span>SOLVER-APP</span>
                </div>
                
                <nav class="sidebar-nav">

                    <!-- Dashboard (sin categoría) -->
                    <a href="/mercedes/dashboard" class="nav-item <?= ($view === 'dashboard') ? 'active' : '' ?>">
                        <span class="nav-icon img-nav-icon">
                            <img src="/mercedes/public/images/analisis-de-los-datos.png" alt="dashboard">
                            <i class="fa-solid fa-chart-pie"></i>
                        </span>
                        DASHBOARD
                    </a>

                    <a href="/mercedes/produccion?fecha=<?= $hoy ?>" 
                        class="nav-item <?= ($view === 'produccion') ? 'active' : '' ?>">
                        <span class="nav-icon img-nav-icon">
                            <img src="/mercedes/public/images/entrega.png" alt="produccion">
                        </span> PRODUCCIÓN
                    </a>

                    <a href="/mercedes/riders" 
                        class="nav-item <?= ($view === 'riders/index' || $view === 'riders/form') ? 'active' : '' ?>">
                        <span class="nav-icon img-nav-icon">
                            <img src="/mercedes/public/images/rider.png" alt="riders">
                        </span> RIDERS
                    </a>

                    <a href="/mercedes/tarifas" 
                        class="nav-item <?= ($view === 'tarifas/index' || $view === 'tarifas/form') ? 'active' : '' ?>">
                        <span class="nav-icon img-nav-icon">
                            <img src="/mercedes/public/images/tarifas.png" alt="tarifas">
                        </span> TARIFAS
                    </a>

                    <?php if ($currentRole === 'admin'): ?>                   
                    <a href="/mercedes/usuarios" 
                        class="nav-item <?= (str_starts_with($view, 'usuarios')) ? 'active' : '' ?>">
                        <span class="nav-icon img-nav-icon">
                            <img src="/mercedes/public/images/nuevo.png" alt="usuarios">
                        </span> USUARIOS
                    </a>
                    <?php endif; ?>

                    <a href="/mercedes/punto-control" 
                        class="nav-item <?= ($view === 'punto-control') ? 'active' : '' ?>">
                        <span class="nav-icon img-nav-icon">
                            <img src="/mercedes/public/images/checkpoint.png" alt="punto de control">
                        </span> PUNTO DE CONTROL
                    </a>
                    <!--
                    <a href="/mercedes/empresas" 
                        class="nav-item <?= ($view === 'empresas') ? 'active' : '' ?>">
                        <span class="nav-icon img-nav-icon">
                            <img src="/mercedes/public/images/empresa.png" alt="empresas">
                        </span> EMPRESAS
                    </a>
                    -->    
                </nav>

                <div class="sidebar-footer">
                    <a href="/mercedes/logout" class="nav-item logout">
                        <span class="nav-icon">&#10132;</span> Cerrar sesión
                    </a>
                </div>
            </aside>

            <!-- el overlay -->
            <div id="sidebar-overlay"></div>

            <div class="main-column">

                <header class="topbar">
                    <button class="hamburger" id="hamburger-btn">☰</button>
                
                    <!-- <?php echo 'value: '.$view ?> -->                      
                    <div class="topbar-title">                        
                        <?= match ($view) {
                            'dashboard' => 'Panel General',
                            'usuarios/index'  => 'Gestión de Usuarios',                            
                            'usuarios/form'  => 'Gestión de Usuarios',
                            'tarifas/index'  => 'Gestión de tarifas',
                            'tarifas/form'  => 'Gestión de tarifas',
                            'tarifas/detalles'  => 'Gestión de tarifas',
                            'tarifas/nuevo_detalle'  => 'Gestión de tarifas',
                            'riders/index'  => 'Gestión de riders',
                            'riders/form'  => 'Gestión de riders',                           
                            'produccion/index'  => 'Gestión de produccion',
                            'produccion/cards'  => 'Gestión de produccion',
                            'configuraciones/puntodecontrol'  => 'Punto de control',
                            default     => 'Inicio'
                        } ?>
                    </div>                    
                    <div class="topbar-user">
                        <div class="topbar-user-info">
                            <span class="user-name"><?= htmlspecialchars($currentUserName) ?></span>
                            <span class="user-role"><?= htmlspecialchars($currentRoleName) ?></span>
                        </div>
                        <div class="topbar-avatar"><?= strtoupper(substr($currentUserName, 0, 1)) ?></div>
                    </div>
                </header>

                <main class="content-body">
                    <?php include __DIR__ . '/../' . $view . '.php'; ?>
                </main>
            </div>

        </div>
        
        <!-- Toast modal container -->
        <div id="toast-modal" class="toast-modal-overlay hidden">
            <div class="toast-modal">
                <span id="toast-icon"></span>
                <span id="toast-message"></span>
            </div>
        </div>

        <script src="/mercedes/public/js/main.js"></script>
        <?php if ($view === 'configuraciones/puntodecontrol'): ?>
            <!-- Google Maps API -->
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA4c614NDknCkm8eRfBwOHWA1l9Vbq1hew"></script>
            <!-- JS específico de Punto de Control -->
            <script src="/mercedes/public/js/puntodecontrol.js"></script>
        <?php endif; ?>

    </body>
</html>
