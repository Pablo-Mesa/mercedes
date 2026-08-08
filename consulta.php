<?php
header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode(["error" => "Debe ingresar un valor"]);
    exit;
}

if (preg_match('/^\d+(-\d+)?$/', $q)) {
    // Si es número → consulta por RUC/CI
    $doc = explode("-", $q)[0];
    $url = "https://turuc.com.py/api/contribuyente/" . urlencode($doc);
} else {
    $q = $q."=GOB&page=0";
    // Si es texto → búsqueda por nombre
    $url = "https://turuc.com.py/api/contribuyente/search/" . urlencode($q);
}

$opts = [
    "http" => [
        "header" => "Accept: application/json\r\n"
    ]
];
$context = stream_context_create($opts);

$response = @file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode(["error" => "No se pudo consultar la API"]);
    exit;
}

echo $response;
