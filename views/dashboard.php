<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{
    --sidebar:#1d1f23;
    --glass:rgba(255,255,255,.08);
    --glass-border:rgba(255,255,255,.10);
    --shadow:0 18px 40px rgba(0,0,0,.35);
}

body{
    min-height:100vh;
    overflow-x:hidden;
    font-family:Segoe UI, sans-serif;
    position:relative;
}

/* Fondo */
body::before{
    content:"";
    position:fixed;
    inset:0;
    background:url('../assets/img/fnd2.jpg') no-repeat center center/cover;
    z-index:-2;
    transform:scale(1.03);
}

body::after{
    content:"";
    position:fixed;
    inset:0;
    background:linear-gradient(to bottom, rgba(0,0,0,.45), rgba(0,0,0,.55));
    z-index:-1;
}

/* Sidebar */
#sidebar{
    position:fixed;
    left:0;
    top:0;
    width:260px;
    height:100vh;
    background:var(--sidebar);
    z-index:1000;
    box-shadow:4px 0 20px rgba(0,0,0,.35);
}

/* Contenido */
.content-area{
    margin-left:260px;
    padding:22px;
    transition:.3s;
}

/* Caja principal */
.hero-box{
    position:relative;
    min-height:82vh;
    border-radius:24px;
    overflow:hidden;
    background:var(--glass);
    border:1px solid var(--glass-border);
    backdrop-filter:blur(6px);
    -webkit-backdrop-filter:blur(6px);
    box-shadow:var(--shadow);
    animation:fadeUp .6s ease;
}

/* Imagen */
.hero-box img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:transform .6s ease;
}

.hero-box:hover img{
    transform:scale(1.03);
}

/* Capa oscura */
.hero-overlay{
    position:absolute;
    inset:0;
    background:linear-gradient(
        to top,
        rgba(0,0,0,.72),
        rgba(0,0,0,.30),
        rgba(0,0,0,.15)
    );
}

/* Texto */
.hero-content{
    position:absolute;
    left:40px;
    bottom:40px;
    z-index:2;
    color:#fff;
    max-width:600px;
}

.hero-badge{
    display:inline-block;
    padding:7px 14px;
    border-radius:50px;
    background:rgba(255,255,255,.14);
    backdrop-filter:blur(8px);
    font-size:13px;
    margin-bottom:14px;
    letter-spacing:.5px;
}

.hero-content h1{
    font-size:42px;
    font-weight:700;
    margin-bottom:10px;
}

.hero-content p{
    font-size:16px;
    opacity:.92;
    margin-bottom:0;
}

/* Animación */
@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(25px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* Tablet */
@media (max-width:992px){
    .hero-content h1{
        font-size:34px;
    }
}

/* Móvil */
@media (max-width:768px){

    .content-area{
        margin-left:0;
        padding:14px;
    }

    .hero-box{
        min-height:65vh;
        border-radius:18px;
    }

    .hero-content{
        left:22px;
        right:22px;
        bottom:24px;
    }

    .hero-content h1{
        font-size:28px;
    }

    .hero-content p{
        font-size:14px;
    }
}
</style>
</head>

<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

    <div class="hero-box">

        <img src="../assets/img/img.png" alt="dashboard">

        <div class="hero-overlay"></div>

        <div class="hero-content">
            <span class="hero-badge">Panel Administrativo</span>
            <h1>Bienvenido al Sistema</h1>
            <p>Gestiona pedidos, usuarios, reportes y operaciones desde un solo lugar.</p>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>