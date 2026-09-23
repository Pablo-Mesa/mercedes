<?php
/**
 * controllers/GruposController.php
 * CRUD completo de grupos, exclusivo para el rol 'admin'.
 */
require_once __DIR__ . '/../models/GruposModel.php';
require_once __DIR__ . '/../models/GrupoEncabezadoTarifaModel.php';
require_once __DIR__ . '/BaseController.php';

class GruposController extends BaseController {

    private GruposModel $grupoModel;

    public function __construct() {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        $this->grupoModel = new GruposModel();
        $this->requireRole(['admin']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');
    }

    public function index(): void {
        $grupos = $this->grupoModel->getAll();
        $view = 'grupos/index';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function create() {
        $grupo = null;
        $formAction = '/mercedes/grupos/crear';

        // Cargar puntos de control dinámicamente
        $puntosControlModel = new PuntoControlModel();
        $puntosControl = $puntosControlModel->getAll();

        $view = 'grupos/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $grupo = $this->grupoModel->getById($id);
        $formAction = '/mercedes/grupos/editar?id=' . $id;

        // Cargar puntos de control dinámicamente
        $puntosControlModel = new PuntoControlModel();
        $puntosControl = $puntosControlModel->getAll();

        $view = 'grupos/form';
        require __DIR__ . '/../views/layouts/main.php';
    }


    public function store(): void {
        $data = $this->procesarDatos();
        $this->grupoModel->insert($data);
        header('Location: /mercedes/grupos');
        exit;
    }

    public function update(): void {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $data = $this->procesarDatos();
        $this->grupoModel->update($id, $data);
        header('Location: /mercedes/grupos');
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $this->grupoModel->delete($id);
        header('Location: /mercedes/grupos');
        exit;
    }

    private function procesarDatos(): array {
        $icono = null;

        if (!empty($_POST['reset_icono'])) {
            $icono = 'default.png';
        } elseif (!empty($_FILES['icono']['name'])) {
            $icono = UploadHelper::upload($_FILES['icono'], 'grupos');
        }

        return [
            'id_punto_control' => (int)($_POST['id_punto_control'] ?? 0),
            'nombre' => trim($_POST['nombre']),
            'descripcion' => trim($_POST['descripcion']),
            'icono' => $icono ?? 'default.png',
            'activo' => (int)($_POST['activo'] ?? 1),
        ];
    }

    public function asignarTarifas(): void {
        $id = (int)($_GET['id'] ?? 0);
        $grupo = $this->grupoModel->getById($id);

        $encabezadoModel = new EncabezadoTarifaModel();
        $encabezados = $encabezadoModel->getAll();

        $grupoEncabezadoModel = new GrupoEncabezadoTarifaModel();
        $encabezadosAsignados = $grupoEncabezadoModel->getEncabezadosByGrupo($id);

        $view = 'grupos/tarifas';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function guardarTarifas(): void {
        $id = (int)($_GET['id'] ?? 0);
        $encabezadosSeleccionados = $_POST['encabezados'] ?? [];

        $grupoEncabezadoModel = new GrupoEncabezadoTarifaModel();
        $grupoEncabezadoModel->syncEncabezados($id, $encabezadosSeleccionados);

        header("Location: /mercedes/grupos");
        exit;
    }

}