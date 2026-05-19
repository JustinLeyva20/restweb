<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
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
    --panel:   #171922;
    --border:  rgba(255,255,255,.06);
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
    background: #0f1117;
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

/* Gradient overlay sobre el canvas */
.hero-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        120deg,
        rgba(13,15,24,.92) 0%,
        rgba(13,15,24,.72) 55%,
        rgba(13,15,24,.30) 100%
    );
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
    background: #1e2130;
    border: 1px solid rgba(255,255,255,.04);
    text-decoration: none;
    transition: border-color .2s, background .2s;
    color: var(--text);
}
.quick-link:hover {
    border-color: rgba(110,231,183,.2);
    background: #1a2030;
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
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 992px) {
    .hero-deco { display: none; }
    .hero-title { font-size: 30px; }
}
@media (max-width: 768px) {
    .content-area { margin-left: 0; padding: 14px; }
    .hero-section { padding: 32px 24px 28px; }
    .hero-title { font-size: 26px; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .bottom-grid { grid-template-columns: 1fr; }
    .quick-links { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
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
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                Panel Administrativo
            </div>
            <h1 class="hero-title">
                Bienvenido al<br><span>Sistema</span>
            </h1>
            <p class="hero-desc">
                Gestiona reservas, pedidos, usuarios y operaciones desde un solo lugar con visibilidad total.
            </p>
        </div>

        <!-- Decoración anillos derecha -->
        <div class="hero-deco">
            <div class="deco-ring">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
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
            <div class="stat-value" id="stat-reservas">—</div>
            <div class="stat-label">Reservas totales</div>
        </div>

        <div class="stat-card" data-color="blue">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div class="stat-value" id="stat-salas">—</div>
            <div class="stat-label">Salas activas</div>
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
            <div class="stat-value" id="stat-platos">—</div>
            <div class="stat-label">Platos en carta</div>
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
            <div class="stat-value" id="stat-bebidas">—</div>
            <div class="stat-label">Bebidas disponibles</div>
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

        <!-- Actividad reciente -->
        <div class="info-card">
            <div class="card-header-row">
                <span class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Estado del sistema
                </span>
                <span class="card-badge">En línea</span>
            </div>
            <div class="activity-list">
                <div class="activity-item">
                    <span class="activity-dot green"></span>
                    <span class="activity-text">Módulo de reservas activo</span>
                    <span class="activity-time">Ahora</span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot indigo"></span>
                    <span class="activity-text">Módulo de salas activo</span>
                    <span class="activity-time">Ahora</span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot amber"></span>
                    <span class="activity-text">Módulo de platos activo</span>
                    <span class="activity-time">Ahora</span>
                </div>
                <div class="activity-item">
                    <span class="activity-dot rose"></span>
                    <span class="activity-text">Módulo de bebidas activo</span>
                    <span class="activity-time">Ahora</span>
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

/* ══════════════════════════════
   CONTADORES — fetch a PHP endpoints
   (Si no existen, muestra 0)
══════════════════════════════ */
function countUp(el, target, duration) {
    var start  = 0;
    var step   = target / (duration / 16);
    var timer  = setInterval(function() {
        start += step;
        if (start >= target) { start = target; clearInterval(timer); }
        el.textContent = Math.floor(start);
    }, 16);
}

function loadStats() {
    var endpoints = [
        { id: "stat-reservas", url: "../controllers/statsController.php?tabla=reservas" },
        { id: "stat-salas",    url: "../controllers/statsController.php?tabla=salas"    },
        { id: "stat-platos",   url: "../controllers/statsController.php?tabla=platos"   },
        { id: "stat-bebidas",  url: "../controllers/statsController.php?tabla=bebidas"  }
    ];

    endpoints.forEach(function(ep) {
        fetch(ep.url)
        .then(function(r) { return r.text(); })
        .then(function(text) {
            var n   = parseInt(text.trim(), 10);
            var el  = document.getElementById(ep.id);
            if (!isNaN(n)) {
                countUp(el, n, 900);
            } else {
                el.textContent = "0";
            }
        })
        .catch(function() {
            document.getElementById(ep.id).textContent = "0";
        });
    });
}

loadStats();
</script>

</body>
</html>