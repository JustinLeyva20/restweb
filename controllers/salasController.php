<?php
require "../config/conexion.php";

# GUARDAR
if (isset($_POST['accion']) && $_POST['accion'] == "guardar") {
    $nombre = $_POST['nombre'];
    $mesas = $_POST['mesas'];

    $sql = $conexion->prepare("INSERT INTO salas(nombre, mesas) VALUES (?, ?)");
    $sql->execute([$nombre, $mesas]);

    header("Location: ../views/salas.php");
}
# EDITAR
if (isset($_POST['accion']) && $_POST['accion'] == "editar") {

    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $mesas = $_POST['mesas'];

    $sql = $conexion->prepare("UPDATE salas SET nombre=?, mesas=? WHERE id=?");
    $sql->execute([$nombre, $mesas, $id]);

    echo "ok";
    exit;
}

# ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    $sql = $conexion->prepare("DELETE FROM salas WHERE id=?");
    $sql->execute([$id]);

    header("Location: ../views/salas.php");
}