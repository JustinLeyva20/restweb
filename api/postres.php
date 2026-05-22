<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/conexion.php';

$stmt = $conexion->prepare("SELECT * FROM postres ORDER BY nombre ASC");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$popStmt = $conexion->query("SELECT LOWER(TRIM(t.nombre)) as nombre FROM (
    SELECT nombre FROM detalle_pedidos UNION ALL SELECT nombre FROM detalle_pedidos_web
) t GROUP BY LOWER(TRIM(t.nombre)) HAVING COUNT(*) >= 5");
$populares = [];
while ($row = $popStmt->fetch(PDO::FETCH_ASSOC)) {
    $populares[$row['nombre']] = true;
}

$result = [];
foreach ($items as $p) {
    $esNuevo = strtotime($p['fecha']) > strtotime('-30 days');
    $esPopular = isset($populares[strtolower(trim($p['nombre']))]);
    $result[] = [
        'id'     => (int)$p['id'],
        'nombre' => $p['nombre'],
        'precio' => (float)$p['precio'],
        'imagen' => $p['imagen'] ? ('../uploads/postres/' . $p['imagen']) : '',
        'badge'  => $esNuevo ? 'Nuevo' : ($esPopular ? 'Popular' : ''),
        'fecha'  => $p['fecha']
    ];
}

echo json_encode($result);
