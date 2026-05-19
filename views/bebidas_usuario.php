<?php
session_start();
require "../config/conexion.php";

// Seguridad
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['rol'] === 'Administrador') {
    header("Location: dashboard.php");
    exit;
}

$nombre_usuario = htmlspecialchars($_SESSION['usuario'] ?? 'Cliente');

// ── Estado de la tienda ──
$tienda_cfg_path = __DIR__ . '/../config/tienda.json';
$tienda_manual = null;
if (file_exists($tienda_cfg_path)) {
    $tienda_cfg = json_decode(file_get_contents($tienda_cfg_path), true);
    $tienda_manual = $tienda_cfg['manual_override'] ?? null;
}
try {
    $stmt = $conexion->query("SELECT horario_apertura, horario_cierre FROM config LIMIT 1");
    $hconf = $stmt->fetch(PDO::FETCH_ASSOC);
    $h_aper = (int)explode(':', $hconf['horario_apertura'] ?? '08:00')[0];
    $h_cier = (int)explode(':', $hconf['horario_cierre'] ?? '20:00')[0];
} catch (Exception $e) { $h_aper = 8; $h_cier = 20; }
$hora_peru = (int)date('G', time() - 5 * 3600);
$tienda_auto = $hora_peru >= $h_aper && $hora_peru < $h_cier;
if ($tienda_manual === true) $tienda_abierta = true;
elseif ($tienda_manual === false) $tienda_abierta = false;
else $tienda_abierta = $tienda_auto;

// Obtener bebidas
$sql = $conexion->prepare("SELECT * FROM bebidas ORDER BY nombre ASC");
$sql->execute();
$bebidas = $sql->fetchAll(PDO::FETCH_ASSOC);

// Convertir a JSON para JavaScript
$bebidasJson = json_encode($bebidas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Delicia — Bebidas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream:     #F5EFE0;
            --warm:      #EDE0C4;
            --gold:      #C8962E;
            --gold-lt:   #E4B84A;
            --brown:     #3B2710;
            --brown-md:  #5C3D1E;
            --green:     #2C4A2E;
            --red:       #8B1A1A;
            --shadow:    rgba(59,39,16,.15);
            --shadow-lg: rgba(59,39,16,.25);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--brown);
            overflow-x: hidden;
        }

        /* ── TOP BAR ─────────────────────────── */
        .top-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 900;
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

        /* ── CARRITO ICONO (top bar) ─────────── */
        .cart-trigger {
            position: relative;
            background: none; border: none; cursor: pointer;
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px;
            background: rgba(200,150,46,.12);
            color: var(--brown);
            transition: background .2s, transform .2s;
        }
        .cart-trigger:hover { background: rgba(200,150,46,.25); transform: scale(1.06); }
        .cart-trigger svg { width: 22px; height: 22px; }
        .cart-badge {
            position: absolute; top: 4px; right: 4px;
            width: 18px; height: 18px; border-radius: 50%;
            background: var(--gold); color: #fff;
            font-size: .65rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transform: scale(0);
            transition: opacity .2s, transform .25s cubic-bezier(.34,1.56,.64,1);
        }
        .cart-badge.visible { opacity: 1; transform: scale(1); }

        /* ── MAIN ────────────────────────────── */
        main {
            padding-top: 64px;
            min-height: 100vh;
        }

        /* ── PAGE HERO ───────────────────────── */
        .page-hero {
            background: linear-gradient(135deg, var(--brown) 0%, var(--brown-md) 100%);
            padding: 3rem 3rem 2.5rem;
            position: relative; overflow: hidden;
        }
        .page-hero::before {
            content: '';
            position: absolute; top: -80px; right: -60px;
            width: 260px; height: 260px; border-radius: 50%;
            background: radial-gradient(circle, rgba(200,150,46,.25), transparent 70%);
            pointer-events: none;
        }
        .page-hero-content { position: relative; z-index: 1; }
        .page-hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 300; color: var(--cream); line-height: 1.1;
            animation: fadeUp .6s .1s both;
        }
        .page-hero h1 em { font-style: italic; color: var(--gold-lt); }
        .page-hero p {
            color: rgba(245,239,224,.6); font-size: .9rem;
            margin-top: .5rem; font-weight: 300;
            animation: fadeUp .6s .25s both;
        }

        @keyframes fadeUp {
            from { opacity:0; transform: translateY(18px); }
            to   { opacity:1; transform: translateY(0); }
        }

        /* ── SEARCH BAR ──────────────────────── */
        .search-wrap {
            padding: 1.8rem 3rem;
            display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;
            background: var(--warm);
            border-bottom: 1px solid rgba(200,150,46,.2);
        }
        .search-box {
            flex: 1; min-width: 220px;
            position: relative;
        }
        .search-box svg {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; color: var(--brown-md); pointer-events: none;
        }
        .search-input {
            width: 100%;
            padding: .75rem 1rem .75rem 2.8rem;
            border: 1.5px solid rgba(200,150,46,.3);
            border-radius: 10px;
            background: rgba(245,239,224,.8);
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem; color: var(--brown);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .search-input::placeholder { color: #a08060; }
        .search-input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(200,150,46,.15);
        }
        .search-count {
            font-size: .82rem; color: var(--brown-md);
            white-space: nowrap;
        }
        .search-count strong { color: var(--gold); font-weight: 600; }

        /* ── GRID DE BEBIDAS ──────────────────── */
        .bebidas-section { padding: 2rem 3rem 4rem; }

        .bebidas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        /* Sin resultados */
        .no-results {
            display: none;
            grid-column: 1/-1;
            text-align: center; padding: 4rem 1rem;
        }
        .no-results .nr-icon { font-size: 3.5rem; margin-bottom: 1rem; }
        .no-results p { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; color: var(--brown-md); }

        /* ── CARD ────────────────────────────── */
        .bebida-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 20px var(--shadow);
            transition: transform .3s, box-shadow .3s;
            display: flex; flex-direction: column;
            animation: fadeUp .5s both;
        }
        .bebida-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 40px var(--shadow-lg);
        }
#cartFloatingBtn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #C8962E;
    color: #fff;
    border: none;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(0,0,0,.25);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    transition: transform .2s, box-shadow .2s;
}

#cartFloatingBtn.visible {
    display: flex;
}

#cartFloatingBtn:hover {
    transform: scale(1.08);
    box-shadow: 0 10px 30px rgba(0,0,0,.35);
}

#cartFloatingBadge {
    position: absolute;
    top: 6px;
    right: 6px;
    background: red;
    color: #fff;
    font-size: 11px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 50px;
    display: none;
}
        /* Imagen / emoji área */
.card-img {
    height: 160px;
    background: linear-gradient(135deg, var(--warm), #efe2c0);
    position: relative;
    flex-shrink: 0;
    overflow: hidden;
}

        /* Imagen real de la bebida */
.card-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .4s ease;
}

.bebida-card:hover .card-thumb {
    transform: scale(1.05);
}
        .card-img::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to bottom, transparent 55%, rgba(59,39,16,.08) 100%);
        }
        .card-badge {
            position: absolute; top: .8rem; right: .8rem;
            background: var(--gold); color: #fff;
            font-size: .62rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; padding: .22rem .6rem; border-radius: 2rem;
            z-index: 1;
        }

        /* Body */
        .card-body {
            padding: 1.1rem 1.2rem;
            flex: 1; display: flex; flex-direction: column;
        }
        .card-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem; font-weight: 600; color: var(--brown);
            line-height: 1.2;
        }
        /* Footer de la card */
        .card-footer {
            display: flex; align-items: center; justify-content: space-between;
            gap: .8rem;
            flex-wrap: wrap;
            margin-top: auto; padding-top: 1rem;
            border-top: 1px solid var(--warm);
        }
        .cart-icon{
    width: 40px;
    height: 40px;
    object-fit: contain;
}
        .card-price {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem; font-weight: 600; color: var(--gold);
            flex-shrink: 0;
        }
        .card-price small { font-size: .85rem; font-weight: 400; color: var(--brown-md); }

        /* Controles cantidad + añadir */
        .card-controls {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .5rem;
            flex: 1 1 150px;
            min-width: 0;
        }
        .qty-btn {
            width: 28px; height: 28px; border-radius: 50%; border: 1.5px solid var(--warm);
            background: transparent; color: var(--brown-md);
            font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: border-color .2s, background .2s, color .2s;
            font-weight: 600; line-height: 1;
        }
        .qty-btn:hover { border-color: var(--gold); background: rgba(200,150,46,.1); color: var(--gold); }
        .qty-display {
            min-width: 24px; text-align: center;
            font-size: .9rem; font-weight: 600; color: var(--brown);
        }
        .btn-add-cart {
            display: flex; align-items: center; gap: .4rem;
            justify-content: center;
            background: var(--brown); color: #fff;
            border: none; border-radius: 8px;
            padding: .5rem .9rem; cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            font-size: .78rem; font-weight: 500;
            transition: background .2s, transform .2s;
            white-space: nowrap;
            min-width: 92px;
            flex-shrink: 0;
        }
        .btn-add-cart svg { width: 14px; height: 14px; flex-shrink: 0; }
        .btn-add-cart:hover { background: var(--gold); transform: translateY(-1px); }
        .btn-add-cart.added { background: var(--green); }

        /* ══════════════════════════════════════
           PANEL CARRITO (drawer lateral)
        ══════════════════════════════════════ */
        #cart-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(59,39,16,.5);
            backdrop-filter: blur(3px);
            z-index: 1300;
        }
        #cart-overlay.visible { display: block; }

        #cart-panel {
            position: fixed; top: 0; right: -420px; width: 400px; height: 100vh;
            background: #fff;
            z-index: 1400;
            display: flex; flex-direction: column;
            box-shadow: -8px 0 40px rgba(59,39,16,.2);
            transition: right .35s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
        }
        #cart-panel.open { right: 0; }

        /* Header del carrito */
        .cart-header {
            padding: 1.4rem 1.5rem;
            background: var(--brown);
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .cart-header-left { display: flex; align-items: center; gap: .7rem; }
        .cart-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem; font-weight: 400; color: var(--cream);
        }
        .cart-header-count {
            background: var(--gold); color: #fff;
            font-size: .72rem; font-weight: 700;
            padding: .15rem .5rem; border-radius: 2rem;
        }
        .cart-close {
            background: rgba(245,239,224,.1); border: none; border-radius: 8px;
            width: 36px; height: 36px; cursor: pointer; color: var(--cream);
            display: flex; align-items: center; justify-content: center;
            transition: background .2s;
        }
        .cart-close:hover { background: rgba(245,239,224,.2); }
        .cart-close svg { width: 18px; height: 18px; }

        /* Lista de ítems */
        .cart-items {
            flex: 1; overflow-y: auto; padding: 1rem 1.2rem;
        }
        .cart-items::-webkit-scrollbar { width: 4px; }
        .cart-items::-webkit-scrollbar-thumb { background: var(--warm); border-radius: 2px; }

        /* Vacío */
        .cart-empty {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; height: 100%;
            color: var(--brown-md); text-align: center; gap: .8rem;
        }
        .cart-empty .ce-icon { font-size: 3.5rem; opacity: .4; }
        .cart-empty p { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; color: var(--brown-md); }
        .cart-empty small { font-size: .8rem; color: #a08060; }

        /* Ítem del carrito */
        .cart-item {
            display: flex; align-items: center; gap: .9rem;
            padding: .9rem 0;
            border-bottom: 1px solid var(--warm);
            animation: fadeUp .3s both;
        }
        .ci-emoji { font-size: 2rem; flex-shrink: 0; }
        .ci-info { flex: 1; min-width: 0; }
        .ci-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem; font-weight: 600; color: var(--brown);
            line-height: 1.25;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .ci-unit { font-size: .75rem; color: #a08060; }
        .ci-controls { display: flex; align-items: center; gap: .4rem; flex-shrink: 0; }
        .ci-qty-btn {
            width: 26px; height: 26px; border-radius: 50%;
            border: 1.5px solid var(--warm); background: transparent;
            color: var(--brown-md); font-size: .9rem; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: border-color .2s, background .2s;
            line-height: 1;
        }
        .ci-qty-btn:hover { border-color: var(--gold); background: rgba(200,150,46,.1); }
        .ci-qty-btn.remove { border-color: rgba(139,26,26,.3); color: var(--red); }
        .ci-qty-btn.remove:hover { background: rgba(139,26,26,.1); }
        .ci-qty { font-size: .9rem; font-weight: 600; min-width: 20px; text-align: center; color: var(--brown); }
        .ci-subtotal {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.05rem; font-weight: 600; color: var(--gold);
            min-width: 60px; text-align: right; flex-shrink: 0;
        }

        /* Footer del carrito */
        .cart-footer {
            padding: 1.2rem 1.5rem;
            border-top: 2px solid var(--warm);
            background: #faf6ee;
            flex-shrink: 0;
        }
        .cart-summary { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem; }
        .cart-summary .label { font-size: .85rem; color: var(--brown-md); }
        .cart-total {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem; font-weight: 600; color: var(--brown);
        }
        .cart-total span { font-size: .9rem; color: var(--gold); font-weight: 400; }
        .btn-checkout {
            width: 100%;
            padding: .95rem;
            background: var(--gold); color: #fff; border: none;
            border-radius: 10px; cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem; font-weight: 600; letter-spacing: .04em;
            text-transform: uppercase;
            box-shadow: 0 6px 20px rgba(200,150,46,.4);
            transition: background .2s, transform .2s, box-shadow .2s;
            display: flex; align-items: center; justify-content: center; gap: .6rem;
        }
        .btn-checkout:hover { background: var(--brown); box-shadow: 0 8px 28px rgba(59,39,16,.35); transform: translateY(-1px); }
        .btn-checkout svg { width: 18px; height: 18px; }
        .btn-clear {
            width: 100%; margin-top: .6rem;
            padding: .6rem; background: transparent; color: #a08060;
            border: 1px solid var(--warm); border-radius: 8px; cursor: pointer;
            font-size: .78rem; font-family: 'DM Sans', sans-serif;
            transition: background .2s, color .2s;
        }
        .btn-clear:hover { background: rgba(139,26,26,.08); color: var(--red); border-color: rgba(139,26,26,.3); }

        /* ── TOAST ───────────────────────────── */
        #toast {
            position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%) translateY(20px);
            background: var(--brown); color: var(--cream);
            padding: .7rem 1.4rem; border-radius: 3rem;
            font-size: .85rem; font-weight: 500;
            box-shadow: 0 6px 24px rgba(59,39,16,.35);
            opacity: 0; transition: opacity .3s, transform .3s;
            pointer-events: none; white-space: nowrap; z-index: 2000;
        }
        #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

        /* ── TIENDA CERRADA ──────────────────── */
        .store-closed-banner {
            background: linear-gradient(135deg, #8B1A1A, #b91c1c);
            color: #fff;
            text-align: center;
            padding: .6rem 1rem;
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .store-closed-banner svg {
            width: 16px; height: 16px;
            vertical-align: middle; margin-right: 6px;
        }
        .btn-add-cart.disabled {
            background: #a08060 !important;
            cursor: not-allowed !important;
            opacity: .6;
        }
        .btn-add-cart.disabled:hover {
            background: #a08060 !important;
            transform: none !important;
        }
        .qty-btn.disabled {
            opacity: .4;
            cursor: not-allowed !important;
            pointer-events: none;
        }
        .ci-qty-btn.disabled {
            opacity: .4;
            cursor: not-allowed !important;
            pointer-events: none;
        }

        /* ── RESPONSIVE ──────────────────────── */
        @media (max-width: 640px) {
            .search-wrap, .bebidas-section { padding-left: 1.2rem; padding-right: 1.2rem; }
            .page-hero { padding: 2rem 1.4rem; }
            #cart-panel { width: 100%; right: -100%; }
        }
    </style>
</head>
<body>

<?php include '../includes/header_cliente.php'; ?>
<?php include '../includes/sidebar_cliente.php'; ?>

<main>

    <!-- HERO -->
    <div class="page-hero">
        <div class="page-hero-content">
            <h1>Nuestras <em>Bebidas</em></h1>
            <p>Hola <?= $nombre_usuario ?>, elige tus bebidas favoritas y agrégalas a tu pedido</p>
        </div>
    </div>

    <?php if (!$tienda_abierta): ?>
    <div class="store-closed-banner">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>
        Tienda cerrada — Horario de atención: 8:00 am a 8:00 pm
    </div>
    <?php endif; ?>

    <!-- BUSCADOR -->
    <div class="search-wrap">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                type="text"
                id="searchInput"
                class="search-input"
                placeholder="Buscar bebidas..."
                oninput="filterBebidas()"
                autocomplete="off"
            >
        </div>
        <p class="search-count">Mostrando <strong id="countNum"><?= count($bebidas) ?></strong> bebida<?= count($bebidas) !== 1 ? 's' : '' ?></p>
    </div>

    <!-- GRID -->
    <section class="bebidas-section">
        <div class="bebidas-grid" id="bebidasGrid">

            <?php
            $emojis = ['🥤','🧃','☕','🧋','🍹','🥛','🍵','🍶','🫖','🍺'];
            $badges = ['Popular','Chef','Nuevo','Especial',''];
            foreach ($bebidas as $i => $bebida):
                $emoji = $emojis[$i % count($emojis)];
                $badge = $badges[$i % count($badges)];
                $nombreSafe = htmlspecialchars($bebida['nombre']);
                $precio     = number_format((float)$bebida['precio'], 2);
            ?>
            <div class="bebida-card"
                 data-id="<?= $bebida['id'] ?>"
                 data-nombre="<?= strtolower($nombreSafe) ?>"
                 style="animation-delay: <?= min($i * 0.07, 0.6) ?>s">

<div class="card-img">
    <?php
$imgSrc = (!empty($bebida['imagen']))
    ? '../uploads/bebidas/' . htmlspecialchars($bebida['imagen'])
    : '../assets/img/default.jpg';
    ?>
    <img src="<?= $imgSrc ?>"
         alt="<?= $nombreSafe ?>"
         class="card-thumb"
         onerror="this.src='../assets/img/default.jpg'">

    <?php if ($badge): ?>
        <span class="card-badge"><?= $badge ?></span>
    <?php endif; ?>
</div>

                <div class="card-body">
                    <div class="card-name"><?= $nombreSafe ?></div>
                    <div class="card-footer">
                        <div class="card-price">
                            <small>S/</small> <?= $precio ?>
                        </div>

                        <div class="card-controls">
                            <button class="qty-btn"
                                onclick="changeQty(<?= $bebida['id'] ?>, -1)"
                                title="Menos">−</button>
                            <span class="qty-display" id="qty-<?= $bebida['id'] ?>">1</span>
                            <button class="qty-btn"
                                onclick="changeQty(<?= $bebida['id'] ?>, 1)"
                                title="Más">+</button>

                            <button class="btn-add-cart"
                                id="addbtn-<?= $bebida['id'] ?>" 
                                <?php
$imgCart = (!empty($bebida['imagen']))
    ? '../uploads/bebidas/' . htmlspecialchars($bebida['imagen'])
    : '../assets/img/default.jpg';
?>
onclick="addToCart(<?= $bebida['id'] ?>, '<?= addslashes($nombreSafe) ?>', <?= $bebida['precio'] ?>, '<?= $emoji ?>','<?= $imgCart ?>', 'bebida')"
                                title="Agregar al carrito">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                </svg>
                                Añadir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Sin resultados -->
            <div class="no-results" id="noResults">
                <div class="nr-icon">🔍</div>
                <p>No encontramos "<span id="noResultsTerm"></span>"</p>
            </div>

        </div>
    </section>

</main>

<!-- ══ CARRITO OVERLAY + PANEL ══ -->
<div id="cart-overlay" onclick="cartClose()"></div>

<div id="cart-panel">
    <div class="cart-header">
        <div class="cart-header-left">
            <h2>Tu Pedido</h2>
            <span class="cart-header-count" id="cartHeaderCount">0 ítems</span>
        </div>
        <button class="cart-close" onclick="cartClose()" title="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="cart-items" id="cartItems">
        <div class="cart-empty" id="cartEmpty">
            <div class="ce-icon">🛒</div>
            <p>Tu carrito está vacío</p>
            <small>Agrega bebidas desde la carta</small>
        </div>
    </div>

    <div class="cart-footer" id="cartFooter" style="display:none;">
        <div class="cart-summary">
            <span class="label">Total estimado</span>
            <div class="cart-total"><span>S/</span> <span id="cartTotal">0.00</span></div>
        </div>
        <button class="btn-checkout" onclick="irAPedido()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
            Confirmar pedido
        </button>
        <button class="btn-clear" onclick="clearCart()">Vaciar carrito</button>
    </div>
</div>

<!-- TOAST -->
<div id="toast"></div>

<script>
/* ══════════════════════════════════════
   ESTADO DEL CARRITO
══════════════════════════════════════ */
var CART_STORAGE_KEY = 'laDeliciaCart';
var TIENDA_ABIERTA = <?= $tienda_abierta ? 'true' : 'false' ?>;
var cart = loadCart(); // { key: { key, id, tipo, nombre, precio, emoji, imgSrc, qty } }

function loadCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_STORAGE_KEY)) || {};
    } catch (e) {
        return {};
    }
}

function saveCart() {
    if (Object.keys(cart).length === 0) {
        localStorage.removeItem(CART_STORAGE_KEY);
        sessionStorage.removeItem('cartData');
        return;
    }
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    sessionStorage.setItem('cartData', JSON.stringify(cart));
}

/* ── Abrir / cerrar carrito ── */
function cartOpen() {
    document.getElementById('cart-panel').classList.add('open');
    document.getElementById('cart-overlay').classList.add('visible');
    document.body.style.overflow = 'hidden';
}
function cartClose() {
    document.getElementById('cart-panel').classList.remove('open');
    document.getElementById('cart-overlay').classList.remove('visible');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e){ if(e.key==='Escape') cartClose(); });

/* ── Cambiar cantidad en la card ── */
function changeQty(id, delta) {
    var el = document.getElementById('qty-' + id);
    var val = parseInt(el.textContent) + delta;
    if (val < 1) val = 1;
    if (val > 99) val = 99;
    el.textContent = val;
}

/* ── Añadir al carrito ── */
function addToCart(id, nombre, precio, emoji, imgSrc, tipo) {
    if (!TIENDA_ABIERTA) {
        showToast('Tienda cerrada — Horario: 8:00 am a 8:00 pm');
        return;
    }
    var qty = parseInt(document.getElementById('qty-' + id).textContent);
    var key = (tipo || 'bebida') + '-' + id;

    if (cart[key]) {
        cart[key].qty += qty;
    } else {
        cart[key] = {
            key: key,
            id: id, 
            tipo: tipo || 'bebida',
            nombre: nombre, 
            precio: parseFloat(precio), 
            emoji: emoji, 
            imgSrc: imgSrc, 
            qty: qty 
        };
    }

    saveCart();
    renderCart();
    showToast('✓ ' + nombre + ' × ' + qty + ' añadido');
}       
/* ── Cambiar cantidad en carrito ── */
function cartChangeQty(key, delta) {
    if (!cart[key]) return;
    if (!TIENDA_ABIERTA && delta > 0) {
        showToast('Tienda cerrada — No puedes agregar más productos');
        return;
    }
    cart[key].qty += delta;
    if (cart[key].qty <= 0) {
        delete cart[key];
    }
    saveCart();
    renderCart();
}

/* ── Vaciar carrito ── */
function clearCart() {
    cart = {};
    saveCart();
    renderCart();
    showToast('Carrito vaciado');
}

/* ── Renderizar carrito ── */
function renderCart() {

    var ids = Object.keys(cart); // 👈 ESTA LÍNEA ES CLAVE

    var totalQty = 0;
    var totalPrice = 0;

    ids.forEach(function(id){
        totalQty   += cart[id].qty;
        totalPrice += cart[id].qty * cart[id].precio;
    });
    // Badge
var badge = document.getElementById('cartBadge');
if (badge) {
    badge.textContent = totalQty;
    if (totalQty > 0) badge.classList.add('visible');
    else              badge.classList.remove('visible');
}

    var floatingBtn = document.getElementById('cartFloatingBtn');
    var floatingBadge = document.getElementById('cartFloatingBadge');
    if (floatingBtn && floatingBadge) {
        floatingBadge.textContent = totalQty;
        floatingBadge.style.display = totalQty > 0 ? 'inline-block' : 'none';
        floatingBtn.classList.toggle('visible', totalQty > 0);
    }

    // Header count
    document.getElementById('cartHeaderCount').textContent = totalQty + ' ítem' + (totalQty !== 1 ? 's' : '');

    // Total
    document.getElementById('cartTotal').textContent = totalPrice.toFixed(2);

    // Lista
// Lista
var container = document.getElementById('cartItems');
var footer    = document.getElementById('cartFooter');

// Validación por seguridad
if (!container || !footer) {
    console.error("Faltan elementos del carrito");
    return;
}

if (ids.length === 0) {
    container.innerHTML = `
        <div class="cart-empty">
            <div class="ce-icon">🛒</div>
            <p>Tu carrito está vacío</p>
            <small>Agrega bebidas desde la carta</small>
        </div>
    `;
    footer.style.display = 'none';
    return;
}

// Si hay productos
footer.style.display = 'block';
container.innerHTML = '';

    ids.forEach(function(id){
        var item = cart[id];
        var div  = document.createElement('div');
        div.className = 'cart-item';
div.innerHTML =
    '<div class="ci-img-wrap">' +
        '<img src="' + item.imgSrc + '" ' +
             'onerror="this.src=\'../assets/img/default.jpg\'" ' +
             'style="width:44px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0;">' +
    '</div>' +
            '<div class="ci-info">' +
                '<div class="ci-name">' + item.nombre + '</div>' +
                '<div class="ci-unit">S/ ' + item.precio.toFixed(2) + ' c/u</div>' +
            '</div>' +
            '<div class="ci-controls">' +
                '<button type="button" class="ci-qty-btn remove" data-key="' + id + '" data-delta="-1" title="Quitar uno">−</button>' +
                '<span class="ci-qty">' + item.qty + '</span>' +
                '<button type="button" class="ci-qty-btn" data-key="' + id + '" data-delta="1" title="Agregar uno">+</button>' +
            '</div>' +
            '<div class="ci-subtotal">S/ ' + (item.qty * item.precio).toFixed(2) + '</div>';
        container.appendChild(div);
    });
}

document.getElementById('cartItems').addEventListener('click', function(e) {
    var btn = e.target.closest('.ci-qty-btn');
    if (!btn) return;
    cartChangeQty(btn.dataset.key, parseInt(btn.dataset.delta, 10));
});

/* ── Ir a confirmar pedido ── */
function irAPedido() {
    var ids = Object.keys(cart);
    if (ids.length === 0) { showToast('Tu carrito está vacío'); return; }
    saveCart();
    window.location.href = 'pedidos_web.php';
}

/* ── Toast ── */
function showToast(msg) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(function(){ t.classList.remove('show'); }, 2200);
}

/* ══════════════════════════════════════
   BUSCADOR
══════════════════════════════════════ */
function filterBebidas() {
    var term   = document.getElementById('searchInput').value.toLowerCase().trim();
    var cards  = document.querySelectorAll('.bebida-card');
    var count  = 0;

    cards.forEach(function(card){
        var nombre = card.dataset.nombre || '';
        var match  = nombre.includes(term);
        card.style.display = match ? '' : 'none';
        if (match) count++;
    });

    document.getElementById('countNum').textContent = count;
    var noResults = document.getElementById('noResults');
    document.getElementById('noResultsTerm').textContent = term;
    noResults.style.display = (count === 0 && term !== '') ? 'block' : 'none';
}

/* Init */
renderCart();
</script>
<button id="cartFloatingBtn" onclick="cartOpen()">
    <img src="../assets/img/carrito.png" alt="Carrito" class="cart-icon">
    <span id="cartFloatingBadge">0</span>
</button> 
<script>renderCart();</script>
</body>
</html>
