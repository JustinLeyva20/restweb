<?php
$rol     = $_SESSION['rol']     ?? '';
$usuario = $_SESSION['usuario'] ?? 'Usuario';

// Iniciales del usuario para el avatar
$partes   = explode(' ', trim($usuario));
$iniciales = strtoupper(
    (isset($partes[0]) ? mb_substr($partes[0], 0, 1) : '') .
    (isset($partes[1]) ? mb_substr($partes[1], 0, 1) : '')
);
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── TOGGLE ── */
#toggleTNBtn {
    position: fixed;
    top: 14px;
    left: 14px;
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 10px;
    background: rgba(99,102,241,.15);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #6366f1;
    font-size: 18px;
    cursor: pointer;
    z-index: 1100;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .2s, transform .15s;
    border: 1px solid rgba(99,102,241,.25);
}
#toggleTNBtn:hover {
    background: rgba(99,102,241,.25);
    transform: scale(1.05);
}
#toggleTNBtn .bar {
    display: flex;
    flex-direction: column;
    gap: 4px;
    pointer-events: none;
}
#toggleTNBtn .bar span {
    display: block;
    width: 16px;
    height: 2px;
    background: #6366f1;
    border-radius: 2px;
    transition: transform .25s, opacity .25s;
}

/* ── SIDEBAR ── */
#sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 252px;
    height: 100vh;
    background: linear-gradient(160deg, #0f1117 0%, #1a1d2e 60%, #111827 100%);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: left .3s cubic-bezier(.4,0,.2,1);
    z-index: 1050;
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
}

/* Glow decorativo */
#sidebar::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -70px;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.18) 0%, transparent 70%);
    pointer-events: none;
}
#sidebar::after {
    content: '';
    position: absolute;
    bottom: -60px;
    left: -40px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16,185,129,.1) 0%, transparent 70%);
    pointer-events: none;
}

#sidebar.hidden { left: -252px; }

/* ── HEADER ── */
.sb-header {
    padding: 24px 18px 18px;
    flex-shrink: 0;
    position: relative;
}

.sb-logo-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-left: 48px; /* espacio para el toggle btn */
}

.sb-logo-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(99,102,241,.4);
}

.sb-logo-text {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -.2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── TARJETA USUARIO ── */
.sb-user-card {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 12px;
    padding: 11px 13px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sb-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #a78bfa);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
    letter-spacing: .5px;
}

.sb-user-name {
    font-size: 13px;
    font-weight: 600;
    color: #f1f5f9;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sb-user-role {
    font-size: 11px;
    color: rgba(255,255,255,.38);
    margin-top: 2px;
}

/* ── MENÚ ── */
.sb-menu {
    flex: 1;
    padding: 4px 10px;
    overflow-y: auto;
    scrollbar-width: none;
}
.sb-menu::-webkit-scrollbar { width: 0; }

.sb-section-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.1px;
    color: rgba(255,255,255,.28);
    text-transform: uppercase;
    padding: 10px 10px 5px;
}

/* ── ITEMS ── */
.sb-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 11px;
    border-radius: 10px;
    margin-bottom: 1px;
    text-decoration: none !important;
    cursor: pointer;
    position: relative;
    transition: background .15s ease;
    border: 1px solid transparent;
}

.sb-item:hover {
    background: rgba(255,255,255,.07);
    text-decoration: none !important;
}

.sb-item.active {
    background: rgba(99,102,241,.18);
    border-color: rgba(99,102,241,.28);
}

/* Línea activa */
.sb-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 18px;
    background: #6366f1;
    border-radius: 0 3px 3px 0;
}

.sb-item-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    transition: transform .15s;
}
.sb-item:hover .sb-item-icon { transform: scale(1.08); }

.sb-item-label {
    font-size: 13.5px;
    font-weight: 500;
    color: rgba(255,255,255,.68);
    transition: color .15s;
}
.sb-item:hover .sb-item-label,
.sb-item.active .sb-item-label {
    color: #fff;
    font-weight: 600;
}

/* ── DIVIDER ── */
.sb-divider {
    height: 1px;
    background: rgba(255,255,255,.07);
    margin: 8px 10px;
}

/* ── ICONOS SVG ── */
.sb-item-icon svg,
.sb-salir-icon svg,
.sb-logo-icon svg {
    width: 16px;
    height: 16px;
    stroke-width: 1.75;
}
.sb-item-icon svg { color: rgba(255,255,255,.55); transition: color .15s; }
.sb-item:hover .sb-item-icon svg,
.sb-item.active .sb-item-icon svg { color: #fff; }


.i-panel   { background: rgba(99,102,241,.15); }
.i-platos  { background: rgba(16,185,129,.15); }
.i-bebidas { background: rgba(14,165,233,.15); }
.i-salas   { background: rgba(245,158,11,.15); }
.i-mesas   { background: rgba(6,182,212,.15);  }
.i-pedidos { background: rgba(239,68,68,.15);  }
.i-config  { background: rgba(148,163,184,.12);}
.i-users   { background: rgba(167,139,250,.18);}

/* ── FOOTER / SALIR ── */
.sb-footer {
    padding: 10px 10px 20px;
    flex-shrink: 0;
    position: relative;
}

.btn-salir {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 11px;
    border-radius: 10px;
    text-decoration: none !important;
    transition: background .15s;
    cursor: pointer;
    border: 1px solid transparent;
}

.btn-salir:hover {
    background: rgba(239,68,68,.12);
    border-color: rgba(239,68,68,.2);
    text-decoration: none !important;
}

.sb-salir-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(239,68,68,.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}

.sb-salir-label {
    font-size: 13.5px;
    font-weight: 500;
    color: rgba(239,68,68,.8);
}
.btn-salir:hover .sb-salir-label { color: #fca5a5; }

/* ── OVERLAY MÓVIL ── */
#sb-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 1040;
    backdrop-filter: blur(2px);
}
#sb-overlay.visible { display: block; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    #sidebar { width: 236px; }
    #sidebar.hidden { left: -236px; }
}

@media (max-width: 480px) {
    #sidebar { width: 220px; }
    #sidebar.hidden { left: -220px; }
    .sb-logo-row { padding-left: 44px; }
}
</style>

<!-- Overlay para móvil -->
<div id="sb-overlay" onclick="closeSidebar()"></div>

<!-- Botón toggle -->
<button id="toggleTNBtn" onclick="toggleSidebar()" aria-label="Abrir menú">
    <div class="bar">
        <span></span><span></span><span></span>
    </div>
</button>

<!-- Sidebar -->
<div id="sidebar">

    <!-- Header -->
    <div class="sb-header">
        <div class="sb-logo-row">
            <div class="sb-logo-icon">
                <i data-lucide="utensils-crossed" style="width:18px;height:18px;stroke:#fff;stroke-width:2;"></i>
            </div>
            <span class="sb-logo-text">
                <?= ($rol === 'Administrador') ? 'Administrador' : 'Panel' ?>
            </span>
        </div>

        <div class="sb-user-card">
            <div class="sb-avatar"><?= htmlspecialchars($iniciales ?: '??') ?></div>
            <div>
                <div class="sb-user-name"><?= htmlspecialchars($usuario) ?></div>
                <div class="sb-user-role"><?= htmlspecialchars($rol ?: 'Usuario') ?></div>
            </div>
        </div>
    </div>

    <!-- Menú -->
    <div class="sb-menu">

        <div class="sb-section-label">Principal</div>

        <a href="dashboard.php" class="sb-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-panel"><i data-lucide="layout-dashboard"></i></div>
            <span class="sb-item-label">Panel</span>
        </a>

        <a href="platos.php" class="sb-item <?= basename($_SERVER['PHP_SELF']) === 'platos.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-platos"><i data-lucide="chef-hat"></i></div>
            <span class="sb-item-label">Platos</span>
        </a>

        <a href="bebidas.php" class="sb-item <?= basename($_SERVER['PHP_SELF']) === 'bebidas.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-bebidas"><i data-lucide="cup-soda"></i></div>
            <span class="sb-item-label">Bebidas</span>
        </a>

        <a href="salas.php" class="sb-item <?= basename($_SERVER['PHP_SELF']) === 'salas.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-salas"><i data-lucide="warehouse"></i></div>
            <span class="sb-item-label">Salas</span>
        </a>

        <a href="ver_pedidos_web.php" class="sb-item <?= basename($_SERVER['PHP_SELF']) === 'ver_pedidos_web.php' ? 'active' : '' ?>">
    <div class="sb-item-icon i-pedidos-web">
        <i data-lucide="globe"></i>
    </div>
    <span class="sb-item-label">Pedidos Web</span>
</a>
        <a href="reservas.php" class="sb-item <?= basename($_SERVER['PHP_SELF']) === 'reservas.php' ? 'active' : '' ?>">
    <div class="sb-item-icon i-reservas">
        <i data-lucide="calendar-check"></i>
    </div>
    <span class="sb-item-label">Reservas Web</span>
</a>
        <div class="sb-divider"></div>
        <div class="sb-section-label">Sistema</div>

        <a href="config.php" class="sb-item <?= basename($_SERVER['PHP_SELF']) === 'config.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-config"><i data-lucide="sliders-horizontal"></i></div>
            <span class="sb-item-label">Configuración</span>
        </a>

        <?php if ($rol === 'Administrador'): ?>
        <a href="usuarios.php" class="sb-item <?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-users"><i data-lucide="users-round"></i></div>
            <span class="sb-item-label">Usuarios</span>
        </a>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <div class="sb-footer">
        <div class="sb-divider" style="margin: 0 0 8px;"></div>
        <a href="../controllers/logout.php" class="btn-salir">
            <div class="sb-salir-icon"><i data-lucide="log-out" style="width:16px;height:16px;stroke:rgba(239,68,68,.8);stroke-width:1.75;"></i></div>
            <span class="sb-salir-label">Salir</span>
        </a>
    </div>

</div>

<script>
(function () {
    var sidebar  = document.getElementById('sidebar');
    var overlay  = document.getElementById('sb-overlay');
    var isMobile = window.innerWidth <= 768;

    if (isMobile) {
        sidebar.classList.add('hidden');
    }

    window.toggleSidebar = function () {
        var isHidden = sidebar.classList.toggle('hidden');
        if (isMobile) {
            overlay.classList.toggle('visible', !isHidden);
        }
    };

    window.closeSidebar = function () {
        sidebar.classList.add('hidden');
        overlay.classList.remove('visible');
    };

    // Marcar ítem activo dinámicamente
    var current = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sb-item').forEach(function (el) {
        var href = el.getAttribute('href');
        if (href && href === current) {
            el.classList.add('active');
        }
    });
    // Inicializar iconos Lucide
    if (typeof lucide !== 'undefined') lucide.createIcons();
})();
</script>
