<?php
require "../config/conexion.php";

$id = $_POST['id'];
$ruc = $_POST['ruc'];
$nombre = $_POST['nombre'];
$telefono = $_POST['telefono'];
$direccion = $_POST['direccion'];
$mensaje = $_POST['mensaje'];
$horario_apertura = $_POST['horario_apertura'] ?? '08:00';
$horario_cierre = $_POST['horario_cierre'] ?? '20:00';

$sql = $conexion->prepare("
    UPDATE config 
    SET ruc=?, nombre=?, telefono=?, direccion=?, mensaje=?,
        horario_apertura=?, horario_cierre=?
    WHERE id=?
");

$sql->execute([$ruc, $nombre, $telefono, $direccion, $mensaje, $horario_apertura, $horario_cierre, $id]);

header("Location: ../views/config.php");