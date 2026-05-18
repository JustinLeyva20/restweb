<?php
require "../config/conexion.php";

$id = $_POST['id'];
$ruc = $_POST['ruc'];
$nombre = $_POST['nombre'];
$telefono = $_POST['telefono'];
$direccion = $_POST['direccion'];
$mensaje = $_POST['mensaje'];

$sql = $conexion->prepare("
    UPDATE config 
    SET ruc=?, nombre=?, telefono=?, direccion=?, mensaje=? 
    WHERE id=?
");

$sql->execute([$ruc, $nombre, $telefono, $direccion, $mensaje, $id]);

header("Location: ../views/config.php");