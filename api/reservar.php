<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

$id_sala  = (int)($data['id_sala'] ?? 0);
$num_mesa = (int)($data['num_mesa'] ?? 0);
$usuario  = trim($data['usuario'] ?? '');
$fecha    = trim($data['fecha'] ?? '');
$hora     = trim($data['hora'] ?? '');
$personas = (int)($data['personas'] ?? 1);
$nota     = trim($data['nota'] ?? '');

if (!$id_sala || !$num_mesa || !$usuario || !$fecha || !$hora || $personas < 1) {
    http_response_code(400);
    echo json_encode(["error" => "Completa todos los campos obligatorios"]);
    exit;
}

$stmt = $conexion->prepare(
    "SELECT COUNT(*) as c FROM reservas
     WHERE id_sala = ? AND num_mesa = ? AND fecha = ? AND estado = 'PENDIENTE'"
);
$stmt->execute([$id_sala, $num_mesa, $fecha]);
$ocupada = $stmt->fetch(PDO::FETCH_ASSOC)['c'] > 0;

if ($ocupada) {
    http_response_code(409);
    echo json_encode(["error" => "Esa mesa ya tiene una reserva activa para esa fecha"]);
    exit;
}

$stmt = $conexion->prepare(
    "INSERT INTO reservas (id_sala, num_mesa, usuario, fecha, hora, personas, nota)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([$id_sala, $num_mesa, $usuario, $fecha, $hora, $personas, $nota]);

$id = $conexion->lastInsertId();

http_response_code(201);
echo json_encode([
    "mensaje" => "Reserva confirmada",
    "id" => (int)$id,
    "id_sala" => $id_sala,
    "num_mesa" => $num_mesa,
    "fecha" => $fecha,
    "hora" => $hora,
    "personas" => $personas,
    "nota" => $nota,
    "estado" => "PENDIENTE",
]);
