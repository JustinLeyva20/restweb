<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/conexion.php';

$stmt = $conexion->query("SELECT nombre, ruc, telefono, direccion, horario_apertura, horario_cierre FROM config LIMIT 1");
$cfg = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($cfg ?: [
    'nombre' => 'Restaurante',
    'ruc' => '',
    'telefono' => '',
    'direccion' => '',
    'horario_apertura' => '08:00',
    'horario_cierre' => '20:00'
]);
