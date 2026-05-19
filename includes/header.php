<?php
require __DIR__ . "/../config/conexion.php";
$config = $conexion->query("SELECT nombre FROM config LIMIT 1")->fetch();

$rol = $_SESSION['rol'] ?? '';

// ── Estado de la tienda (solo admin) ──
$tienda_abierta = true;
$tienda_manual = null;
if ($rol === 'Administrador') {
    $cfg_path = __DIR__ . '/../config/tienda.json';
    if (file_exists($cfg_path)) {
        $cfg = json_decode(file_get_contents($cfg_path), true);
        $tienda_manual = $cfg['manual_override'] ?? null;
    }
    try {
        $stmt = $conexion->query("SELECT horario_apertura, horario_cierre FROM config LIMIT 1");
        $hconf = $stmt->fetch(PDO::FETCH_ASSOC);
        $h_aper = (int)explode(':', $hconf['horario_apertura'] ?? '08:00')[0];
        $h_cier = (int)explode(':', $hconf['horario_cierre'] ?? '20:00')[0];
    } catch (Exception $e) { $h_aper = 8; $h_cier = 20; }
    $hora_peru = (int)date('G', time() - 5 * 3600);
    $auto = $hora_peru >= $h_aper && $hora_peru < $h_cier;
    if ($tienda_manual === true)      $tienda_abierta = true;
    elseif ($tienda_manual === false) $tienda_abierta = false;
    else                               $tienda_abierta = $auto;
}
?>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<style>

/* ── HEADER ── */
.navbar-custom {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 500;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 24px;

    background: rgba(0, 0, 0, 0.82);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(255, 255, 255, .055);
    box-shadow: 0 4px 24px rgba(0, 0, 0, .28);

    transform: translateY(0);
    transition: transform .35s cubic-bezier(.4, 0, .2, 1),
                opacity    .35s ease,
                box-shadow .25s ease;
    will-change: transform;
}

.navbar-custom.hidden {
    transform: translateY(-100%);
    opacity: 0;
    pointer-events: none;
}

/* Línea inferior sutil — color según estado */
.navbar-custom::after {
    content: "";
    position: absolute;
    bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 0; height: 2px;
    border-radius: 999px;
    transition: width .4s ease, background .3s;
}
.navbar-custom.store-open::after {
    background: linear-gradient(90deg, transparent, #6ee7b7, transparent);
}
.navbar-custom.store-closed::after {
    background: linear-gradient(90deg, transparent, #f87171, transparent);
}
.navbar-custom:hover::after { width: 60%; }

/* ── TÍTULO ── */
.titulo-navbar {
    font-family: 'Sora', sans-serif;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: .04em;
    color: #f1f5f9;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    user-select: none;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

@keyframes headerPulse {
    0%, 100% { opacity: 1;  box-shadow: 0 0 8px  rgba(110,231,183,.6); }
    50%       { opacity: .6; box-shadow: 0 0 16px rgba(110,231,183,.9); }
}

/* ── STORE TOGGLE (solo admin) ── */
.store-toggle-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 14px 6px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.04);
    transition: background .3s, border-color .3s, box-shadow .3s;
    position: absolute;
    right: 24px;
}
.store-toggle-wrap.open {
    background: rgba(110,231,183,.08);
    border-color: rgba(110,231,183,.25);
    box-shadow: 0 0 20px rgba(110,231,183,.15);
}
.store-toggle-wrap.closed {
    background: rgba(248,113,113,.08);
    border-color: rgba(248,113,113,.25);
    box-shadow: 0 0 20px rgba(248,113,113,.15);
}

.store-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    transition: background .3s, box-shadow .3s;
}
.store-dot.open  { background: #6ee7b7; box-shadow: 0 0 8px rgba(110,231,183,.6); }
.store-dot.closed { background: #f87171; box-shadow: 0 0 8px rgba(248,113,113,.6); }

.store-label {
    font-size: 13px;
    font-weight: 600;
    color: #e2e8f0;
    transition: color .3s;
}

.store-toggle-btn {
    width: 30px; height: 30px;
    border: none; border-radius: 8px;
    background: rgba(255,255,255,.06);
    cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    color: #94a3b8;
    transition: background .2s, color .2s, transform .15s;
    flex-shrink: 0;
}
.store-toggle-btn:hover {
    background: rgba(255,255,255,.12);
    transform: scale(1.1);
}
.store-toggle-btn.open  { color: #6ee7b7; background: rgba(110,231,183,.12); }
.store-toggle-btn.closed { color: #f87171; background: rgba(248,113,113,.12); }

/* Empujar contenido */
body { padding-top: 64px; }

/* ── RESPONSIVE ── */
@media (max-width: 991px) {
    .titulo-navbar { font-size: 16px; }
    .store-toggle-wrap { padding: 5px 10px 5px 8px; gap: 6px; }
    .store-label { font-size: 11px; }
    .store-toggle-btn { width: 26px; height: 26px; }
    .store-toggle-btn svg { width: 13px; height: 13px; }
}

@media (max-width: 576px) {
    .navbar-custom { height: 56px; padding: 0 12px; }
    .titulo-navbar { font-size: 14px; }
    .store-toggle-wrap { padding: 4px 8px 4px 6px; gap: 4px; right: 12px; }
    .store-label { display: none; }
    .store-dot { width: 7px; height: 7px; }
    .store-toggle-btn { width: 24px; height: 24px; }
    .store-toggle-btn svg { width: 12px; height: 12px; }
    body { padding-top: 56px; }
}

</style>

<nav class="navbar-custom <?= $rol === 'Administrador' ? ($tienda_abierta ? 'store-open' : 'store-closed') : '' ?>" id="mainHeader">
    <span class="titulo-navbar">
        <?= htmlspecialchars($config['nombre'] ?? 'Panel') ?>
    </span>

    <?php if ($rol === 'Administrador'): ?>
    <div class="store-toggle-wrap <?= $tienda_abierta ? 'open' : 'closed' ?>" id="storeToggleWrap">
        <span class="store-dot <?= $tienda_abierta ? 'open' : 'closed' ?>" id="headerStoreDot"></span>
        <span class="store-label" id="headerStoreLabel">
            <?= $tienda_abierta ? 'Abierta' : 'Cerrada' ?>
        </span>
        <button class="store-toggle-btn <?= $tienda_abierta ? 'open' : 'closed' ?>" id="headerToggleBtn"
                data-current="<?= $tienda_abierta ? '1' : '0' ?>"
                onclick="toggleTienda(this)"
                title="<?= $tienda_abierta ? 'Cerrar tienda' : 'Abrir tienda' ?>">
            <i data-lucide="power" style="width:14px;height:14px;"></i>
        </button>
    </div>
    <?php endif; ?>
</nav>

<script>
(function () {
    var header     = document.getElementById("mainHeader");
    var lastScroll = 0;
    var threshold  = 60;
    var ticking    = false;

    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(function () {
                var current = window.scrollY || window.pageYOffset;
                if (current > threshold && current > lastScroll) {
                    header.classList.add("hidden");
                } else {
                    header.classList.remove("hidden");
                }
                lastScroll = current <= 0 ? 0 : current;
                ticking    = false;
            });
            ticking = true;
        }
    }

    window.addEventListener("scroll", onScroll, { passive: true });
})();

/* ── Toggle tienda ── */
function toggleTienda(btn) {
    var current = btn.dataset.current;
    var nuevo = current === '1' ? '0' : '1';
    var fd = new FormData();
    fd.append('accion', 'toggle');
    fd.append('valor', nuevo);

    fetch('../controllers/tiendaController.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            btn.dataset.current = data.abierta ? '1' : '0';
            var open = data.abierta;
            document.getElementById('headerStoreLabel').textContent = open ? 'Abierta' : 'Cerrada';
            var dot = document.getElementById('headerStoreDot');
            dot.className = 'store-dot ' + (open ? 'open' : 'closed');
            btn.className = 'store-toggle-btn ' + (open ? 'open' : 'closed');
            btn.title = open ? 'Cerrar tienda' : 'Abrir tienda';
            var wrap = document.getElementById('storeToggleWrap');
            wrap.className = 'store-toggle-wrap ' + (open ? 'open' : 'closed');
            var nav = document.getElementById('mainHeader');
            nav.className = nav.className.replace(/store-open|store-closed/g, '').trim() + ' ' + (open ? 'store-open' : 'store-closed');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    })
    .catch(function() {});
}
</script>
