<?php
require "../config/conexion.php";

# GUARDAR
if ($_POST['accion'] == "guardar") {

    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $rol = $_POST['rol'];
    $pass = password_hash(trim($_POST['pass']), PASSWORD_DEFAULT);

    $sql = $conexion->prepare("
        INSERT INTO usuarios(nombre, correo, pass, rol)
        VALUES (?, ?, ?, ?)
    ");

    $sql->execute([$nombre, $correo, $pass, $rol]);

    header("Location: ../views/usuarios.php");
    exit;
}
if ($_POST['accion'] == "editar") {

    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    $sql = $conexion->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=? WHERE id=?");
    $ok = $sql->execute([$nombre, $correo, $rol, $id]);

    if ($ok) {
        echo "ok";
    } else {
        echo "error";
    }
}

# ELIMINAR
if (isset($_GET['eliminar'])) {

    $id = $_GET['eliminar'];

    $sql = $conexion->prepare("DELETE FROM usuarios WHERE id=?");
    $sql->execute([$id]);

    header("Location: ../views/usuarios.php");
}