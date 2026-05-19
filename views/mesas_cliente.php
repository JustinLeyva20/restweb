<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }
if ($_SESSION['rol'] === 'Administrador') { header("Location: dashboard.php"); exit; }

$nombre_usuario = htmlspecialchars($_SESSION['usuario'] ?? 'Cliente');

if (isset($_GET['cancelar'])) {
    $id = (int)$_GET['cancelar'];
    $conexion->prepare("
        UPDATE reservas SET estado = 'CANCELADA'
        WHERE id = ? AND usuario = ?
    ")->execute([$id, $nombre_usuario]);
    header("Location: mis_reservas.php"); exit;
}

$nombre_usuario = htmlspecialchars($_SESSION['usuario'] ?? 'Cliente');

$salas = $conexion->query("SELECT * FROM salas ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$ocupados = $conexion->query("
    SELECT id_sala, num_mesa FROM reservas
    WHERE estado = 'PENDIENTE'
")->fetchAll(PDO::FETCH_ASSOC);

$ocupadasSet = [];
foreach ($ocupados as $o) {
    $ocupadasSet[$o['id_sala'] . '-' . $o['num_mesa']] = true;
}

$mensajeReserva = null;
$tipoMensaje    = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'reservar') {
    $id_sala  = (int)($_POST['id_sala']  ?? 0);
    $num_mesa = (int)($_POST['num_mesa'] ?? 0);
    $fecha    = $_POST['fecha']    ?? '';
    $hora     = $_POST['hora']     ?? '';
    $personas = (int)($_POST['personas'] ?? 1);
    $nota     = htmlspecialchars($_POST['nota'] ?? '');

    if ($id_sala && $num_mesa && $fecha && $hora && $personas >= 1) {
        $clave = $id_sala . '-' . $num_mesa;
        if (isset($ocupadasSet[$clave])) {
            $mensajeReserva = "Esa mesa ya tiene una reserva activa hoy. Elige otra.";
            $tipoMensaje    = 'error';
        } else {
            $stmt = $conexion->prepare("
                INSERT INTO reservas (id_sala, num_mesa, usuario, fecha, hora, personas, nota)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id_sala, $num_mesa, $nombre_usuario, $fecha, $hora, $personas, $nota]);
            $mensajeReserva = "¡Reserva confirmada! Mesa $num_mesa reservada para el $fecha a las $hora.";

            $ocupados2 = $conexion->query("
                SELECT id_sala, num_mesa FROM reservas
                WHERE estado = 'PENDIENTE'
            ")->fetchAll(PDO::FETCH_ASSOC);
            $ocupadasSet = [];
            foreach ($ocupados2 as $o) {
                $ocupadasSet[$o['id_sala'] . '-' . $o['num_mesa']] = true;
            }
        }
    } else {
        $mensajeReserva = "Completa todos los campos obligatorios.";
        $tipoMensaje    = 'error';
    }
}

$salasJson    = json_encode($salas, JSON_HEX_TAG | JSON_HEX_QUOT);
$ocupadasJson = json_encode(array_keys($ocupadasSet), JSON_HEX_TAG);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Delicia — Mesas</title>
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
        --shadow:   rgba(59,39,16,.15);
        --shadow-lg:rgba(59,39,16,.28);
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
    @keyframes popIn  {
        0%   { opacity:0; transform:scale(.85) translateY(16px); }
        70%  { transform:scale(1.03) translateY(-2px); }
        100% { opacity:1; transform:scale(1) translateY(0); }
    }
    @keyframes pulse-ring {
        0%,100%{ box-shadow: 0 0 0 0 rgba(200,150,46,.45); }
        50%    { box-shadow: 0 0 0 8px rgba(200,150,46,.0); }
    }

    /* Lucide helpers */
    .icon svg { display:block; }

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

    /* ── PAGE HERO ── */
    .page-hero {
        background: linear-gradient(135deg, #1f0f04 0%, var(--brown) 50%, var(--brown-md) 100%);
        padding: 2.8rem 3rem 2.2rem;
        position: relative; overflow: hidden;
    }
    .page-hero::before {
        content:''; position:absolute; top:-90px; right:-70px;
        width:280px; height:280px; border-radius:50%;
        background: radial-gradient(circle, rgba(200,150,46,.28), transparent 65%);
        pointer-events:none;
    }
    .page-hero::after {
        content:''; position:absolute; bottom:-60px; left:20%;
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

    /* Leyenda */
    .legend {
        display:flex; gap:1.2rem; flex-wrap:wrap;
        margin-top:1.4rem;
        animation: fadeUp .6s .4s both;
    }
    .legend-item {
        display:flex; align-items:center; gap:.5rem;
        font-size:.78rem; color:rgba(245,239,224,.7);
    }
    .legend-dot { width:12px; height:12px; border-radius:3px; flex-shrink:0; }
    .ld-libre    { background:var(--green-lt); }
    .ld-ocupada  { background:var(--red); }
    .ld-selected { background:var(--gold); }

    /* ── TABS ── */
    .sala-tabs {
        display:flex; gap:.6rem; flex-wrap:wrap;
        padding:1.4rem 3rem;
        background:var(--warm);
        border-bottom: 1px solid rgba(200,150,46,.2);
    }
    .sala-tab {
        padding:.55rem 1.4rem;
        border-radius:3rem;
        border: 1.5px solid rgba(200,150,46,.3);
        background:transparent;
        color:var(--brown-md);
        font-family:'DM Sans',sans-serif;
        font-size:.85rem; font-weight:500;
        cursor:pointer;
        transition:all .2s;
        display: flex; align-items: center; gap: .5rem;
    }
    .sala-tab svg { width: 15px; height: 15px; stroke-width: 1.8; }
    .sala-tab:hover { border-color:var(--gold); background:rgba(200,150,46,.08); color:var(--brown); }
    .sala-tab.active {
        background:var(--gold); border-color:var(--gold); color:#fff;
        box-shadow:0 4px 16px rgba(200,150,46,.4);
    }

    /* ── WORKSPACE ── */
    .workspace {
        display:grid;
        grid-template-columns: 1fr 340px;
        gap:0;
        min-height: calc(100vh - 64px - 180px);
    }

    /* ── PLANO ── */
    .floor-area { padding: 2.5rem 3rem; position:relative; }
    .floor-label {
        font-family:'Cormorant Garamond',serif;
        font-size:1.4rem; font-weight:400; color:var(--brown);
        margin-bottom:1.6rem;
        display:flex; align-items:center; gap:.8rem;
    }
    .floor-label svg { width: 22px; height: 22px; stroke-width: 1.6; color: var(--gold); }
    .floor-label::after {
        content:''; flex:1; height:1px;
        background:linear-gradient(to right, var(--gold), transparent);
    }

    .mesas-grid {
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(110px,1fr));
        gap:1.1rem;
    }

    /* ── MESA CARD ── */
    .mesa-card {
        aspect-ratio:1;
        border-radius:16px;
        display:flex; flex-direction:column;
        align-items:center; justify-content:center; gap:.3rem;
        cursor:pointer;
        position:relative;
        transition:transform .25s, box-shadow .25s;
        animation: popIn .4s both;
        user-select:none;
    }
    .mesa-card:hover { transform:translateY(-4px) scale(1.04); }

    .mesa-card.libre {
        background: linear-gradient(145deg, #e8f5e9, #c8e6c9);
        border: 2px solid rgba(44,74,46,.25);
        box-shadow: 0 4px 18px rgba(44,74,46,.15);
    }
    .mesa-card.libre:hover { box-shadow: 0 10px 30px rgba(44,74,46,.25); border-color: var(--green-lt); }
    .mesa-card.ocupada {
        background: linear-gradient(145deg, #fce4e4, #f5c0c0);
        border: 2px solid rgba(139,26,26,.2);
        box-shadow: 0 4px 18px rgba(139,26,26,.1);
        cursor:not-allowed; opacity:.85;
    }
    .mesa-card.selected {
        background: linear-gradient(145deg, #fff8e1, #ffe082);
        border: 2px solid var(--gold);
        box-shadow: 0 0 0 4px rgba(200,150,46,.2), 0 8px 24px rgba(200,150,46,.3);
        animation: pulse-ring 1.8s ease-in-out infinite;
    }

    /* Icono SVG dentro de la mesa card */
    .mesa-icon {
        width: 36px; height: 36px;
        display: flex; align-items: center; justify-content: center;
    }
    .mesa-icon svg { width: 28px; height: 28px; stroke-width: 1.6; }
    .libre   .mesa-icon svg { color: var(--green);    }
    .ocupada .mesa-icon svg { color: var(--red);      }
    .selected .mesa-icon svg{ color: var(--gold);     }

    .mesa-num {
        font-family:'Cormorant Garamond',serif;
        font-size:1.1rem; font-weight:600; color:var(--brown);
    }
    .mesa-status {
        font-size:.62rem; font-weight:600;
        letter-spacing:.08em; text-transform:uppercase;
    }
    .libre   .mesa-status { color:var(--green); }
    .ocupada .mesa-status { color:var(--red);   }
    .selected .mesa-status{ color:var(--gold);  }

    .mesa-badge {
        position:absolute; top:.5rem; right:.6rem;
        font-size:.65rem; font-weight:700;
        color:rgba(59,39,16,.45);
    }

    /* ── PANEL RESERVA ── */
    .reserva-panel {
        background:#fff;
        border-left: 1px solid var(--warm);
        padding:2rem 1.8rem;
        display:flex; flex-direction:column; gap:1.2rem;
        position:sticky; top:64px;
        height:calc(100vh - 64px);
        overflow-y:auto;
    }
    .reserva-panel::-webkit-scrollbar { width:4px; }
    .reserva-panel::-webkit-scrollbar-thumb { background:var(--warm); border-radius:2px; }

    .panel-title {
        font-family:'Cormorant Garamond',serif;
        font-size:1.5rem; font-weight:400; color:var(--brown);
        border-bottom:1px solid var(--warm); padding-bottom:.8rem;
    }

    /* Preview */
    .mesa-preview {
        background:linear-gradient(135deg, var(--warm), #efe2c2);
        border-radius:12px; padding:1rem 1.2rem;
        display:flex; align-items:center; gap:.9rem;
        transition:all .3s;
    }
    .mesa-preview.empty {
        background:rgba(237,224,196,.4);
        border:1.5px dashed rgba(200,150,46,.3);
        justify-content:center; flex-direction:column; gap:.3rem;
        text-align:center;
    }
    .preview-icon {
        width: 44px; height: 44px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .preview-icon svg { width: 32px; height: 32px; stroke-width: 1.5; color: var(--gold); }
    .preview-sala { font-size:.72rem; letter-spacing:.1em; text-transform:uppercase; color:var(--brown-md); }
    .preview-mesa { font-family:'Cormorant Garamond',serif; font-size:1.3rem; font-weight:600; color:var(--brown); }
    .preview-estado {
        font-size:.72rem; color:var(--green); font-weight:600;
        letter-spacing:.06em; text-transform:uppercase;
        display: flex; align-items: center; gap: .3rem;
    }
    .preview-estado svg { width: 12px; height: 12px; stroke-width: 2.5; }
    .empty-hint { font-size:.82rem; color:#a08060; }
    .empty-hint strong { color:var(--gold); }
    .empty-icon { width: 40px; height: 40px; opacity: .35; color: var(--brown-md); }
    .empty-icon svg { width: 40px; height: 40px; stroke-width: 1.4; }

    /* Formulario */
    .form-group { display:flex; flex-direction:column; gap:.4rem; }
    .form-label {
        font-size:.78rem; font-weight:600;
        letter-spacing:.06em; text-transform:uppercase; color:var(--brown-md);
        display: flex; align-items: center; gap: .4rem;
    }
    .form-label svg { width: 14px; height: 14px; stroke-width: 2; }

    .form-input, .form-select, .form-textarea {
        padding:.7rem .9rem;
        border:1.5px solid var(--warm);
        border-radius:9px;
        background:var(--cream);
        font-family:'DM Sans',sans-serif;
        font-size:.88rem; color:var(--brown);
        outline:none;
        transition:border-color .2s, box-shadow .2s;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color:var(--gold);
        box-shadow:0 0 0 3px rgba(200,150,46,.15);
    }
    .form-textarea { resize:vertical; min-height:70px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; }

    /* Botón reservar */
    .btn-reservar {
        width:100%; padding:.9rem;
        background:var(--gold); color:#fff; border:none;
        border-radius:10px; cursor:pointer;
        font-family:'DM Sans',sans-serif;
        font-size:.92rem; font-weight:600; letter-spacing:.04em;
        text-transform:uppercase;
        box-shadow:0 6px 20px rgba(200,150,46,.4);
        transition:background .2s, transform .2s, box-shadow .2s;
        display:flex; align-items:center; justify-content:center; gap:.5rem;
    }
    .btn-reservar svg { width:17px; height:17px; stroke-width: 2.2; }
    .btn-reservar:hover:not(:disabled) {
        background:var(--brown);
        box-shadow:0 8px 28px rgba(59,39,16,.3);
        transform:translateY(-1px);
    }
    .btn-reservar:disabled { opacity:.5; cursor:not-allowed; transform:none; }

    /* Mensaje */
    .mensaje-reserva {
        border-radius:10px; padding:.85rem 1rem;
        font-size:.85rem; font-weight:500;
        display:flex; align-items:flex-start; gap:.6rem;
        animation: fadeIn .4s;
    }
    .mensaje-reserva svg { width: 16px; height: 16px; flex-shrink: 0; stroke-width: 2.2; margin-top: 1px; }
    .mensaje-reserva.ok {
        background:rgba(44,74,46,.1); border:1px solid rgba(44,74,46,.25); color:var(--green);
    }
    .mensaje-reserva.error {
        background:rgba(139,26,26,.08); border:1px solid rgba(139,26,26,.2); color:var(--red);
    }

    /* Info tel */
    .info-tel {
        background:rgba(200,150,46,.08); border-radius:10px; padding:1rem;
        border:1px solid rgba(200,150,46,.2); margin-top:.4rem;
        display: flex; align-items: flex-start; gap: .6rem;
    }
    .info-tel svg { width: 16px; height: 16px; color: var(--gold); flex-shrink:0; margin-top:2px; stroke-width: 1.8; }
    .info-tel p { font-size:.78rem; color:var(--brown-md); line-height:1.6; }
    .info-tel strong { color:var(--gold); }

    /* ── MODAL ── */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 3000;
        background: rgba(59,39,16,.55);
        backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center;
        padding: 1rem;
        animation: fadeIn .3s;
    }
    .modal-box {
        background: #fff; border-radius: 20px;
        padding: 2.4rem 2rem; max-width: 420px; width: 100%;
        text-align: center;
        box-shadow: 0 24px 60px rgba(59,39,16,.25);
        animation: popIn .4s;
    }
    .modal-icon {
        width: 64px; height: 64px; border-radius: 50%;
        background: linear-gradient(135deg, var(--gold), #92400e);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto .8rem;
        box-shadow: 0 8px 24px rgba(200,150,46,.35);
        color: #fff;
    }
    .modal-icon svg { width: 30px; height: 30px; stroke-width: 2; }
    .modal-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.7rem; font-weight: 600;
        color: var(--brown); margin-bottom: .7rem;
    }
    .modal-body {
        font-size: .9rem; color: var(--brown-md);
        line-height: 1.7; margin-bottom: 1.6rem;
    }
    .modal-body a { color: var(--gold); font-weight: 600; text-decoration: underline; }
    .modal-btn {
        padding: .75rem 2rem;
        background: var(--gold); color: #fff; border: none;
        border-radius: 3rem; cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        font-size: .88rem; font-weight: 600;
        letter-spacing: .04em; text-transform: uppercase;
        box-shadow: 0 6px 18px rgba(200,150,46,.35);
        transition: background .2s, transform .2s;
        display: inline-flex; align-items: center; gap: .4rem;
    }
    .modal-btn svg { width: 15px; height: 15px; stroke-width: 2.2; }
    .modal-btn:hover { background: var(--brown); transform: translateY(-1px); }

    /* ── TOAST ── */
    #toast {
        position:fixed; bottom:2rem; left:50%;
        transform:translateX(-50%) translateY(20px);
        background:var(--brown); color:var(--cream);
        padding:.7rem 1.5rem; border-radius:3rem;
        font-size:.85rem; font-weight:500;
        box-shadow:0 6px 24px rgba(59,39,16,.35);
        opacity:0; transition:opacity .3s, transform .3s;
        pointer-events:none; white-space:nowrap; z-index:2000;
        display: flex; align-items: center; gap: .5rem;
    }
    #toast svg { width: 15px; height: 15px; stroke-width: 2.2; flex-shrink: 0; }
    #toast.show { opacity:1; transform:translateX(-50%) translateY(0); }

    /* ── RESPONSIVE ── */
    @media (max-width:900px) {
        .workspace { grid-template-columns:1fr; }
        .reserva-panel { position:static; height:auto; border-left:none; border-top:1px solid var(--warm); }
        .floor-area, .sala-tabs { padding-left:1.4rem; padding-right:1.4rem; }
        .page-hero { padding:2rem 1.4rem; }
    }
    @media (max-width:500px) {
        .mesas-grid { grid-template-columns: repeat(auto-fill, minmax(90px,1fr)); gap:.7rem; }
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
            <h1>Elige tu <em>mesa</em></h1>
            <p>Hola <?= $nombre_usuario ?> — selecciona una mesa disponible y reserva en segundos</p>
            <div class="legend">
                <div class="legend-item"><div class="legend-dot ld-libre"></div> Disponible</div>
                <div class="legend-item"><div class="legend-dot ld-ocupada"></div> Ocupada</div>
                <div class="legend-item"><div class="legend-dot ld-selected"></div> Seleccionada</div>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="sala-tabs" id="salaTabs">
        <?php foreach ($salas as $i => $sala): ?>
        <button
            class="sala-tab <?= $i===0?'active':'' ?>"
            onclick="setSala(<?= $sala['id'] ?>, this)"
            data-sala="<?= $sala['id'] ?>"
        >
            <i data-lucide="landmark"></i>
            <?= htmlspecialchars($sala['nombre']) ?>
            <small style="opacity:.7;font-size:.7rem;"> · <?= $sala['mesas'] ?> mesas</small>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- WORKSPACE -->
    <div class="workspace">

        <!-- PLANO DE MESAS -->
        <div class="floor-area">
            <?php foreach ($salas as $i => $sala): ?>
            <div id="plano-<?= $sala['id'] ?>"
                 class="sala-plano"
                 style="display:<?= $i===0?'block':'none' ?>">

                <div class="floor-label">
                    <i data-lucide="layout-dashboard"></i>
                    <?= htmlspecialchars($sala['nombre']) ?>
                </div>

                <div class="mesas-grid">
                    <?php for ($m = 1; $m <= $sala['mesas']; $m++):
                        $clave = $sala['id'] . '-' . $m;
                        $estaOcupada = isset($ocupadasSet[$clave]);
                        $estadoClass = $estaOcupada ? 'ocupada' : 'libre';
                        $delay = min(($m - 1) * 0.04, 0.5);
                    ?>
                    <div
                        class="mesa-card <?= $estadoClass ?>"
                        id="mesa-<?= $sala['id'] ?>-<?= $m ?>"
                        data-sala="<?= $sala['id'] ?>"
                        data-sala-nombre="<?= htmlspecialchars($sala['nombre']) ?>"
                        data-mesa="<?= $m ?>"
                        data-ocupada="<?= $estaOcupada ? '1':'0' ?>"
                        onclick="seleccionarMesa(this)"
                        style="animation-delay:<?= $delay ?>s"
                        title="<?= $estaOcupada ? 'Mesa ocupada' : 'Mesa '.$m.' — Haz clic para reservar' ?>"
                    >
                        <span class="mesa-icon">
                            <?php if ($estaOcupada): ?>
                                <i data-lucide="circle-x"></i>
                            <?php else: ?>
                                <i data-lucide="armchair"></i>
                            <?php endif; ?>
                        </span>
                        <span class="mesa-num">Mesa <?= $m ?></span>
                        <span class="mesa-status"><?= $estaOcupada ? 'Ocupada' : 'Libre' ?></span>
                        <span class="mesa-badge">#<?= $m ?></span>
                    </div>
                    <?php endfor; ?>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <!-- PANEL RESERVA -->
        <aside class="reserva-panel">

            <h2 class="panel-title">Reservar Mesa</h2>

            <?php if ($mensajeReserva): ?>
            <div class="mensaje-reserva <?= $tipoMensaje ?>">
                <?php if ($tipoMensaje === 'ok'): ?>
                    <i data-lucide="circle-check"></i>
                <?php else: ?>
                    <i data-lucide="triangle-alert"></i>
                <?php endif; ?>
                <?= $mensajeReserva ?>
            </div>
            <?php endif; ?>

            <!-- Preview -->
            <div class="mesa-preview empty" id="mesaPreview">
                <div class="empty-icon"><i data-lucide="armchair"></i></div>
                <span class="empty-hint">Haz clic en una mesa <strong>disponible</strong> para seleccionarla</span>
            </div>

            <!-- Formulario -->
            <form method="POST" id="reservaForm" onsubmit="return validarForm()">
                <input type="hidden" name="accion"   value="reservar">
                <input type="hidden" name="id_sala"  id="inputSala"  value="">
                <input type="hidden" name="num_mesa" id="inputMesa"  value="">

                <div style="display:flex;flex-direction:column;gap:.9rem;">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i data-lucide="calendar"></i> Fecha *
                            </label>
                            <input type="date" name="fecha" id="inputFecha" class="form-input"
                                   min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i data-lucide="clock"></i> Hora *
                            </label>
                            <input type="time" name="hora" id="inputHora" class="form-input"
                                   value="<?= date('H:i') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i data-lucide="users"></i> Número de personas *
                        </label>
                        <select name="personas" class="form-select" required>
                            <?php for($p=1;$p<=10;$p++): ?>
                            <option value="<?=$p?>" <?=$p===2?'selected':''?>><?=$p?> persona<?=$p>1?'s':''?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i data-lucide="notebook-pen"></i> Nota especial
                        </label>
                        <textarea name="nota" class="form-textarea"
                                  placeholder="Ej: cumpleaños, silla para bebé, alergia..."><?= $_POST['nota'] ?? '' ?></textarea>
                    </div>

                    <button type="submit" class="btn-reservar" id="btnReservar" disabled>
                        <i data-lucide="calendar-check"></i>
                        Confirmar Reserva
                    </button>

                </div>
            </form>

            <div class="info-tel">
                <i data-lucide="phone"></i>
                <p>
                    También puedes llamar al <strong>957 847 894</strong> para reservar.<br>
                    Recuerda llegar 10 minutos antes de tu hora reservada.
                </p>
            </div>

        </aside>
    </div>

</main>

<!-- MODAL -->
<div class="modal-overlay" id="modalConfirmacion" style="display:none;">
    <div class="modal-box">
        <div class="modal-icon">
            <i data-lucide="party-popper"></i>
        </div>
        <div class="modal-title">¡Solicitud enviada!</div>
        <div class="modal-body">
            Tu reserva ha sido recibida con éxito.<br>
            Estamos verificando la disponibilidad de la mesa seleccionada y te confirmaremos a la brevedad.<br><br>
            Mientras tanto, puedes revisar el estado de tu reserva en
            <a href="mis_reservas.php">Mis Reservas</a>.
        </div>
        <button class="modal-btn" onclick="cerrarModal()">
            <i data-lucide="check"></i>
            Entendido
        </button>
    </div>
</div>

<div id="toast">
    <i data-lucide="info"></i>
    <span id="toastMsg"></span>
</div>

<script>
var salas       = <?= $salasJson ?>;
var ocupadas    = <?= $ocupadasJson ?>;
var salaActual  = <?= $salas[0]['id'] ?? 1 ?>;
var mesaSeleccionada = null;

function setSala(salaId, btn) {
    salaActual = salaId;
    document.querySelectorAll('.sala-tab').forEach(function(t){ t.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.sala-plano').forEach(function(p){ p.style.display='none'; });
    document.getElementById('plano-' + salaId).style.display = 'block';
    if (mesaSeleccionada && mesaSeleccionada.salaId !== salaId) {
        limpiarSeleccion();
    }
}

function seleccionarMesa(el) {
    if (el.dataset.ocupada === '1') {
        showToast('Esa mesa está ocupada', 'ban');
        el.style.transform = 'translateX(-4px)';
        setTimeout(function(){ el.style.transform=''; }, 300);
        return;
    }

    // Restaurar cards anteriores
    document.querySelectorAll('.mesa-card.selected').forEach(function(c){
        c.classList.remove('selected');
        c.classList.add('libre');
        c.querySelector('.mesa-icon').innerHTML = '<i data-lucide="armchair"></i>';
        c.querySelector('.mesa-status').textContent = 'Libre';
    });

    el.classList.remove('libre');
    el.classList.add('selected');
    el.querySelector('.mesa-icon').innerHTML = '<i data-lucide="star"></i>';
    el.querySelector('.mesa-status').textContent = 'Seleccionada';

    lucide.createIcons(); // Re-render nuevos iconos

    var salaId     = parseInt(el.dataset.sala);
    var salaNombre = el.dataset.salaNombre;
    var numMesa    = parseInt(el.dataset.mesa);

    mesaSeleccionada = { salaId: salaId, salaName: salaNombre, numMesa: numMesa };

    document.getElementById('inputSala').value = salaId;
    document.getElementById('inputMesa').value = numMesa;

    document.getElementById('mesaPreview').classList.remove('empty');
    document.getElementById('mesaPreview').innerHTML =
        '<div class="preview-icon"><i data-lucide="armchair"></i></div>' +
        '<div class="preview-info">' +
            '<div class="preview-sala">' + salaNombre + '</div>' +
            '<div class="preview-mesa">Mesa ' + numMesa + '</div>' +
            '<div class="preview-estado"><i data-lucide="circle-check"></i> Disponible</div>' +
        '</div>';

    lucide.createIcons();
    document.getElementById('btnReservar').disabled = false;
    showToast('Mesa ' + numMesa + ' seleccionada', 'check');
}

function limpiarSeleccion() {
    document.querySelectorAll('.mesa-card.selected').forEach(function(c){
        c.classList.remove('selected');
        c.classList.add('libre');
        c.querySelector('.mesa-icon').innerHTML = '<i data-lucide="armchair"></i>';
        c.querySelector('.mesa-status').textContent = 'Libre';
    });
    lucide.createIcons();
    mesaSeleccionada = null;
    document.getElementById('inputSala').value = '';
    document.getElementById('inputMesa').value = '';
    document.getElementById('btnReservar').disabled = true;
    var preview = document.getElementById('mesaPreview');
    preview.classList.add('empty');
    preview.innerHTML =
        '<div class="empty-icon"><i data-lucide="armchair"></i></div>' +
        '<span class="empty-hint">Haz clic en una mesa <strong>disponible</strong> para seleccionarla</span>';
    lucide.createIcons();
}

function validarForm() {
    if (!document.getElementById('inputSala').value ||
        !document.getElementById('inputMesa').value) {
        showToast('Selecciona una mesa primero', 'triangle-alert');
        return false;
    }
    return true;
}

function showToast(msg, icon) {
    icon = icon || 'info';
    var t   = document.getElementById('toast');
    var svg = t.querySelector('i');
    svg.setAttribute('data-lucide', icon);
    document.getElementById('toastMsg').textContent = msg;
    lucide.createIcons();
    t.classList.add('show');
    clearTimeout(window._tt);
    window._tt = setTimeout(function(){ t.classList.remove('show'); }, 2400);
}

function cerrarModal() {
    document.getElementById('modalConfirmacion').style.display = 'none';
}

<?php if ($mensajeReserva && $tipoMensaje === 'ok'): ?>
setTimeout(function(){
    document.getElementById('modalConfirmacion').style.display = 'flex';
}, 300);
<?php endif; ?>

// Init
lucide.createIcons();
</script>

</body>
</html>