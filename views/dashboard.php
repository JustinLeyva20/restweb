<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require "../config/conexion.php";

$total_reservas   = $conexion->query("SELECT COUNT(*) FROM reservas WHERE estado='CONFIRMADA'")->fetchColumn();
$total_salas      = $conexion->query("SELECT COUNT(*) FROM salas")->fetchColumn();
$total_platos     = $conexion->query("SELECT COUNT(*) FROM platos")->fetchColumn();
$total_bebidas    = $conexion->query("SELECT COUNT(*) FROM bebidas")->fetchColumn();
$total_postres    = $conexion->query("SELECT COUNT(*) FROM postres")->fetchColumn();
$total_usuarios   = $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_pedidos_web = $conexion->query("SELECT COUNT(*) FROM pedidos_web WHERE estado NOT IN ('ENTREGADO','CANCELADO')")->fetchColumn();
$total_reportes   = $conexion->query("SELECT COUNT(*) FROM reportes WHERE estado!='RESUELTO'")->fetchColumn();

$stmt = $conexion->query("SELECT horario_apertura, horario_cierre FROM config LIMIT 1");
$hconf = $stmt->fetch(PDO::FETCH_ASSOC);
$h_aper = (int)explode(':', $hconf['horario_apertura'] ?? '08:00')[0];
$h_cier = (int)explode(':', $hconf['horario_cierre'] ?? '20:00')[0];
$hora_peru = (int)date('G', time() - 5 * 3600);
$en_horario = $hora_peru >= $h_aper && $hora_peru < $h_cier;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --green:   #6ee7b7;
    --green-d: #064e3b;
    --bg:      #0d0f18;
    --panel:   rgba(13,15,24,.65);
    --border:  rgba(255,255,255,.08);
    --muted:   #64748b;
    --text:    #e2e8f0;
}

body {
    min-height: 100vh;
    overflow-x: hidden;
    font-family: 'Sora', sans-serif;
background: url('../assets/img/fnd.jpg') no-repeat center center fixed;
background-size: cover;
    color: var(--text);
}

/* ── SIDEBAR ── */
#sidebar {
    position: fixed; left: 0; top: 0;
    width: 260px; height: 100vh;
    background: #0d0f18;
    border-right: 1px solid var(--border);
    z-index: 1000;
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

/* ── CONTENT ── */
.content-area {
    margin-left: 260px;
    padding: 24px;
    min-height: 100vh;
}

/* ══════════════════════════════
   HERO SECTION
══════════════════════════════ */
.hero-section {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    background: rgba(13, 15, 24, 0);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border: 1px solid var(--border);
    padding: 52px 48px 48px;
    margin-bottom: 20px;
    min-height: 280px;
    display: flex;
    align-items: flex-end;
}

/* Canvas background */
#hero-canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 560px;
    animation: fadeUp .7s ease both;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 14px;
    border-radius: 999px;
    background: rgba(110,231,183,.10);
    border: 1px solid rgba(110,231,183,.22);
    color: var(--green);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: .04em;
    margin-bottom: 18px;
}
.hero-badge-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--green);
    animation: pulse 2s infinite;
}


.hero-title {
    font-size: 38px;
    font-weight: 700;
    color: #f1f5f9;
    line-height: 1.15;
    margin-bottom: 12px;
    letter-spacing: -.02em;
}
.hero-title span {
    color: var(--green);
}

.hero-desc {
    font-size: 15px;
    color: #94a3b8;
    line-height: 1.6;
    font-weight: 300;
    max-width: 420px;
}

/* Decoración derecha del hero */
.hero-deco {
    position: absolute;
    right: 48px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 12px;
    animation: fadeUp .9s ease both;
}

.deco-ring {
    width: 120px; height: 120px;
    border-radius: 50%;
    border: 1px solid rgba(110,231,183,.12);
    display: flex; align-items: center; justify-content: center;
    position: relative;
}
.deco-ring::before {
    content: "";
    position: absolute;
    width: 86px; height: 86px;
    border-radius: 50%;
    border: 1px solid rgba(110,231,183,.20);
}
.deco-ring::after {
    content: "";
    position: absolute;
    width: 54px; height: 54px;
    border-radius: 50%;
    background: rgba(110,231,183,.06);
    border: 1px solid rgba(110,231,183,.30);
}

.deco-ring svg {
    width: 26px; height: 26px;
    stroke: var(--green);
    position: relative; z-index: 1;
}

/* ══════════════════════════════
   STAT CARDS
══════════════════════════════ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 20px;
    animation: fadeUp .8s ease both;
}

.stat-card {
    background: var(--panel);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px 22px;
    position: relative;
    overflow: hidden;
    transition: border-color .25s, transform .25s;
    cursor: default;
}
.stat-card:hover {
    border-color: rgba(110,231,183,.2);
    transform: translateY(-2px);
}
.stat-card::before {
    content: "";
    position: absolute;
    top: -40px; right: -40px;
    width: 100px; height: 100px;
    border-radius: 50%;
    background: var(--accent-glow);
    filter: blur(30px);
    opacity: .45;
    pointer-events: none;
}

.stat-card[data-color="green"]  { --accent-glow: rgba(110,231,183,.4); }
.stat-card[data-color="blue"]   { --accent-glow: rgba(99,102,241,.4);  }
.stat-card[data-color="amber"]  { --accent-glow: rgba(251,191,36,.4);  }
.stat-card[data-color="rose"]   { --accent-glow: rgba(251,113,133,.4); }

.stat-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
    flex-shrink: 0;
}
.stat-icon svg { width: 18px; height: 18px; }

.stat-card[data-color="green"] .stat-icon  { background: rgba(110,231,183,.10); }
.stat-card[data-color="green"] .stat-icon svg { stroke: #6ee7b7; }
.stat-card[data-color="blue"]  .stat-icon  { background: rgba(99,102,241,.10); }
.stat-card[data-color="blue"]  .stat-icon svg { stroke: #a5b4fc; }
.stat-card[data-color="amber"] .stat-icon  { background: rgba(251,191,36,.10); }
.stat-card[data-color="amber"] .stat-icon svg { stroke: #fcd34d; }
.stat-card[data-color="rose"]  .stat-icon  { background: rgba(251,113,133,.10); }
.stat-card[data-color="rose"]  .stat-icon svg { stroke: #fda4af; }

.stat-value {
    font-size: 26px; font-weight: 700;
    color: #f1f5f9; line-height: 1;
    margin-bottom: 4px;
    letter-spacing: -.02em;
}
.stat-label {
    font-size: 12px; color: var(--muted);
    font-weight: 400;
}

/* ══════════════════════════════
   BOTTOM ROW
══════════════════════════════ */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    animation: fadeUp 1s ease both;
}

.info-card {
    background: var(--panel);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px 24px;
}

.card-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.card-title {
    font-size: 13px; font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .06em;
    display: flex; align-items: center; gap: 7px;
}
.card-title svg { width: 14px; height: 14px; stroke: #64748b; }

.card-badge {
    font-size: 11px; padding: 3px 10px;
    border-radius: 999px;
    background: rgba(110,231,183,.08);
    color: var(--green);
    border: 1px solid rgba(110,231,183,.15);
}

/* Quick links */
.quick-links {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.quick-link {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(13,15,24,.4);
    border: 1px solid rgba(255,255,255,.04);
    text-decoration: none;
    transition: border-color .2s, background .2s;
    color: var(--text);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
.quick-link:hover {
    border-color: rgba(110,231,183,.2);
    background: rgba(13,15,24,.6);
    color: var(--text);
}
.quick-link-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.quick-link-icon svg { width: 15px; height: 15px; }

.ql-green  { background: rgba(110,231,183,.10); }
.ql-green  svg { stroke: #6ee7b7; }
.ql-indigo { background: rgba(99,102,241,.10);  }
.ql-indigo svg { stroke: #a5b4fc; }
.ql-amber  { background: rgba(251,191,36,.10);  }
.ql-amber  svg { stroke: #fcd34d; }
.ql-rose   { background: rgba(251,113,133,.10); }
.ql-rose   svg { stroke: #fda4af; }

.quick-link-text { font-size: 13px; font-weight: 500; }
.quick-link-sub  { font-size: 11px; color: var(--muted); }

/* Activity list */
.activity-list { display: flex; flex-direction: column; gap: 12px; }

.activity-item {
    display: flex; align-items: center; gap: 12px;
}
.activity-dot {
    width: 8px; height: 8px;
    border-radius: 50%; flex-shrink: 0;
}
.activity-dot.green  { background: #6ee7b7; box-shadow: 0 0 8px rgba(110,231,183,.5); }
.activity-dot.indigo { background: #a5b4fc; box-shadow: 0 0 8px rgba(165,180,252,.5); }
.activity-dot.amber  { background: #fcd34d; box-shadow: 0 0 8px rgba(252,211,77,.5);  }
.activity-dot.rose   { background: #fda4af; box-shadow: 0 0 8px rgba(253,164,175,.5); }

.activity-text { font-size: 13px; color: #cbd5e1; flex: 1; }
.activity-time { font-size: 11px; color: var(--muted); white-space: nowrap; }

/* ── Animaciones ── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0);    }
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: .4; }
}

/* ── Responsive ── */
@media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 992px) {
    .hero-deco { display: none; }
    .hero-title { font-size: 30px; }
}
@media (max-width: 768px) {
    .content-area { margin-left: 0; padding: 14px; }
    .hero-section { padding: 32px 24px 28px; }
    .hero-title { font-size: 26px; }
    .stats-grid { grid-template-columns: repeat(3, 1fr); }
    .bottom-grid { grid-template-columns: 1fr; }
    .quick-links { grid-template-columns: 1fr; }
}
@media (max-width: 576px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .hero-title { font-size: 24px; }
}
@media (max-width: 400px) {
    .stats-grid { grid-template-columns: 1fr; }
    .hero-title { font-size: 22px; }
}
</style>
</head>

<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

    <!-- ── HERO ── -->
    <div class="hero-section">
        <canvas id="hero-canvas"></canvas>
        <div class="hero-gradient"></div>

        <div class="hero-content">
            <h1 class="hero-title">
                Bienvenido al<br><span>Sistema</span>
            </h1>
            <p class="hero-desc">
                Gestiona reservas, pedidos, usuarios y operaciones desde un solo lugar con visibilidad total.
            </p>
        </div>

        <!-- Decoración anillos derecha -->
        <div class="hero-deco">
        </div>
    </div>

    <!-- ── STAT CARDS ── -->
    <div class="stats-grid">

        <div class="stat-card" data-color="green">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8"  y1="2" x2="8"  y2="6"/>
                    <line x1="3"  y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="stat-value"><?= $total_reservas ?></div>
            <div class="stat-label">Reservas</div>
        </div>

        <div class="stat-card" data-color="blue">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div class="stat-value"><?= $total_salas ?></div>
            <div class="stat-label">Salas</div>
        </div>

        <div class="stat-card" data-color="amber">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                    <line x1="6" y1="1" x2="6" y2="4"/>
                    <line x1="10" y1="1" x2="10" y2="4"/>
                    <line x1="14" y1="1" x2="14" y2="4"/>
                </svg>
            </div>
            <div class="stat-value"><?= $total_platos ?></div>
            <div class="stat-label">Platos</div>
        </div>

        <div class="stat-card" data-color="rose">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8h1a4 4 0 0 1 0 8h-1"/>
                    <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/>
                    <line x1="6"  y1="1" x2="6"  y2="4"/>
                    <line x1="10" y1="1" x2="10" y2="4"/>
                    <line x1="14" y1="1" x2="14" y2="4"/>
                </svg>
            </div>
            <div class="stat-value"><?= $total_bebidas ?></div>
            <div class="stat-label">Bebidas</div>
        </div>

        <div class="stat-card" data-color="green">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                    <line x1="6" y1="1" x2="6" y2="4"/>
                    <line x1="10" y1="1" x2="10" y2="4"/>
                    <line x1="14" y1="1" x2="14" y2="4"/>
                </svg>
            </div>
            <div class="stat-value"><?= $total_postres ?></div>
            <div class="stat-label">Postres</div>
        </div>

        <div class="stat-card" data-color="blue">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="17" cy="15" r="1"/><circle cx="7" cy="15" r="1"/>
                    <path d="M8 9h8"/><path d="M7 5c-2 0-4 2-4 4v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9c0-2-2-4-4-4"/>
                </svg>
            </div>
            <div class="stat-value"><?= $total_pedidos_web ?></div>
            <div class="stat-label">Pedidos Web</div>
        </div>

        <div class="stat-card" data-color="amber">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-value"><?= $total_usuarios ?></div>
            <div class="stat-label">Usuarios</div>
        </div>

        <div class="stat-card" data-color="rose">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
            <div class="stat-value"><?= $total_reportes ?></div>
            <div class="stat-label">Reportes</div>
        </div>

    </div>

    <!-- ── BOTTOM ROW ── -->
    <div class="bottom-grid">

        <!-- Accesos rápidos -->
        <div class="info-card">
            <div class="card-header-row">
                <span class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                        <polyline points="13 2 13 9 20 9"/>
                    </svg>
                    Accesos rápidos
                </span>
            </div>
            <div class="quick-links">

                <a href="reservas.php" class="quick-link">
                    <div class="quick-link-icon ql-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8"  y1="2" x2="8"  y2="6"/>
                            <line x1="3"  y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <div class="quick-link-text">Reservas</div>
                        <div class="quick-link-sub">Gestionar</div>
                    </div>
                </a>

                <a href="salas.php" class="quick-link">
                    <div class="quick-link-icon ql-indigo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <div>
                        <div class="quick-link-text">Salas</div>
                        <div class="quick-link-sub">Administrar</div>
                    </div>
                </a>

                <a href="platos.php" class="quick-link">
                    <div class="quick-link-icon ql-amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                            <line x1="6" y1="1" x2="6" y2="4"/>
                            <line x1="10" y1="1" x2="10" y2="4"/>
                            <line x1="14" y1="1" x2="14" y2="4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="quick-link-text">Platos</div>
                        <div class="quick-link-sub">Ver carta</div>
                    </div>
                </a>

                <a href="bebidas.php" class="quick-link">
                    <div class="quick-link-icon ql-rose">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 8h1a4 4 0 0 1 0 8h-1"/>
                            <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/>
                            <line x1="6"  y1="1" x2="6"  y2="4"/>
                            <line x1="10" y1="1" x2="10" y2="4"/>
                            <line x1="14" y1="1" x2="14" y2="4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="quick-link-text">Bebidas</div>
                        <div class="quick-link-sub">Ver carta</div>
                    </div>
                </a>

            </div>
        </div>

        <!-- Estado del sistema -->
        <div class="info-card">
            <div class="card-header-row">
                <span class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Estado del sistema
                </span>
                <span class="card-badge" style="<?= $en_horario ? '' : 'background:rgba(248,113,113,.08);color:#f87171;border-color:rgba(248,113,113,.15)' ?>">
                    <?= $en_horario ? 'En horario' : 'Fuera de horario' ?>
                </span>
            </div>
            <div class="activity-list">
                <div class="activity-item">
                    <span class="activity-dot <?= $total_reservas > 0 ? 'green' : 'rose' ?>"></span>
                    <span class="activity-text"><?= $total_reservas ?> reservas confirmadas</span>
                    <span class="activity-time">Activas</span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot <?= $total_salas > 0 ? 'indigo' : 'rose' ?>"></span>
                    <span class="activity-text"><?= $total_salas ?> salas disponibles</span>
                    <span class="activity-time">—</span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot <?= $total_platos > 0 ? 'amber' : 'rose' ?>"></span>
                    <span class="activity-text"><?= $total_platos ?> platos en carta</span>
                    <span class="activity-time">—</span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot <?= $total_bebidas > 0 ? 'rose' : 'rose' ?>"></span>
                    <span class="activity-text"><?= $total_bebidas ?> bebidas disponibles</span>
                    <span class="activity-time">—</span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot green"></span>
                    <span class="activity-text">Sesión iniciada como <strong style="color:#e2e8f0"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Admin') ?></strong></span>
                    <span class="activity-time">Hoy</span>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ══════════════════════════════
   CANVAS — fondo animado de partículas + líneas
══════════════════════════════ */
(function() {
    var canvas = document.getElementById("hero-canvas");
    var ctx    = canvas.getContext("2d");
    var W, H, nodes = [], RAF;

    var GREEN  = "110,231,183";
    var BLUE   = "99,102,241";
    var COLS   = [GREEN, GREEN, GREEN, BLUE];

    function resize() {
        W = canvas.width  = canvas.offsetWidth;
        H = canvas.height = canvas.offsetHeight;
    }

    function Node() {
        this.x  = Math.random() * W;
        this.y  = Math.random() * H;
        this.vx = (Math.random() - .5) * .45;
        this.vy = (Math.random() - .5) * .45;
        this.r  = Math.random() * 1.8 + .6;
        this.c  = COLS[Math.floor(Math.random() * COLS.length)];
        this.o  = Math.random() * .5 + .2;
    }

    Node.prototype.tick = function() {
        this.x += this.vx;
        this.y += this.vy;
        if (this.x < 0 || this.x > W) this.vx *= -1;
        if (this.y < 0 || this.y > H) this.vy *= -1;
    };

    function init() {
        nodes = [];
        var count = Math.floor((W * H) / 9000);
        count = Math.max(30, Math.min(count, 90));
        for (var i = 0; i < count; i++) nodes.push(new Node());
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);

        /* Líneas entre nodos cercanos */
        for (var i = 0; i < nodes.length; i++) {
            for (var j = i + 1; j < nodes.length; j++) {
                var dx   = nodes[i].x - nodes[j].x;
                var dy   = nodes[i].y - nodes[j].y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 140) {
                    var alpha = (1 - dist / 140) * .22;
                    ctx.beginPath();
                    ctx.strokeStyle = "rgba(" + nodes[i].c + "," + alpha + ")";
                    ctx.lineWidth   = .7;
                    ctx.moveTo(nodes[i].x, nodes[i].y);
                    ctx.lineTo(nodes[j].x, nodes[j].y);
                    ctx.stroke();
                }
            }
        }

        /* Nodos */
        for (var k = 0; k < nodes.length; k++) {
            var n = nodes[k];
            n.tick();
            ctx.beginPath();
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fillStyle = "rgba(" + n.c + "," + n.o + ")";
            ctx.fill();
        }

        RAF = requestAnimationFrame(draw);
    }

    resize();
    init();
    draw();

    window.addEventListener("resize", function() {
        cancelAnimationFrame(RAF);
        resize();
        init();
        draw();
    });
})();

</script>

</body>
</html>