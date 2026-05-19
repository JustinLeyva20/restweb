<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$cfg_path = __DIR__ . '/../config/tienda.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'toggle') {

    $valor = $_POST['valor'] ?? '';

    if ($valor === 'auto') {
        $manual = null;
    } elseif ($valor === '1') {
        $manual = true;
    } elseif ($valor === '0') {
        $manual = false;
    } else {
        echo json_encode(['ok' => false, 'error' => 'Valor inválido']);
        exit;
    }

    file_put_contents($cfg_path, json_encode(['manual_override' => $manual]));

    // Calcular estado actual
    require __DIR__ . '/../config/conexion.php';
    try {
        $stmt = $conexion->query("SELECT horario_apertura, horario_cierre FROM config LIMIT 1");
        $hconf = $stmt->fetch(PDO::FETCH_ASSOC);
        $h_aper = (int)explode(':', $hconf['horario_apertura'] ?? '08:00')[0];
        $h_cier = (int)explode(':', $hconf['horario_cierre'] ?? '20:00')[0];
    } catch (Exception $e) {
        $h_aper = 8; $h_cier = 20;
    }
    $hora_peru = (int)date('G', time() - 5 * 3600);
    $auto = $hora_peru >= $h_aper && $hora_peru < $h_cier;
    if ($manual === true) $abierta = true;
    elseif ($manual === false) $abierta = false;
    else $abierta = $auto;

    echo json_encode(['ok' => true, 'abierta' => $abierta, 'manual' => $manual]);
    exit;
}

// GET: consultar estado
$manual = null;
if (file_exists($cfg_path)) {
    $cfg = json_decode(file_get_contents($cfg_path), true);
    $manual = $cfg['manual_override'] ?? null;
}
require __DIR__ . '/../config/conexion.php';
try {
    $stmt = $conexion->query("SELECT horario_apertura, horario_cierre FROM config LIMIT 1");
    $hconf = $stmt->fetch(PDO::FETCH_ASSOC);
    $h_aper = (int)explode(':', $hconf['horario_apertura'] ?? '08:00')[0];
    $h_cier = (int)explode(':', $hconf['horario_cierre'] ?? '20:00')[0];
} catch (Exception $e) {
    $h_aper = 8; $h_cier = 20;
}
$hora_peru = (int)date('G', time() - 5 * 3600);
$auto = $hora_peru >= $h_aper && $hora_peru < $h_cier;
if ($manual === true) $abierta = true;
elseif ($manual === false) $abierta = false;
else $abierta = $auto;

echo json_encode(['ok' => true, 'abierta' => $abierta, 'manual' => $manual, 'hora' => $hora_peru]);
