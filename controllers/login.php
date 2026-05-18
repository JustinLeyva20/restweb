<?php
session_start();
require "../config/conexion.php";

$correo = $_POST['correo'];
$pass = $_POST['pass'];

$sql = $conexion->prepare("SELECT * FROM usuarios WHERE correo=?");
$sql->execute([$correo]);
$user = $sql->fetch();

if (!$user) {
    header("Location: ../views/login.php?error=1");
    exit;
}

$dbPass = $user['pass'];

if (password_get_info($dbPass)['algo']) {

    if (password_verify($pass, $dbPass)) {
$_SESSION['usuario'] = $user['nombre'];
$_SESSION['rol'] = $user['rol'];

if ($user['rol'] === 'Administrador') {
    header("Location: ../views/dashboard.php");
} else {
    header("Location: ../views/platos_usuario.php");
}
exit;

    } else {
        header("Location: ../views/login.php?error=2");
        exit;
    }

}

else {

    if ($pass === $dbPass) {

        $newHash = password_hash($pass, PASSWORD_BCRYPT);

        $update = $conexion->prepare("UPDATE usuarios SET pass=? WHERE id=?");
        $update->execute([$newHash, $user['id']]);

        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];

        header("Location: ../views/dashboard.php");
        exit;

    } else {
        header("Location: ../views/login.php?error=2");
        exit;
    }
}