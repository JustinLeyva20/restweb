<?php
session_start();
require "../config/conexion.php";
require "../config/cloudinary.php";

function subirImagen(string $key): ?string
{
    if (empty($_FILES[$key]['name'])) return null;

    $ext      = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg','jpeg','png','webp','gif'];

    if (!in_array($ext, $allowed)) return null;
    if ($_FILES[$key]['size'] > 2 * 1024 * 1024) return null;

    $tmp = $_FILES[$key]['tmp_name'];
    return cloudinaryUpload($tmp);
}

if (isset($_GET['eliminar'])) {

    $id = (int)$_GET['eliminar'];

    $stmt = $conexion->prepare("SELECT imagen FROM postres WHERE id = ?");
    $stmt->execute([$id]);
    $postre = $stmt->fetch();

    if ($postre && $postre['imagen']) {
        cloudinaryDelete($postre['imagen']);
    }

    $conexion->prepare("DELETE FROM postres WHERE id = ?")->execute([$id]);
    header("Location: ../views/postres.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar') {

        $nombre = trim($_POST['nombre']);
        $precio = (float)$_POST['precio'];
        $imagen = subirImagen('imagen');

        $stmt = $conexion->prepare(
             "INSERT INTO postres (nombre, precio, imagen, fecha)
              VALUES (?, ?, ?, CURRENT_DATE)"
        );
        $stmt->execute([$nombre, $precio, $imagen]);

        header("Location: ../views/postres.php");
        exit;
    }

    if ($accion === 'editar') {

        $id     = (int)$_POST['id'];
        $nombre = trim($_POST['nombre']);
        $precio = (float)$_POST['precio'];
        $imagen = subirImagen('imagen');

        if ($imagen) {
            $stmt = $conexion->prepare("SELECT imagen FROM postres WHERE id = ?");
            $stmt->execute([$id]);
            $vieja = $stmt->fetchColumn();

            if ($vieja) {
                cloudinaryDelete($vieja);
            }

            $stmt = $conexion->prepare(
                "UPDATE postres SET nombre=?, precio=?, imagen=? WHERE id=?"
            );
            $stmt->execute([$nombre, $precio, $imagen, $id]);

        } else {
            $stmt = $conexion->prepare(
                "UPDATE postres SET nombre=?, precio=? WHERE id=?"
            );
            $stmt->execute([$nombre, $precio, $id]);
        }

        echo "ok";
        exit;
    }
}
