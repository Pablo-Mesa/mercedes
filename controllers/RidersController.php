<?php
/**
 * controllers/RidersController.php
 * CRUD completo y reversible de riders, exclusivo para el rol 'admin'.
 */
require_once __DIR__ . '/../models/RiderModel.php';
require_once __DIR__ . '/../helpers/UploadHelper.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/GruposModel.php';

class RidersController extends BaseController {
    
    private RiderModel $riderModel;

    public function __construct() {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        $this->riderModel = new RiderModel();
        $this->requireRole(['admin', 'operaciones']);
        $this->requireAnyToolEnabled();
    }

    public function index(): void {
        $riders = $this->riderModel->getAll();
        $view = 'riders/index';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function create() {
        $rider = null;
        $formAction = '/mercedes/riders/crear';
        $grupos = (new GruposModel())->getAll(); // ✅ corregido
        $gruposAsignados = [];
        $view = 'riders/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $rider = $this->riderModel->getById($id);
        $formAction = '/mercedes/riders/editar?id=' . $id;
        $grupos = (new GruposModel())->getAll(); // ✅ corregido
        $gruposAsignados = $this->riderModel->getGroups($id);
        $view = 'riders/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function store(): void {
        $data = $this->procesarDatos();
        $riderId = $this->riderModel->insert($data);
        if (!empty($_POST['grupos'])) {
            $this->riderModel->assignToGroups($riderId, $_POST['grupos']);
        }
        header('Location: /mercedes/riders');
        exit;
    }

    public function update(): void {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id || !$this->riderModel->getById($id)) {
            $_SESSION['error'] = 'El rider solicitado no existe.';
            header('Location: /mercedes/riders');
            exit;
        }
        $data = $this->procesarDatos($_POST);
        $this->riderModel->update($id, $data);
        if (!empty($_POST['grupos'])) {
            $this->riderModel->assignToGroups($id, $_POST['grupos']);
        }
        $_SESSION['success'] = 'Rider actualizado correctamente.';
        header('Location: /mercedes/riders');
        exit;
    }

    public function toggleStatus(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->riderModel->toggleStatus($id);
            $_SESSION['success'] = 'Estado de rider actualizado correctamente.';
        }
        header('Location: /mercedes/riders');
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $this->riderModel->delete($id);
        header('Location: /mercedes/riders');
        exit;
    }

    private function procesarDatos(): array {
        $fotoPerfil = null;

        if (!empty($_POST['reset_foto'])) {
            $fotoPerfil = 'default.png';
        } elseif (!empty($_FILES['foto_perfil']['name'])) {
            $fotoPerfil = UploadHelper::upload($_FILES['foto_perfil'], 'riders');
        }

        return [
            'name' => trim($_POST['name']),
            'contacto' => trim($_POST['contacto']),
            'is_active' => (int)($_POST['is_active'] ?? 0),
            'foto_perfil' => $fotoPerfil ?? 'default.png',
        ];
    }
}