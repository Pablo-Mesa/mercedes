<?php
require_once __DIR__ . '/../helpers/TurnoHelper.php';

// Simulación de turnos cargados desde la BD
$turnos = [
    ['id' => 1, 'turno' => 'Mañana', 'hora_inicio' => '06:00:00', 'hora_fin' => '12:00:00'],
    ['id' => 2, 'turno' => 'Tarde',  'hora_inicio' => '12:00:00', 'hora_fin' => '18:00:00'],
    ['id' => 3, 'turno' => 'Noche',  'hora_inicio' => '18:00:00', 'hora_fin' => '23:59:59'],
];

// Función auxiliar para probar distintas horas
function probarHora($hora, $turnos) {
    // Forzamos la hora actual simulada
    $turnoId = null;
    foreach ($turnos as $t) {
        if ($hora >= $t['hora_inicio'] && ($t['hora_fin'] === null || $hora <= $t['hora_fin'])) {
            $turnoId = $t['id'];
            break;
        }
    }
    echo "Hora simulada: $hora => Turno ID: $turnoId\n";
}

// Casos de prueba
probarHora('07:30:00', $turnos); // debería ser Mañana (id=1)
probarHora('13:15:00', $turnos); // debería ser Tarde (id=2)
probarHora('20:45:00', $turnos); // debería ser Noche (id=3)
probarHora('23:59:59', $turnos); // debería ser Noche (id=3)
