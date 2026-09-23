<?php
/**
 * Control de asistencias para administracion y operaciones.
 * La consulta de llegadas se desarrollara en la vista correspondiente.
 */
require_once __DIR__ . '/BaseController.php';

class AdminAsistenciasController extends BaseController
{
    public function __construct()
    {
        $this->startSession();
        $this->requireRole(['admin', 'operaciones']);
        $this->requireToolEnabled(
            'tool_asistencias_enabled',
            'El Control de Asistencias esta desactivado.'
        );
    }

    public function index(): void
    {
        $view = 'asistencias/index';
        require __DIR__ . '/../views/layouts/main.php';
    }
}
