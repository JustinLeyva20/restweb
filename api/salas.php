<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/conexion.php';

$stmt = $conexion->query("SELECT id, nombre, mesas FROM salas ORDER BY id ASC");
$salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = array_map(function ($s) {
    return [
        "id" => (int)$s['id'],
        "nombre" => $s['nombre'],
        "mesas" => (int)$s['mesas'],
    ];
}, $salas);

echo json_encode($result);
