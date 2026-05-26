<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); exit;
}
if ($_SESSION['rol'] === 'Administrador') {
    header("Location: dashboard.php"); exit;
}

$usuario = $_SESSION['usuario'];

$pedidos = $conexion->prepare("
    SELECT p.*, 
           GROUP_CONCAT(d.cantidad, 'x ', d.nombre SEPARATOR ' · ') AS detalle_resumen
    FROM pedidos_web p
    LEFT JOIN detalle_pedidos_web d ON d.id_pedido = p.id
    WHERE p.usuario = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$pedidos->execute([$usuario]);
$pedidos = $pedidos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>La Delicia — Mis Pedidos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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
    --red:      #8B1A1A;
    --shadow:   rgba(59,39,16,.15);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--brown);
    min-height: 100vh;
}

@keyframes fadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}

/* Lucide helpers */
.lc { display: inline-flex; align-items: center; justify-content: center; }
.lc svg { display: block; }

/* TOP BAR */
.top-bar {
    position: fixed; top:0; left:0; right:0; z-index:900;
    height: 64px;
    display: flex; align-items: center;
    padding: 0 2rem 0 4.5rem;
    background: rgba(245,239,224,.92);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(200,150,46,.22);
}
.top-logo {
    font-family: 'Merriweather', serif;
    font-size: 1.5rem; font-weight: 600;
    color: var(--brown); text-decoration: none;
}
.top-logo span { color: var(--gold); }

main { padding-top: 64px; min-height: 100vh; }

/* HERO */
.page-hero {
    background: linear-gradient(135deg, #1f0f04, var(--brown) 50%, var(--brown-md));
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
    font-family:'Merriweather',serif;
    font-size:clamp(2rem,4vw,3rem); font-weight:300;
    color:var(--cream); animation: fadeUp .6s .1s both;
}
.hero-inner h1 em { font-style:italic; color:var(--gold-lt); }
.hero-inner p {
    color:rgba(245,239,224,.6); font-size:.9rem;
    margin-top:.5rem; animation: fadeUp .6s .25s both;
}

/* FILTROS */
.filtros-bar {
    display: flex; gap: .6rem; flex-wrap: wrap;
    padding: 1.2rem 3rem;
    background: var(--warm);
    border-bottom: 1px solid rgba(200,150,46,.2);
}
.filtro-btn {
    padding: .5rem 1.2rem; border-radius: 3rem;
    border: 1.5px solid rgba(200,150,46,.3);
    background: transparent; color: var(--brown-md);
    font-family: 'DM Sans', sans-serif;
    font-size: .82rem; font-weight: 500;
    cursor: pointer; transition: all .2s;
    display: flex; align-items: center; gap: .4rem;
}
.filtro-btn svg { width: 14px; height: 14px; stroke-width: 2; }
.filtro-btn:hover {
    border-color: var(--gold);
    background: rgba(200,150,46,.08); color: var(--brown);
}
.filtro-btn.active {
    background: var(--gold); border-color: var(--gold);
    color: #fff; box-shadow: 0 4px 14px rgba(200,150,46,.35);
}

/* CONTENT */
.content { padding: 2.5rem 3rem 4rem; }

/* EMPTY STATE */
.empty-state {
    text-align: center; padding: 5rem 1rem;
    animation: fadeUp .5s both;
}
.empty-state .es-icon {
    width: 80px; height: 80px; border-radius: 50%;
    background: rgba(200,150,46,.1);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    color: var(--gold);
}
.empty-state .es-icon svg { width: 36px; height: 36px; stroke-width: 1.5; }
.empty-state h3 {
    font-family: 'Merriweather', serif;
    font-size: 1.6rem; color: var(--brown-md); margin-bottom: .5rem;
}
.empty-state p { color: #a08060; font-size: .9rem; margin-bottom: 1.5rem; }
.btn-ir-carta {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--gold); color: #fff;
    padding: .75rem 1.8rem; border-radius: 3rem;
    text-decoration: none; font-weight: 600;
    box-shadow: 0 6px 20px rgba(200,150,46,.4);
    transition: background .2s, transform .2s;
}
.btn-ir-carta svg { width: 16px; height: 16px; stroke-width: 2; }
.btn-ir-carta:hover { background: var(--brown); transform: translateY(-2px); }

/* GRID */
.pedidos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.4rem;
}

/* CARD */
.pedido-card {
    background: #fff;
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 2px 20px var(--shadow);
    animation: fadeUp .5s both;
    transition: transform .3s, box-shadow .3s;
}
.pedido-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px var(--shadow);
}

/* Header card */
.pc-header {
    padding: 1rem 1.3rem;
    display: flex; align-items: center; justify-content: space-between;
}
.pc-header.PENDIENTE  { background: linear-gradient(135deg,#fffbeb,#fef3c7); border-bottom:2px solid #fde68a; }
.pc-header.PREPARANDO { background: linear-gradient(135deg,#eff6ff,#dbeafe); border-bottom:2px solid #bfdbfe; }
.pc-header.EN_CAMINO  { background: linear-gradient(135deg,#f0fdf4,#dcfce7); border-bottom:2px solid #bbf7d0; }
.pc-header.ENTREGADO  { background: linear-gradient(135deg,#f0fdf4,#dcfce7); border-bottom:2px solid #bbf7d0; }
.pc-header.CANCELADO  { background: linear-gradient(135deg,#fff1f2,#ffe4e6); border-bottom:2px solid #fecdd3; }

.pc-id {
    font-family: 'Merriweather', serif;
    font-size: 1.1rem; font-weight: 600; color: var(--brown);
}
.pc-fecha { font-size: .72rem; color: var(--brown-md); margin-top: .1rem; }

/* Badge estado */
.pc-badge {
    font-size: .68rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; padding: .3rem .8rem; border-radius: 2rem;
    white-space: nowrap;
    display: inline-flex; align-items: center; gap: .35rem;
}
.pc-badge svg { width: 12px; height: 12px; stroke-width: 2.5; }
.badge-PENDIENTE  { background:rgba(234,179,8,.15);  color:#92400e; }
.badge-PREPARANDO { background:rgba(59,130,246,.12); color:#1e40af; }
.badge-EN_CAMINO  { background:rgba(34,197,94,.12);  color:#166534; }
.badge-ENTREGADO  { background:rgba(34,197,94,.12);  color:#166534; }
.badge-CANCELADO  { background:rgba(220,38,38,.1);   color:#991b1b; }

/* Body */
.pc-body { padding: 1.1rem 1.3rem; }

.pc-row {
    display: flex; align-items: flex-start; gap: .6rem;
    padding: .45rem 0;
    border-bottom: 1px solid var(--warm);
    font-size: .86rem; color: var(--brown-md);
}
.pc-row:last-child { border-bottom: none; }
.pc-icon {
    width: 20px; flex-shrink: 0; margin-top: .05rem;
    display: flex; align-items: center; justify-content: center;
    color: var(--brown-md);
}
.pc-icon svg { width: 15px; height: 15px; stroke-width: 1.8; }
.pc-row strong { color: var(--brown); font-weight: 600; }
.pc-row .detalle-text { color: #a08060; font-size: .8rem; line-height: 1.5; }

/* Footer */
.pc-footer {
    padding: .9rem 1.3rem;
    background: #faf6ee;
    border-top: 1px solid var(--warm);
    display: flex; justify-content: space-between; align-items: center;
}
.pc-total {
    font-family: 'Merriweather', serif;
    font-size: 1.4rem; font-weight: 600; color: var(--gold);
}
.pc-total small { font-size: .8rem; color: var(--brown-md); font-weight: 400; }
.pc-metodo {
    font-size: .75rem; color: var(--brown-md);
    background: var(--warm); padding: .3rem .8rem;
    border-radius: 2rem; font-weight: 500;
    display: inline-flex; align-items: center; gap: .35rem;
}
.pc-metodo svg { width: 13px; height: 13px; stroke-width: 1.8; }

/* Empty filtro */
.empty-filtro {
    display: none; grid-column: 1/-1;
    text-align: center; padding: 3rem 1rem;
}
.empty-filtro p {
    font-family: 'Merriweather', serif;
    font-size: 1.3rem; color: var(--brown-md);
    display: flex; align-items: center; justify-content: center; gap: .5rem;
}
.empty-filtro svg { width: 20px; height: 20px; stroke-width: 1.8; color: var(--gold); }

@media (max-width: 640px) {
    .content { padding: 1.5rem 1.2rem 3rem; }
    .page-hero { padding: 2rem 1.4rem; }
    .filtros-bar { padding: 1rem 1.2rem; }
    .pedidos-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<?php include '../includes/header_cliente.php'; ?>
<?php include '../includes/sidebar_cliente.php'; ?>

<main>

    <div class="page-hero">
        <div class="hero-inner">
            <h1>Mis <em>Pedidos</em></h1>
            <p>Aquí puedes ver el estado de todos tus pedidos de delivery</p>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-bar">
        <button class="filtro-btn active" onclick="filtrar('todos', this)">
            <i data-lucide="layout-grid"></i> Todos
        </button>
        <button class="filtro-btn" onclick="filtrar('PENDIENTE', this)">
            <i data-lucide="clock"></i> Pendientes
        </button>
        <button class="filtro-btn" onclick="filtrar('PREPARANDO', this)">
            <i data-lucide="chef-hat"></i> Preparando
        </button>
        <button class="filtro-btn" onclick="filtrar('EN_CAMINO', this)">
            <i data-lucide="bike"></i> En camino
        </button>
        <button class="filtro-btn" onclick="filtrar('ENTREGADO', this)">
            <i data-lucide="circle-check"></i> Entregados
        </button>
        <button class="filtro-btn" onclick="filtrar('CANCELADO', this)">
            <i data-lucide="circle-x"></i> Cancelados
        </button>
    </div>

    <div class="content">

        <?php if (empty($pedidos)): ?>

        <div class="empty-state">
            <div class="es-icon"><i data-lucide="bike"></i></div>
            <h3>Sin pedidos aún</h3>
            <p>No has realizado ningún pedido de delivery todavía.</p>
            <a href="platos_usuario.php" class="btn-ir-carta">
                <i data-lucide="utensils"></i> Ir a la carta
            </a>
        </div>

        <?php else: ?>

        <div class="pedidos-grid" id="pedidosGrid">

            <?php foreach ($pedidos as $i => $p):
                $iconoMetodo = match($p['metodo_pago']) {
                    'EFECTIVO' => 'banknote',
                    'YAPE'     => 'smartphone',
                    'PLIN'     => 'zap',
                    'TARJETA'  => 'credit-card',
                    default    => 'wallet'
                };
                $iconoEstado = match($p['estado']) {
                    'PENDIENTE'  => 'clock',
                    'PREPARANDO' => 'chef-hat',
                    'EN_CAMINO'  => 'bike',
                    'ENTREGADO'  => 'circle-check',
                    'CANCELADO'  => 'circle-x',
                    default      => 'circle'
                };
            ?>

            <div class="pedido-card"
                 data-estado="<?= $p['estado'] ?>"
                 style="animation-delay: <?= min($i * 0.07, 0.5) ?>s">

                <!-- HEADER -->
                <div class="pc-header <?= $p['estado'] ?>">
                    <div>
                        <div class="pc-id">
                            Pedido #<?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?>
                        </div>
                        <div class="pc-fecha">
                            <?= date('d/m/Y', strtotime($p['fecha'])) ?>
                            · <?= substr($p['hora'], 0, 5) ?>
                        </div>
                    </div>
                    <span class="pc-badge badge-<?= $p['estado'] ?>">
                        <i data-lucide="<?= $iconoEstado ?>"></i>
                        <?= str_replace('_', ' ', $p['estado']) ?>
                    </span>
                </div>

                <!-- BODY -->
                <div class="pc-body">

                    <div class="pc-row">
                        <span class="pc-icon"><i data-lucide="user"></i></span>
                        <span><?= htmlspecialchars($p['nombre_cliente']) ?>
                            · <strong><?= htmlspecialchars($p['telefono']) ?></strong>
                        </span>
                    </div>

                    <div class="pc-row">
                        <span class="pc-icon"><i data-lucide="map-pin"></i></span>
                        <span><?= htmlspecialchars($p['direccion']) ?></span>
                    </div>

                    <div class="pc-row">
                        <span class="pc-icon"><i data-lucide="utensils"></i></span>
                        <span class="detalle-text">
                            <?= htmlspecialchars($p['detalle_resumen'] ?? '—') ?>
                        </span>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="pc-footer">
                    <div class="pc-total">
                        <small>S/</small> <?= number_format($p['total'], 2) ?>
                    </div>
                    <span class="pc-metodo">
                        <i data-lucide="<?= $iconoMetodo ?>"></i>
                        <?= $p['metodo_pago'] ?>
                    </span>
                </div>

            </div>

            <?php endforeach; ?>

            <div class="empty-filtro" id="emptyFiltro">
                <p><i data-lucide="search-x"></i> No hay pedidos en esta categoría</p>
            </div>

        </div>

        <?php endif; ?>

    </div>
</main>

<script>
function filtrar(estado, btn) {
    document.querySelectorAll('.filtro-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    var cards    = document.querySelectorAll('.pedido-card');
    var visibles = 0;

    cards.forEach(function(card) {
        var mostrar = estado === 'todos' || card.dataset.estado === estado;
        card.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
    });

    document.getElementById('emptyFiltro').style.display =
        visibles === 0 ? 'block' : 'none';
}

lucide.createIcons();
</script>

</body>
</html>