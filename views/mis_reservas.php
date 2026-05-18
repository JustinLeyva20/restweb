<?php
session_start();
require "../config/conexion.php";

// Debe tener sesión activa
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); exit;
}

// Los administradores NO deben entrar aquí
if ($_SESSION['rol'] === 'Administrador') {
    header("Location: dashboard.php"); exit;
}

$usuario = $_SESSION['usuario'];

$reservas = $conexion->prepare("
    SELECT r.*, s.nombre AS sala_nombre
    FROM reservas r
    JOIN salas s ON s.id = r.id_sala
    WHERE r.usuario = ?
    ORDER BY r.fecha DESC, r.hora DESC
");
$reservas->execute([$usuario]);
$reservas = $reservas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mis Reservas — La Delicia</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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
    --shadow:   rgba(59,39,16,.15);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--brown);
    overflow-x: hidden;
}

@keyframes fadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}

/* TOP BAR */
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
    font-size: 1.5rem; font-weight: 600;
    color: var(--brown); text-decoration: none;
}
.top-logo span { color: var(--gold); }

/* MAIN */
main { padding-top: 64px; min-height: 100vh; }

/* HERO */
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
    font-size: clamp(2rem,4vw,3rem);
    font-weight:300; color:var(--cream);
    animation: fadeUp .6s .1s both;
}
.hero-inner h1 em { font-style:italic; color:var(--gold-lt); }
.hero-inner p {
    color:rgba(245,239,224,.6); font-size:.9rem;
    margin-top:.5rem; font-weight:300;
    animation: fadeUp .6s .25s both;
}

/* CONTENIDO */
.content { padding: 2.5rem 3rem 4rem; }

/* VACÍO */
.empty-state {
    text-align:center; padding: 5rem 1rem;
    animation: fadeUp .5s both;
}
.empty-state .es-icon { font-size: 4rem; margin-bottom: 1rem; opacity: .5; }
.empty-state h3 {
    font-family:'Cormorant Garamond',serif;
    font-size:1.6rem; color:var(--brown-md); margin-bottom:.5rem;
}
.empty-state p { color:#a08060; font-size:.9rem; margin-bottom:1.5rem; }
.btn-reservar-now {
    display:inline-flex; align-items:center; gap:.5rem;
    background:var(--gold); color:#fff;
    padding:.75rem 1.8rem; border-radius:3rem;
    text-decoration:none; font-weight:600; font-size:.9rem;
    box-shadow: 0 6px 20px rgba(200,150,46,.4);
    transition: background .2s, transform .2s;
}
.btn-reservar-now:hover { background:var(--brown); transform:translateY(-2px); }

/* GRID DE TARJETAS */
.reservas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.4rem;
}

/* TARJETA */
.reserva-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 20px var(--shadow);
    animation: fadeUp .5s both;
    transition: transform .3s, box-shadow .3s;
}
.reserva-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px var(--shadow);
}
.filtros-bar {
    display: flex; gap: .6rem; flex-wrap: wrap;
    padding: 1.2rem 3rem;
    background: var(--warm);
    border-bottom: 1px solid rgba(200,150,46,.2);
}

.filtro-btn {
    padding: .5rem 1.2rem;
    border-radius: 3rem;
    border: 1.5px solid rgba(200,150,46,.3);
    background: transparent;
    color: var(--brown-md);
    font-family: 'DM Sans', sans-serif;
    font-size: .82rem; font-weight: 500;
    cursor: pointer;
    transition: all .2s;
}
.filtro-btn:hover {
    border-color: var(--gold);
    background: rgba(200,150,46,.08);
    color: var(--brown);
}
.filtro-btn.active {
    background: var(--gold);
    border-color: var(--gold);
    color: #fff;
    box-shadow: 0 4px 14px rgba(200,150,46,.35);
}

@media (max-width: 640px) {
    .filtros-bar { padding: 1rem 1.2rem; }
}
/* Cabecera de la tarjeta */
.rc-header {
    padding: 1.1rem 1.3rem;
    display: flex; align-items: center; justify-content: space-between;
}
.rc-header.PENDIENTE  { background: linear-gradient(135deg, #fffbeb, #fef3c7); border-bottom: 2px solid #fde68a; }
.rc-header.CONFIRMADA { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-bottom: 2px solid #bbf7d0; }
.rc-header.CANCELADA  { background: linear-gradient(135deg, #fff1f2, #ffe4e6); border-bottom: 2px solid #fecdd3; }

.rc-sala {
    font-family:'Cormorant Garamond',serif;
    font-size:1.15rem; font-weight:600; color:var(--brown);
}
.rc-mesa { font-size:.78rem; color:var(--brown-md); margin-top:.1rem; }

/* Badge estado */
.rc-badge {
    font-size:.68rem; font-weight:700; letter-spacing:.08em;
    text-transform:uppercase; padding:.3rem .75rem; border-radius:2rem;
}
.badge-PENDIENTE  { background:rgba(234,179,8,.15);  color:#92400e; }
.badge-CONFIRMADA { background:rgba(22,163,74,.12);  color:#166534; }
.badge-CANCELADA  { background:rgba(220,38,38,.1);   color:#991b1b; }

/* Cuerpo */
.rc-body { padding: 1.2rem 1.3rem; }

.rc-row {
    display:flex; align-items:center; gap:.6rem;
    padding:.45rem 0;
    border-bottom:1px solid var(--warm);
    font-size:.87rem; color:var(--brown-md);
}
.rc-row:last-child { border-bottom:none; }
.rc-row strong { color:var(--brown); font-weight:600; }
.rc-row .rc-icon { font-size:1rem; width:20px; text-align:center; flex-shrink:0; }

/* Footer tarjeta */
.rc-footer {
    padding: .9rem 1.3rem;
    background: #faf6ee;
    border-top: 1px solid var(--warm);
    display:flex; justify-content:space-between; align-items:center;
}
.rc-date-created { font-size:.72rem; color:#a08060; }

.btn-cancelar-reserva {
    font-size:.75rem; padding:.35rem .9rem;
    border-radius:2rem; border:1px solid rgba(139,26,26,.25);
    background:rgba(139,26,26,.07); color:var(--red);
    cursor:pointer; text-decoration:none;
    transition:background .2s;
}
.btn-cancelar-reserva:hover { background:rgba(139,26,26,.15); }

/* RESPONSIVE */
@media (max-width:640px) {
    .content { padding: 1.5rem 1.2rem 3rem; }
    .page-hero { padding: 2rem 1.4rem; }
    .reservas-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<?php include '../includes/header_cliente.php'; ?>
<?php include '../includes/sidebar_cliente.php'; ?>

<header class="top-bar">
    <a href="inicio.php" class="top-logo">La <span>Delicia</span></a>
</header>

<main>

    <div class="page-hero">
        <div class="hero-inner">
            <h1>Mis <em>Reservas</em></h1>
            <p>Aquí puedes ver y gestionar todas tus reservas de mesa</p>
        </div>
    </div>
    <!-- FILTROS -->
<div class="filtros-bar">
    <button class="filtro-btn active" onclick="filtrar('todos', this)">Todos</button>
    <button class="filtro-btn" onclick="filtrar('PENDIENTE',  this)">🟡 Pendientes</button>
    <button class="filtro-btn" onclick="filtrar('CONFIRMADA', this)">🟢 Confirmadas</button>
    <button class="filtro-btn" onclick="filtrar('CANCELADA',  this)">🔴 Canceladas</button>
</div>

    <div class="content">

        <?php if (empty($reservas)): ?>

        <div class="empty-state">
            <div class="es-icon">🗓️</div>
            <h3>Sin reservas aún</h3>
            <p>No tienes ninguna reserva registrada. ¡Reserva tu mesa favorita!</p>
            <a href="mesas_cliente.php" class="btn-reservar-now">
                🪑 Reservar una mesa
            </a>
        </div>

        <?php else: ?>

        <div class="reservas-grid">
            <?php foreach ($reservas as $i => $r): ?>
<div class="reserva-card"
     data-estado="<?= $r['estado'] ?>"
     style="animation-delay:<?= min($i * 0.08, 0.5) ?>s">

                <div class="rc-header <?= $r['estado'] ?>">
                    <div>
                        <div class="rc-sala">🏛 <?= htmlspecialchars($r['sala_nombre']) ?></div>
                        <div class="rc-mesa">Mesa <?= $r['num_mesa'] ?></div>
                    </div>
                    <span class="rc-badge badge-<?= $r['estado'] ?>">
                        <?= match($r['estado']) {
                            'PENDIENTE'  => '🟡 Pendiente',
                            'CONFIRMADA' => '🟢 Confirmada',
                            'CANCELADA'  => '🔴 Cancelada',
                        } ?>
                    </span>
                </div>

                <div class="rc-body">
                    <div class="rc-row">
                        <span class="rc-icon">📅</span>
                        <span>Fecha: <strong><?= date('d/m/Y', strtotime($r['fecha'])) ?></strong></span>
                    </div>
                    <div class="rc-row">
                        <span class="rc-icon">🕐</span>
                        <span>Hora: <strong><?= substr($r['hora'], 0, 5) ?></strong></span>
                    </div>
                    <div class="rc-row">
                        <span class="rc-icon">👥</span>
                        <span>Personas: <strong><?= $r['personas'] ?></strong></span>
                    </div>
                    <?php if ($r['nota']): ?>
                    <div class="rc-row">
                        <span class="rc-icon">📝</span>
                        <span><?= htmlspecialchars($r['nota']) ?></span>
                    </div>
                    <?php if ($r['motivo']): ?>
<div class="rc-row">
    <span class="rc-icon">💬</span>
    <span>Restaurante: <strong><?= htmlspecialchars($r['motivo']) ?></strong></span>
</div>
<?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="rc-footer">
                    <span class="rc-date-created">
                        Creada: <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                    </span>

                    <?php if ($r['estado'] === 'PENDIENTE'): ?>
                    <a href="mesas_cliente.php?cancelar=<?= $r['id'] ?>"
                       class="btn-cancelar-reserva"
                       onclick="return confirm('¿Cancelar esta reserva?')">
                        ✕ Cancelar
                    </a>
                    <?php endif; ?>
                </div>

            </div>
            <?php endforeach; ?>

        <!-- Mensaje si el filtro no tiene resultados -->
        <div id="emptyFiltro" style="display:none; grid-column:1/-1; text-align:center; padding:4rem 1rem;">
            <div style="font-size:3rem;opacity:.4;margin-bottom:1rem;">🔍</div>
            <p style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:var(--brown-md);">
                No hay reservas en esta categoría
            </p>
        </div>
        </div>

        <?php endif; ?>

    </div>
</main>
<script>
function filtrar(estado, btn) {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    var tarjetas = document.querySelectorAll('.reserva-card');
    var visibles = 0;

    tarjetas.forEach(function(card) {
        var mostrar = estado === 'todos' || card.dataset.estado === estado;
        card.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
    });

    // Mostrar/ocultar estado vacío
    var empty = document.getElementById('emptyFiltro');
    if (empty) empty.style.display = visibles === 0 ? 'block' : 'none';
}
</script>

</body>
</html>