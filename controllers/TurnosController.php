<?php
/**
 * controllers/TurnosController.php
 * Controlador para gestionar turnos asociados a grupos.
 */
require_once __DIR__ . '/../models/TurnoModel.php';
require_once __DIR__ . '/../models/GruposModel.php';

class TurnosController extends BaseController
{
    private TurnoModel $turnoModel;
    private GruposModel $grupoModel;

    public function __construct(){
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/mercedes/login');
        }
        $this->turnoModel = new TurnoModel();
        $this->grupoModel = new GruposModel();
        $this->requireRole(['admin']);
        $this->requireToolEnabled('tool_cuaderno_enabled', 'El Cuaderno está desactivado.');
    }

    /**
     * Lista todos los turnos de un grupo.
     */
    public function index(): void
    {
        $id_grupo = (int)($_GET['id'] ?? 0);

        if ($id_grupo > 0) {
            // Vista normal de turnos por grupo
            $grupo = $this->grupoModel->getById($id_grupo);
            $turnos = $this->turnoModel->getByGrupo($id_grupo);
            $view = 'turnos/index';
        } else {
            // Vista general de todos los turnos agrupados por grupo
            $grupos = $this->grupoModel->getAll();
            $turnosPorGrupo = [];

            foreach ($grupos as $g) {
                $turnosPorGrupo[$g['id_grupo']] = $this->turnoModel->getByGrupo($g['id_grupo']);
            }

            $view = 'turnos/general';
        }

        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Muestra formulario de creación de turno.
     */
    public function create(): void
    {
        $id_grupo = (int)($_GET['id_grupo'] ?? 0);
        $grupo = $this->grupoModel->getById($id_grupo);

        $view = 'turnos/form';
        require __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Guarda un nuevo turno.
     */
    public function store(): void
    {
        $data = [
            'turno' => $_POST['turno'],
            'hora_inicio' => $_POST['hora_inicio'],
            'hora_fin' => $_POST['hora_fin'],
            'id_grupo' => (int)$_POST['id_grupo']
        ];

        // Procesar subida de icono
        if (!empty($_FILES['icono']['name'])) {
            $uploadDir = __DIR__ . '/../public/assets/icons/';
            // Generar nombre único para evitar colisiones
            $fileName = uniqid('turno_') . '_' . basename($_FILES['icono']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['icono']['tmp_name'], $targetPath)) {
                // Guardar ruta pública en BD
                $data['url_icono'] = '/mercedes/public/assets/icons/' . $fileName;
            }
        } else {
            // Si no se subió archivo, usar el valor del campo texto (opcional)
            $data['url_icono'] = $_POST['url_icono'] ?? null;
        }

        $this->turnoModel->insert($data);
        header("Location: /mercedes/turnos?id=" . $data['id_grupo']);
        exit;
    }

    /**
     * Muestra formulario de edición de turno.
     */
    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $turno = $this->turnoModel->getById($id);

        $view = 'turnos/form';
        require __DIR__ . '/../views/layouts/main.php';
    }
    
    /**
     * Actualiza un turno existente.
     */
    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'turno' => $_POST['turno'],
            'hora_inicio' => $_POST['hora_inicio'],
            'hora_fin' => $_POST['hora_fin'],
            'id_grupo' => (int)$_POST['id_grupo']
        ];

        // Procesar subida de icono
        if (!empty($_FILES['icono']['name'])) {
            $uploadDir = __DIR__ . '/../public/assets/icons/';
            $fileName = uniqid('turno_') . '_' . basename($_FILES['icono']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['icono']['tmp_name'], $targetPath)) {
                $data['url_icono'] = '/mercedes/public/assets/icons/' . $fileName;
            }
        } else {
            // Mantener el icono actual si no se subió uno nuevo
            $data['url_icono'] = $_POST['url_icono'] ?? null;
        }

        $this->turnoModel->update($id, $data);
        header("Location: /mercedes/turnos?id=" . $data['id_grupo']);
        exit;
    }

    /**
     * Elimina un turno.
     */
    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $turno = $this->turnoModel->getById($id);

        if ($turno) {
            $this->turnoModel->delete($id);
            header("Location: /mercedes/turnos?id=" . $turno['id_grupo']);
            exit;
        }
    }
}