<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$mes    = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$anio   = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
$mes    = max(1, min(12, $mes));
$anio   = max(2020, min(2099, $anio));

$dias_mes = (int)date('t', mktime(0, 0, 0, $mes, 1, $anio));

// ── INGRESOS (web, entregados) ──
$stmt = $conexion->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos_web WHERE estado='ENTREGADO' AND EXTRACT(MONTH FROM created_at)=? AND EXTRACT(YEAR FROM created_at)=?");
$stmt->execute([$mes, $anio]);
$ventas = (float)$stmt->fetchColumn();

// ── ÓRDENES ──
$stmt = $conexion->prepare("SELECT COUNT(*) FROM pedidos_web WHERE EXTRACT(MONTH FROM created_at)=? AND EXTRACT(YEAR FROM created_at)=?");
$stmt->execute([$mes, $anio]);
$total_ordenes = (int)$stmt->fetchColumn();

// ── ÓRDENES POR ESTADO ──
$estados = [];
$stmt = $conexion->prepare("SELECT estado, COUNT(*) as c FROM pedidos_web WHERE EXTRACT(MONTH FROM created_at)=? AND EXTRACT(YEAR FROM created_at)=? GROUP BY estado");
$stmt->execute([$mes, $anio]);
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) $estados[$r['estado']] = (int)$r['c'];

$estados_orden = ['PENDIENTE', 'PREPARANDO', 'EN_CAMINO', 'ENTREGADO', 'CANCELADO'];
$colores_estado = [
    'PENDIENTE' => '#fbbf24', 'PREPARANDO' => '#60a5fa',
    'EN_CAMINO' => '#a78bfa', 'ENTREGADO' => '#6ee7b7', 'CANCELADO' => '#f87171'
];

// ── INGRESOS POR DÍA ──
$ventas_diarias = [];
for ($d = 1; $d <= $dias_mes; $d++) {
    $f = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
    $stmt = $conexion->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos_web WHERE estado='ENTREGADO' AND DATE(created_at)=?");
    $stmt->execute([$f]);
    $ventas_diarias[] = (float)$stmt->fetchColumn();
}

// ── PRODUCTOS MÁS VENDIDOS (top 10) ──
$stmt = $conexion->prepare(
    "SELECT dpw.nombre, SUM(dpw.cantidad) as total_vendido
     FROM detalle_pedidos_web dpw
     JOIN pedidos_web pw ON dpw.id_pedido = pw.id
     WHERE pw.estado='ENTREGADO' AND EXTRACT(MONTH FROM pw.created_at)=? AND EXTRACT(YEAR FROM pw.created_at)=?
     GROUP BY dpw.nombre
     ORDER BY total_vendido DESC LIMIT 10"
);
$stmt->execute([$mes, $anio]);
$top_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$top_nombres = [];
$top_cantidades = [];
foreach ($top_rows as $r) {
    $top_nombres[] = $r['nombre'];
    $top_cantidades[] = (int)$r['total_vendido'];
}

// ── VENTAS POR CATEGORÍA ──
$categorias = ['Platos' => 0, 'Bebidas' => 0, 'Postres' => 0, 'Otros' => 0];
$stmt = $conexion->prepare(
    "SELECT dpw.nombre, SUM(dpw.cantidad * dpw.precio) as total
     FROM detalle_pedidos_web dpw
     JOIN pedidos_web pw ON dpw.id_pedido = pw.id
     WHERE pw.estado='ENTREGADO' AND EXTRACT(MONTH FROM pw.created_at)=? AND EXTRACT(YEAR FROM pw.created_at)=?
     GROUP BY dpw.nombre"
);
$stmt->execute([$mes, $anio]);
$cat_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cat_rows as $c) {
    $nom = $c['nombre'];
    $val = (float)$c['total'];
    $cat = 'Otros';
    foreach (['platos', 'bebidas', 'postres'] as $tabla) {
        $s = $conexion->prepare("SELECT COUNT(*) FROM $tabla WHERE nombre = ?");
        $s->execute([$nom]);
        if ($s->fetchColumn() > 0) { $cat = ucfirst($tabla); break; }
    }
    $categorias[$cat] += $val;
}

// ── MÉTODO DE PAGO ──
$metodos = [];
$stmt = $conexion->prepare("SELECT metodo_pago, COUNT(*) as c FROM pedidos_web WHERE EXTRACT(MONTH FROM created_at)=? AND EXTRACT(YEAR FROM created_at)=? GROUP BY metodo_pago");
$stmt->execute([$mes, $anio]);
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) $metodos[$r['metodo_pago']] = (int)$r['c'];

// ── RESERVAS ──
$stmt = $conexion->prepare("SELECT COUNT(*) FROM reservas WHERE EXTRACT(MONTH FROM fecha)=? AND EXTRACT(YEAR FROM fecha)=?");
$stmt->execute([$mes, $anio]);
$reservas_mes = (int)$stmt->fetchColumn();

$stmt = $conexion->prepare("SELECT COUNT(*) FROM reservas WHERE EXTRACT(MONTH FROM fecha)=? AND EXTRACT(YEAR FROM fecha)=? AND estado='CONFIRMADA'");
$stmt->execute([$mes, $anio]);
$reservas_confirmadas = (int)$stmt->fetchColumn();

// ── COMPARATIVA MES ANTERIOR ──
$mes_prev = $mes == 1 ? 12 : $mes - 1;
$anio_prev = $mes == 1 ? $anio - 1 : $anio;

$stmt = $conexion->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos_web WHERE estado='ENTREGADO' AND EXTRACT(MONTH FROM created_at)=? AND EXTRACT(YEAR FROM created_at)=?");
$stmt->execute([$mes_prev, $anio_prev]);
$ventas_prev = (float)$stmt->fetchColumn();

$dif_porcentaje = $ventas_prev > 0 ? round(($ventas - $ventas_prev) / $ventas_prev * 100, 1) : 0;
$dif_ordenes = 0;
$stmt = $conexion->prepare("SELECT COUNT(*) FROM pedidos_web WHERE EXTRACT(MONTH FROM created_at)=? AND EXTRACT(YEAR FROM created_at)=?");
$stmt->execute([$mes_prev, $anio_prev]);
$ord_prev = (int)$stmt->fetchColumn();
$dif_ordenes = $ord_prev > 0 ? round(($total_ordenes - $ord_prev) / $ord_prev * 100, 1) : 0;

// Meses en español
$meses_es = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Estadísticas</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Sora', sans-serif;
    overflow-x: hidden;
    min-height: 100vh;
    background: url('../assets/img/fnd.jpg') no-repeat center center fixed;
    background-size: cover;
}

#sidebar {
    position: fixed; top: 0; left: 0;
    width: 260px; height: 100vh;
    background: #0d0f18;
    border-right: 1px solid rgba(255,255,255,.05);
    z-index: 200;
}

#toggleBtn, #toggleTNBtn {
    position: fixed; top: 14px; left: 14px;
    width: 38px !important; height: 38px !important;
    padding: 0 !important; border: none;
    border-radius: 8px !important;
    background: #1e2130; color: #94a3b8;
    font-size: 18px !important; line-height: 38px !important;
    text-align: center; z-index: 1100;
}

.content-area { margin-left: 260px; padding: 28px; min-height: 100vh; }

/* ── HEADER ── */
.page-header {
    display: flex; justify-content: space-between;
    align-items: center; gap: 12px; flex-wrap: wrap;
    margin-bottom: 24px;
}

.page-title {
    margin: 0; color: #f1f5f9;
    font-size: 20px; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
}
.page-title-dot {
    width: 8px; height: 8px;
    border-radius: 50%; background: #8b5cf6;
    flex-shrink: 0;
}
.page-sub {
    color: #64748b; font-size: 13px; font-weight: 400;
}

/* ── SELECTOR DE MES ── */
.filter-form {
    display: flex; align-items: center; gap: 10px;
    flex-wrap: wrap;
}
.filter-form select {
    background: #171922;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px;
    padding: 8px 14px 8px 12px;
    color: #e2e8f0;
    font-family: 'Sora', sans-serif;
    font-size: 13px;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 32px;
}
.filter-form select:focus { border-color: #8b5cf6; }
.filter-form .btn-filtrar {
    background: #8b5cf6; color: #fff; border: none;
    border-radius: 10px; padding: 8px 18px;
    font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: opacity .2s;
    display: flex; align-items: center; gap: 6px;
}
.filter-form .btn-filtrar:hover { opacity: .85; }
.filter-form .btn-filtrar svg { width: 14px; height: 14px; }

/* ── KPI CARDS ── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}

.kpi-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform .2s, box-shadow .2s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,.25); }

.kpi-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.kpi-icon svg { width: 20px; height: 20px; }

.kpi-body { flex: 1; min-width: 0; }
.kpi-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 2px; }
.kpi-value { font-size: 22px; font-weight: 700; color: #f1f5f9; line-height: 1.2; }
.kpi-diff {
    font-size: 11px; font-weight: 600; margin-top: 2px;
}
.kpi-diff.up { color: #6ee7b7; }
.kpi-diff.down { color: #f87171; }
.kpi-diff.neutral { color: #64748b; }

.icon-ventas    { background: rgba(139,92,246,.15); color: #a78bfa; }
.icon-ordenes   { background: rgba(16,185,129,.15); color: #6ee7b7; }
.icon-reservas  { background: rgba(236,72,153,.15);  color: #f9a8d4; }

/* ── CHARTS ── */
.charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.charts-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.chart-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
}

.chart-title {
    font-size: 13px; font-weight: 600; color: #94a3b8;
    margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
}
.chart-title svg { width: 16px; height: 16px; }

.chart-container { position: relative; width: 100%; }
.chart-container canvas { width: 100% !important; }

/* ── TOP PRODUCTOS ── */
.top-list { list-style: none; }
.top-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,.05);
}
.top-item:last-child { border-bottom: none; }
.top-rank {
    width: 22px; height: 22px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; flex-shrink: 0;
    background: rgba(139,92,246,.15); color: #a78bfa;
}
.top-rank.gold { background: rgba(245,158,11,.2); color: #fbbf24; }
.top-rank.silver { background: rgba(148,163,184,.15); color: #94a3b8; }
.top-rank.bronze { background: rgba(180,83,9,.2); color: #d4a373; }
.top-name { flex: 1; font-size: 13px; color: #e2e8f0; }
.top-qty { font-size: 13px; font-weight: 600; color: #a78bfa; }

/* ESTADOS lista */
.status-list { list-style: none; }
.status-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.05);
    font-size: 13px;
}
.status-item:last-child { border-bottom: none; }
.status-label { color: #94a3b8; display: flex; align-items: center; gap: 8px; }
.status-label .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.status-count { color: #f1f5f9; font-weight: 600; }

/* MÉTODO DE PAGO */
.metodo-list { list-style: none; }
.metodo-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.05);
    font-size: 13px;
}
.metodo-item:last-child { border-bottom: none; }
.metodo-label { color: #94a3b8; display: flex; align-items: center; gap: 8px; }
.metodo-count { color: #f1f5f9; font-weight: 600; }

/* ── RESPONSIVE ── */
@media (max-width: 992px) {
    .content-area { margin-left: 0; padding: 16px; }
    .charts-grid { grid-template-columns: 1fr; }
    .charts-grid-2 { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .page-title { font-size: 18px; }
    .kpi-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; }
    .kpi-card { padding: 14px; }
    .kpi-value { font-size: 18px; }
    .chart-card { padding: 14px; }
}
@media (max-width: 480px) {
    .content-area { padding: 10px; }
    .kpi-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
    .kpi-card { padding: 10px; gap: 10px; }
    .kpi-icon { width: 36px; height: 36px; }
    .kpi-icon svg { width: 16px; height: 16px; }
    .kpi-value { font-size: 16px; }
    .kpi-label { font-size: 10px; }
    .filter-form select { font-size: 12px; padding: 6px 28px 6px 10px; }
    .filter-form .btn-filtrar { font-size: 12px; padding: 6px 12px; }
}
</style>
</head>
<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

    <div class="page-header">
        <h2 class="page-title">
            <span class="page-title-dot"></span>
            Estadísticas
            <span class="page-sub">— Web</span>
        </h2>

        <form class="filter-form" method="GET">
            <select name="mes">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= $mes === $i ? 'selected' : '' ?>><?= $meses_es[$i] ?></option>
                <?php endfor; ?>
            </select>
            <select name="anio">
                <?php for ($i = (int)date('Y'); $i >= 2020; $i--): ?>
                <option value="<?= $i ?>" <?= $anio === $i ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn-filtrar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Filtrar
            </button>
        </form>
    </div>

    <!-- KPI CARDS -->
    <div class="kpi-grid">

        <div class="kpi-card">
            <div class="kpi-icon icon-ventas"><i data-lucide="trending-up"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Ventas</div>
                <div class="kpi-value">S/ <?= number_format($ventas, 2) ?></div>
                <div class="kpi-diff <?= $dif_porcentaje > 0 ? 'up' : ($dif_porcentaje < 0 ? 'down' : 'neutral') ?>">
                    <?= $dif_porcentaje > 0 ? '+' : '' ?><?= $dif_porcentaje ?>% vs mes anterior
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon icon-ordenes"><i data-lucide="shopping-cart"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Órdenes</div>
                <div class="kpi-value"><?= $total_ordenes ?></div>
                <div class="kpi-diff <?= $dif_ordenes > 0 ? 'up' : ($dif_ordenes < 0 ? 'down' : 'neutral') ?>">
                    <?= $dif_ordenes > 0 ? '+' : '' ?><?= $dif_ordenes ?>% vs mes anterior
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon icon-ventas"><i data-lucide="package-check"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Entregadas</div>
                <div class="kpi-value"><?= $estados['ENTREGADO'] ?? 0 ?></div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon icon-reservas"><i data-lucide="calendar-check"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Reservas</div>
                <div class="kpi-value"><?= $reservas_mes ?></div>
                <div class="kpi-diff neutral"><?= $reservas_confirmadas ?> confirmadas</div>
            </div>
        </div>

    </div>

    <!-- GRÁFICOS: línea ingresos + dona categorías -->
    <div class="charts-grid">

        <div class="chart-card">
            <div class="chart-title">
                <i data-lucide="trending-up"></i> Ingresos diarios — <?= $meses_es[$mes] ?>
            </div>
            <div class="chart-container">
                <canvas id="chartDiario"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <i data-lucide="pie-chart"></i> Ventas por categoría
            </div>
            <div class="chart-container">
                <canvas id="chartCategorias"></canvas>
            </div>
        </div>

    </div>

    <!-- GRÁFICOS 2: top productos + métodos de pago -->
    <div class="charts-grid-2">

        <div class="chart-card">
            <div class="chart-title">
                <i data-lucide="bar-chart-3"></i> Productos más vendidos
            </div>
            <div class="chart-container">
                <canvas id="chartTopProductos"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <i data-lucide="credit-card"></i> Métodos de pago
            </div>
            <?php if (!empty($metodos)): ?>
            <ul class="metodo-list">
                <?php foreach ($metodos as $met => $cnt): ?>
                <li class="metodo-item">
                    <span class="metodo-label">
                        <?php
                        $iconos = ['EFECTIVO' => 'banknote', 'YAPE' => 'smartphone', 'PLIN' => 'smartphone', 'TARJETA' => 'credit-card'];
                        $icon = $iconos[$met] ?? 'circle';
                        ?>
                        <i data-lucide="<?= $icon ?>" style="width:14px;height:14px;color:#64748b;"></i>
                        <?= $met ?>
                    </span>
                    <span class="metodo-count"><?= $cnt ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p style="color:#475569;text-align:center;padding:2rem 0;font-size:13px;">Sin datos este mes</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- FILA: estados web + top productos -->
    <div class="charts-grid-2">

        <div class="chart-card">
            <div class="chart-title">
                <i data-lucide="globe"></i> Estados de pedidos
            </div>
            <ul class="status-list">
                <?php foreach ($estados_orden as $est): ?>
                <li class="status-item">
                    <span class="status-label">
                        <span class="dot" style="background:<?= $colores_estado[$est] ?>"></span>
                        <?= $est ?>
                    </span>
                    <span class="status-count"><?= $estados[$est] ?? 0 ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <i data-lucide="trophy"></i> Top productos
            </div>
            <?php if (!empty($top_nombres)): ?>
            <ul class="top-list">
                <?php $i = 0; foreach ($top_nombres as $idx => $nom): $i++; $cnt = $top_cantidades[$idx]; ?>
                <li class="top-item">
                    <span class="top-rank <?= $i === 1 ? 'gold' : ($i === 2 ? 'silver' : ($i === 3 ? 'bronze' : '')) ?>"><?= $i ?></span>
                    <span class="top-name"><?= htmlspecialchars($nom) ?></span>
                    <span class="top-qty"><?= $cnt ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p style="color:#475569;text-align:center;padding:2rem 0;font-size:13px;">Sin ventas este mes</p>
            <?php endif; ?>
        </div>

    </div>

</div>

<script>
lucide.createIcons();

// ── Ingresos diarios ──
new Chart(document.getElementById('chartDiario'), {
    type: 'line',
    data: {
        labels: <?= json_encode(range(1, $dias_mes)) ?>,
        datasets: [{
            label: 'Ingresos (S/)',
            data: <?= json_encode($ventas_diarias) ?>,
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139,92,246,.12)',
            borderWidth: 2,
            fill: true,
            tension: .35,
            pointRadius: 3,
            pointBackgroundColor: '#8b5cf6',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#64748b', font: { size: 10 } } },
            y: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#64748b', font: { size: 10 }, callback: function(v) { return 'S/ ' + v.toFixed(0); } } }
        }
    }
});

// ── Categorías ──
new Chart(document.getElementById('chartCategorias'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($categorias)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($categorias)) ?>,
            backgroundColor: ['#8b5cf6', '#14b8a6', '#f472b6', '#64748b'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'bottom', labels: { color: '#94a3b8', font: { size: 11 }, padding: 14 } }
        }
    }
});

// ── Top productos ──
new Chart(document.getElementById('chartTopProductos'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($top_nombres) ?>,
        datasets: [{
            label: 'Unidades vendidas',
            data: <?= json_encode($top_cantidades) ?>,
            backgroundColor: 'rgba(139,92,246,.65)',
            borderColor: '#8b5cf6',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#64748b', font: { size: 10 } } },
            y: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } }
        }
    }
});
</script>

</body>
</html>
