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

/* ────────────────────────────────────────────────
   ELIMINAR
──────────────────────────────────────────────── */
if (isset($_GET['eliminar'])) {

    $id = (int)$_GET['eliminar'];

    /* Borrar imagen del disco si existe */
    $stmt = $conexion->prepare("SELECT imagen FROM bebidas WHERE id = ?");
    $stmt->execute([$id]);
    $bebida = $stmt->fetch();

    if ($bebida && $bebida['imagen']) {
        cloudinaryDelete($bebida['imagen']);
    }

    $conexion->prepare("DELETE FROM bebidas WHERE id = ?")->execute([$id]);
    header("Location: ../views/bebidas.php");
    exit;
}

/* ────────────────────────────────────────────────
   POST: guardar / editar
──────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    /* ── GUARDAR (nueva bebida) ── */
    if ($accion === 'guardar') {

        $nombre = trim($_POST['nombre']);
        $precio = (float)$_POST['precio'];
        $imagen = subirImagen('imagen'); // null si no se subió

        $stmt = $conexion->prepare(
             "INSERT INTO bebidas (nombre, precio, imagen, fecha)
              VALUES (?, ?, ?, CURRENT_DATE)"
        );
        $stmt->execute([$nombre, $precio, $imagen]);

        header("Location: ../views/bebidas.php");
        exit;
    }

    /* ── EDITAR (bebida existente) ── */
    if ($accion === 'editar') {

        $id     = (int)$_POST['id'];
        $nombre = trim($_POST['nombre']);
        $precio = (float)$_POST['precio'];
        $imagen = subirImagen('imagen'); // null si no se cambió

        if ($imagen) {
            /* Borrar imagen anterior */
            $stmt = $conexion->prepare("SELECT imagen FROM bebidas WHERE id = ?");
            $stmt->execute([$id]);
            $vieja = $stmt->fetchColumn();

            if ($vieja) {
                cloudinaryDelete($vieja);
            }

            $stmt = $conexion->prepare(
                "UPDATE bebidas SET nombre=?, precio=?, imagen=? WHERE id=?"
            );
            $stmt->execute([$nombre, $precio, $imagen, $id]);

        } else {
            /* Sin imagen nueva → solo nombre y precio */
            $stmt = $conexion->prepare(
                "UPDATE bebidas SET nombre=?, precio=? WHERE id=?"
            );
            $stmt->execute([$nombre, $precio, $id]);
        }

        echo "ok";
        exit;
    }
}
