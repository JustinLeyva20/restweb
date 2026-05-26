<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $correo = trim($_GET['correo'] ?? '');
    if (empty($correo)) {
        http_response_code(400);
        echo json_encode(["error" => "Parámetro correo requerido"]);
        exit;
    }
    $stmt = $conexion->prepare("SELECT id, asunto, descripcion, created_at FROM reportes WHERE correo = ? ORDER BY created_at DESC");
    $stmt->execute([$correo]);
    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($reportes);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $correo      = trim($data['correo'] ?? '');
    $asunto      = trim($data['asunto'] ?? '');
    $descripcion = trim($data['descripcion'] ?? '');

    if (empty($correo) || empty($asunto) || empty($descripcion)) {
        http_response_code(400);
        echo json_encode(["error" => "Todos los campos son requeridos"]);
        exit;
    }

    $stmt = $conexion->prepare("INSERT INTO reportes (correo, asunto, descripcion) VALUES (?, ?, ?)");
    $stmt->execute([$correo, $asunto, $descripcion]);

    echo json_encode(["mensaje" => "Reporte enviado correctamente"]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
