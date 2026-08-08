<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/TurnoHelper.php';

// Conexión a la BD
$db = Database::getConnection();

// Obtener turnos reales
$stmt = $db->prepare("SELECT * FROM turnos ORDER BY hora_inicio ASC");
$stmt->execute();
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular turno actual
$turnoActualId = TurnoHelper::getTurnoActual($turnos);

// Mostrar resultados
date_default_timezone_set('America/Asuncion');
echo "Hora actual en Paraguay: " . date('H:i:s') . PHP_EOL;
echo "Turno actual ID: " . $turnoActualId . PHP_EOL;

// Mostrar detalle del turno
foreach ($turnos as $t) {
    if ($t['id'] == $turnoActualId) {
        echo "Turno actual: {$t['turno']} ({$t['hora_inicio']} - {$t['hora_fin']})" . PHP_EOL;
        break;
    }
}
