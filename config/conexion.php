<?php
$conexion = new PDO("mysql:host=localhost;dbname=restaurante", "root", "");
$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>