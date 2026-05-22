<?php
session_start();
require "../config/conexion.php";

$nombre   = trim($_POST['nombre']   ?? '');
$correo   = trim($_POST['correo']   ?? '');
$pass     = $_POST['pass']          ?? '';
$confirm  = $_POST['pass_confirm']  ?? '';
$telefono = trim($_POST['telefono'] ?? '');
$direccion= trim($_POST['direccion']?? '');
$rol      = 'Usuario'; // Siempre fijo, nunca del POST

// Validar campos obligatorios
if (empty($nombre) || empty($correo) || empty($pass) || empty($confirm)) {
    header("Location: ../views/register.php?error=3");
    exit;
}

// Validar que las contraseñas coincidan
if ($pass !== $confirm) {
    header("Location: ../views/register.php?error=2");
    exit;
}

// Verificar si el correo ya existe
$check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
$check->execute([$correo]);

if ($check->fetch()) {
    header("Location: ../views/register.php?error=1");
    exit;
}

// Hashear la contraseña
$hash = password_hash($pass, PASSWORD_BCRYPT);

// Insertar el nuevo usuario
$sql = $conexion->prepare(
    "INSERT INTO usuarios (nombre, correo, pass, rol, telefono, direccion)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$sql->execute([$nombre, $correo, $hash, $rol, $telefono ?: null, $direccion ?: null]);

header("Location: ../views/login.php?success=1");
exit;