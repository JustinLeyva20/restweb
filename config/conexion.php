<?php
date_default_timezone_set('America/Lima');

// ── NEON POSTGRESQL ──
$conexion = new PDO(
    "pgsql:host=ep-blue-firefly-ajwhbjh6-pooler.c-3.us-east-2.aws.neon.tech port=5432 dbname=neondb sslmode=require options='endpoint=ep-blue-firefly-ajwhbjh6-pooler'",
    "neondb_owner",
    "npg_xs9OYRo8eltd"
);

$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fijar zona horaria de la sesión PostgreSQL a Perú
$conexion->exec("SET TIME ZONE 'America/Lima'");
?>