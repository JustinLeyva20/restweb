<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $correo = trim($_GET['correo'] ?? '');
    if (empty($correo)) {
        http_response_code(400);
        echo json_encode(['error' => 'Correo requerido']);
        exit;
    }
    $stmt = $conexion->prepare("SELECT id, nombre, correo, telefono, direccion, created_at FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    echo json_encode([
        'id'             => (int)$user['id'],
        'nombre'         => $user['nombre'],
        'correo'         => $user['correo'],
        'telefono'       => $user['telefono'],
        'direccion'      => $user['direccion'],
        'fecha_registro' => $user['created_at'] ?? ''
    ]);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $correo    = trim($data['correo'] ?? '');
    $nombre    = trim($data['nombre'] ?? '');
    $telefono  = trim($data['telefono'] ?? '');
    $direccion = trim($data['direccion'] ?? '');

    if (empty($correo)) {
        http_response_code(400);
        echo json_encode(['error' => 'Correo requerido']);
        exit;
    }
    $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ? WHERE correo = ?");
    $stmt->execute([$nombre, $telefono, $direccion, $correo]);
    echo json_encode(['mensaje' => 'Perfil actualizado']);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
