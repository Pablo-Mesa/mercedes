<?php

require_once __DIR__ . '/../repositories/AsistenciasRepository.php';

class AsistenciaStreamService
{
    private AsistenciasRepository $repository;

    public function __construct(AsistenciasRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Escuchar en tiempo real nuevas marcaciones de asistencia (SSE)
     */
    public function escucharNuevasLlegadas(int $ultimoId, string $fecha): void
    {

        $tiempoInicio = time();
        $duracionMaxima = 25; // Segundos antes de cerrar limpiamente para que el navegador reconecte

        while ((time() - $tiempoInicio) < $duracionMaxima) {
            if (connection_aborted()) {
                break;
            }

            $nuevasLlegadas = $this->repository->obtenerLlegadasPosterioresA($ultimoId, $fecha);

            if (!empty($nuevasLlegadas)) {
                foreach ($nuevasLlegadas as $llegada) {
                    $ultimoId = (int)$llegada['id'];
                    echo "id: {$ultimoId}\n";
                    echo "event: nueva_llegada\n";
                    echo "data: " . json_encode($llegada, JSON_UNESCAPED_UNICODE) . "\n\n";
                }
                flush();
            } else {
                echo ": ping\n\n";
                flush();
            }

            sleep(2);
        }
    }

}
