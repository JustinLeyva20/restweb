$nuevoEstado<?php
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

// Estadísticas rápidas
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
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family:'Sora',sans-serif;
    background:#0f1117;
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
.page-title::before {
    content:""; width:8px; height:8px;
    border-radius:50%; background:#6ee7b7;
}
.count-badge {
    font-size:11px; color:#6ee7b7;
    background:rgba(110,231,183,.10);
    border:1px solid rgba(110,231,183,.18);
    padding:5px 12px; border-radius:999px;
}

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
    padding:16px;
    display:flex; flex-direction:column; gap:6px;
    transition:transform .2s;
}
.stat-card:hover { transform:translateY(-2px); }
.stat-num {
    font-size:28px; font-weight:600; line-height:1;
}
.stat-label {
    font-size:11px; font-weight:500;
    text-transform:uppercase; letter-spacing:.08em;
    color:#64748b;
}
.stat-dot {
    width:8px; height:8px; border-radius:50%; margin-bottom:2px;
}
.estado-select {
    border: none;
    border-radius: 999px;
    padding: 5px 11px;
    font-family: 'Sora', sans-serif;
    font-size: 11px; font-weight: 600;
    cursor: pointer; outline: none;
    transition: all .2s;
}
.estado-select.b-PENDIENTE  { background:rgba(251,191,36,.12); color:#facc15; }
.estado-select.b-PREPARANDO { background:rgba(96,165,250,.1);  color:#60a5fa; }
.estado-select.b-EN_CAMINO  { background:rgba(52,211,153,.1);  color:#34d399; }
.estado-select.b-ENTREGADO  { background:rgba(110,231,183,.1); color:#6ee7b7; }
.estado-select.b-CANCELADO  { background:rgba(248,113,113,.1); color:#f87171; }

.estado-select option {
    background: #1e2130;
    color: #e2e8f0;
}

.s-pendiente  .stat-num { color:#facc15; }
.s-pendiente  .stat-dot { background:#facc15; }
.s-preparando .stat-num { color:#60a5fa; }
.s-preparando .stat-dot { background:#60a5fa; }
.s-encamino   .stat-num { color:#34d399; }
.s-encamino   .stat-dot { background:#34d399; }
.s-entregado  .stat-num { color:#6ee7b7; }
.s-entregado  .stat-dot { background:#6ee7b7; }
.s-cancelado  .stat-num { color:#f87171; }
.s-cancelado  .stat-dot { background:#f87171; }

/* FILTROS */
.filtros-wrap {
    display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px;
}
.filtro-btn {
    height:34px; padding:0 16px; border-radius:999px;
    font-family:'Sora',sans-serif; font-size:12px; font-weight:500;
    cursor:pointer; background:#1e2130; color:#94a3b8;
    border:1px solid rgba(255,255,255,.06); transition:all .2s;
}
.filtro-btn:hover { background:#252836; color:#cbd5e1; }
.filtro-btn.active {
    background:#6ee7b7; color:#064e3b;
    border-color:#6ee7b7;
    box-shadow:0 4px 14px rgba(110,231,183,.25);
}

/* TABLA */
.table-card {
    background:#171922;
    border:1px solid rgba(255,255,255,.06);
    border-radius:18px; overflow:hidden;
    box-shadow:0 14px 35px rgba(0,0,0,.25);
}
.table-wrapper { overflow-x:auto; }
.table { margin:0; min-width:1100px; }

.table thead th {
    background:#1e2130 !important;
    color:#64748b !important;
    font-size:11px; text-transform:uppercase;
    letter-spacing:.06em; border:none !important;
    padding:14px 16px !important; white-space:nowrap;
}
.table tbody td {
    background:#171922 !important;
    color:#cbd5e1;
    border-top:1px solid rgba(255,255,255,.04) !important;
    padding:14px 16px !important;
    vertical-align:middle;
}
.table tbody tr:hover td { background:#1b1e28 !important; }

.td-id { color:#64748b; font-family:monospace; white-space:nowrap; }
.td-detalle { max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#94a3b8; font-size:12px; }
.td-dir { max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:12px; }

/* TOTAL */
.total-price { color:#6ee7b7; font-weight:600; white-space:nowrap; }

/* BADGES */
.badge-modern {
    font-size:11px; font-weight:600;
    padding:5px 11px; border-radius:999px;
    white-space:nowrap; display:inline-flex;
    align-items:center; gap:4px;
}
.b-PENDIENTE  { background:rgba(251,191,36,.12); color:#facc15; border:1px solid rgba(251,191,36,.2); }
.b-PREPARANDO { background:rgba(96,165,250,.1);  color:#60a5fa; border:1px solid rgba(96,165,250,.2); }
.b-EN_CAMINO  { background:rgba(52,211,153,.1);  color:#34d399; border:1px solid rgba(52,211,153,.2); }
.b-ENTREGADO  { background:rgba(110,231,183,.1); color:#6ee7b7; border:1px solid rgba(110,231,183,.2); }
.b-CANCELADO  { background:rgba(248,113,113,.1); color:#f87171; border:1px solid rgba(248,113,113,.2); }

/* METODO PAGO */
.metodo-badge {
    font-size:11px; padding:4px 10px; border-radius:999px;
    background:#1e2130; color:#94a3b8;
    border:1px solid rgba(255,255,255,.06);
    white-space:nowrap;
}

/* BOTONES ACCIÓN */
.acciones-wrap { display:flex; gap:5px; flex-wrap:wrap; }
.btn-accion {
    height:28px; padding:0 10px; border:none;
    border-radius:7px; font-size:11px; font-weight:600;
    font-family:'Sora',sans-serif; cursor:pointer;
    text-decoration:none; display:inline-flex;
    align-items:center; white-space:nowrap; transition:.2s;
}
.btn-preparar  { background:rgba(96,165,250,.12);  color:#60a5fa; border:1px solid rgba(96,165,250,.2); }
.btn-preparar:hover  { background:rgba(96,165,250,.22); color:#60a5fa; }
.btn-enviar    { background:rgba(52,211,153,.1);   color:#34d399; border:1px solid rgba(52,211,153,.2); }
.btn-enviar:hover    { background:rgba(52,211,153,.2); color:#34d399; }
.btn-entregar  { background:rgba(110,231,183,.1);  color:#6ee7b7; border:1px solid rgba(110,231,183,.2); }
.btn-entregar:hover  { background:rgba(110,231,183,.2); color:#6ee7b7; }
.btn-cancelar  { background:rgba(248,113,113,.1);  color:#f87171; border:1px solid rgba(248,113,113,.2); }
.btn-cancelar:hover  { background:rgba(248,113,113,.2); color:#f87171; }

/* EMPTY ROW */
.empty-row td {
    padding:3rem !important; text-align:center;
    color:#475569 !important; font-size:13px;
}

/* MODAL DETALLE */
.modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.7);
    backdrop-filter:blur(4px);
    z-index:2000;
    align-items:center; justify-content:center;
}
.modal-overlay.visible { display:flex; }

.modal-box {
    background:#171922;
    border:1px solid rgba(255,255,255,.08);
    border-radius:18px; padding:28px;
    width:100%; max-width:480px; margin:1rem;
    box-shadow:0 24px 60px rgba(0,0,0,.6);
    animation: modalIn .25s ease;
}
@keyframes modalIn {
    from { opacity:0; transform:translateY(16px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
.modal-header {
    display:flex; align-items:center;
    justify-content:space-between; margin-bottom:18px;
}
.modal-title { color:#f1f5f9; font-size:16px; font-weight:600; }
.modal-close {
    background:none; border:none; color:#64748b;
    font-size:20px; cursor:pointer; transition:color .2s; line-height:1;
}
.modal-close:hover { color:#f1f5f9; }

.modal-section {
    background:#1e2130; border-radius:10px;
    padding:14px; margin-bottom:12px;
}
.modal-section-title {
    font-size:10px; font-weight:600; text-transform:uppercase;
    letter-spacing:.1em; color:#64748b; margin-bottom:10px;
}
.modal-row {
    display:flex; justify-content:space-between;
    align-items:flex-start; gap:.5rem;
    font-size:13px; color:#94a3b8; padding:5px 0;
    border-bottom:1px solid rgba(255,255,255,.04);
}
.modal-row:last-child { border-bottom:none; }
.modal-row strong { color:#e2e8f0; text-align:right; flex-shrink:0; }

.detalle-item {
    display:flex; justify-content:space-between;
    align-items:center; padding:7px 0;
    border-bottom:1px solid rgba(255,255,255,.04);
    font-size:13px;
}
.detalle-item:last-child { border-bottom:none; }
.detalle-item .di-name { color:#e2e8f0; }
.detalle-item .di-qty  { color:#94a3b8; font-size:12px; }
.detalle-item .di-price{ color:#6ee7b7; font-weight:600; }

.modal-total {
    display:flex; justify-content:space-between;
    align-items:center; padding-top:10px;
    margin-top:4px;
}
.modal-total-label { color:#94a3b8; font-size:13px; }
.modal-total-price {
    color:#6ee7b7; font-size:20px; font-weight:600;
}

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
        <h2 class="page-title">Pedidos Delivery</h2>
        <span class="count-badge" id="countBadge"><?= count($pedidos) ?> registros</span>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card s-pendiente">
            <div class="stat-dot"></div>
            <div class="stat-num"><?= $stats['PENDIENTE'] ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card s-preparando">
            <div class="stat-dot"></div>
            <div class="stat-num"><?= $stats['PREPARANDO'] ?></div>
            <div class="stat-label">Preparando</div>
        </div>
        <div class="stat-card s-encamino">
            <div class="stat-dot"></div>
            <div class="stat-num"><?= $stats['EN_CAMINO'] ?></div>
            <div class="stat-label">En camino</div>
        </div>
        <div class="stat-card s-entregado">
            <div class="stat-dot"></div>
            <div class="stat-num"><?= $stats['ENTREGADO'] ?></div>
            <div class="stat-label">Entregados</div>
        </div>
        <div class="stat-card s-cancelado">
            <div class="stat-dot"></div>
            <div class="stat-num"><?= $stats['CANCELADO'] ?></div>
            <div class="stat-label">Cancelados</div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-wrap">
        <button class="filtro-btn active" onclick="filtrar('todos',this)">🗂 Todos</button>
        <button class="filtro-btn" onclick="filtrar('PENDIENTE',this)">🟡 Pendientes</button>
        <button class="filtro-btn" onclick="filtrar('PREPARANDO',this)">🔵 Preparando</button>
        <button class="filtro-btn" onclick="filtrar('EN_CAMINO',this)">🟢 En camino</button>
        <button class="filtro-btn" onclick="filtrar('ENTREGADO',this)">✅ Entregados</button>
        <button class="filtro-btn" onclick="filtrar('CANCELADO',this)">🔴 Cancelados</button>
    </div>

    <!-- TABLA -->
    <div class="table-card">
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
                    <td colspan="10">🛵 No hay pedidos de delivery aún</td>
                </tr>
                <?php endif; ?>

                <?php foreach ($pedidos as $p):
                    $iconoMetodo = match($p['metodo_pago']) {
                        'EFECTIVO' => '💵',
                        'YAPE'     => '📱',
                        'PLIN'     => '💸',
                        'TARJETA'  => '💳',
                        default    => '💰'
                    };
                ?>
                <tr data-estado="<?= $p['estado'] ?>"
                    data-id="<?= $p['id'] ?>">

                    <td class="td-id">
                        <?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?>
                    </td>

                    <td style="white-space:nowrap;font-size:12px;">
                        <?= date('d/m/Y', strtotime($p['fecha'])) ?><br>
                        <span style="color:#64748b"><?= substr($p['hora'],0,5) ?></span>
                    </td>

                    <td style="font-weight:500">
                        <?= htmlspecialchars($p['nombre_cliente']) ?><br>
                        <span style="color:#64748b;font-size:11px">
                            <?= htmlspecialchars($p['usuario']) ?>
                        </span>
                    </td>

                    <td style="font-size:12px;color:#94a3b8">
                        <?= htmlspecialchars($p['telefono']) ?>
                    </td>

                    <td class="td-dir"
                        title="<?= htmlspecialchars($p['direccion']) ?>">
                        <?= htmlspecialchars($p['direccion']) ?>
                    </td>

                    <td class="td-detalle"
                        title="<?= htmlspecialchars($p['detalle_resumen'] ?? '') ?>">
                        <?= htmlspecialchars($p['detalle_resumen'] ?? '—') ?>
                    </td>

                    <td>
                        <span class="metodo-badge">
                            <?= $iconoMetodo ?> <?= $p['metodo_pago'] ?>
                        </span>
                    </td>

                    <td class="total-price">
                        S/ <?= number_format($p['total'], 2) ?>
                    </td>

<td>
    <select class="estado-select b-<?= $p['estado'] ?>"
            onchange="cambiarEstado(<?= $p['id'] ?>, this)">
        <option value="PENDIENTE"  <?= $p['estado']==='PENDIENTE'  ?'selected':'' ?>>🟡 Pendiente</option>
        <option value="PREPARANDO" <?= $p['estado']==='PREPARANDO' ?'selected':'' ?>>🔵 Preparando</option>
        <option value="EN_CAMINO"  <?= $p['estado']==='EN_CAMINO'  ?'selected':'' ?>>🟢 En camino</option>
        <option value="ENTREGADO"  <?= $p['estado']==='ENTREGADO'  ?'selected':'' ?>>✅ Entregado</option>
        <option value="CANCELADO"  <?= $p['estado']==='CANCELADO'  ?'selected':'' ?>>🔴 Cancelado</option>
    </select>
</td>

<td>
    <button class="btn-accion"
            style="background:rgba(148,163,184,.1);color:#94a3b8;border:1px solid rgba(148,163,184,.15)"
            onclick="verDetalle(<?= $p['id'] ?>)">
        👁 Ver
    </button>
</td>

                </tr>
                <?php endforeach; ?>

                <tr id="emptyFiltro" style="display:none" class="empty-row">
                    <td colspan="10">🔍 No hay pedidos en esta categoría</td>
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
            <span class="modal-title" id="modalTitle">Detalle del pedido</span>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <div id="modalBody"></div>
    </div>
</div>

<script>
/* ── Datos para el modal ── */
var pedidosData = <?= json_encode($pedidos, JSON_HEX_TAG | JSON_HEX_QUOT) ?>;

/* Detalle completo por pedido */
var detallesData = <?php
    $detalles = $conexion->query("
        SELECT id_pedido, nombre, precio, cantidad
        FROM detalle_pedidos_web
        ORDER BY id_pedido, id
    ")->fetchAll(PDO::FETCH_ASSOC);

    $map = [];
    foreach ($detalles as $d) {
        $map[$d['id_pedido']][] = $d;
    }
    echo json_encode($map, JSON_HEX_TAG | JSON_HEX_QUOT);
?>;
/* ── Cambiar estado vía fetch ── */
function cambiarEstado(id, select) {
    var nuevoEstado = select.value;
    var url = 'ver_pedidos_web.php?accion=' + nuevoEstado + '&id=' + id + '&ajax=1';
    
fetch(url)
    .then(function(response) { return response.text(); })
    .then(function(text) {
        console.log('Respuesta del servidor:', text);
        select.className = 'estado-select b-' + nuevoEstado;
        select.closest('tr').dataset.estado = nuevoEstado;
    });
}
/* ── Generar recibo PDF ── */
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
    document.getElementById('countBadge').textContent = count + ' registros';
}

/* ── Modal detalle ── */
function verDetalle(id) {
    var p = pedidosData.find(function(x){ return x.id == id; });
    if (!p) return;

    var items = detallesData[id] || [];
    var iconoMetodo = {EFECTIVO:'💵',YAPE:'📱',PLIN:'💸',TARJETA:'💳'}[p.metodo_pago] || '💰';

    var html = '';

    // Info cliente
    html += '<div class="modal-section">';
    html += '<div class="modal-section-title">Cliente</div>';
    html += '<div class="modal-row"><span>Nombre</span><strong>' + p.nombre_cliente + '</strong></div>';
    html += '<div class="modal-row"><span>Teléfono</span><strong>' + p.telefono + '</strong></div>';
    html += '<div class="modal-row"><span>Usuario</span><strong>' + p.usuario + '</strong></div>';
    html += '<div class="modal-row"><span>Dirección</span><strong style="max-width:220px;text-align:right">' + p.direccion + '</strong></div>';
    html += '</div>';

    // Info pedido
    html += '<div class="modal-section">';
    html += '<div class="modal-section-title">Pedido</div>';
    html += '<div class="modal-row"><span>Fecha</span><strong>' + p.fecha + '</strong></div>';
    html += '<div class="modal-row"><span>Hora</span><strong>' + p.hora.substring(0,5) + '</strong></div>';
    html += '<div class="modal-row"><span>Método de pago</span><strong>' + iconoMetodo + ' ' + p.metodo_pago + '</strong></div>';
    html += '</div>';

    // Detalle platos
    html += '<div class="modal-section">';
    html += '<div class="modal-section-title">Platos</div>';

    if (items.length > 0) {
        items.forEach(function(item) {
            var subtotal = (parseFloat(item.precio) * parseInt(item.cantidad)).toFixed(2);
            html += '<div class="detalle-item">';
            html += '<span class="di-name">' + item.nombre + '</span>';
            html += '<span class="di-qty">× ' + item.cantidad + '</span>';
            html += '<span class="di-price">S/ ' + subtotal + '</span>';
            html += '</div>';
        });
    } else {
        html += '<p style="color:#475569;font-size:13px;text-align:center;padding:.5rem">Sin detalle disponible</p>';
    }

    html += '<div class="modal-total">';
    html += '<span class="modal-total-label">Total</span>';
    html += '<span class="modal-total-price">S/ ' + parseFloat(p.total).toFixed(2) + '</span>';
    html += '</div>';
    html += '</div>';

    document.getElementById('modalTitle').textContent =
        'Pedido #' + String(p.id).padStart(3, '0');
    document.getElementById('modalBody').innerHTML = html;


    // ← AQUÍ, después del cierre del último div y antes de asignar al modal:
    html += '<button onclick="generarRecibo(' + id + ')" style="' +
        'width:100%;margin-top:8px;padding:.75rem;border:none;border-radius:10px;' +
        'background:linear-gradient(135deg,#ca8a04,#92400e);color:#fff;' +
        'font-family:Sora,sans-serif;font-size:.85rem;font-weight:600;' +
        'cursor:pointer;letter-spacing:.04em;text-transform:uppercase;' +
        'box-shadow:0 6px 18px rgba(200,150,46,.35);">🧾 Generar Recibo PDF</button>';

    document.getElementById('modalTitle').textContent =
        'Pedido #' + String(p.id).padStart(3, '0');
    document.getElementById('modalBody').innerHTML = html;
    document.getElementById('modalOverlay').classList.add('visible');
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
</script>

</body>
</html>