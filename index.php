<?php
// Establecer zona horaria de Paraguay antes de cualquier función date()
date_default_timezone_set('America/Asuncion');
/**
 * index.php
 * Front Controller - Punto de entrada único de la aplicación.
 * La ruta base se detecta para admitir /mercedes en WAMP y la raíz del subdominio.
 */

// Las rutas de la API móvil utilizan tokens Bearer.
// No necesitan iniciar sesiones PHP.
$rutaSolicitada = isset($_GET['url']) ? trim($_GET['url'], '/') : '';

$esApiMovil = str_starts_with($rutaSolicitada, 'api/');

$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$scriptDirectory = str_replace('\\', '/', dirname($scriptName));
define('BASE_PATH', $scriptDirectory === '/' || $scriptDirectory === '.'
    ? ''
    : rtrim($scriptDirectory, '/'));

$environment = strtolower((string)(getenv('MERCEDES_ENV') ?: ''));
$serverAddress = (string)($_SERVER['SERVER_ADDR'] ?? '');
$serverName = strtolower((string)($_SERVER['SERVER_NAME'] ?? ''));
$documentRoot = strtolower(str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? '')));
$scriptFilename = strtolower(str_replace('\\', '/', (string)($_SERVER['SCRIPT_FILENAME'] ?? '')));
$isWamp = PHP_OS_FAMILY === 'Windows'
    && $serverName === 'localhost'
    && str_ends_with($documentRoot, '/wamp64/www')
    && str_starts_with($scriptFilename, $documentRoot . '/');
$isLocalEnvironment = PHP_SAPI === 'cli'
    || $environment === 'local'
    || $isWamp
    || (PHP_OS_FAMILY === 'Windows' && in_array($serverAddress, ['127.0.0.1', '::1'], true));

if (!$isLocalEnvironment) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

$isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
    || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;

if ($isHttps) {
    header('Strict-Transport-Security: max-age=15552000');
}

if (!$esApiMovil && session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_PATH === '' ? '/' : BASE_PATH . '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
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
require_once __DIR__ . '/controllers/AboutdevController.php';

require_once __DIR__ . '/controllers/ApiAuthController.php';
require_once __DIR__ . '/controllers/ApiProduccionController.php';
require_once __DIR__ . '/controllers/ApiAsistenciasController.php';
require_once __DIR__ . '/controllers/ApiEmpresaController.php';

require_once __DIR__ . '/models/ApiToken.php';
require_once __DIR__ . '/models/ApiLoginIntento.php';
require_once __DIR__ . '/models/ProduccionModel.php';

require_once __DIR__ . '/models/AsistenciaModel.php';
require_once __DIR__ . '/models/GruposModel.php';
require_once __DIR__ . '/models/TurnoModel.php';
require_once __DIR__ . '/app/repositories/AsistenciasRepository.php';
require_once __DIR__ . '/app/services/AsistenciaStreamService.php';
require_once __DIR__ . '/models/SettingsModel.php';

// Captura y limpia la ruta solicitada (sin query string, sin slashes sobrantes)
$url = isset($_GET['url']) ? trim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);

switch ($url) {

    case '':
        // Redirige automáticamente a /login si entran a la raíz
        header('Location: login');
        exit;
        break;

    // =====================================
    // API SOLVER MOBILE
    // =====================================
    case 'api/login':
        $apiAuth = new ApiAuthController();
        $apiAuth->login();
        break;
        
    case 'api/me':
        $apiAuth = new ApiAuthController();
        $apiAuth->me();
        break;
    
    case 'api/logout':
        $apiAuth = new ApiAuthController();
        $apiAuth->logout();
        break;
        
        case 'api/producciones':
        $apiProduccion = new ApiProduccionController();
        $apiProduccion->index();
        break;
    
        case 'api/asistencias':
        $apiAsistencias = new ApiAsistenciasController();
        $apiAsistencias->index();
        break;
        
    case 'api/asistencias/opciones':
        $apiAsistencias = new ApiAsistenciasController();
        $apiAsistencias->opciones();
        break;
    
        case 'api/asistencias/marcar':
        $apiAsistencias = new ApiAsistenciasController();
        $apiAsistencias->marcar();
        break;

    case 'api/empresas':
        $apiEmpresa = new ApiEmpresaController();
        $apiEmpresa->index();
        break;
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
    case 'tarifas/nuevo_detalle':
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

    case 'riders/toggleStatus':
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

    case 'produccion/board-populating-machine':
        $produccionController = new ProduccionController();
        $produccionController->boardPopulatingMachine();
        break;

    case 'produccion/store':
        $produccionController = new ProduccionController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $produccionController->store();
        } else {
            // opcional: redirigir al listado si no es POST
            header('Location: ' . BASE_PATH . '/produccion');
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
    
    case 'produccion/pdf':
        $produccionController = new ProduccionController();
        $produccionController->exportarPdfDiario();
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
    
    case 'turnos/toggleStatus':
        $turnosController = new TurnosController();
        $turnosController->toggleStatus();
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

    case 'asistencias/stream':
        $adminAsistenciasController = new AdminAsistenciasController();
        $adminAsistenciasController->stream();
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
    
    // Mostrar formulario de activación (GET)
    case 'activar-rider':
        $authController = new AuthController();
        $authController->showActivateRider();
        break;

    // Procesar activación (POST)
    case 'activar-rider/store':
        $authController = new AuthController();
        $authController->storeActivateRider();
        break;

    // Enviar enlace de activación a Rider
    case 'riders/sendActivation':
        $ridersController = new RidersController();
        $ridersController->sendActivation();
        break;        
    
    // Página About Dev
    case 'aboutdev':
        $aboutdevController = new AboutdevController();
        $aboutdevController->index();
        break;
   
    default:
        http_response_code(404);
        echo '<h1 style="font-family: sans-serif; text-align:center; margin-top: 80px;">404 - Página no encontrada</h1>';
        break;
}
