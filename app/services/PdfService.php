<?php

require_once __DIR__ . '/../../libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private string $viewsBasePath;
    private string $publicBasePath;

    public function __construct()
    {
        $this->viewsBasePath = __DIR__ . '/../../views/';
        $this->publicBasePath = __DIR__ . '/../../public/';
    }

    /**
     * Renderiza una vista y la envía al navegador (ver en pestaña o descargar).
     */
    public function generarParaNavegador(
        string $vistaPath,
        array $datos = [],
        string $nombreArchivo = 'documento.pdf',
        bool $forzarDescarga = false,
        string $orientacion = 'portrait'
    ): array {
        $resultado = $this->renderizarDompdf($vistaPath, $datos, $orientacion);

        if (!$resultado['success']) {
            return $resultado;
        }

        /** @var Dompdf $dompdf */
        $dompdf = $resultado['dompdf'];
        $dompdf->stream($nombreArchivo, ['Attachment' => $forzarDescarga]);

        return [
            'success' => true,
            'message' => 'PDF generado correctamente.'
        ];
    }

    /**
     * Renderiza una vista y guarda el archivo PDF en una ruta física (para backups).
     */
    public function guardarEnDisco(
        string $vistaPath,
        array $datos,
        string $rutaDestinoCompleta,
        string $orientacion = 'portrait'
    ): array {
        $resultado = $this->renderizarDompdf($vistaPath, $datos, $orientacion);

        if (!$resultado['success']) {
            return $resultado;
        }

        $directorio = dirname($rutaDestinoCompleta);
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
            return [
                'success' => false,
                'ruta' => null,
                'message' => 'No se pudo crear el directorio de destino.'
            ];
        }

        /** @var Dompdf $dompdf */
        $dompdf = $resultado['dompdf'];
        $contenidoPdf = $dompdf->output();

        if (file_put_contents($rutaDestinoCompleta, $contenidoPdf) === false) {
            return [
                'success' => false,
                'ruta' => null,
                'message' => 'Error al escribir el archivo PDF en el disco.'
            ];
        }

        return [
            'success' => true,
            'ruta' => $rutaDestinoCompleta,
            'message' => 'PDF guardado correctamente.'
        ];
    }

    /**
     * Convierte una imagen dentro de public/ a Base64 para incrustarla de forma segura en el PDF.
     */
    public function convertirImagenABase64(string $rutaRelativaEnPublic): string
    {
        $rutaAbsoluta = $this->publicBasePath . ltrim($rutaRelativaEnPublic, '/');

        if (!file_exists($rutaAbsoluta)) {
            return '';
        }

        $extension = pathinfo($rutaAbsoluta, PATHINFO_EXTENSION);
        $contenido = file_get_contents($rutaAbsoluta);

        if ($contenido === false) {
            return '';
        }

        return 'data:image/' . $extension . ';base64,' . base64_encode($contenido);
    }

    private function renderizarDompdf(string $vistaPath, array $datos, string $orientacion): array
    {
        $rutaPlantilla = $this->viewsBasePath . ltrim($vistaPath, '/') . '.php';

        if (!file_exists($rutaPlantilla)) {
            return [
                'success' => false,
                'dompdf' => null,
                'message' => 'La plantilla PDF no existe: ' . $vistaPath
            ];
        }

        extract($datos, EXTR_SKIP);

        ob_start();
        require $rutaPlantilla;
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientacion);
        $dompdf->render();

        return [
            'success' => true,
            'dompdf' => $dompdf,
            'message' => 'Renderizado exitoso.'
        ];
    }
}
