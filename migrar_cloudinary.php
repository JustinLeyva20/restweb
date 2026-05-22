<?php
require "config/conexion.php";
require "config/cloudinary.php";

$tablas = [
    'platos'   => __DIR__ . '/uploads/platos/',
    'bebidas'  => __DIR__ . '/uploads/bebidas/',
    'postres'  => __DIR__ . '/uploads/postres/',
];

foreach ($tablas as $tabla => $dir) {
    echo "Migrando {$tabla}...\n";

    $stmt = $conexion->query("SELECT id, imagen FROM {$tabla} WHERE imagen IS NOT NULL AND imagen != ''");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        if (strpos($row['imagen'], 'http') === 0) {
            continue; // ya es URL
        }

        $ruta = $dir . $row['imagen'];
        if (!file_exists($ruta)) {
            echo "  [SKIP] id {$row['id']}: archivo no encontrado ({$row['imagen']})\n";
            continue;
        }

        echo "  Subiendo id {$row['id']}: {$row['imagen']}... ";
        $url = cloudinaryUpload($ruta);

        if ($url) {
            $upd = $conexion->prepare("UPDATE {$tabla} SET imagen = ? WHERE id = ?");
            $upd->execute([$url, $row['id']]);
            echo "OK -> {$url}\n";
        } else {
            echo "ERROR\n";
        }
    }
}

echo "\nMigración completada.\n";
