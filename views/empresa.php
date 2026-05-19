<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }
if ($_SESSION['rol'] === 'Administrador') { header("Location: dashboard.php"); exit; }

$nombre_usuario = htmlspecialchars($_SESSION['usuario'] ?? 'Cliente');
$stmt = $conexion->query("SELECT ruc, nombre, telefono, direccion, mensaje FROM config LIMIT 1");
$datos_empresa = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Delicia — Nuestra Empresa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
    :root {
        --cream:    #F5EFE0;
        --warm:     #EDE0C4;
        --gold:     #C8962E;
        --gold-lt:  #E4B84A;
        --brown:    #3B2710;
        --brown-md: #5C3D1E;
        --green:    #2C4A2E;
        --green-lt: #4a7c4e;
        --red:      #8B1A1A;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--cream);
        color: var(--brown);
        overflow-x: hidden;
    }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(18px); }
        to   { opacity:1; transform:translateY(0); }
    }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
    @keyframes popIn {
        0%   { opacity:0; transform:scale(.85) translateY(16px); }
        70%  { transform:scale(1.03) translateY(-2px); }
        100% { opacity:1; transform:scale(1) translateY(0); }
    }

    /* ── TOP BAR ── */
    .top-bar {
        position: fixed; top:0; left:0; right:0; z-index:900;
        height: 64px;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 2rem 0 4.5rem;
        background: rgba(245,239,224,.92);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(200,150,46,.22);
    }
    .top-logo {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem; font-weight: 600; letter-spacing: .04em;
        color: var(--brown); text-decoration: none;
    }
    .top-logo span { color: var(--gold); }
    .top-nav { display:flex; gap:1.2rem; align-items:center; }
    .top-nav a {
        font-size:.8rem; font-weight:500; letter-spacing:.08em;
        text-transform:uppercase; color:var(--brown-md);
        text-decoration:none; transition:color .2s;
    }
    .top-nav a:hover { color:var(--gold); }
    .top-nav .cta {
        background:var(--gold); color:#fff;
        padding:.4rem 1.1rem; border-radius:2rem;
        font-size:.76rem; transition:background .2s, transform .2s;
        display: flex; align-items: center; gap: .4rem;
    }
    .top-nav .cta svg { width: 14px; height: 14px; stroke-width: 2.2; }
    .top-nav .cta:hover { background:var(--brown); transform:translateY(-1px); }

    main { padding-top: 64px; min-height: 100vh; }

    /* ── HERO ── */
    .page-hero {
        background: linear-gradient(135deg, #1f0f04 0%, var(--brown) 50%, var(--brown-md) 100%);
        padding: 2.8rem 3rem 2.2rem;
        position: relative; overflow: hidden;
    }
    .page-hero::before {
        content:'';
        position:absolute; top:-90px; right:-70px;
        width:280px; height:280px; border-radius:50%;
        background: radial-gradient(circle, rgba(200,150,46,.28), transparent 65%);
        pointer-events:none;
    }
    .page-hero::after {
        content:'';
        position:absolute; bottom:-60px; left:20%;
        width:200px; height:200px; border-radius:50%;
        background: radial-gradient(circle, rgba(44,74,46,.2), transparent 65%);
        pointer-events:none;
    }
    .hero-inner { position:relative; z-index:1; }
    .hero-inner h1 {
        font-family:'Cormorant Garamond',serif;
        font-size:clamp(2rem,4vw,3rem);
        font-weight:300; color:var(--cream); line-height:1.1;
        animation: fadeUp .6s .1s both;
    }
    .hero-inner h1 em { font-style:italic; color:var(--gold-lt); }
    .hero-inner p {
        color:rgba(245,239,224,.6); font-size:.9rem;
        margin-top:.5rem; font-weight:300;
        animation: fadeUp .6s .25s both;
    }

    /* ── CONTENIDO ── */
    .content-wrapper {
        padding: 2.5rem 3rem;
        max-width: 780px;
    }

    /* CARD EMPRESA */
    .empresa-card {
        background: #fff;
        border: 1px solid var(--warm);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(59,39,16,.08);
        animation: popIn .5s both;
    }

    /* BANNER */
    .empresa-banner {
        background: linear-gradient(135deg, #1f0f04 0%, var(--brown) 60%, var(--brown-md) 100%);
        padding: 2rem 2rem;
        display: flex; align-items: center; gap: 1.4rem;
        position: relative; overflow: hidden;
    }
    .empresa-banner::before {
        content:'';
        position:absolute; top:-50px; right:-40px;
        width:180px; height:180px; border-radius:50%;
        background: radial-gradient(circle, rgba(200,150,46,.25), transparent 65%);
        pointer-events:none;
    }
    .empresa-avatar {
        width: 68px; height: 68px; border-radius: 16px; flex-shrink:0;
        background: linear-gradient(135deg, var(--gold), #92400e);
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        box-shadow: 0 8px 20px rgba(200,150,46,.4);
        position: relative; z-index:1;
    }
    .empresa-avatar svg { width: 32px; height: 32px; stroke-width: 1.6; }

    .banner-info { position: relative; z-index:1; }
    .banner-nombre {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem; font-weight: 600;
        color: var(--cream); margin-bottom: .4rem;
    }
    .banner-ruc {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: 3px 12px; border-radius: 999px;
        background: rgba(200,150,46,.2);
        border: 1px solid rgba(200,150,46,.35);
        color: var(--gold-lt);
        font-size: .75rem; font-weight: 700; letter-spacing: .4px;
    }
    .banner-ruc svg { width: 12px; height: 12px; stroke-width: 2.2; }

    /* FILAS */
    .data-body { padding: 0; }

    .data-row {
        display: flex; align-items: center; gap: 1.2rem;
        padding: 1.2rem 2rem;
        border-bottom: 1px solid var(--warm);
        transition: background .15s;
        animation: fadeUp .5s both;
    }
    .data-row:last-child { border-bottom: none; }
    .data-row:hover { background: rgba(200,150,46,.04); }

    .data-icon {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink:0;
        display: flex; align-items: center; justify-content: center;
    }
    .data-icon svg { width: 20px; height: 20px; stroke-width: 1.8; }

    .icon-ruc  { background: rgba(200,150,46,.12); color: var(--gold);     }
    .icon-tel  { background: rgba(44,74,46,.1);    color: var(--green-lt); }
    .icon-dir  { background: rgba(99,102,241,.1);  color: #6366f1;         }
    .icon-msg  { background: rgba(236,72,153,.1);  color: #ec4899;         }

    .data-label {
        font-size: .7rem; font-weight: 700;
        letter-spacing: .5px; text-transform: uppercase;
        color: #a08060; margin-bottom: .25rem;
    }
    .data-value {
        color: var(--brown);
        font-size: .95rem; font-weight: 500;
        line-height: 1.5;
    }

    /* RESPONSIVE */
    @media (max-width:900px) {
        .content-wrapper { padding: 1.4rem; }
        .page-hero { padding: 2rem 1.4rem; }
    }
    </style>
</head>
<body>

<?php include '../includes/header_cliente.php'; ?>
<?php include '../includes/sidebar_cliente.php'; ?>

<main>

    <!-- HERO -->
    <div class="page-hero">
        <div class="hero-inner">
            <h1>Nuestra <em>empresa</em></h1>
            <p>Hola <?= $nombre_usuario ?>, aquí encontrarás toda la información de contacto del restaurante</p>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="empresa-card">

            <!-- BANNER -->
            <div class="empresa-banner">
                <div class="empresa-avatar">
                    <i data-lucide="utensils-crossed"></i>
                </div>
                <div class="banner-info">
                    <div class="banner-nombre"><?= htmlspecialchars($datos_empresa['nombre']) ?></div>
                    <span class="banner-ruc">
                        <i data-lucide="badge-check"></i>
                        RUC <?= htmlspecialchars($datos_empresa['ruc']) ?>
                    </span>
                </div>
            </div>

            <!-- DATOS -->
            <div class="data-body">

                <div class="data-row" style="animation-delay:.1s">
                    <div class="data-icon icon-ruc">
                        <i data-lucide="id-card"></i>
                    </div>
                    <div>
                        <div class="data-label">RUC</div>
                        <div class="data-value"><?= htmlspecialchars($datos_empresa['ruc']) ?></div>
                    </div>
                </div>

                <div class="data-row" style="animation-delay:.2s">
                    <div class="data-icon icon-tel">
                        <i data-lucide="phone"></i>
                    </div>
                    <div>
                        <div class="data-label">Teléfono</div>
                        <div class="data-value"><?= htmlspecialchars($datos_empresa['telefono']) ?></div>
                    </div>
                </div>

                <div class="data-row" style="animation-delay:.3s">
                    <div class="data-icon icon-dir">
                        <i data-lucide="map-pin"></i>
                    </div>
                    <div>
                        <div class="data-label">Dirección</div>
                        <div class="data-value"><?= htmlspecialchars($datos_empresa['direccion']) ?></div>
                    </div>
                </div>

                <div class="data-row" style="animation-delay:.4s">
                    <div class="data-icon icon-msg">
                        <i data-lucide="message-circle"></i>
                    </div>
                    <div>
                        <div class="data-label">Mensaje al cliente</div>
                        <div class="data-value"><?= htmlspecialchars($datos_empresa['mensaje']) ?></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</main>

<script>
    lucide.createIcons();
</script>

</body>
</html>