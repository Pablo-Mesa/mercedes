<?php

require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../models/RiderModel.php';
require_once __DIR__ . '/../models/DetalleTarifaModel.php';
require_once __DIR__ . '/../models/GruposModel.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../helpers/TurnoHelper.php';
require_once __DIR__ . '/../app/services/ProduccionService.php';
require_once __DIR__ . '/../app/services/PdfService.php';

class ProduccionController extends BaseController
{
    private ProduccionModel $produccionModel;
    private ProduccionService $produccionService;
    private RiderModel $riderModel;
    private DetalleTarifaModel $detalleTarifaModel;

    public function __construct()
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_PATH . '/login');
        }

        $this->produccionModel = new ProduccionModel();
        $this->produccionService = new ProduccionService($this->produccionModel);

        $this->riderModel = new RiderModel();
        $this->detalleTarifaModel = new DetalleTarifaModel();

        $this->requireRole(['admin', 'operaciones']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');
    }

    public function index(): void
    {
        $data = $this->buildIndexData();
        $this->renderLayout($data['view'], $data);
    }

    public function boardPopulatingMachine(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET', 'POST'], true)) {
            http_response_code(405);
            header('Allow: GET, POST');
            echo 'Método no permitido.';
            return;
        }

        $fecha = date('Y-m-d');
        $resultado = null;
        $error = null;

        if ($method === 'POST') {
            $fecha = trim((string) ($_POST['fecha'] ?? ''));
            $grupoId = null;

            if (($_SESSION['user_role'] ?? null) === 'operaciones') {
                $grupoId = (int) ($_SESSION['grupo_id'] ?? 0);
                if ($grupoId <= 0) {
                    $error = 'Tu sesión no tiene un grupo válido para poblar producción.';
                }
            }

            if ($error === null) {
                try {
                    $resultado = $this->produccionService->poblarProduccion(
                        $fecha,
                        (int) $_SESSION['user_id'],
                        $grupoId
                    );
                } catch (DomainException $exception) {
                    $error = $exception->getMessage();
                } catch (PDOException $exception) {
                    error_log('[ProduccionController::boardPopulatingMachine] ' . $exception->getMessage());
                    $error = 'No se pudo completar el poblado. Se revirtió la operación.';
                }
            }
        }

        $this->renderLayout('produccion/board_populating_machine', [
            'fechaMaquina' => $fecha,
            'resultadoMaquina' => $resultado,
            'errorMaquina' => $error,
        ]);
    }
    
    public function store(): void
    {
        $payload = [
            'id_usuario' => $_SESSION['user_id'] ?? null,
            'id_rider' => $_POST['id_rider'] ?? null,
            'id_detalle_tarifa' => $_POST['id_detalle_tarifa'] ?? null,
            'id_turno' => $_POST['id_turno'] ?? 1,
            'id_grupo' => $_POST['id_grupo'] ?? ($_SESSION['grupo_id'] ?? 0),
            'id_accion' => $_POST['id_accion'] ?? null,
            'total_factura' => trim($_POST['total_factura'] ?? ''),
            'vuelto' => trim($_POST['vuelto'] ?? ''),
            'fecha_creacion' => $_POST['fecha_creacion'] ?? date('Y-m-d H:i:s'),
        ];

        $errors = $this->produccionService->validarRegistro($payload);

        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => implode(' ', $errors),
            ], 400);
        }

        $success = $this->produccionService->registrar($payload);

        $this->json([
            'success' => $success,
            'message' => $success
                ? 'Producción registrada correctamente.'
                : 'No se pudo guardar la producción.',
        ]);
    }

    public function detalle(): void
    {
        $riderId = (int) ($_GET['rider'] ?? $_GET['id'] ?? 0);
        $fecha = $_GET['fecha'] ?? null;

        if ($riderId <= 0) {
            $this->json(['error' => 'Rider inválido'], 400);
        }

        $this->json($this->produccionModel->getProduccionesByRider($riderId, $fecha));
    }

    public function rendicionUpdate(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $estado = (int) ($_GET['estado'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'ID inválido'], 400);
        }

        $this->json($this->produccionService->actualizarRendicion($id, $estado));
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->produccionModel->delete($id);
            $_SESSION['success'] = 'Producción eliminada.';
        }

        $this->redirect(BASE_PATH . '/produccion?vista=tarjetas');
    }

    public function getRidersByGrupoAjax(): void
    {
        $grupoId = $_GET['grupo'] ?? null;

        $this->json($grupoId
            ? $this->produccionModel->getRidersByGrupo((int) $grupoId)
            : []);
    }

    public function getTarifasByGrupoAjax(): void
    {
        $grupoId = $_GET['grupo'] ?? null;

        $this->json($grupoId
            ? $this->detalleTarifaModel->getByGrupo((int) $grupoId)
            : []);
    }

    public function getTurnosByGrupoAjax(): void
    {
        $grupoId = $_GET['grupo'] ?? null;

        $this->json($grupoId
            ? $this->produccionModel->getTurnosByGrupo((int) $grupoId)
            : []);
    }

    public function byRider(): void
    {
        $riderId = (int) ($_GET['id'] ?? 0);
        $fecha = $_GET['fecha'] ?? null;

        if ($riderId <= 0) {
            $this->json(['error' => 'Rider inválido'], 400);
        }

        $grupoId = $_SESSION['user_role'] === 'operaciones' ? ($_SESSION['grupo_id'] ?? null) : null;

        $this->json($this->produccionModel->getProduccionesByRider($riderId, $fecha, $grupoId));
    }

    private function buildIndexData(): array
    {
        $fecha  = $_GET['fecha'] ?? date('Y-m-d');
        $turno  = isset($_GET['turno']) && $_GET['turno'] !== '' ? (int) $_GET['turno'] : null;
        $rider  = isset($_GET['rider']) && $_GET['rider'] !== '' ? (int) $_GET['rider'] : null;
        $accion = isset($_GET['accion']) && $_GET['accion'] !== '' ? (int) $_GET['accion'] : null;
        $vista  = $_GET['vista'] ?? 'tarjetas';

        $view = $vista === 'tarjetas' ? 'produccion/cards' : 'produccion/index';

        $data = [
            'fecha'  => $fecha,
            'turno'  => $turno,
            'rider'  => $rider,
            'accion' => $accion,
            'vista'  => $vista,
            'view'   => $view,
        ];

        // Datos base siempre disponibles
        $produccionesResumen = $this->produccionModel->getProduccionesConDetalle($fecha, $turno, $rider, $accion);
        $data['produccionesResumen'] = $produccionesResumen; // para la tabla

        $produccionesResumenTotales = $this->produccionService->getProduccionPorFecha($fecha);
        $resumenPorGrupo = $this->produccionService->getResumenPorGrupo($produccionesResumenTotales);

        if ($vista === 'tarjetas') {
            // Consolidar por rider_id para las cards
            $produccionesPorRider = [];
            foreach ($produccionesResumen as $grupo) {
                foreach ($grupo['riders'] as $p) {
                    $riderId = $p['rider_id'];

                    if (!isset($produccionesPorRider[$riderId])) {
                        $produccionesPorRider[$riderId] = [
                            'rider_id'     => $riderId,
                            'rider_nombre' => $p['rider_nombre'],
                            'rider_foto'   => $p['rider_foto'] ?? 'default.png',
                            'facturas'     => [],
                            'total_tarifa' => 0,
                        ];
                    }

                    $produccionesPorRider[$riderId]['facturas'] = array_merge(
                        $produccionesPorRider[$riderId]['facturas'],
                        $p['facturas']
                    );
                    $produccionesPorRider[$riderId]['total_tarifa'] += (float)$p['total_tarifa'];
                }
            }
            $data['produccionesPorRider'] = $produccionesPorRider; // para las cards
        } else {
            $data['producciones'] = $this->produccionModel->getProduccionesFiltradas($fecha, $turno, $rider, $accion);
        }

        // Mantener el resto del código intacto
        $grupoId = $_SESSION['grupo_id'] ?? null;

        if ($_SESSION['user_role'] === 'admin') {
            $grupos  = (new GruposModel())->getAll();
            $grupoId = $_POST['id_grupo'] ?? $_GET['grupo'] ?? null;

            if ($grupoId) {
                $data['riders']        = $this->produccionModel->getRidersByGrupo((int)$grupoId);
                $data['tarifas']       = $this->produccionModel->getTarifasByGrupo((int)$grupoId);
                $data['turnos']        = $this->produccionModel->getTurnosByGrupo((int)$grupoId);
                $data['resumenGlobal'] = $this->produccionModel->getProduccionResumenPorGrupo($fecha, (int)$grupoId);
            } else {
                $data['riders']        = [];
                $data['tarifas']       = [];
                $data['turnos']        = [];
                $data['resumenGlobal'] = $this->produccionModel->getProduccionResumenGlobal($fecha);
            }
        } else {
            $grupos = [];
            $data['riders']        = $grupoId ? $this->produccionModel->getRidersByGrupo((int)$grupoId) : [];
            $data['tarifas']       = $grupoId ? $this->produccionModel->getTarifasByGrupo((int)$grupoId) : [];
            $data['turnos']        = $grupoId ? $this->produccionModel->getTurnosByGrupo((int)$grupoId) : [];
            $data['resumenGlobal'] = $this->produccionModel->getProduccionResumenPorGrupo($fecha, (int)$grupoId);
        }

        if ($_SESSION['user_role'] === 'operaciones' && $grupoId) {
            $grupoModel = new GruposModel();
            $grupo      = $grupoModel->getById($grupoId);

            if (!empty($grupo['icono'])) {
                $data['grupoIcono'] = BASE_PATH . '/public/uploads/grupos/' . $grupo['icono'];
            }
        }

        $data['grupos']        = $grupos ?? [];
        $data['acciones']      = $this->produccionModel->getAccionesRider();
        $data['turnoActualId'] = TurnoHelper::getTurnoActual($data['turnos']);
        $data['resumenPorGrupo'] = $resumenPorGrupo;

        return $data;
    }

    public function exportarPdfDiario(): void
    {
        $data = $this->buildIndexData();
        $pdfService = new PdfService();

        $data['logoBase64'] = $pdfService->convertirImagenABase64('images/icono_solver_nobg.png');

        // Convertimos los íconos de cada grupo a Base64 para que Dompdf los muestre sin problemas
        foreach ($data['produccionesResumen'] as &$grupo) {
            if (!empty($grupo['grupo_icono'])) {
                $grupo['grupo_icono_base64'] = $pdfService->convertirImagenABase64('uploads/grupos/' . $grupo['grupo_icono']);
            } else {
                $grupo['grupo_icono_base64'] = '';
            }
        }
        unset($grupo);

        // Usamos orientación 'landscape' (horizontal) para que entren cómodas las columnas de los riders
        $nombreArchivo = 'detalle_produccion_' . $data['fecha'] . '.pdf';

        $resultado = $pdfService->generarParaNavegador(
            'pdfs/produccion_diaria',
            $data,
            $nombreArchivo,
            false,
            'landscape'
        );

        if (!$resultado['success']) {
            echo htmlspecialchars($resultado['message']);
        }
    }

    private function renderLayout(string $view, array $data): void
    {
        $data['view'] = $view;
        require __DIR__ . '/../views/layouts/main.php';
    }
}