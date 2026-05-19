<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }
if ($_SESSION['rol'] === 'Administrador') { header("Location: dashboard.php"); exit; }

$nombre_usuario = htmlspecialchars($_SESSION['usuario'] ?? 'Cliente');

$msg      = null;
$msg_tipo = 'ok';

// Enviar reporte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'enviar') {
    $asunto      = trim($_POST['asunto'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($asunto) || empty($descripcion)) {
        $msg      = "Todos los campos son obligatorios.";
        $msg_tipo = 'error';
    } else {
        // Obtener correo del usuario
        $stmt = $conexion->prepare("SELECT correo FROM usuarios WHERE nombre = ? LIMIT 1");
        $stmt->execute([$nombre_usuario]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        $correo = $u['correo'] ?? null;

        $conexion->prepare(
            "INSERT INTO reportes (usuario, correo, asunto, descripcion, estado) VALUES (?, ?, ?, ?, 'PENDIENTE')"
        )->execute([$nombre_usuario, $correo, $asunto, $descripcion]);

        $msg = "Reporte enviado correctamente. Revisaremos tu caso pronto.";
    }
}

// Obtener reportes del usuario
$stmt = $conexion->prepare("SELECT * FROM reportes WHERE usuario = ? ORDER BY id DESC");
$stmt->execute([$nombre_usuario]);
$mis_reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Delicia — Reportes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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

    main { padding-top: 64px; min-height: 100vh; }

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

    .content-wrapper {
        padding: 2.5rem 3rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.6rem;
        max-width: 1100px;
    }

    .card {
        background: #fff;
        border: 1px solid var(--warm);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(59,39,16,.08);
        animation: popIn .5s both;
    }

    .card-header {
        background: linear-gradient(135deg, #1f0f04 0%, var(--brown) 60%, var(--brown-md) 100%);
        padding: 1.4rem 1.8rem;
        display: flex; align-items: center; gap: 1rem;
        position: relative; overflow: hidden;
    }
    .card-header::before {
        content:''; position:absolute; top:-40px; right:-30px;
        width:140px; height:140px; border-radius:50%;
        background: radial-gradient(circle, rgba(200,150,46,.25), transparent 65%);
        pointer-events:none;
    }
    .card-header-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink:0;
        background: linear-gradient(135deg, var(--gold), #92400e);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 6px 16px rgba(200,150,46,.4);
        position: relative; z-index:1;
        color: #fff;
    }
    .card-header-icon svg { width: 22px; height: 22px; stroke-width: 1.8; }
    .card-header-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem; font-weight: 600;
        color: var(--cream);
        position: relative; z-index:1;
    }

    .card-body { padding: 1.5rem 1.8rem; }

    .form-group { display: flex; flex-direction: column; gap: .35rem; margin-bottom: 1rem; }
    .form-label {
        font-size: .72rem; font-weight: 700;
        letter-spacing: .5px; text-transform: uppercase; color: var(--brown-md);
        display: flex; align-items: center; gap: .4rem;
    }
    .form-label svg { width: 14px; height: 14px; stroke-width: 2; }

    .form-input, .form-textarea {
        padding: .65rem .9rem;
        border: 1.5px solid var(--warm);
        border-radius: 9px;
        background: var(--cream);
        font-family: 'DM Sans', sans-serif;
        font-size: .88rem; color: var(--brown);
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-input:focus, .form-textarea:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(200,150,46,.15);
    }
    .form-textarea { resize: vertical; min-height: 100px; }

    .btn-submit {
        width: 100%; padding: .8rem;
        background: var(--gold); color: #fff; border: none;
        border-radius: 10px; cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        font-size: .88rem; font-weight: 600; letter-spacing: .04em;
        text-transform: uppercase;
        box-shadow: 0 6px 18px rgba(200,150,46,.35);
        transition: background .2s, transform .2s;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .btn-submit svg { width: 16px; height: 16px; stroke-width: 2.2; }
    .btn-submit:hover { background: var(--brown); transform: translateY(-1px); }

    .msg {
        margin: 0 1.8rem 1rem;
        padding: .75rem 1rem;
        border-radius: 10px;
        font-size: .84rem; font-weight: 500;
        animation: fadeIn .4s;
        display: flex; align-items: center; gap: .5rem;
    }
    .msg svg { width: 16px; height: 16px; flex-shrink: 0; stroke-width: 2.2; }
    .msg.ok {
        background: rgba(44,74,46,.1);
        border: 1px solid rgba(44,74,46,.25);
        color: var(--green);
    }
    .msg.error {
        background: rgba(139,26,26,.08);
        border: 1px solid rgba(139,26,26,.2);
        color: var(--red);
    }

    /* Estado badge */
    .estado-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 50px;
        font-size: 11px; font-weight: 600; white-space: nowrap;
    }
    .est-PENDIENTE {
        background: rgba(251,191,36,.12); color: #b8860b;
        border: 1px solid rgba(251,191,36,.3);
    }
    .est-REVISADO {
        background: rgba(96,165,250,.12); color: #2563eb;
        border: 1px solid rgba(96,165,250,.3);
    }
    .est-RESUELTO {
        background: rgba(44,74,46,.12); color: var(--green);
        border: 1px solid rgba(44,74,46,.25);
    }

    /* Tabla de mis reportes */
    .table-wrap { overflow-x: auto; }
    .reportes-table {
        width: 100%; min-width: 500px;
        border-collapse: collapse;
    }
    .reportes-table thead th {
        font-size: .68rem; font-weight: 700; letter-spacing: .5px;
        text-transform: uppercase; color: #a08060;
        padding: .8rem .5rem .8rem 0;
        border-bottom: 2px solid var(--warm);
        text-align: left;
    }
    .reportes-table tbody td {
        padding: .8rem .5rem .8rem 0;
        border-bottom: 1px solid var(--warm);
        font-size: .85rem; color: var(--brown);
        vertical-align: top;
    }
    .reportes-table tbody tr:last-child td { border-bottom: none; }

    .td-asunto { font-weight: 600; }
    .td-desc { color: var(--brown-md); }
    .td-fecha { white-space: nowrap; color: #a08060; font-size: .78rem; }

    .empty-state {
        text-align: center; padding: 2rem 0; color: #a08060;
    }
    .empty-state p { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; }

    @media (max-width:900px) {
        .content-wrapper { grid-template-columns: 1fr; padding: 1.4rem; }
        .page-hero { padding: 2rem 1.4rem; }
    }
    </style>
</head>
<body>

<?php include '../includes/header_cliente.php'; ?>
<?php include '../includes/sidebar_cliente.php'; ?>

<main>

    <div class="page-hero">
        <div class="hero-inner">
            <h1>Reportar un <em>problema</em></h1>
            <p>Hola <?= $nombre_usuario ?>, cuéntanos cualquier incidencia o sugerencia</p>
        </div>
    </div>

    <div class="content-wrapper">

        <!-- ── FORMULARIO ── -->
        <div class="card" style="animation-delay:.1s">

            <div class="card-header">
                <div class="card-header-icon">
                    <i data-lucide="alert-circle"></i>
                </div>
                <div class="card-header-title">Nuevo reporte</div>
            </div>

            <?php if ($msg): ?>
            <div class="msg <?= $msg_tipo ?>" style="margin-top:1rem;">
                <?php if ($msg_tipo === 'ok'): ?>
                    <i data-lucide="circle-check"></i>
                <?php else: ?>
                    <i data-lucide="circle-alert"></i>
                <?php endif; ?>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="card-body">
                <input type="hidden" name="accion" value="enviar">

                <div class="form-group">
                    <label class="form-label">
                        <i data-lucide="tag"></i>
                        Asunto
                    </label>
                    <input type="text" name="asunto" class="form-input"
                           placeholder="Ej: Problema con mi pedido" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i data-lucide="file-text"></i>
                        Descripción
                    </label>
                    <textarea name="descripcion" class="form-textarea"
                              placeholder="Describe el problema con detalle..." required></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i data-lucide="send"></i>
                    Enviar reporte
                </button>
            </form>

        </div>

        <!-- ── MIS REPORTES ── -->
        <div class="card" style="animation-delay:.2s">

            <div class="card-header">
                <div class="card-header-icon">
                    <i data-lucide="list"></i>
                </div>
                <div class="card-header-title">Mis reportes</div>
            </div>

            <div class="card-body">

                <?php if (empty($mis_reportes)): ?>
                    <div class="empty-state">
                        <p>No has realizado ningún reporte aún</p>
                    </div>
                <?php else: ?>
                <div class="table-wrap">
                    <table class="reportes-table">
                        <thead>
                            <tr>
                                <th>Asunto</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mis_reportes as $r): ?>
                            <tr>
                                <td class="td-asunto"><?= htmlspecialchars($r['asunto']) ?></td>
                                <td class="td-desc"><?= htmlspecialchars($r['descripcion']) ?></td>
                                <td>
                                    <span class="estado-badge est-<?= $r['estado'] ?>">
                                        <?= $r['estado'] ?>
                                    </span>
                                </td>
                                <td class="td-fecha"><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

</main>

<script>
    lucide.createIcons();
</script>

</body>
</html>
