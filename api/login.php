<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $correo = trim($data['correo'] ?? '');
    $pass   = $data['pass'] ?? '';

    if (empty($correo) || empty($pass)) {
        http_response_code(400);
        echo json_encode(['error' => 'Correo y contraseña requeridos']);
        exit;
    }

    $stmt = $conexion->prepare("SELECT id, nombre, correo, pass, telefono, direccion FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($pass, $user['pass'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales inválidas']);
        exit;
    }

    echo json_encode([
        'id'       => (int)$user['id'],
        'nombre'   => $user['nombre'],
        'correo'   => $user['correo'],
        'telefono' => $user['telefono'],
        'direccion'=> $user['direccion']
    ]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
