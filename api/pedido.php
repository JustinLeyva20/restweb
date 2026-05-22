<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

$usuario      = trim($data['usuario'] ?? '');
$nombreCliente= trim($data['nombre_cliente'] ?? '');
$telefono     = trim($data['telefono'] ?? '');
$direccion    = trim($data['direccion'] ?? '');
$metodoPago   = strtoupper(trim($data['metodo_pago'] ?? ''));
$productos    = $data['productos'] ?? [];

$metodosValidos = ['EFECTIVO','YAPE','PLIN','TARJETA'];

if (empty($usuario) || empty($nombreCliente) || empty($direccion) || empty($productos) || !in_array($metodoPago, $metodosValidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Completa todos los campos requeridos']);
    exit;
}

$fecha = date('Y-m-d');
$hora  = date('H:i:s');
$total = 0;
foreach ($productos as $item) {
    $total += (float)($item['precio'] ?? 0) * (int)($item['cantidad'] ?? 1);
}

try {
    $conexion->beginTransaction();

    $stmt = $conexion->prepare("INSERT INTO pedidos_web (usuario, nombre_cliente, telefono, direccion, fecha, hora, metodo_pago, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$usuario, $nombreCliente, $telefono, $direccion, $fecha, $hora, $metodoPago, $total]);
    $pedidoId = $conexion->lastInsertId();

    $stmtDet = $conexion->prepare("INSERT INTO detalle_pedidos_web (id_pedido, nombre, precio, cantidad) VALUES (?, ?, ?, ?)");
    foreach ($productos as $item) {
        $stmtDet->execute([$pedidoId, $item['nombre'], (float)$item['precio'], (int)$item['cantidad']]);
    }

    $conexion->commit();

    echo json_encode([
        'pedido_id' => (int)$pedidoId,
        'mensaje'   => 'Pedido #' . str_pad($pedidoId, 3, '0', STR_PAD_LEFT) . ' realizado',
        'total'     => $total
    ]);
} catch (Exception $e) {
    $conexion->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar el pedido']);
}
