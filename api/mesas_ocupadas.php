<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/conexion.php';

$fecha = trim($_GET['fecha'] ?? date('Y-m-d'));

$stmt = $conexion->prepare("SELECT id_sala, num_mesa FROM reservas WHERE estado = 'PENDIENTE' AND fecha = ?");
$stmt->execute([$fecha]);
$ocupadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($ocupadas as $o) {
    $result[] = [
        "id_sala" => (int)$o['id_sala'],
        "num_mesa" => (int)$o['num_mesa'],
    ];
}

echo json_encode($result);
