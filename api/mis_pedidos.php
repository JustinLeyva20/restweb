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
    echo json_encode(['error' => 'Usuario requerido']);
    exit;
}

$stmt = $conexion->prepare("SELECT id, nombre_cliente, telefono, direccion, fecha, hora, metodo_pago, total, estado FROM pedidos_web WHERE usuario = ? ORDER BY fecha DESC, hora DESC");
$stmt->execute([$usuario]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($pedidos as $p) {
    $detStmt = $conexion->prepare("SELECT nombre, precio, cantidad FROM detalle_pedidos_web WHERE id_pedido = ?");
    $detStmt->execute([$p['id']]);
    $detalles = $detStmt->fetchAll(PDO::FETCH_ASSOC);

    $result[] = [
        'id'            => (int)$p['id'],
        'nombre_cliente'=> $p['nombre_cliente'],
        'telefono'      => $p['telefono'],
        'direccion'     => $p['direccion'],
        'fecha'         => $p['fecha'],
        'hora'          => $p['hora'],
        'metodo_pago'   => $p['metodo_pago'],
        'total'         => (float)$p['total'],
        'estado'        => $p['estado'],
        'productos'     => $detalles
    ];
}

echo json_encode($result);
