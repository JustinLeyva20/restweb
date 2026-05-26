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
$nombre    = trim($data['nombre'] ?? '');
$correo    = trim($data['correo'] ?? '');
$pass      = $data['password'] ?? $data['pass'] ?? '';
$telefono  = trim($data['telefono'] ?? '');
$direccion = trim($data['direccion'] ?? '');

if (empty($nombre) || empty($correo) || empty($pass)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre, correo y contraseña requeridos']);
    exit;
}

$check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
$check->execute([$correo]);
if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'El correo ya está registrado']);
    exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, pass, telefono, direccion, rol) VALUES (?, ?, ?, ?, ?, 'cliente')");
$stmt->execute([$nombre, $correo, $hash, $telefono, $direccion]);

    $id = $conexion->lastInsertId();
    echo json_encode([
        'id'             => (int)$id,
        'nombre'         => $nombre,
        'correo'         => $correo,
        'telefono'       => $telefono,
        'direccion'      => $direccion,
        'fecha_registro' => date('Y-m-d H:i:s')
    ]);
