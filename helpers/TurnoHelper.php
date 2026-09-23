<?php
class TurnoHelper {
    public static function getTurnoActual(array $turnos): ?int {
        // Hora actual en Paraguay
        date_default_timezone_set('America/Asuncion');
        $horaActual = new DateTime('now');
        //
        foreach ($turnos as $t) {
            $inicio = new DateTime($t['hora_inicio']);
            $fin    = !empty($t['hora_fin']) ? new DateTime($t['hora_fin']) : new DateTime('23:59:59');

            // 🚀 Flexibilidad:
            // Entrada: permitir marcar 10 minutos antes
            $inicio->modify('-10 minutes');

            // Salida: permitir marcar hasta 90 minutos después
            $fin->modify('+90 minutes');

            if ($horaActual >= $inicio && $horaActual <= $fin) {
                return (int)$t['id'];
            }
        }
        return null;
    }
}
