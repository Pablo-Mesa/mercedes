<?php
// helpers/GrupoHelper.php
require_once __DIR__ . '/../models/GruposModel.php';

class GrupoHelper {
    public static function getGrupoIcono(): ?string {
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'operaciones' && !empty($_SESSION['grupo_id'])) {
            $grupoModel = new GruposModel();
            $grupo = $grupoModel->getById($_SESSION['grupo_id']);
            if (!empty($grupo['icono'])) {
                return '/mercedes/public/uploads/grupos/' . $grupo['icono'];
            }
        }
        return null;
    }
}