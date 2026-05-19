<?php
// sidebar_cliente.php — Overlay mode · Paleta La Delicia
$rol     = $_SESSION['rol']     ?? '';
$usuario = $_SESSION['usuario'] ?? 'Usuario';

$partes    = explode(' ', trim($usuario));
$iniciales = strtoupper(
    (isset($partes[0]) ? mb_substr($partes[0], 0, 1) : '') .
    (isset($partes[1]) ? mb_substr($partes[1], 0, 1) : '')
);

// Detectar página activa
$paginaActual = basename($_SERVER['PHP_SELF']);
?>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<style>
/* ══════════════════════════════════════════
   SIDEBAR OVERLAY — La Delicia
   ══════════════════════════════════════════ */

/* Botón hamburguesa */
#sb-toggle {
    position: fixed;
    top: 14px;
    left: 16px;
    z-index: 1200;
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 10px;
    background: rgba(200,150,46,.18);
    color: #C8962E;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .2s, transform .2s;
}
#sb-toggle:hover {
    background: rgba(200,150,46,.32);
    transform: scale(1.06);
}
#sb-toggle .icon-open  { display: flex; }
#sb-toggle .icon-close { display: none;  }
#sb-toggle.is-open .icon-open  { display: none;  }
#sb-toggle.is-open .icon-close { display: flex; }

/* Overlay oscuro */
#sb-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(59,39,16,.55);
    backdrop-filter: blur(2px);
    z-index: 1090;
}
#sb-overlay.visible { display: block; }

/* SIDEBAR — fuera de pantalla por defecto */
#sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 272px;
    height: 100vh;
    z-index: 1100;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    transition: left .32s cubic-bezier(.4,0,.2,1);
    background: linear-gradient(160deg, #2e1a08 0%, #3B2710 45%, #1f0f04 100%);
    font-family: 'Cormorant Garamond', 'DM Sans', serif;
}
#sidebar.is-open { left: 0; }

/* Brillos decorativos */
#sidebar::before {
    content: '';
    position: absolute;
    top: -80px; right: -60px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(200,150,46,.22), transparent 70%);
    pointer-events: none;
}
#sidebar::after {
    content: '';
    position: absolute;
    bottom: -50px; left: -30px;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(139,26,26,.2), transparent 70%);
    pointer-events: none;
}

/* ── HEADER ── */
.sb-header {
    padding: 22px 18px 16px;
    border-bottom: 1px solid rgba(200,150,46,.18);
    position: relative;
}
.sb-logo-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    padding-left: 44px;
}
.sb-logo-icon {
    width: 34px; height: 34px;
    border-radius: 10px;
    background: linear-gradient(135deg, #C8962E, #E4B84A);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    box-shadow: 0 4px 14px rgba(200,150,46,.4);
}
.sb-logo-text {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem;
    font-weight: 600;
    color: #F5EFE0;
    letter-spacing: .04em;
}
.sb-logo-text span { color: #E4B84A; }

/* Tarjeta usuario */
.sb-user-card {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(200,150,46,.15);
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.sb-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #C8962E, #E4B84A);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: .85rem;
    font-weight: 700;
    flex-shrink: 0;
}
.sb-user-name {
    color: #F5EFE0;
    font-family: 'DM Sans', sans-serif;
    font-size: .85rem;
    font-weight: 500;
}
.sb-user-role {
    color: rgba(245,239,224,.4);
    font-family: 'DM Sans', sans-serif;
    font-size: .72rem;
}

/* ── MENÚ ── */
.sb-menu {
    flex: 1;
    padding: 14px 10px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.sb-section-label {
    font-family: 'DM Sans', sans-serif;
    font-size: .65rem;
    letter-spacing: .15em;
    text-transform: uppercase;
    color: rgba(245,239,224,.3);
    padding: 10px 10px 4px;
}

/* ÍTEM */
.sb-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 10px;
    color: rgba(245,239,224,.65);
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    font-weight: 400;
    transition: background .2s, color .2s, transform .15s;
    position: relative;
}
.sb-item:hover {
    background: rgba(200,150,46,.12);
    color: #F5EFE0;
    transform: translateX(3px);
}
.sb-item.active {
    background: rgba(200,150,46,.22);
    color: #E4B84A;
    font-weight: 600;
}
.sb-item.active::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; bottom: 20%;
    width: 3px;
    border-radius: 2px;
    background: #C8962E;
}

/* Ícono del ítem */
.sb-item-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.i-inicio   { background: rgba(200,150,46,.18); color: #E4B84A; }
.i-platos   { background: rgba(92,61,30,.4);    color: #d4a55a; }
.i-bebidas  { background: rgba(44,74,74,.35);   color: #7fc7c7; }
.i-mesas    { background: rgba(44,74,46,.35);   color: #7cb87e; }
.i-pedidos  { background: rgba(139,26,26,.3);   color: #d47070; }
.i-reservas { background: rgba(200,150,46,.15); color: #c8b46e; }
.i-empresa  { background: rgba(59,39,16,.5);    color: #a08060; }
.i-cuenta   { background: rgba(245,239,224,.08);color: #c8b49a; }
.i-postres  { background: rgba(236,72,153,.18); color: #f9a8d4; }
.i-reportes { background: rgba(244,63,94,.18);  color: #f87171; }

/* ── FOOTER ── */
.sb-footer {
    padding: 12px 10px 18px;
    border-top: 1px solid rgba(200,150,46,.12);
}
.btn-salir {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 10px;
    color: #d47070;
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    transition: background .2s;
}
.btn-salir:hover { background: rgba(139,26,26,.2); }

/* Scrollbar */
#sidebar::-webkit-scrollbar { width: 4px; }
#sidebar::-webkit-scrollbar-track { background: transparent; }
#sidebar::-webkit-scrollbar-thumb { background: rgba(200,150,46,.25); border-radius: 2px; }
</style>

<!-- Overlay -->
<div id="sb-overlay" onclick="sbClose()"></div>

<!-- Botón hamburguesa -->
<button id="sb-toggle" onclick="sbToggle()" aria-label="Abrir menú">
    <span class="icon-open"><i data-lucide="menu" style="width:18px;height:18px;"></i></span>
    <span class="icon-close"><i data-lucide="x" style="width:18px;height:18px;"></i></span>
</button>

<!-- SIDEBAR -->
<nav id="sidebar" aria-label="Menú de cliente">

    <div class="sb-header">
        <div class="sb-logo-row">
            <div class="sb-logo-icon">
                <i data-lucide="utensils-crossed" style="width:17px;height:17px;"></i>
            </div>
            <span class="sb-logo-text">La <span>Delicia</span></span>
        </div>

        <div class="sb-user-card">
            <div class="sb-avatar"><?= htmlspecialchars($iniciales) ?></div>
            <div>
                <div class="sb-user-name"><?= htmlspecialchars($usuario) ?></div>
                <div class="sb-user-role">Cliente</div>
            </div>
        </div>
    </div>

    <div class="sb-menu">
        <span class="sb-section-label">Navegación</span>

        <a href="inicio.php" class="sb-item <?= $paginaActual === 'inicio.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-inicio"><i data-lucide="home" style="width:15px;height:15px;"></i></div>
            Inicio
        </a>

        <a href="platos_usuario.php" class="sb-item <?= $paginaActual === 'platos_usuario.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-platos"><i data-lucide="chef-hat" style="width:15px;height:15px;"></i></div>
            Platos
        </a>

        <a href="bebidas_usuario.php" class="sb-item <?= $paginaActual === 'bebidas_usuario.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-bebidas"><i data-lucide="cup-soda" style="width:15px;height:15px;"></i></div>
            Bebidas
        </a>
        <a href="postres_cliente.php" class="sb-item <?= $paginaActual === 'postres_cliente.php' ? 'active' : '' ?>">
    <div class="sb-item-icon i-postres"><i data-lucide="ice-cream" style="width:15px;height:15px;"></i></div>
    Postres
</a>

        <a href="mesas_cliente.php" class="sb-item <?= $paginaActual === 'mesas_cliente.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-mesas"><i data-lucide="calendar-check" style="width:15px;height:15px;"></i></div>
            Reservas
        </a>

        <span class="sb-section-label">Mis cosas</span>

        <a href="mis_pedidos.php" class="sb-item <?= $paginaActual === 'mis_pedidos.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-pedidos"><i data-lucide="receipt" style="width:15px;height:15px;"></i></div>
            Mis Pedidos
        </a>

        <a href="mis_reservas.php" class="sb-item <?= $paginaActual === 'mis_reservas.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-reservas"><i data-lucide="calendar" style="width:15px;height:15px;"></i></div>
            Mis Reservas
        </a>

        <span class="sb-section-label">General</span>

        <a href="empresa.php" class="sb-item <?= $paginaActual === 'empresa.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-empresa"><i data-lucide="building" style="width:15px;height:15px;"></i></div>
            Empresa
        </a>

        <a href="cuenta.php" class="sb-item <?= $paginaActual === 'cuenta.php' ? 'active' : '' ?>">
            <div class="sb-item-icon i-cuenta"><i data-lucide="user" style="width:15px;height:15px;"></i></div>
            Mi Cuenta
        </a>
        <a href="reportes_cliente.php" class="sb-item <?= $paginaActual === 'reportes_cliente.php' ? 'active' : '' ?>">
    <div class="sb-item-icon i-reportes"><i data-lucide="alert-circle" style="width:15px;height:15px;"></i></div>
    Reportes
</a>
    </div>

    <div class="sb-footer">
        <a href="../controllers/logout.php" class="btn-salir">
            <i data-lucide="log-out" style="width:15px;height:15px;"></i>
            Cerrar sesión
        </a>
    </div>

</nav>

<script>
(function () {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sb-overlay');
    var toggle  = document.getElementById('sb-toggle');

    function sbOpen() {
        sidebar.classList.add('is-open');
        overlay.classList.add('visible');
        toggle.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function sbClose() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('visible');
        toggle.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function sbToggle() {
        sidebar.classList.contains('is-open') ? sbClose() : sbOpen();
    }

    // Exponer globalmente
    window.sbToggle = sbToggle;
    window.sbClose  = sbClose;

    // Cerrar con ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') sbClose();
    });

    // Inicializar Lucide
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) lucide.createIcons();
    });
    // Si el DOM ya cargó (include tardío)
    if (document.readyState !== 'loading' && window.lucide) lucide.createIcons();
})();
</script>
