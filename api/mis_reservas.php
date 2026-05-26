<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/conexion.php';

$usuario = trim($_GET['usuario'] ?? '');
if (empty($usuario)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetro usuario requerido"]);
    exit;
}

$stmt = $conexion->prepare(
    "SELECT r.id, r.id_sala, s.nombre as sala_nombre, r.num_mesa, r.usuario,
            r.fecha, r.hora, r.personas, r.nota, r.estado, r.created_at
     FROM reservas r
     JOIN salas s ON s.id = r.id_sala
     WHERE r.usuario = ?
     ORDER BY r.fecha DESC, r.hora DESC"
);
$stmt->execute([$usuario]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = array_map(function ($r) {
    return [
        "id" => (int)$r['id'],
        "id_sala" => (int)$r['id_sala'],
        "sala_nombre" => $r['sala_nombre'],
        "num_mesa" => (int)$r['num_mesa'],
        "usuario" => $r['usuario'],
        "fecha" => $r['fecha'],
        "hora" => substr($r['hora'], 0, 5),
        "personas" => (int)$r['personas'],
        "nota" => $r['nota'],
        "estado" => $r['estado'],
        "created_at" => $r['created_at'],
    ];
}, $reservas);

echo json_encode($result);
