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
$correo         = trim($data['correo'] ?? '');
$passwordActual = $data['password_actual'] ?? '';
$passwordNueva  = $data['password_nueva'] ?? '';

if (empty($correo) || empty($passwordActual) || empty($passwordNueva)) {
    http_response_code(400);
    echo json_encode(["error" => "Todos los campos son requeridos"]);
    exit;
}

if (strlen($passwordNueva) < 6) {
    http_response_code(400);
    echo json_encode(["error" => "La nueva contraseña debe tener al menos 6 caracteres"]);
    exit;
}

$stmt = $conexion->prepare("SELECT pass FROM usuarios WHERE correo = ?");
$stmt->execute([$correo]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(["error" => "Usuario no encontrado"]);
    exit;
}

if (!password_verify($passwordActual, $user['pass'])) {
    http_response_code(401);
    echo json_encode(["error" => "La contraseña actual es incorrecta"]);
    exit;
}

$hash = password_hash($passwordNueva, PASSWORD_DEFAULT);
$stmt = $conexion->prepare("UPDATE usuarios SET pass = ? WHERE correo = ?");
$stmt->execute([$hash, $correo]);

echo json_encode(["mensaje" => "Contraseña actualizada correctamente"]);
