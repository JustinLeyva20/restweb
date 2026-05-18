<?php
require "../config/conexion.php";

$id = $_GET['id'];

$sql = $conexion->prepare("UPDATE pedidos SET estado='FINALIZADO' WHERE id=?");
$sql->execute([$id]);

header("Location: ../views/lista_pedidos.php");