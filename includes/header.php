<?php
require "../config/conexion.php";
$config = $conexion->query("SELECT nombre FROM config LIMIT 1")->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

<style>

/* NAVBAR */
.navbar-custom{
    background:#000;
    box-shadow:0px 2px 10px rgba(0,0,0,0.4);
    min-height:75px;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:10px 20px;
    position: relative; /* 👈 importante */
}

/* TITULO CENTRADO REAL */
.titulo-navbar{
    position: absolute; /* 👈 clave */
    left: 50%;
    transform: translateX(-50%);

    font-family:'Poppins', sans-serif;
    font-size:32px;
    font-weight:800;
    letter-spacing:1px;
    color:#fff;
    text-shadow:0 3px 8px rgba(255,255,255,0.15);
    text-align:center;
}

/* TABLET */
@media (max-width:991px){
    .titulo-navbar{
        font-size:24px;
    }
}

/* CELULAR */
@media (max-width:576px){
    .titulo-navbar{
        font-size:20px;
        line-height:1.3;
    }

    .navbar-custom{
        padding:12px;
        min-height:auto;
    }
}

</style>
</head>

<body>

<nav class="navbar-custom">

    <div class="titulo-navbar">
        <?= $config['nombre'] ?>
    </div>

</nav>

</body>
</html>