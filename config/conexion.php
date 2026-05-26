<?php
date_default_timezone_set('America/Lima');

// ── HOSTINGER MYSQL ──
$conexion = new PDO(
    "mysql:host=localhost;dbname=u895172347_ladelicia;charset=utf8",
    "u895172347_admin",
    "Counter_9090"
);

$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>