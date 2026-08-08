<?php
class TurnoHelper {
    public static function getTurnoActual(array $turnos): ?int {
        date_default_timezone_set('America/Asuncion');
        $horaActual = date('H:i:s');

        foreach ($turnos as $t) {
            if ($horaActual >= $t['hora_inicio'] &&
                ($t['hora_fin'] === null || $horaActual <= $t['hora_fin'])) {
                return (int)$t['id'];
            }
        }
        return null;
    }
}
