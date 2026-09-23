<?php
/**
 * index.php
 * Front Controller - Punto de entrada único de la aplicación.
 * Ruta base absoluta del proyecto: /mercedes/
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', '/mercedes');

// Si el sistema no ha sido instalado, ejecuta el instalador automático.
if (!file_exists(__DIR__ . '/config/installed.txt')) {
    require_once __DIR__ . '/config/setup.php';
}

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/TarifasController.php';
require_once __DIR__ . '/controllers/RidersController.php';
require_once __DIR__ . '/controllers/DetallesController.php';
require_once __DIR__ . '/controllers/ProduccionController.php';
require_once __DIR__ . '/controllers/RiderProduccionController.php';
require_once __DIR__ . '/controllers/ConfiguracionesController.php';
require_once __DIR__ . '/controllers/GruposController.php';
require_once __DIR__ . '/controllers/TurnosController.php';
require_once __DIR__ . '/controllers/AsistenciasController.php';
require_once __DIR__ . '/controllers/AdminAsistenciasController.php';
require_once __DIR__ . '/controllers/InvitadosController.php';
require_once __DIR__ . '/controllers/HerramientasController.php';

// Captura y limpia la ruta solicitada (sin query string, sin slashes sobrantes)
$url = isset($_GET['url']) ? trim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);

switch ($url) {

    case '':
    case 'login':
        $auth = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->login();
        } else {
            $auth->showLogin();
        }
        break;

    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        break;

    case 'dashboard':
        $dashboard = new DashboardController();
        $dashboard->index();
        break;

    case 'usuarios':
        $userController = new UserController();
        $userController->index();
        break;

    case 'usuarios/crear':
        $userController = new UserController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userController->store();
        } else {
            $userController->create();
        }
        break;

    case 'usuarios/editar':
        $userController = new UserController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userController->update();
        } else {
            $userController->edit();
        }
        break;

    case 'usuarios/estado':
        $userController = new UserController();
        $userController->toggleStatus();
        break;

    case 'tarifas':
        $tarifasController = new TarifasController();
        $tarifasController->index();
        break;

    case 'tarifas/crear':
        $tarifasController = new TarifasController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tarifasController->store();
        } else {
            $tarifasController->create();
        }
        break;    

    case 'tarifas/editar':
        $tarifasController = new TarifasController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tarifasController->update();
        } else {
            $tarifasController->edit();
        }
        break;
    
    case 'tarifas/estado':
        $tarifasController = new TarifasController();
        $tarifasController->toggleEncabezado();
        break;    
        
    case 'tarifas/eliminar':
        $tarifasController = new TarifasController();
        $tarifasController->delete();
        break;    

    case 'tarifas/detalles':
        $tarifasController = new TarifasController();
        $tarifasController->detalles();        
        break;
                
    case 'detalles/crear':
        $detallesController = new DetallesController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $detallesController->store($_GET['id_encabezado']);
        } else {
            $detallesController->create($_GET['id_encabezado']);
        }
    break;

    case 'detalles/editar':
        $detallesController = new DetallesController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $detallesController->update($_GET['id'], $_GET['id_encabezado']);
        } else {
            $detallesController->edit($_GET['id'], $_GET['id_encabezado']);
        }
        break;

    case 'tarifas/detalle_estado':
        $detallesController = new DetallesController();
        $id = (int)($_GET['id'] ?? 0);
        $id_encabezado = (int)($_GET['id_encabezado'] ?? 0);
        $detallesController->toggleDetalle($id, $id_encabezado);
        break;
    

    case 'detalles/eliminar':
        $detallesController = new DetallesController();
        $detallesController->delete($_GET['id'], $_GET['id_encabezado']);
        break;

    case 'tarifas/nuevo_detalle':
        $detallesController = new DetallesController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $detallesController->store($_GET['id_encabezado']);
        } else {
            $detallesController->create($_GET['id_encabezado']);
        }
        break;

    case 'riders':
        $ridersController = new RidersController();
        $ridersController->index();
        break;

    case 'riders/crear':
        $ridersController = new RidersController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ridersController->store();
        } else {
            $ridersController->create();
        }
        break;    

    case 'riders/editar':
        $ridersController = new RidersController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ridersController->update();
        } else {
            $ridersController->edit();
        }
        break;
    
    case 'riders/estado':
        $ridersController = new RidersController();
        $ridersController->toggleStatus();
        break;    
        
    case 'riders/eliminar':
        $ridersController = new RidersController();
        $ridersController->delete();
        break;
    
    case 'produccion':
        $produccionController = new ProduccionController();
        $produccionController->index();
        break;

    case 'produccion/store':
        $produccionController = new ProduccionController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $produccionController->store();
        } else {
            // opcional: redirigir al listado si no es POST
            header('Location: /mercedes/produccion');
            exit;
        }
        break;

    case 'produccion/detalle':
    case 'produccion/byRider':
        $produccionController = new ProduccionController();
        $produccionController->byRider();
        break;

    case 'produccion/rendicion_update':
        $produccionController = new ProduccionController();
        $produccionController->rendicionUpdate();
        break;

    case 'produccion/delete':
        $produccionController = new ProduccionController();
        $produccionController->delete();
        break;    

    // --- Punto de control ---    
    case 'punto-control':
        $configController = new ConfiguracionesController();
        $configController->puntodecontrol();
        break;

    case 'configuraciones/guardar_punto_control':
        $configController = new ConfiguracionesController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $configController->guardarPuntoControl();
        } else {
            http_response_code(405); // Método no permitido
            echo "Método no permitido";
        }
        break;

    // --- Grupos ---    
    case 'grupos':
        $gruposController = new GruposController();
        $gruposController->index();
        break;

    case 'grupos/crear':
        $gruposController = new GruposController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gruposController->store();
        } else {
            $gruposController->create();
        }
        break;

    case 'grupos/editar':
        $gruposController = new GruposController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gruposController->update();
        } else {
            $gruposController->edit();
        }
        break;

    case 'grupos/eliminar':
        $gruposController = new GruposController();
        $gruposController->delete();
        break;

    case 'grupos/asignar_tarifas':
        $controller = new GruposController();
        $controller->asignarTarifas();
        break;

    case 'grupos/guardar_tarifas':
        $controller = new GruposController();
        $controller->guardarTarifas();
        break;
    
    // --- Turnos ---
    case 'turnos':
        $turnosController = new TurnosController();
        $turnosController->index();
        break;

    case 'turnos/create':
        $turnosController = new TurnosController();
        $turnosController->create();
        break;

    case 'turnos/store':
        $turnosController = new TurnosController();
        $turnosController->store();
        break;

    case 'turnos/edit':
        $turnosController = new TurnosController();
        $turnosController->edit();
        break;

    case 'turnos/update':
        $turnosController = new TurnosController();
        $turnosController->update();
        break;

    case 'turnos/delete':
        $turnosController = new TurnosController();
        $turnosController->delete();
        break;
    
    case 'produccion/getRidersByGrupoAjax':
        $produccionController = new ProduccionController();
        $produccionController->getRidersByGrupoAjax();
        break;

    case 'produccion/getTarifasByGrupoAjax':
        $produccionController = new ProduccionController();
        $produccionController->getTarifasByGrupoAjax();
        break;

    case 'produccion/getTurnosByGrupoAjax':
        $produccionController = new ProduccionController();
        $produccionController->getTurnosByGrupoAjax();
        break;

    case 'rider_produccion':
        $riderProduccion = new RiderProduccionController();
        $riderProduccion->index();
        break;

    case 'rider_produccion/detalle':
        $riderProduccion = new RiderProduccionController();
        $riderProduccion->detalle();
        break;

    case 'rider_asistencia':
        $asistenciasController = new AsistenciasController();
        $asistenciasController->index();
        break;

    case 'rider_asistencia/form':
        $asistenciasController = new AsistenciasController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $asistenciasController->store();
        } else {
            $asistenciasController->form(); // nuevo método que carga la vista
        }
        break;

    case 'asistencias':
        $adminAsistenciasController = new AdminAsistenciasController();
        $adminAsistenciasController->index();
        break;

    case 'invitados':
        $invitadosController = new InvitadosController();
        $invitadosController->index();
        break;

    case 'herramientas':
        $herramientasController = new HerramientasController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $herramientasController->save();
        } else {
            $herramientasController->index();
        }
        break;    
    
    default:
        http_response_code(404);
        echo '<h1 style="font-family: sans-serif; text-align:center; margin-top: 80px;">404 - Página no encontrada</h1>';
        break;
}
