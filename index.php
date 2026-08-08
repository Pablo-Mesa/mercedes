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
require_once __DIR__ . '/controllers/ConfiguracionesController.php';


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
        $produccionController->detalle();
        break;

    case 'produccion/rendicion_update':
        $produccionController = new ProduccionController();
        $produccionController->rendicionUpdate();
        break;

    case 'produccion/delete':
        $produccionController = new ProduccionController();
        $produccionController->delete();
        break;    

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
    

    default:
        http_response_code(404);
        echo '<h1 style="font-family: sans-serif; text-align:center; margin-top: 80px;">404 - Página no encontrada</h1>';
        break;
}
