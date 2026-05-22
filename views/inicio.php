<?php
session_start();
require "../config/conexion.php";

// Seguridad
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Evitar que admin entre aquí
if ($_SESSION['rol'] === 'Administrador') {
    header("Location: dashboard.php");
    exit;
}

$nombre_usuario = $_SESSION['usuario'] ?? 'Cliente';
$usuario = $_SESSION['usuario'];
$hora = (int) date('H');
if ($hora < 12)      $saludo = "Buenos días";
elseif ($hora < 19)  $saludo = "Buenas tardes";
else                 $saludo = "Buenas noches";

$stats = [
    'mesas' => 0,
    'salas' => 0,
    'platos' => 0,
    'bebidas' => 0,
];

$statsRow = $conexion->query("
    SELECT
        COALESCE((SELECT SUM(mesas) FROM salas), 0) AS mesas,
        COALESCE((SELECT COUNT(*) FROM salas), 0) AS salas,
        COALESCE((SELECT COUNT(*) FROM platos), 0) AS platos,
        COALESCE((SELECT COUNT(*) FROM bebidas), 0) AS bebidas
")->fetch(PDO::FETCH_ASSOC);

if ($statsRow) {
    $stats['mesas'] = (int) $statsRow['mesas'];
    $stats['salas'] = (int) $statsRow['salas'];
    $stats['platos'] = (int) $statsRow['platos'];
    $stats['bebidas'] = (int) $statsRow['bebidas'];
}

$platosDiaStmt = $conexion->query("
    SELECT id, nombre, precio, fecha, imagen, 'plato' AS tipo
    FROM platos
    ORDER BY fecha DESC, id DESC
    LIMIT 3
");
$platosDia = $platosDiaStmt->fetchAll(PDO::FETCH_ASSOC);

$bebidasDiaStmt = $conexion->query("
    SELECT id, nombre, precio, fecha, imagen, 'bebida' AS tipo
    FROM bebidas
    ORDER BY fecha DESC, id DESC
    LIMIT 1
");
$recomendados = array_merge($platosDia, $bebidasDiaStmt->fetchAll(PDO::FETCH_ASSOC));

$pedidosRecientesStmt = $conexion->prepare("
    SELECT p.*,
           STRING_AGG(CONCAT(d.cantidad, 'x ', d.nombre), ' · ') AS detalle_resumen
    FROM pedidos_web p
    LEFT JOIN detalle_pedidos_web d ON d.id_pedido = p.id
    WHERE p.usuario = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 2
");
$pedidosRecientesStmt->execute([$usuario]);
$pedidosRecientes = $pedidosRecientesStmt->fetchAll(PDO::FETCH_ASSOC);

$configStmt = $conexion->query("
    SELECT ruc, nombre, telefono, direccion, mensaje
    FROM config
    ORDER BY id ASC
    LIMIT 1
");
$configRow = $configStmt->fetch(PDO::FETCH_ASSOC);
$config = [
    'ruc' => $configRow['ruc'] ?? '',
    'nombre' => $configRow['nombre'] ?? 'Restaurante La Delicia',
    'telefono' => $configRow['telefono'] ?? '',
    'direccion' => $configRow['direccion'] ?? '',
    'mensaje' => $configRow['mensaje'] ?? 'Gracias por su visita',
];

$config = [
    'ruc' => $configRow['ruc'] ?? '',
    'nombre' => $configRow['nombre'] ?? 'Restaurante La Delicia',
    'telefono' => $configRow['telefono'] ?? '',
    'direccion' => $configRow['direccion'] ?? '',
    'mensaje' => $configRow['mensaje'] ?? 'Gracias por su visita',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Delicia — Inicio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream:    #F5EFE0;
            --warm:     #EDE0C4;
            --gold:     #C8962E;
            --gold-lt:  #E4B84A;
            --brown:    #3B2710;
            --brown-md: #5C3D1E;
            --green:    #2C4A2E;
            --red:      #8B1A1A;
            --shadow:   rgba(59,39,16,.18);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--brown);
            overflow-x: hidden;
        }

        /* ── MAIN ── */
        main {
            margin-left: 0;
            padding-top: 64px;
            min-height: 100vh;
        }

        /* ── TOP BAR ── */
        .top-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 900;
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem 0 4.5rem;
            background: rgba(245,239,224,.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(200,150,46,.22);
        }
        .top-logo {
            font-family: 'Merriweather', serif;
            font-size: 1.55rem; font-weight: 600; letter-spacing: .04em;
            color: var(--brown); text-decoration: none;
        }
        .top-logo span { color: var(--gold); }
        .top-nav { display: flex; gap: 1.6rem; align-items: center; }
        .top-nav a {
            font-size: .8rem; font-weight: 500; letter-spacing: .08em;
            text-transform: uppercase; color: var(--brown-md);
            text-decoration: none; transition: color .2s;
        }
        .top-nav a:hover { color: var(--gold); }
        .top-nav .cta {
            background: var(--gold); color: #fff;
            padding: .4rem 1.1rem; border-radius: 2rem;
            font-size: .76rem; transition: background .2s, transform .2s;
        }
        .top-nav .cta:hover { background: var(--brown); transform: translateY(-1px); }

        /* ── HERO ── */
        .hero {
            position: relative; overflow: hidden;
            min-height: 520px;
            display: flex; align-items: flex-end;
            padding: 3rem 3.5rem;
        }
        .hero-bg {
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 70% 40%, #5C3D1E 0%, #3B2710 55%, #1a0f05 100%);
        }
        .hero-bg::after {
            content: '';
            position: absolute; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.08'/%3E%3C/svg%3E");
            opacity: .35; pointer-events: none;
        }

        .orb {
            position: absolute; border-radius: 50%;
            filter: blur(60px); pointer-events: none;
            animation: drift 8s ease-in-out infinite alternate;
        }
        .orb-1 { width:320px;height:320px; right:10%;top:-60px;   background:rgba(200,150,46,.22); animation-delay:0s;  }
        .orb-2 { width:220px;height:220px; right:35%;bottom:-40px; background:rgba(139,26,26,.28);  animation-delay:-3s; }
        .orb-3 { width:180px;height:180px; right:5%; bottom:20%;   background:rgba(44,74,46,.3);    animation-delay:-6s; }
        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(30px,20px) scale(1.08); }
        }

        .hero-content { position: relative; z-index: 2; max-width: 600px; }
        .hero-eyebrow {
            font-size: .72rem; letter-spacing: .2em; text-transform: uppercase;
            color: var(--gold-lt); margin-bottom: .8rem;
            opacity: 0; animation: fadeUp .6s .2s forwards;
        }
        .hero-title {
            font-family: 'Merriweather', serif;
            font-size: clamp(2.8rem,5vw,4.2rem);
            font-weight: 300; line-height: 1.08;
            color: var(--cream);
            opacity: 0; animation: fadeUp .7s .4s forwards;
        }
        .hero-title em { font-style: italic; color: var(--gold-lt); }
        .hero-sub {
            margin-top: 1rem; font-size: .95rem; font-weight: 300;
            color: rgba(245,239,224,.65); max-width: 420px; line-height: 1.65;
            opacity: 0; animation: fadeUp .7s .6s forwards;
        }
        .hero-actions {
            display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;
            opacity: 0; animation: fadeUp .7s .8s forwards;
        }
        .btn-primary {
            display: inline-flex; align-items: center; gap: .5rem;
            background: var(--gold); color: #fff;
            padding: .85rem 2rem; border-radius: 3rem;
            font-size: .88rem; font-weight: 500; letter-spacing: .06em;
            text-decoration: none; text-transform: uppercase;
            box-shadow: 0 8px 30px rgba(200,150,46,.45);
            transition: transform .25s, box-shadow .25s;
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 14px 36px rgba(200,150,46,.55); }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: .5rem;
            border: 1px solid rgba(245,239,224,.3); color: var(--cream);
            padding: .85rem 1.8rem; border-radius: 3rem;
            font-size: .88rem; font-weight: 300; letter-spacing: .04em;
            text-decoration: none;
            transition: border-color .2s, color .2s, background .2s;
        }
        .btn-ghost:hover { border-color: var(--gold-lt); color: var(--gold-lt); background: rgba(200,150,46,.08); }

        .hero-visual {
            position: absolute; right: 6%; top: 50%;
            transform: translateY(-50%);
            z-index: 2; width: 280px; height: 280px;
            opacity: 0;
            animation: fadeIn 1s 1s forwards, spin-slow 22s linear infinite;
        }
        @keyframes spin-slow {
            from { transform: translateY(-50%) rotate(0deg);   }
            to   { transform: translateY(-50%) rotate(360deg); }
        }
        @keyframes fadeIn  { to { opacity: 1; } }
        @keyframes fadeUp  {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        /* ── STATS BAR ── */
        .stats-bar {
            display: flex; justify-content: space-around; flex-wrap: wrap;
            background: var(--brown-md); padding: 1.4rem 3rem;
            border-bottom: 2px solid var(--gold);
        }
        .stat { text-align: center; padding: .5rem 1rem; }
        .stat-num {
            font-family: 'Merriweather', serif;
            font-size: 2rem; font-weight: 600; color: var(--gold-lt);
            display: block; line-height: 1;
        }
        .stat-label { font-size: .72rem; letter-spacing: .15em; text-transform: uppercase; color: rgba(245,239,224,.55); }

        /* ── CONTENT ── */
        .content { padding: 3rem 3.5rem; }

        /* ── GREETING ── */
        .greeting-card {
            background: linear-gradient(135deg, var(--warm) 0%, #f0e4c8 100%);
            border-left: 4px solid var(--gold);
            border-radius: 12px;
            padding: 1.6rem 2rem;
            margin-bottom: 3rem;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
            box-shadow: 0 4px 24px var(--shadow);
            animation: fadeUp .6s .3s both;
        }
        .greeting-text h2 {
            font-family: 'Merriweather', serif;
            font-size: 1.75rem; font-weight: 400; color: var(--brown);
        }
        .greeting-text h2 strong { color: var(--gold); font-weight: 600; }
        .greeting-text p { font-size: .88rem; color: var(--brown-md); margin-top: .3rem; }
        .live-time {
            font-family: 'Merriweather', serif;
            font-size: 2.2rem; color: var(--brown-md);
            min-width: 100px; text-align: right;
        }

        /* ── SECTION HEADER ── */
        .section-header { display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.6rem; }
        .section-header h3 {
            font-family: 'Merriweather', serif;
            font-size: 1.7rem; font-weight: 400; color: var(--brown);
            white-space: nowrap;
        }
        .section-header::after {
            content: ''; flex: 1; height: 1px;
            background: linear-gradient(to right, var(--gold), transparent);
        }

        /* ── PLATOS GRID ── */
        .platos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.4rem;
            margin-bottom: 3.5rem;
        }
        .plato-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 16px var(--shadow);
            transition: transform .3s, box-shadow .3s;
            position: relative;
            animation: fadeUp .5s both;
        }
        .plato-card:nth-child(1){ animation-delay:.1s; }
        .plato-card:nth-child(2){ animation-delay:.2s; }
        .plato-card:nth-child(3){ animation-delay:.3s; }
        .plato-card:nth-child(4){ animation-delay:.4s; }
        .plato-card:hover { transform: translateY(-6px); box-shadow: 0 12px 36px rgba(59,39,16,.2); }

        /* ── IMAGEN DEL PLATO/BEBIDA ── */
        .plato-img {
            height: 160px; width: 100%;
            background: var(--warm);
            display: flex; align-items: center; justify-content: center;
            font-size: 4rem; position: relative; overflow: hidden;
        }
        .plato-img img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .4s ease;
        }
        .plato-card:hover .plato-img img {
            transform: scale(1.06);
        }
        /* Overlay sutil sobre imagen */
        .plato-img::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(59,39,16,.15) 100%);
            pointer-events: none;
            z-index: 1;
        }
        /* Fallback emoji cuando no hay imagen */
        .plato-img-fallback {
            font-size: 4rem;
            display: flex; align-items: center; justify-content: center;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, var(--warm) 0%, #e8d4a8 100%);
        }
        .plato-badge {
            position: absolute; top: .7rem; right: .7rem;
            background: var(--gold); color: #fff;
            font-size: .65rem; font-weight: 600; letter-spacing: .1em;
            text-transform: uppercase; padding: .2rem .55rem; border-radius: 2rem;
            z-index: 2;
        }
        .plato-body { padding: 1rem 1.1rem 1.2rem; }
        .plato-name { font-family: 'Merriweather', serif; font-size: 1.1rem; font-weight: 600; color: var(--brown); }
        .plato-desc { font-size: .78rem; color: #7a6040; margin-top: .2rem; line-height: 1.5; }
        .plato-footer { display: flex; align-items: center; justify-content: space-between; margin-top: .9rem; }
        .plato-price { font-family: 'Merriweather', serif; font-size: 1.35rem; font-weight: 600; color: var(--gold); }
        .btn-add {
            width: 34px; height: 34px; border-radius: 50%; border: none;
            background: var(--brown); color: #fff; font-size: 1.2rem;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background .2s, transform .25s;
        }
        .btn-add:hover { background: var(--gold); transform: rotate(90deg) scale(1.1); }

        /* ── QUICK ACTIONS ── */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px,1fr));
            gap: 1.2rem;
            margin-bottom: 3.5rem;
        }
        .action-card {
            border-radius: 14px; padding: 1.6rem;
            display: flex; flex-direction: column; align-items: flex-start; gap: .8rem;
            text-decoration: none; color: var(--cream);
            transition: transform .3s, box-shadow .3s;
            animation: fadeUp .5s both;
            position: relative;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            min-height: 160px;
        }
        /* Overlay oscuro para legibilidad sobre la imagen */
        .action-card::before {
            content: '';
            position: absolute; inset: 0;
            background: rgba(25, 12, 3, 0.58);
            border-radius: 14px;
            transition: background .3s;
            z-index: 0;
        }
        .action-card:hover::before {
            background: rgba(25, 12, 3, 0.38);
        }
        /* Borde dorado sutil */
        .action-card::after {
            content: '';
            position: absolute; inset: 0;
            border-radius: 14px;
            border: 1px solid rgba(200,150,46,.25);
            pointer-events: none;
            z-index: 1;
            transition: border-color .3s;
        }
        .action-card:hover::after {
            border-color: rgba(200,150,46,.6);
        }
        /* Todos los hijos por encima del overlay */
        .action-card > * { position: relative; z-index: 2; }

        .action-card:nth-child(1){ animation-delay:.15s; }
        .action-card:nth-child(2){ animation-delay:.25s; }
        .action-card:nth-child(3){ animation-delay:.35s; }
        .action-card:nth-child(4){ animation-delay:.45s; }
        .action-card:nth-child(5){ animation-delay:.55s; }
        .action-card:hover { transform: translateY(-5px); box-shadow: 0 14px 40px rgba(0,0,0,.32); }

        .action-icon  { font-size: 2.2rem; }
        .action-label {
            font-family: 'Merriweather', serif;
            font-size: 1.2rem; font-weight: 600;
            color: #fff;
            text-shadow: 0 1px 6px rgba(0,0,0,.4);
        }
        .action-desc  {
            font-size: .78rem; opacity: .82; line-height: 1.4;
            color: rgba(245,239,224,.9);
            text-shadow: 0 1px 4px rgba(0,0,0,.5);
        }

        /* ── PEDIDOS ── */
        .pedidos-list { display: flex; flex-direction: column; gap: .9rem; margin-bottom: 3.5rem; }
        .pedido-row {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.4rem;
            display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap;
            box-shadow: 0 2px 14px var(--shadow);
            animation: fadeUp .5s both;
        }
        .pedido-row:nth-child(1){ animation-delay:.1s; }
        .pedido-row:nth-child(2){ animation-delay:.2s; }
        .pedido-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
        .dot-pendiente  { background: var(--gold); animation: pulse-dot 1.5s ease-in-out infinite; }
        .dot-finalizado { background: #2C4A2E; }
        .dot-cancelado  { background: var(--red);  }
        @keyframes pulse-dot {
            0%,100% { box-shadow: 0 0 0 4px rgba(200,150,46,.2); }
            50%      { box-shadow: 0 0 0 8px rgba(200,150,46,.07); }
        }
        .pedido-info { flex: 1; min-width: 160px; }
        .pedido-info strong { font-size: .95rem; color: var(--brown); display: block; }
        .pedido-info span   { font-size: .78rem; color: #8a6d40; }
        .pedido-total { font-family: 'Merriweather', serif; font-size: 1.4rem; font-weight: 600; color: var(--gold); }
        .pedido-estado {
            font-size: .7rem; font-weight: 600; letter-spacing: .1em;
            text-transform: uppercase; padding: .3rem .8rem; border-radius: 2rem;
        }
        .estado-PENDIENTE  { background: rgba(200,150,46,.15); color: var(--gold);  }
        .estado-PREPARANDO { background: rgba(200,150,46,.15); color: var(--gold);  }
        .estado-EN_CAMINO  { background: rgba(200,150,46,.15); color: var(--gold);  }
        .estado-ENTREGADO  { background: rgba(44,74,46,.15);   color: var(--green); }
        .estado-FINALIZADO { background: rgba(44,74,46,.15);   color: var(--green); }
        .estado-CANCELADO  { background: rgba(139,26,26,.15);  color: var(--red);   }

        /* ── FOOTER ── */
        .site-footer {
            text-align: center; padding: 2rem;
            border-top: 1px solid var(--warm);
            font-size: .78rem; color: #a08060; letter-spacing: .06em;
        }
        .site-footer span { color: var(--gold); }

        /* ── RESPONSIVE ── */
        @media (max-width: 640px) {
            .content { padding: 1.5rem 1.2rem; }
            .hero { padding: 2rem 1.4rem; min-height: 380px; }
            .hero-visual { display: none; }
            .stats-bar { padding: 1rem 1rem; gap: .5rem; }
            .top-nav .cta { display: none; }
        }
    </style>
</head>
<body>

<?php include '../includes/header_cliente.php'; ?>
<?php include '../includes/sidebar_cliente.php'; ?>

<main>

    <!-- ── HERO ── -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <svg class="hero-visual" viewBox="0 0 280 280" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="140" cy="140" r="130" stroke="rgba(200,150,46,.18)" stroke-width="1"/>
            <circle cx="140" cy="140" r="110" stroke="rgba(200,150,46,.12)" stroke-width=".5"/>
            <circle cx="140" cy="140" r="90"  stroke="rgba(200,150,46,.2)"  stroke-width="1" stroke-dasharray="4 6"/>
            <circle cx="140" cy="140" r="70"  fill="rgba(200,150,46,.06)" stroke="rgba(200,150,46,.25)" stroke-width="1"/>
            <text x="140" y="155" text-anchor="middle" font-size="54" font-family="serif">🍽</text>
        </svg>

        <div class="hero-content">
            <p class="hero-eyebrow">✦ Restaurante La Delicia · Lima, Perú</p>
            <h1 class="hero-title">
                Sabor que<br>
                <em>enamora</em> el paladar
            </h1>
            <p class="hero-sub">
                Explora nuestros platos y bebidas, realiza tus pedidos y reserva tu próxima visita.
            </p>
            <div class="hero-actions">
                <a href="platos_usuario.php" class="btn-primary">Ver platos</a>
                <a href="bebidas_usuario.php" class="btn-ghost">Ver bebidas →</a>
            </div>
        </div>
    </section>

    <!-- ── STATS BAR ── -->
    <div class="stats-bar">
        <div class="stat"><span class="stat-num"><?= $stats['mesas'] ?></span><span class="stat-label">Mesas registradas</span></div>
        <div class="stat"><span class="stat-num"><?= $stats['salas'] ?></span><span class="stat-label">Salas activas</span></div>
        <div class="stat"><span class="stat-num"><?= $stats['platos'] ?></span><span class="stat-label">Platos disponibles</span></div>
        <div class="stat"><span class="stat-num"><?= $stats['bebidas'] ?></span><span class="stat-label">Bebidas disponibles</span></div>
    </div>

    <!-- ── CONTENIDO ── -->
    <div class="content">

        <!-- Saludo -->
        <div class="greeting-card">
            <div class="greeting-text">
                <h2><?= $saludo ?>, <strong><?= htmlspecialchars($nombre_usuario) ?></strong></h2>
                <p>Hoy tenemos todo listo para ofrecerte la mejor experiencia. ¡Buen provecho!</p>
            </div>
            <div class="live-time" id="live-clock">--:--:--</div>
        </div>

        <!-- Recomendados -->
        <div class="section-header"><h3>Recomendados</h3></div>
        <div class="platos-grid">

            <?php
            $platoFallbacks  = ['🍗','🍚','🥘','🍲','🥗','🍜'];
            $bebidaFallbacks = ['🥤','🧃','☕','🧋','🍹','🥛'];

            foreach ($recomendados as $i => $item):
                $esBebida = $item['tipo'] === 'bebida';
                $carpeta  = $esBebida ? 'bebidas' : 'platos';
                $fallbacks = $esBebida ? $bebidaFallbacks : $platoFallbacks;
                $emoji    = $fallbacks[$i % count($fallbacks)];
                $nombre   = htmlspecialchars($item['nombre']);
                $precio   = number_format((float)$item['precio'], 2);
                $detalle  = $esBebida
                    ? 'Bebida disponible para acompañar tu pedido'
                    : 'Plato disponible en nuestra carta';
                $href     = $esBebida ? 'bebidas_usuario.php' : 'platos_usuario.php';

                // Ruta de la imagen desde la carpeta uploads
                $imgFile  = !empty($item['imagen']) ? trim($item['imagen']) : '';
                $imgPath  = $imgFile ? htmlspecialchars($imgFile) : '';
            ?>
            <a class="plato-card" href="<?= $href ?>" style="text-decoration:none;color:inherit;">
                <div class="plato-img">
                    <?php if ($imgPath): ?>
                        <img
                            src="<?= $imgPath ?>"
                            alt="<?= $nombre ?>"
                            loading="lazy"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <!-- Fallback emoji si la imagen falla -->
                        <div class="plato-img-fallback" style="display:none; position:absolute; inset:0;">
                            <?= $emoji ?>
                        </div>
                    <?php else: ?>
                        <div class="plato-img-fallback">
                            <?= $emoji ?>
                        </div>
                    <?php endif; ?>
                    <span class="plato-badge"><?= $esBebida ? 'Bebida' : 'Plato' ?></span>
                </div>
                <div class="plato-body">
                    <div class="plato-name"><?= $nombre ?></div>
                    <div class="plato-desc"><?= $detalle ?></div>
                    <div class="plato-footer">
                        <span class="plato-price">S/ <?= $precio ?></span>
                        <button class="btn-add" title="Ver" onclick="return false;">+</button>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>

            <?php if (empty($recomendados)): ?>
            <div class="plato-card">
                <div class="plato-img">
                    <div class="plato-img-fallback">🍽</div>
                </div>
                <div class="plato-body">
                    <div class="plato-name">Carta en preparación</div>
                    <div class="plato-desc">Aún no hay platos ni bebidas registrados.</div>
                    <div class="plato-footer">
                        <span class="plato-price">Pronto</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Acciones rápidas -->
        <div class="section-header"><h3>Acciones rápidas</h3></div>
        <div class="actions-grid">

            <a href="platos_usuario.php" class="action-card"
               style="background-image: url('../assets/img/platos.png');">
                <span class="action-label">Ver Platos</span>
                <span class="action-desc">Explora todos los platos y precios disponibles</span>
            </a>

            <a href="bebidas_usuario.php" class="action-card"
               style="background-image: url('../assets/img/bebidas.png');">
                <span class="action-label">Ver Bebidas</span>
                <span class="action-desc">Explora las bebidas disponibles para tu pedido</span>
            </a>

            <a href="mesas_cliente.php" class="action-card"
               style="background-image: url('../assets/img/reservar.png');">
                <span class="action-label">Reservar Mesa</span>
                <span class="action-desc">Aparta tu espacio en sala principal o segundo piso</span>
            </a>

            <a href="mis_pedidos.php" class="action-card"
               style="background-image: url('../assets/img/pedidos.png');">
                <span class="action-label">Mis Pedidos</span>
                <span class="action-desc">Revisa el estado y detalle de tus pedidos</span>
            </a>

            <a href="mis_reservas.php" class="action-card"
               style="background-image: url('../assets/img/reservas.png');">    
                <span class="action-label">Mis Reservas</span>
                <span class="action-desc">Consulta tus reservas realizadas</span>
            </a>

        </div>

        <!-- Pedidos recientes -->
        <div class="section-header"><h3>Pedidos recientes</h3></div>
        <div class="pedidos-list">

            <?php foreach ($pedidosRecientes as $pedido): ?>
            <?php
                $estado = $pedido['estado'] ?? 'PENDIENTE';
                $dotClass = in_array($estado, ['ENTREGADO', 'FINALIZADO'], true)
                    ? 'dot-finalizado'
                    : ($estado === 'CANCELADO' ? 'dot-cancelado' : 'dot-pendiente');
                $detalle = $pedido['detalle_resumen'] ?: 'Sin detalle registrado';
            ?>
            <div class="pedido-row">
                <div class="pedido-dot <?= $dotClass ?>"></div>
                <div class="pedido-info">
                    <strong>Pedido #<?= (int)$pedido['id'] ?></strong>
                    <span><?= htmlspecialchars($detalle) ?></span>
                </div>
                <span class="pedido-total">S/ <?= number_format((float)$pedido['total'], 2) ?></span>
                <span class="pedido-estado estado-<?= htmlspecialchars($estado) ?>">
                    <?= htmlspecialchars(str_replace('_', ' ', ucfirst(strtolower($estado)))) ?>
                </span>
            </div>
            <?php endforeach; ?>

            <?php if (empty($pedidosRecientes)): ?>
            <div class="pedido-row">
                <div class="pedido-dot dot-pendiente"></div>
                <div class="pedido-info">
                    <strong>Aún no tienes pedidos</strong>
                    <span>Cuando confirmes un pedido, aparecerá aquí.</span>
                </div>
                <a href="platos_usuario.php" class="btn-ghost" style="color:var(--brown);border-color:var(--warm);">Pedir ahora →</a>
            </div>
            <?php endif; ?>

        </div>

    </div><!-- /content -->

</main>

<script>
/* ── Reloj en vivo ── */
(function tick() {
    var now = new Date();
    var pad = function(n){ return String(n).padStart(2,'0'); };
    var el  = document.getElementById('live-clock');
    if (el) el.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    setTimeout(tick, 1000);
})();

/* ── Feedback btn-add ── */
document.querySelectorAll('.btn-add').forEach(function(btn){
    btn.addEventListener('click', function(e){
        e.preventDefault();
        this.textContent = '✓';
        this.style.background = 'var(--green)';
        var self = this;
        setTimeout(function(){ self.textContent = '+'; self.style.background = ''; }, 1300);
    });
});

/* ── Scroll-reveal con IntersectionObserver ── */
var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
        if (e.isIntersecting) {
            e.target.style.animationPlayState = 'running';
            io.unobserve(e.target);
        }
    });
}, { threshold: .12 });

document.querySelectorAll('.plato-card, .action-card, .pedido-row').forEach(function(el){
    el.style.animationPlayState = 'paused';
    io.observe(el);
});
</script>

</body>
</html>