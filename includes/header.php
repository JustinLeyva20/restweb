<?php
require "../config/conexion.php";
$config = $conexion->query("SELECT nombre FROM config LIMIT 1")->fetch();
?>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

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

    /* Glassmorphism oscuro */
    background: rgba(0, 0, 0, 0.82);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(255, 255, 255, .055);
    box-shadow: 0 4px 24px rgba(0, 0, 0, .28);

    /* Transición de ocultar/mostrar */
    transform: translateY(0);
    transition: transform .35s cubic-bezier(.4, 0, .2, 1),
                opacity    .35s ease,
                box-shadow .25s ease;
    will-change: transform;
}

/* Estado oculto (añadido por JS al hacer scroll) */
.navbar-custom.hidden {
    transform: translateY(-100%);
    opacity: 0;
    pointer-events: none;
}

/* Línea verde sutil en el borde inferior al hacer hover */
.navbar-custom::after {
    content: "";
    position: absolute;
    bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 0; height: 2px;
    background: linear-gradient(90deg, transparent, #6ee7b7, transparent);
    border-radius: 999px;
    transition: width .4s ease;
}
.navbar-custom:hover::after { width: 60%; }

/* ── TÍTULO ── */
.titulo-navbar {
    font-family: 'Sora', sans-serif;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: .04em;
    color: #f1f5f9;
    text-align: center;
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    user-select: none;
}

@keyframes headerPulse {
    0%, 100% { opacity: 1;  box-shadow: 0 0 8px  rgba(110,231,183,.6); }
    50%       { opacity: .6; box-shadow: 0 0 16px rgba(110,231,183,.9); }
}

/* Empujar contenido para que no quede debajo del header */
body { padding-top: 64px; }

/* ── RESPONSIVE ── */
@media (max-width: 991px) {
    .titulo-navbar { font-size: 16px; }
}

@media (max-width: 576px) {
    .navbar-custom { height: 56px; padding: 0 14px; }
    .titulo-navbar { font-size: 15px; }
    body { padding-top: 56px; }
}

</style>

<nav class="navbar-custom" id="mainHeader">
    <span class="titulo-navbar">
        <?= htmlspecialchars($config['nombre'] ?? 'Panel') ?>
    </span>
</nav>

<script>
(function () {
    var header     = document.getElementById("mainHeader");
    var lastScroll = 0;
    var threshold  = 60;   /* px mínimos antes de ocultar */
    var ticking    = false;

    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(function () {
                var current = window.scrollY || window.pageYOffset;

                if (current > threshold && current > lastScroll) {
                    /* Scrolleando hacia abajo → ocultar */
                    header.classList.add("hidden");
                } else {
                    /* Scrolleando hacia arriba o en el top → mostrar */
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
</script>