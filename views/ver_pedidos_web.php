<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); exit;
}

// Cambiar estado
if (isset($_GET['accion'], $_GET['id'])) {
    $id     = (int)$_GET['id'];
    $accion = strtoupper(trim($_GET['accion']));

    $estadosValidos = ['PENDIENTE', 'PREPARANDO', 'EN_CAMINO', 'ENTREGADO', 'CANCELADO'];

    if (in_array($accion, $estadosValidos)) {
        $stmt = $conexion->prepare("UPDATE pedidos_web SET estado = ? WHERE id = ?");
        $stmt->execute([$accion, $id]);
    }

    if (!isset($_GET['ajax'])) {
        header("Location: ver_pedidos_web.php"); exit;
    }
    exit;
}

$pedidos = $conexion->query("
    SELECT p.*,
           GROUP_CONCAT(d.cantidad, 'x ', d.nombre SEPARATOR ' · ') AS detalle_resumen
    FROM pedidos_web p
    LEFT JOIN detalle_pedidos_web d ON d.id_pedido = p.id
    GROUP BY p.id
    ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$stats = [
    'PENDIENTE'  => 0,
    'PREPARANDO' => 0,
    'EN_CAMINO'  => 0,
    'ENTREGADO'  => 0,
    'CANCELADO'  => 0,
];
foreach ($pedidos as $p) {
    if (isset($stats[$p['estado']])) $stats[$p['estado']]++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pedidos Delivery — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family:'Sora',sans-serif;
    background: url('../assets/img/fnd.jpg') no-repeat center center fixed;
    background-size: cover;
    min-height:100vh;
    overflow-x:hidden;
}

/* SIDEBAR */
#sidebar {
    position:fixed; top:0; left:0;
    width:260px; height:100vh;
    background:#0d0f18;
    border-right:1px solid rgba(255,255,255,.05);
    z-index:200;
}

#toggleBtn, #toggleTNBtn {
    position:fixed; top:14px; left:14px;
    width:38px !important; height:38px !important;
    padding:0 !important; border:none;
    border-radius:8px !important;
    background:#1e2130; color:#94a3b8;
    font-size:18px !important; line-height:38px !important;
    text-align:center; cursor:pointer; z-index:1100;
}

/* LAYOUT */
.content-area { margin-left:260px; padding:28px; min-height:100vh; }

/* HEADER */
.page-header {
    display:flex; justify-content:space-between;
    align-items:center; margin-bottom:24px;
    gap:15px; flex-wrap:wrap;
}
.page-title {
    color:#f1f5f9; font-size:20px; font-weight:600;
    display:flex; align-items:center; gap:10px; margin:0;
}
.page-title-dot {
    width:8px; height:8px;
    border-radius:50%; background:#6ee7b7;
    flex-shrink:0;
}
.count-badge {
    font-size:11px; color:#6ee7b7;
    background:rgba(110,231,183,.10);
    border:1px solid rgba(110,231,183,.18);
    padding:5px 12px; border-radius:999px;
    display:flex; align-items:center; gap:5px;
}
.count-badge svg { width:13px; height:13px; stroke-width:2; }

/* STATS */
.stats-grid {
    display:grid;
    grid-template-columns: repeat(5, 1fr);
    gap:12px;
    margin-bottom:24px;
}
.stat-card {
    background:#171922;
    border:1px solid rgba(255,255,255,.06);
    border-radius:14px;
    padding:18px 16px;
    display:flex; flex-direction:column; gap:8px;
    transition:transform .2s, box-shadow .2s;
    position:relative; overflow:hidden;
}
.stat-card::before {
    content:'';
    position:absolute; top:0; left:0; right:0;
    height:2px; border-radius:14px 14px 0 0;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.3); }

.stat-icon-wrap {
    width:38px; height:38px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
}
.stat-icon-wrap svg { width:18px; height:18px; stroke-width:1.8; }

.stat-num {
    font-size:26px; font-weight:700; line-height:1; letter-spacing:-.5px;
}
.stat-label {
    font-size:11px; font-weight:500;
    text-transform:uppercase; letter-spacing:.08em;
    color:#64748b;
}

/* COLORES ESTADO */
.s-pendiente  .stat-card::before { background:#facc15; }
.s-pendiente  .stat-num          { color:#facc15; }
.s-pendiente  .stat-icon-wrap    { background:rgba(251,191,36,.12); color:#facc15; }

.s-preparando .stat-card::before { background:#60a5fa; }
.s-preparando .stat-num          { color:#60a5fa; }
.s-preparando .stat-icon-wrap    { background:rgba(96,165,250,.12); color:#60a5fa; }

.s-encamino   .stat-card::before { background:#34d399; }
.s-encamino   .stat-num          { color:#34d399; }
.s-encamino   .stat-icon-wrap    { background:rgba(52,211,153,.12); color:#34d399; }

.s-entregado  .stat-card::before { background:#6ee7b7; }
.s-entregado  .stat-num          { color:#6ee7b7; }
.s-entregado  .stat-icon-wrap    { background:rgba(110,231,183,.12); color:#6ee7b7; }

.s-cancelado  .stat-card::before { background:#f87171; }
.s-cancelado  .stat-num          { color:#f87171; }
.s-cancelado  .stat-icon-wrap    { background:rgba(248,113,113,.12); color:#f87171; }

/* ESTADO SELECT */
.estado-select {
    border: none;
    border-radius: 999px;
    padding: 5px 11px;
    font-family: 'Sora', sans-serif;
    font-size: 11px; font-weight: 600;
    cursor: pointer; outline: none;
    transition: all .2s;
    appearance: none;
    -webkit-appearance: none;
    padding-right: 24px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
}
.estado-select.b-PENDIENTE  { background-color:rgba(251,191,36,.12); color:#facc15; }
.estado-select.b-PREPARANDO { background-color:rgba(96,165,250,.1);  color:#60a5fa; }
.estado-select.b-EN_CAMINO  { background-color:rgba(52,211,153,.1);  color:#34d399; }
.estado-select.b-ENTREGADO  { background-color:rgba(110,231,183,.1); color:#6ee7b7; }
.estado-select.b-CANCELADO  { background-color:rgba(248,113,113,.1); color:#f87171; }
.estado-select option { background: #1e2130; color: #e2e8f0; }

/* FILTROS */
.filtros-wrap {
    display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px;
    align-items:center;
}
.filtros-label {
    font-size:11px; font-weight:600; letter-spacing:.08em;
    text-transform:uppercase; color:#475569;
    margin-right:4px;
}
.filtro-btn {
    height:34px; padding:0 14px; border-radius:999px;
    font-family:'Sora',sans-serif; font-size:12px; font-weight:500;
    cursor:pointer; background:#1e2130; color:#94a3b8;
    border:1px solid rgba(255,255,255,.06); transition:all .2s;
    display:flex; align-items:center; gap:6px;
}
.filtro-btn svg { width:13px; height:13px; stroke-width:2; }
.filtro-btn:hover { background:#252836; color:#cbd5e1; }
.filtro-btn.active {
    background:#6ee7b7; color:#064e3b;
    border-color:#6ee7b7;
    box-shadow:0 4px 14px rgba(110,231,183,.25);
}
.filtro-btn.active svg { color:#064e3b; }

/* TABLA */
.table-card {
    background:#171922;
    border:1px solid rgba(255,255,255,.06);
    border-radius:18px; overflow:hidden;
    box-shadow:0 14px 35px rgba(0,0,0,.25);
}
.table-toolbar {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 18px;
    border-bottom:1px solid rgba(255,255,255,.05);
    gap:10px; flex-wrap:wrap;
}
.table-toolbar-title {
    font-size:13px; font-weight:600; color:#94a3b8;
    display:flex; align-items:center; gap:7px;
}
.table-toolbar-title svg { width:15px; height:15px; stroke-width:2; color:#6ee7b7; }

.table-wrapper { overflow-x:auto; }
.table { margin:0; min-width:1100px; }

.table thead th {
    background:#1a1e2e !important;
    color:#475569 !important;
    font-size:10px; text-transform:uppercase;
    letter-spacing:.1em; border:none !important;
    padding:14px 16px !important; white-space:nowrap;
    font-weight:600;
}
.table thead th:first-child { border-radius:0; }

.table tbody td {
    background:#171922 !important;
    color:#cbd5e1;
    border-top:1px solid rgba(255,255,255,.04) !important;
    padding:13px 16px !important;
    vertical-align:middle;
}
.table tbody tr { transition:background .15s; }
.table tbody tr:hover td { background:#1b1e2b !important; }

.td-id {
    color:#475569; font-family:'Sora',monospace;
    font-size:12px; white-space:nowrap; font-weight:600;
}
.td-detalle {
    max-width:200px; overflow:hidden;
    text-overflow:ellipsis; white-space:nowrap;
    color:#64748b; font-size:12px;
}
.td-dir {
    max-width:150px; overflow:hidden;
    text-overflow:ellipsis; white-space:nowrap; font-size:12px;
}

/* CLIENTE CELL */
.cliente-cell { display:flex; flex-direction:column; gap:2px; text-align:left; }
.cliente-name { font-size:13px; font-weight:600; color:#e2e8f0; }
.cliente-user { font-size:11px; color:#475569; }

/* FECHA */
.fecha-cell { display:flex; flex-direction:column; gap:2px; text-align:center; }
.fecha-day  { font-size:13px; font-weight:500; color:#cbd5e1; }
.fecha-hora { font-size:11px; color:#475569; }

/* TOTAL */
.total-price {
    color:#6ee7b7; font-weight:700;
    white-space:nowrap; font-size:14px;
    font-variant-numeric: tabular-nums;
}

/* METODO PAGO */
.metodo-badge {
    font-size:11px; padding:4px 10px; border-radius:999px;
    background:#1e2130; color:#94a3b8;
    border:1px solid rgba(255,255,255,.07);
    white-space:nowrap;
    display:inline-flex; align-items:center; gap:5px;
}
.metodo-badge svg { width:12px; height:12px; stroke-width:2; }

/* BOTÓN VER */
.btn-ver {
    height:30px; padding:0 12px; border:none;
    border-radius:8px; font-size:11px; font-weight:600;
    font-family:'Sora',sans-serif; cursor:pointer;
    display:inline-flex; align-items:center; gap:5px;
    background:rgba(148,163,184,.1); color:#94a3b8;
    border:1px solid rgba(148,163,184,.15);
    transition:all .2s; white-space:nowrap;
}
.btn-ver svg { width:13px; height:13px; stroke-width:2; }
.btn-ver:hover {
    background:rgba(148,163,184,.2); color:#cbd5e1;
    border-color:rgba(148,163,184,.3);
}

/* EMPTY ROW */
.empty-row td {
    padding:3.5rem !important; text-align:center;
    color:#475569 !important; font-size:13px;
}
.empty-inner {
    display:flex; flex-direction:column;
    align-items:center; gap:10px;
}
.empty-inner svg { width:36px; height:36px; stroke-width:1.2; color:#334155; }
.empty-inner span { color:#475569; font-size:13px; }

/* ── MODAL ── */
.modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.72);
    backdrop-filter:blur(5px);
    -webkit-backdrop-filter:blur(5px);
    z-index:2000;
    align-items:center; justify-content:center;
}
.modal-overlay.visible { display:flex; }

.modal-box {
    background:#171922;
    border:1px solid rgba(255,255,255,.08);
    border-radius:20px; padding:0;
    width:100%; max-width:500px; margin:1rem;
    box-shadow:0 32px 64px rgba(0,0,0,.7);
    animation: modalIn .22s ease;
    overflow:hidden;
}
@keyframes modalIn {
    from { opacity:0; transform:translateY(18px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}

.modal-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 22px;
    background:#1e2130;
    border-bottom:1px solid rgba(255,255,255,.06);
}
.modal-header-left { display:flex; align-items:center; gap:10px; }
.modal-header-icon {
    width:36px; height:36px; border-radius:9px;
    background:rgba(110,231,183,.12);
    display:flex; align-items:center; justify-content:center;
    color:#6ee7b7;
}
.modal-header-icon svg { width:18px; height:18px; stroke-width:1.8; }
.modal-title { color:#f1f5f9; font-size:15px; font-weight:600; }
.modal-subtitle { color:#64748b; font-size:11px; margin-top:1px; }

.modal-close {
    width:30px; height:30px; border-radius:8px;
    background:rgba(255,255,255,.06); border:none; color:#64748b;
    cursor:pointer; transition:all .2s;
    display:flex; align-items:center; justify-content:center;
}
.modal-close svg { width:16px; height:16px; stroke-width:2; }
.modal-close:hover { background:rgba(248,113,113,.12); color:#f87171; }

.modal-body { padding:18px 22px; display:flex; flex-direction:column; gap:12px; max-height:70vh; overflow-y:auto; }
.modal-body::-webkit-scrollbar { width:4px; }
.modal-body::-webkit-scrollbar-track { background:transparent; }
.modal-body::-webkit-scrollbar-thumb { background:#2a2f45; border-radius:4px; }

.modal-section {
    background:#1e2130;
    border:1px solid rgba(255,255,255,.05);
    border-radius:12px; overflow:hidden;
}
.modal-section-header {
    display:flex; align-items:center; gap:7px;
    padding:10px 14px;
    border-bottom:1px solid rgba(255,255,255,.05);
    background:rgba(255,255,255,.02);
}
.modal-section-header svg { width:14px; height:14px; stroke-width:2; color:#6ee7b7; }
.modal-section-title {
    font-size:10px; font-weight:700; text-transform:uppercase;
    letter-spacing:.12em; color:#64748b;
}

.modal-row {
    display:flex; justify-content:space-between;
    align-items:center; gap:.5rem;
    font-size:13px; color:#64748b; padding:9px 14px;
    border-bottom:1px solid rgba(255,255,255,.03);
}
.modal-row:last-child { border-bottom:none; }
.modal-row strong { color:#e2e8f0; text-align:right; flex-shrink:0; max-width:240px; }

.detalle-item {
    display:flex; justify-content:space-between; align-items:center;
    padding:9px 14px;
    border-bottom:1px solid rgba(255,255,255,.03);
    font-size:13px;
}
.detalle-item:last-child { border-bottom:none; }
.di-info { display:flex; flex-direction:column; gap:2px; }
.di-name { color:#e2e8f0; font-weight:500; }
.di-qty  { color:#475569; font-size:11px; }
.di-price{ color:#6ee7b7; font-weight:700; white-space:nowrap; }

.modal-total-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:12px 14px;
    background:rgba(110,231,183,.05);
    border-top:1px solid rgba(110,231,183,.12);
}
.modal-total-label { color:#94a3b8; font-size:13px; font-weight:500;
    display:flex; align-items:center; gap:6px;
}
.modal-total-label svg { width:14px; height:14px; stroke-width:2; color:#6ee7b7; }
.modal-total-price { color:#6ee7b7; font-size:20px; font-weight:700; letter-spacing:-.5px; }

/* BOTÓN PDF en modal */
.btn-pdf {
    width:100%; padding:.85rem; border:none; border-radius:11px;
    background:linear-gradient(135deg, #ca8a04, #92400e);
    color:#fff; font-family:'Sora',sans-serif;
    font-size:12px; font-weight:700;
    cursor:pointer; letter-spacing:.06em; text-transform:uppercase;
    box-shadow:0 6px 18px rgba(200,150,46,.3);
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition:opacity .2s, transform .15s;
}
.btn-pdf svg { width:16px; height:16px; stroke-width:2; }
.btn-pdf:hover { opacity:.88; transform:translateY(-1px); }

/* RESPONSIVE */
@media(max-width:992px) {
    .content-area { margin-left:0; padding:15px; }
    .stats-grid { grid-template-columns: repeat(2,1fr); }
}
@media(max-width:600px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .page-header { flex-direction:column; align-items:flex-start; }
}
</style>
</head>
<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

    <!-- HEADER -->
    <div class="page-header">
        <h2 class="page-title">
            <span class="page-title-dot"></span>
            Pedidos Delivery
        </h2>
        <span class="count-badge" id="countBadge">
            <i data-lucide="package"></i>
            <?= count($pedidos) ?> registros
        </span>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="s-pendiente">
            <div class="stat-card">
                <div class="stat-icon-wrap"><i data-lucide="clock"></i></div>
                <div class="stat-num"><?= $stats['PENDIENTE'] ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        <div class="s-preparando">
            <div class="stat-card">
                <div class="stat-icon-wrap"><i data-lucide="chef-hat"></i></div>
                <div class="stat-num"><?= $stats['PREPARANDO'] ?></div>
                <div class="stat-label">Preparando</div>
            </div>
        </div>
        <div class="s-encamino">
            <div class="stat-card">
                <div class="stat-icon-wrap"><i data-lucide="bike"></i></div>
                <div class="stat-num"><?= $stats['EN_CAMINO'] ?></div>
                <div class="stat-label">En camino</div>
            </div>
        </div>
        <div class="s-entregado">
            <div class="stat-card">
                <div class="stat-icon-wrap"><i data-lucide="circle-check"></i></div>
                <div class="stat-num"><?= $stats['ENTREGADO'] ?></div>
                <div class="stat-label">Entregados</div>
            </div>
        </div>
        <div class="s-cancelado">
            <div class="stat-card">
                <div class="stat-icon-wrap"><i data-lucide="circle-x"></i></div>
                <div class="stat-num"><?= $stats['CANCELADO'] ?></div>
                <div class="stat-label">Cancelados</div>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-wrap">
        <span class="filtros-label">Filtrar:</span>
        <button class="filtro-btn active" onclick="filtrar('todos',this)">
            <i data-lucide="layout-grid"></i> Todos
        </button>
        <button class="filtro-btn" onclick="filtrar('PENDIENTE',this)">
            <i data-lucide="clock"></i> Pendientes
        </button>
        <button class="filtro-btn" onclick="filtrar('PREPARANDO',this)">
            <i data-lucide="chef-hat"></i> Preparando
        </button>
        <button class="filtro-btn" onclick="filtrar('EN_CAMINO',this)">
            <i data-lucide="bike"></i> En camino
        </button>
        <button class="filtro-btn" onclick="filtrar('ENTREGADO',this)">
            <i data-lucide="circle-check"></i> Entregados
        </button>
        <button class="filtro-btn" onclick="filtrar('CANCELADO',this)">
            <i data-lucide="circle-x"></i> Cancelados
        </button>
    </div>

    <!-- TABLA -->
    <div class="table-card">
        <div class="table-toolbar">
            <div class="table-toolbar-title">
                <i data-lucide="table-2"></i>
                Listado de pedidos
            </div>
        </div>
        <div class="table-wrapper">
            <table class="table text-center align-middle" id="tablaPedidos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha / Hora</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Detalle</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (empty($pedidos)): ?>
                <tr class="empty-row">
                    <td colspan="10">
                        <div class="empty-inner">
                            <i data-lucide="package-open"></i>
                            <span>No hay pedidos de delivery aún</span>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($pedidos as $p):
                    $iconoMetodo = match($p['metodo_pago']) {
                        'EFECTIVO' => 'banknote',
                        'YAPE'     => 'smartphone',
                        'PLIN'     => 'send',
                        'TARJETA'  => 'credit-card',
                        default    => 'wallet'
                    };
                ?>
                <tr data-estado="<?= $p['estado'] ?>" data-id="<?= $p['id'] ?>">

                    <td class="td-id"><?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?></td>

                    <td>
                        <div class="fecha-cell">
                            <span class="fecha-day"><?= date('d/m/Y', strtotime($p['fecha'])) ?></span>
                            <span class="fecha-hora"><?= substr($p['hora'],0,5) ?></span>
                        </div>
                    </td>

                    <td>
                        <div class="cliente-cell">
                            <span class="cliente-name"><?= htmlspecialchars($p['nombre_cliente']) ?></span>
                            <span class="cliente-user"><?= htmlspecialchars($p['usuario']) ?></span>
                        </div>
                    </td>

                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                        <?= htmlspecialchars($p['telefono']) ?>
                    </td>

                    <td class="td-dir" title="<?= htmlspecialchars($p['direccion']) ?>">
                        <?= htmlspecialchars($p['direccion']) ?>
                    </td>

                    <td class="td-detalle" title="<?= htmlspecialchars($p['detalle_resumen'] ?? '') ?>">
                        <?= htmlspecialchars($p['detalle_resumen'] ?? '—') ?>
                    </td>

                    <td>
                        <span class="metodo-badge">
                            <i data-lucide="<?= $iconoMetodo ?>"></i>
                            <?= $p['metodo_pago'] ?>
                        </span>
                    </td>

                    <td class="total-price">
                        S/ <?= number_format($p['total'], 2) ?>
                    </td>

                    <td>
                        <select class="estado-select b-<?= $p['estado'] ?>"
                                onchange="cambiarEstado(<?= $p['id'] ?>, this)">
                            <option value="PENDIENTE"  <?= $p['estado']==='PENDIENTE'  ?'selected':'' ?>>Pendiente</option>
                            <option value="PREPARANDO" <?= $p['estado']==='PREPARANDO' ?'selected':'' ?>>Preparando</option>
                            <option value="EN_CAMINO"  <?= $p['estado']==='EN_CAMINO'  ?'selected':'' ?>>En camino</option>
                            <option value="ENTREGADO"  <?= $p['estado']==='ENTREGADO'  ?'selected':'' ?>>Entregado</option>
                            <option value="CANCELADO"  <?= $p['estado']==='CANCELADO'  ?'selected':'' ?>>Cancelado</option>
                        </select>
                    </td>

                    <td>
                        <button class="btn-ver" onclick="verDetalle(<?= $p['id'] ?>)">
                            <i data-lucide="eye"></i> Ver
                        </button>
                    </td>

                </tr>
                <?php endforeach; ?>

                <tr id="emptyFiltro" style="display:none" class="empty-row">
                    <td colspan="10">
                        <div class="empty-inner">
                            <i data-lucide="search-x"></i>
                            <span>No hay pedidos en esta categoría</span>
                        </div>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL DETALLE -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">

        <div class="modal-header">
            <div class="modal-header-left">
                <div class="modal-header-icon">
                    <i data-lucide="receipt-text"></i>
                </div>
                <div>
                    <div class="modal-title" id="modalTitle">Detalle del pedido</div>
                    <div class="modal-subtitle" id="modalSubtitle"></div>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="modalBody"></div>

    </div>
</div>

<script>
var pedidosData  = <?= json_encode($pedidos, JSON_HEX_TAG | JSON_HEX_QUOT) ?>;
var detallesData = <?php
    $detalles = $conexion->query("
        SELECT id_pedido, nombre, precio, cantidad
        FROM detalle_pedidos_web
        ORDER BY id_pedido, id
    ")->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($detalles as $d) { $map[$d['id_pedido']][] = $d; }
    echo json_encode($map, JSON_HEX_TAG | JSON_HEX_QUOT);
?>;

/* ── Cambiar estado ── */
function cambiarEstado(id, select) {
    var nuevoEstado = select.value;
    var url = 'ver_pedidos_web.php?accion=' + nuevoEstado + '&id=' + id + '&ajax=1';
    fetch(url)
        .then(function(r){ return r.text(); })
        .then(function(){
            select.className = 'estado-select b-' + nuevoEstado;
            select.closest('tr').dataset.estado = nuevoEstado;
        });
}

/* ── Generar recibo ── */
function generarRecibo(id) {
    window.open('recibo_pdf.php?id=' + id, '_blank');
}

/* ── Filtrar ── */
function filtrar(estado, btn) {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    var filas = document.querySelectorAll('#tablaPedidos tbody tr[data-estado]');
    var count = 0;
    filas.forEach(function(fila) {
        var mostrar = estado === 'todos' || fila.dataset.estado === estado;
        fila.style.display = mostrar ? '' : 'none';
        if (mostrar) count++;
    });
    document.getElementById('emptyFiltro').style.display = count === 0 ? '' : 'none';
    document.getElementById('countBadge').innerHTML =
        '<i data-lucide="package" style="width:13px;height:13px;stroke-width:2;"></i> ' + count + ' registros';
    lucide.createIcons();
}

/* ── Modal detalle ── */
function verDetalle(id) {
    var p = pedidosData.find(function(x){ return x.id == id; });
    if (!p) return;

    var items = detallesData[id] || [];

    var metodoIcono = {
        EFECTIVO: 'banknote', YAPE: 'smartphone',
        PLIN: 'send', TARJETA: 'credit-card'
    }[p.metodo_pago] || 'wallet';

    var estadoLabel = {
        PENDIENTE:'Pendiente', PREPARANDO:'Preparando',
        EN_CAMINO:'En camino', ENTREGADO:'Entregado', CANCELADO:'Cancelado'
    }[p.estado] || p.estado;

    document.getElementById('modalTitle').textContent =
        'Pedido #' + String(p.id).padStart(3, '0');
    document.getElementById('modalSubtitle').textContent =
        estadoLabel + ' · ' + p.fecha + ' ' + p.hora.substring(0,5);

    var html = '';

    // Sección cliente
    html += '<div class="modal-section">';
    html += '<div class="modal-section-header"><i data-lucide="user-round"></i><span class="modal-section-title">Cliente</span></div>';
    html += '<div class="modal-row"><span>Nombre</span><strong>' + p.nombre_cliente + '</strong></div>';
    html += '<div class="modal-row"><span>Teléfono</span><strong>' + p.telefono + '</strong></div>';
    html += '<div class="modal-row"><span>Usuario</span><strong>' + p.usuario + '</strong></div>';
    html += '<div class="modal-row"><span>Dirección</span><strong style="max-width:220px;text-align:right">' + p.direccion + '</strong></div>';
    html += '</div>';

    // Sección pedido
    html += '<div class="modal-section">';
    html += '<div class="modal-section-header"><i data-lucide="clipboard-list"></i><span class="modal-section-title">Info del pedido</span></div>';
    html += '<div class="modal-row"><span>Fecha</span><strong>' + p.fecha + '</strong></div>';
    html += '<div class="modal-row"><span>Hora</span><strong>' + p.hora.substring(0,5) + '</strong></div>';
    html += '<div class="modal-row"><span>Método de pago</span><strong>' + p.metodo_pago + '</strong></div>';
    html += '</div>';

    // Sección platos
    html += '<div class="modal-section">';
    html += '<div class="modal-section-header"><i data-lucide="utensils"></i><span class="modal-section-title">Productos</span></div>';

    if (items.length > 0) {
        items.forEach(function(item) {
            var subtotal = (parseFloat(item.precio) * parseInt(item.cantidad)).toFixed(2);
            html += '<div class="detalle-item">';
            html += '<div class="di-info"><span class="di-name">' + item.nombre + '</span><span class="di-qty">' + item.cantidad + ' unidad(es) × S/ ' + parseFloat(item.precio).toFixed(2) + '</span></div>';
            html += '<span class="di-price">S/ ' + subtotal + '</span>';
            html += '</div>';
        });
    } else {
        html += '<div style="padding:14px;color:#475569;font-size:13px;text-align:center;">Sin detalle disponible</div>';
    }

    html += '<div class="modal-total-row">';
    html += '<span class="modal-total-label"><i data-lucide="circle-dollar-sign"></i> Total</span>';
    html += '<span class="modal-total-price">S/ ' + parseFloat(p.total).toFixed(2) + '</span>';
    html += '</div>';
    html += '</div>';

    // Botón PDF
    html += '<button class="btn-pdf" onclick="generarRecibo(' + id + ')">';
    html += '<i data-lucide="printer"></i> Generar Recibo PDF';
    html += '</button>';

    document.getElementById('modalBody').innerHTML = html;
    document.getElementById('modalOverlay').classList.add('visible');
    lucide.createIcons();
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('visible');
}

document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModal();
});

// Init Lucide
lucide.createIcons();
</script>

</body>
</html>