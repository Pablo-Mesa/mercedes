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

require_once __DIR__ . '/../../helpers/IconHelper.php';
require_once __DIR__ . '/../../helpers/GrupoHelper.php';
require_once __DIR__ . '/../../models/SettingsModel.php';
$grupoIcono = GrupoHelper::getGrupoIcono();
$settingsModel = new SettingsModel();
$cuadernoActivo = $settingsModel->getBoolean('tool_cuaderno_enabled');
$asistenciasActivas = $settingsModel->getBoolean('tool_asistencias_enabled');
$esAdmin = $currentRole === 'admin';
$adminOrOperaciones = in_array($currentRole, ['admin', 'operaciones'], true);

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Panel Administrativo</title>
        <link rel="icon" type="image/svg+xml" href="/mercedes/public/images/icono_solver_nobg.png">
        <link rel="stylesheet" href="/mercedes/public/css/style.css">
        <link rel="stylesheet" href="/mercedes/public/css/css_cubo.css">
        <script src="/mercedes/public/js/tool-kit-v002.js"></script>                        
        <!-- Copia esto dentro de tu <head> -->
        <link rel="stylesheet" href="https://cloudflare.com">                
    </head>
    <body>
        <!-- * -->         
        <div class="dashboard-layout">
            <!-- side bar -->
            <aside class="sidebar">
                <!-- marca/icono app -->
                <div class="sidebar-brand">
                    <div id="contenedorCuboMain"></div>
                    <span>SOLVER-APP</span>
                </div>

                <!-- side bar navegacion -->
                <nav class="sidebar-nav">                    
                    <!-- Dashboard: solo admin -->
                    <?php if ($cuadernoActivo && $esAdmin): ?>
                        <a href="/mercedes/dashboard" class="nav-item ">
                            <!-- <?= ($view === 'dashboard') ? 'active' : '' ?> --> 
                            <span class="img-nav-icon" >
                                <?= render_icon('menu', 'nav-icon') ?>
                            </span>
                            DASHBOARD
                        </a>
                        <hr class="nav-divider">
                    <?php endif; ?>

                    <!-- Producción: Cuaderno para admin + operaciones -->
                    <?php if ($cuadernoActivo && $adminOrOperaciones): ?>
                        <?php $hoy = date('Y-m-d'); ?>
                        <a href="/mercedes/produccion?fecha=<?= $hoy ?>" 
                            class="nav-item <?= ($view === 'produccion') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('produccion', 'nav-icon') ?>
                            </span> PRODUCCIÓN
                        </a>                        
                    <?php endif; ?>

                    <!-- Llegadas: Control de Asistencias para admin + operaciones -->
                    <?php if ($asistenciasActivas && $adminOrOperaciones): ?>
                        <a href="/mercedes/asistencias" 
                            class="nav-item <?= ($view === 'asistencias/index') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('llegada', 'nav-icon') ?>
                            </span> LLEGADAS
                        </a>
                    <?php endif; ?>

                    <!-- Riders: disponible desde cualquiera de los dos módulos -->
                    <?php if (($cuadernoActivo || $asistenciasActivas) && $adminOrOperaciones): ?>
                        <a href="/mercedes/riders" 
                            class="nav-item <?= ($view === 'riders/index' || $view === 'riders/form') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('riders', 'nav-icon') ?>
                            </span> RIDERS
                        </a>
                        <hr class="nav-divider">
                    <?php endif; ?>

                    <!-- Punto de Control: Control de Asistencias, solo admin -->
                    <?php if ($asistenciasActivas && $esAdmin): ?>
                        <a href="/mercedes/punto-control" 
                            class="nav-item <?= ($view === 'configuraciones/puntodecontrol') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('puntodecontrol', 'nav-icon') ?>
                            </span> PUNTO DE CONTROL
                        </a>
                    <?php endif; ?>

                    <!-- Configuraciones de Cuaderno: solo admin -->
                    <?php if ($cuadernoActivo && $esAdmin): ?>
                        <a href="/mercedes/grupos" 
                            class="nav-item <?= ($view === 'grupos/index' || $view === 'grupos/form') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('empresas', 'nav-icon') ?>
                            </span> EMPRESAS
                        </a>
                        <a href="/mercedes/turnos" 
                            class="nav-item <?= ($view === 'turnos/index' || $view === 'turnos/form') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('turnos', 'nav-icon') ?>
                            </span> TURNOS
                        </a>
                        <a href="/mercedes/tarifas" 
                            class="nav-item <?= ($view === 'tarifas/index' || $view === 'tarifas/form') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('tarifas', 'nav-icon') ?>
                            </span> TARIFAS
                        </a>
                    <?php endif; ?>
                    
                    <!-- Producción del Rider: solo rol rider -->
                    <?php if ($_SESSION['user_role'] === 'rider'): ?>
                        <?php $hoy = date('Y-m-d'); ?>
                        <?php if ($cuadernoActivo): ?>
                        <a href="/mercedes/rider_produccion?fecha=<?= $hoy ?>" 
                            class="nav-item <?= ($view === 'rider_produccion/index' || $view === 'rider_produccion/detalle') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('produccion', 'nav-icon') ?>
                            </span> MI PRODUCCIÓN
                        </a>
                        <?php endif; ?>
                        <?php if ($asistenciasActivas): ?>
                        <a href="/mercedes/rider_asistencia?fecha=<?= $hoy ?>" 
                            class="nav-item <?= ($view === 'rider_asistencia/index' || $view === 'rider_asistencia/form') ? 'active' : '' ?>">
                            <span class="img-nav-icon" >
                                <?= render_icon('llegada', 'nav-icon') ?>
                            </span> LLEGADAS
                        </a>
                        <?php endif; ?>
                        <hr class="nav-divider">
                    <?php endif; ?>

                    <!-- Producción de Riders: solo rol invitado -->
                    <?php if ($cuadernoActivo && $_SESSION['user_role'] === 'invitado'): ?>
                        <a href="/mercedes/invitados" 
                            class="nav-item <?= ($view === 'invitados/index') ? 'active' : '' ?>">
                            <span class="img-nav-icon">
                                <?= render_icon('produccion', 'nav-icon') ?>
                            </span>
                            PRODUCCIÓN DE RIDERS
                        </a>
                        <hr class="nav-divider">
                    <?php endif; ?>    
                </nav>

                <!-- side bar footer -->        
                <div class="sidebar-footer">
                    <div class="config-menu">
                        <button class="config-btn nav-item logout" id="configBtn" aria-expanded="false" aria-controls="configDropdown">
                        <span class="img-nav-icon"><?= render_icon('settings', 'nav-icon') ?></span>
                        Opciones
                        </button>
                        <div class="config-dropdown" id="configDropdown">
                        <!-- Configuraciones y Usuarios: solo admin -->
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="/mercedes/usuarios" class="value">
                            <?= render_icon('user', 'nav-icon') ?> Usuarios
                            </a>
                            <a href="/mercedes/herramientas" class="value">
                            <?= render_icon('tools', 'nav-icon') ?> Herramientas
                            </a>
                        <?php endif; ?>                            
                        <a href="/mercedes/about" class="value">
                            <?= render_icon('dev', 'nav-icon') ?> About Dev
                        </a>
                        <a href="/mercedes/logout" class="value">
                            <?= render_icon('logout', 'nav-icon') ?> Cerrar sesión
                        </a>
                        </div>
                    </div>
                </div>

            </aside>

            <!-- el overlay -->
            <div id="sidebar-overlay"></div>
            
            <!-- columna principal -->
            <div class="main-column">
                <!-- encabezado -->        
                <header class="topbar">
                    <button class="hamburger" id="hamburger-btn">☰</button>                
                    <!-- <?php echo 'value: '.$view ?> --> 
                    <!-- titulo en barra superior -->                         
                    <div class="topbar-title">          
                        <?= match ($view) {
                            'dashboard' => '<span class="img-nav-icon">' . render_icon('dashboard', 'nav-icon') . '</span> Resumen mensual',                            
                            'usuarios/index' => '<span class="img-nav-icon">' . render_icon('user', 'nav-icon') . '</span> Gestión de Usuarios',                            
                            'usuarios/form' => '<span class="img-nav-icon">' . render_icon('user', 'nav-icon') . '</span> Gestión de Usuarios',
                            'tarifas/index' => '<span class="img-nav-icon">' . render_icon('tarifas', 'nav-icon') . '</span> Gestión de tarifas',
                            'tarifas/form' => '<span class="img-nav-icon">' . render_icon('tarifas', 'nav-icon') . '</span> Gestión de tarifas',
                            'tarifas/detalles' => '<span class="img-nav-icon">' . render_icon('tarifas', 'nav-icon') . '</span> Gestión de tarifas',
                            'tarifas/nuevo_detalle' => '<span class="img-nav-icon">' . render_icon('tarifas', 'nav-icon') . '</span> Gestión de tarifas',
                            'riders/index' => '<span class="img-nav-icon">' . render_icon('riders', 'nav-icon') . '</span> Gestión de riders',
                            'riders/form' => '<span class="img-nav-icon">' . render_icon('riders', 'nav-icon') . '</span> Gestión de riders',                           
                            'produccion/index' => '<span class="img-nav-icon">' . render_icon('produccion', 'nav-icon') . '</span> Gestión de produccion',
                            'produccion/cards' => '<span class="img-nav-icon">' . render_icon('produccion', 'nav-icon') . '</span> Gestión de produccion',
                            'configuraciones/puntodecontrol' => '<span class="img-nav-icon">' . render_icon('puntodecontrol', 'nav-icon') . '</span> Punto de control',
                            'grupos/index' => '<span class="img-nav-icon">' . render_icon('empresas', 'nav-icon') . '</span> Gestión de empresas',
                            'grupos/form' => '<span class="img-nav-icon">' . render_icon('empresas', 'nav-icon') . '</span> Gestión de empresas',
                            'grupos/tarifas' => '<span class="img-nav-icon">' . render_icon('empresas', 'nav-icon') . '</span> Gestión tarifas y empresas', 
                            'turnos/index' => '<span class="img-nav-icon">' . render_icon('turnos', 'nav-icon') . '</span> Turnos asociados a un grupo',
                            'turnos/form' => '<span class="img-nav-icon">' . render_icon('turnos', 'nav-icon') . '</span> Turnos',
                            'turnos/general' => '<span class="img-nav-icon">' . render_icon('turnos', 'nav-icon') . '</span> Vista General de Turnos',
                            'rider_produccion/index' => '📊 Mi Producción',
                            'rider_produccion/detalle' => '📊 Detalle de Producción',
                            'rider_asistencia/index' => '🙋 Asistencias',
                            'rider_asistencia/form' => '⏲️ Marcar llegada',
                            'asistencias/index' => '<span class="img-nav-icon">' . render_icon('llegada', 'nav-icon') . '</span> Llegadas',
                            'invitados/index' => '<span class="img-nav-icon">' . render_icon('produccion', 'nav-icon') . '</span> Produccion',                            
                            'herramientas/index' => '<span class="img-nav-icon">' . render_icon('tools', 'nav-icon') . '</span> Herramientas',
                            default => 'Inicio'
                        } ?>
                    </div>
                    <!-- usuario e icono barra superior -->                    
                    <div class="topbar-user">
                        <!-- mostrar nombre de usuario y rol -->
                        <div class="topbar-user-info">
                            <span class="user-name"><?= htmlspecialchars($currentUserName) ?></span>
                            <span class="user-role"><?= htmlspecialchars($currentRoleName) ?></span>
                        </div>
                        <!-- mostrar icono grupo -->
                        <?php if ($_SESSION['user_role'] === 'operaciones' && !empty($grupoIcono)): ?>
                            <div class="topbar-avatar-bg-transparent">
                                <img src="<?= htmlspecialchars($grupoIcono) ?>" 
                                    alt="Grupo" 
                                    style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                            </div>
                        <?php else: ?>
                            <div class="topbar-avatar"><?= strtoupper(substr($currentUserName, 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                </header>
                <!-- aqui se cargan las vistas -->        
                <main class="content-body">
                    <?php
                        if (isset($data) && is_array($data)) {
                            extract($data, EXTR_SKIP);
                        }
                        include __DIR__ . '/../' . $view . '.php';
                    ?>
                </main>
            </div>
        </div>
        
        <!-- Toast modal container -->
        <div id="toast-modal" class="toast-modal-overlay">
            <div class="toast-modal">
                <span id="toast-icon"></span>
                <span id="toast-message"></span>
            </div>
        </div>

        <!-- views/layouts/footer.php -->
        <modal-confirmacion id="modal-global"></modal-confirmacion>

        <!-- llamar al javascript principal -->
        <script src="/mercedes/public/js/main.js"></script>
        <script src="/mercedes/public/js/loader.js"></script>
        <!-- dibujar icono de la app -->
        <script>
            // Dibujar cubo en el layout principal
            drawCube("contenedorCuboMain", false, "24px");
        </script>        
        <!-- si vista = punto de control utilizamos maps.googleapis -->
        <?php if ($view === 'configuraciones/puntodecontrol'): ?>
            <!-- Leaflet CSS y JS -->
            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
            <!-- JS específico de Punto de Control -->
            <script src="/mercedes/public/js/puntodecontrol.js"></script>
        <?php endif; ?>
    </body>
</html>