<?php
// Establecer zona horaria de Paraguay antes de cualquier función date()
date_default_timezone_set('America/Asuncion');

if (!defined('BASE_PATH')) {
    define('BASE_PATH', '');
}
// Evita que se ejecute desde el navegador por seguridad
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado. Este script solo puede ejecutarse desde consola.');
}

// 1. Cargar dependencias de tu proyecto (ajusta las rutas si tu conexión DB se carga en otro archivo)
require_once __DIR__ . '/../config/database.php'; // <-- Asegúrate de incluir tu archivo de conexión a BD
require_once __DIR__ . '/../models/ProduccionModel.php';
require_once __DIR__ . '/../app/services/ProduccionService.php';
require_once __DIR__ . '/../app/services/PdfService.php';

// 2. Definir la fecha a respaldar (puedes pasarla por argumento o usa la fecha actual)
$fecha = $argv[1] ?? date('Y-m-d');

// 3. Simular contexto de administrador para que la plantilla incluya todos los grupos
$_SESSION['user_role'] = 'admin';
$_SESSION['grupo_id']  = null;

echo "[{$fecha}] Iniciando generacion de backup PDF...\n";

try {
    // 4. Reutilizar exactamente tu lógica de datos existente
    $produccionModel   = new ProduccionModel();
    $produccionService = new ProduccionService($produccionModel);
    $pdfService        = new PdfService();

    $produccionesResumen = $produccionModel->getProduccionesConDetalle($fecha, null, null, null);

    // Convertir íconos de grupos a Base64
    foreach ($produccionesResumen as &$grupo) {
        if (!empty($grupo['grupo_icono'])) {
            $grupo['grupo_icono_base64'] = $pdfService->convertirImagenABase64('uploads/grupos/' . $grupo['grupo_icono']);
        } else {
            $grupo['grupo_icono_base64'] = '';
        }
    }
    unset($grupo);

    $data = [
        'fecha'               => $fecha,
        'turno'               => null,
        'rider'               => null,
        'accion'              => null,
        'logoBase64'          => $pdfService->convertirImagenABase64('images/iconodemiapp.png'),
        'produccionesResumen' => $produccionesResumen,
    ];

    // 5. Definir ruta en carpeta segura (organizada por Año/Mes)
    $carpetaMes = __DIR__ . '/../storage/backups_pdf/' . date('Y/m', strtotime($fecha));
    $nombreArchivo = 'produccion_' . $fecha . '_' . date('His') . '.pdf';
    $rutaDestino = $carpetaMes . '/' . $nombreArchivo;

    // 6. Guardar en disco usando el método que ya creamos en PdfService
    $resultado = $pdfService->guardarEnDisco(
        'pdfs/produccion_diaria',
        $data,
        $rutaDestino,
        'landscape'
    );

    if ($resultado['success']) {
        echo "[OK] Backup guardado en: {$resultado['ruta']}\n";
    } else {
        echo "[ERROR] {$resultado['message']}\n";
    }

} catch (Throwable $e) {
    echo "[EXCEPCION] " . $e->getMessage() . "\n";
}
