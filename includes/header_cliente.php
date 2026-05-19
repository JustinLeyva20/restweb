<?php
require "../config/conexion.php";
$config = $conexion->query("SELECT nombre FROM config LIMIT 1")->fetch();
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Sora:wght@400;600&display=swap');

.navbar-custom {
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    z-index: 1000;
    min-height: 68px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 24px;

    /* Gradiente que contrasta con el fondo oscuro #0f1117 */
    background: linear-gradient(135deg, #1a0a00 0%, #2d1200 50%, #1a0800 100%);
    border-bottom: 1px solid rgba(200,150,46,.25);
    box-shadow: 0 4px 24px rgba(0,0,0,.5);

    /* Transición para el hide/show al scroll */
    transition: transform .35s cubic-bezier(.4,0,.2,1),
                opacity  .35s ease,
                box-shadow .3s ease;
}

/* Estado oculto al hacer scroll */
.navbar-custom.hidden {
    transform: translateY(-100%);
    opacity: 0;
    pointer-events: none;
}

/* Línea decorativa dorada inferior */
.navbar-custom::after {
    content: '';
    position: absolute;
    bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 120px; height: 2px;
    background: linear-gradient(to right, transparent, #C8962E, transparent);
    border-radius: 2px;
}

/* Brillo decorativo izquierdo */
.navbar-custom::before {
    content: '';
    position: absolute;
    top: -40px; left: -40px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(200,150,46,.12), transparent 65%);
    pointer-events: none;
}

/* TÍTULO */
.titulo-navbar {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
}

.titulo-navbar .nav-icon {
    width: 34px; height: 34px;
    border-radius: 10px;
    background: linear-gradient(135deg, #C8962E, #E4B84A);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    box-shadow: 0 4px 14px rgba(200,150,46,.4);
    flex-shrink: 0;
}

.titulo-navbar .nav-text {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px;
    font-weight: 600;
    color: #F5EFE0;
    letter-spacing: .06em;
}

.titulo-navbar .nav-text span {
    color: #E4B84A;
}

/* PUNTO DE ESTADO (online) */
.nav-status {
    position: absolute;
    right: 24px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: 'Sora', sans-serif;
    font-size: 11px;
    color: rgba(245,239,224,.45);
    letter-spacing: .06em;
}

.nav-status-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #6ee7b7;
    box-shadow: 0 0 6px rgba(110,231,183,.6);
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .6; transform: scale(1.3); }
}
</style>

<nav class="navbar-custom" id="mainNavbar">

    <div class="titulo-navbar">
        <span class="nav-text">
            <?php
                $nombre = $config['nombre'] ?? 'Restaurante';
                $partes = explode(' ', $nombre, 2);
                echo htmlspecialchars($partes[0]);
                if (isset($partes[1])) {
                    echo ' <span>' . htmlspecialchars($partes[1]) . '</span>';
                }
            ?>
        </span>
    </div>
</nav>

<script>
(function () {
    var navbar    = document.getElementById('mainNavbar');
    var lastY     = window.scrollY;
    var threshold = 60; // px que debe bajar para ocultarse

    window.addEventListener('scroll', function () {
        var currentY = window.scrollY;

        if (currentY > lastY && currentY > threshold) {
            // Scrolleando hacia abajo → ocultar
            navbar.classList.add('hidden');
        } else {
            // Scrolleando hacia arriba → mostrar
            navbar.classList.remove('hidden');
        }

        lastY = currentY;
    }, { passive: true });
})();
</script>