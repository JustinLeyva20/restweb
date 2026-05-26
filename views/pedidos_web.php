<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); exit;
}
if ($_SESSION['rol'] === 'Administrador') {
    header("Location: dashboard.php"); exit;
}

$usuario       = $_SESSION['usuario'];
$resultado     = null;

// Obtener datos del usuario para autocompletar
$stmtUser = $conexion->prepare("SELECT telefono, direccion FROM usuarios WHERE correo = ? OR nombre = ?");
$stmtUser->execute([$usuario, $usuario]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
$userPhone   = $userData['telefono'] ?? '';
$userAddress = $userData['direccion'] ?? '';
$tipoResultado = null;
$pedidoId      = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartData'])) {

    $cartData      = json_decode($_POST['cartData'], true);
    $direccion     = htmlspecialchars(trim($_POST['direccion']       ?? ''));
    $fecha         = date('Y-m-d');
    $hora          = date('H:i:s');
    $metodoPago    = $_POST['metodo_pago'] ?? '';
    $nombreCliente = htmlspecialchars(trim($_POST['nombre_cliente']  ?? ''));
    $telefono      = htmlspecialchars(trim($_POST['telefono']        ?? ''));

    $metodosValidos = ['EFECTIVO','YAPE','PLIN','TARJETA'];

    if (
        empty($cartData) || empty($direccion) || empty($fecha) || empty($hora) ||
        !in_array($metodoPago, $metodosValidos) ||
        empty($nombreCliente) || empty($telefono)
    ) {
        $resultado     = "Completa todos los campos antes de confirmar.";
        $tipoResultado = 'error';
    } else {
        try {
            $conexion->beginTransaction();

            $total = 0;
            foreach ($cartData as $item) {
                $total += (float)$item['precio'] * (int)$item['qty'];
            }

            $stmt = $conexion->prepare("
                INSERT INTO pedidos_web
                    (usuario, nombre_cliente, telefono, direccion, fecha, hora, metodo_pago, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$usuario, $nombreCliente, $telefono, $direccion, $fecha, $hora, $metodoPago, $total]);
            $pedidoId = $conexion->lastInsertId();

            $stmtDetalle = $conexion->prepare("
                INSERT INTO detalle_pedidos_web (id_pedido, nombre, precio, cantidad)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($cartData as $item) {
                $stmtDetalle->execute([
                    $pedidoId,
                    $item['nombre'],
                    (float)$item['precio'],
                    (int)$item['qty']
                ]);
            }

            $conexion->commit();
            $resultado     = "¡Pedido #" . str_pad($pedidoId, 3, '0', STR_PAD_LEFT) . " realizado! Pronto comenzamos a prepararlo.";
            $tipoResultado = 'ok';

        } catch (Exception $e) {
            $conexion->rollBack();
            $resultado     = "Ocurrió un error al procesar tu pedido. Intenta de nuevo.";
            $tipoResultado = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>La Delicia — Delivery</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<style>
:root {
    --cream:    #F5EFE0;
    --warm:     #EDE0C4;
    --gold:     #C8962E;
    --gold-lt:  #E4B84A;
    --brown:    #3B2710;
    --brown-md: #5C3D1E;
    --green:    #2C4A2E;
    --red:      #8B1A1A;
    --shadow:   rgba(59,39,16,.15);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--brown);
    min-height: 100vh;
}

@keyframes fadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes popIn {
    0%   { opacity:0; transform:scale(.9); }
    70%  { transform:scale(1.03); }
    100% { opacity:1; transform:scale(1); }
}

/* TOP BAR */
.top-bar {
    position: fixed; top:0; left:0; right:0; z-index:900;
    height: 64px;
    display: flex; align-items: center;
    padding: 0 2rem 0 4.5rem;
    background: rgba(245,239,224,.92);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(200,150,46,.22);
}
.top-logo {
    font-family: 'Merriweather', serif;
    font-size: 1.5rem; font-weight: 600;
    color: var(--brown); text-decoration: none;
}
.top-logo span { color: var(--gold); }

main { padding-top: 64px; min-height: 100vh; }

/* HERO */
.page-hero {
    background: linear-gradient(135deg, #1f0f04, var(--brown) 50%, var(--brown-md));
    padding: 2.8rem 3rem 2.2rem;
    position: relative; overflow: hidden;
}
.page-hero::before {
    content:''; position:absolute; top:-90px; right:-70px;
    width:280px; height:280px; border-radius:50%;
    background: radial-gradient(circle, rgba(200,150,46,.28), transparent 65%);
    pointer-events:none;
}
.hero-inner { position:relative; z-index:1; }
.hero-inner h1 {
    font-family:'Merriweather',serif;
    font-size:clamp(2rem,4vw,3rem); font-weight:300;
    color:var(--cream); animation: fadeUp .6s .1s both;
}
.hero-inner h1 em { font-style:italic; color:var(--gold-lt); }
.hero-inner p {
    color:rgba(245,239,224,.6); font-size:.9rem;
    margin-top:.5rem; animation: fadeUp .6s .25s both;
}

/* CONTENT */
.content {
    max-width: 900px; margin: 0 auto;
    padding: 2.5rem 1.5rem 4rem;
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem; align-items: start;
}

/* ── RESULTADO ── */
.resultado-wrap { grid-column: 1 / -1; animation: popIn .5s both; }

.resultado-card {
    border-radius: 16px; padding: 2.5rem 2rem;
    display: flex; flex-direction: column;
    align-items: center; text-align: center; gap: 1rem;
}
.resultado-card.ok {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 2px solid rgba(34,197,94,.3);
}
.resultado-card.error {
    background: linear-gradient(135deg, #fff1f2, #ffe4e6);
    border: 2px solid rgba(220,38,38,.2);
}
.resultado-icon {
    width: 80px; height: 80px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.resultado-card.ok    .resultado-icon { background: rgba(34,197,94,.15); color: #166534; }
.resultado-card.error .resultado-icon { background: rgba(220,38,38,.1);  color: #991b1b; }
.resultado-icon svg { width: 38px; height: 38px; stroke-width: 1.6; }

.resultado-title {
    font-family: 'Merriweather', serif;
    font-size: 1.9rem; font-weight: 600;
}
.ok    .resultado-title { color: #166534; }
.error .resultado-title { color: #991b1b; }
.resultado-msg { font-size: .95rem; color: var(--brown-md); max-width: 480px; }

.resultado-btns {
    display: flex; gap: .8rem; flex-wrap: wrap;
    justify-content: center; margin-top: .5rem;
}
.btn-resultado {
    padding: .7rem 1.6rem; border-radius: 3rem; border: none;
    font-family: 'DM Sans', sans-serif; font-size: .88rem;
    font-weight: 600; cursor: pointer; text-decoration: none;
    transition: background .2s, transform .2s;
    display: inline-flex; align-items: center; gap: .4rem;
}
.btn-resultado svg { width: 15px; height: 15px; stroke-width: 2.2; }
.btn-volver { background: var(--brown); color: #fff; }
.btn-volver:hover { background: var(--brown-md); transform: translateY(-1px); }
.btn-mispedidos {
    background: var(--gold); color: #fff;
    box-shadow: 0 4px 16px rgba(200,150,46,.35);
}
.btn-mispedidos:hover { background: var(--brown); transform: translateY(-1px); }

/* ── FORMULARIO ── */
.form-card {
    background: #fff; border-radius: 16px; padding: 1.8rem;
    box-shadow: 0 2px 20px var(--shadow);
    animation: fadeUp .5s .1s both;
}
.form-card h2 {
    font-family: 'Merriweather', serif;
    font-size: 1.4rem; font-weight: 600; color: var(--brown);
    margin-bottom: 1.4rem; padding-bottom: .8rem;
    border-bottom: 1px solid var(--warm);
    display: flex; align-items: center; gap: .6rem;
}
.form-card h2 svg { width: 22px; height: 22px; stroke-width: 1.7; color: var(--gold); }

.form-group { display:flex; flex-direction:column; gap:.4rem; margin-bottom:1rem; }
.form-label {
    font-size:.75rem; font-weight:600;
    text-transform:uppercase; letter-spacing:.08em; color:var(--brown-md);
    display: flex; align-items: center; gap: .35rem;
}
.form-label svg { width: 13px; height: 13px; stroke-width: 2; }

.form-select, .form-input, .form-textarea {
    padding:.7rem .9rem;
    border:1.5px solid var(--warm); border-radius:9px;
    background:var(--cream); color:var(--brown);
    font-family:'DM Sans',sans-serif; font-size:.9rem;
    outline:none; transition:border-color .2s, box-shadow .2s;
    width: 100%;
}
.form-select:focus, .form-input:focus, .form-textarea:focus {
    border-color:var(--gold);
    box-shadow:0 0 0 3px rgba(200,150,46,.15);
}
.form-textarea { resize:vertical; min-height:70px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; }

/* Métodos de pago */
.metodos-label {
    font-size:.75rem; font-weight:600;
    text-transform:uppercase; letter-spacing:.08em; color:var(--brown-md);
    display: flex; align-items: center; gap: .35rem;
    margin-bottom: .6rem;
}
.metodos-label svg { width: 13px; height: 13px; stroke-width: 2; }

.metodos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .6rem;
}
.metodo-btn {
    border: 1.5px solid var(--warm);
    border-radius: 10px; padding: .75rem .4rem;
    background: var(--cream); cursor: pointer;
    text-align: center; transition: all .2s;
    display: flex; flex-direction: column;
    align-items: center; gap: .35rem;
}
.metodo-btn:hover { border-color: var(--gold); background: rgba(200,150,46,.06); }
.metodo-btn.selected {
    border-color: var(--gold);
    background: rgba(200,150,46,.12);
    box-shadow: 0 0 0 3px rgba(200,150,46,.15);
}
.metodo-btn input { display: none; }
.metodo-icon {
    width: 49px; height: 40px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(200,150,46,.1); color: var(--gold);
    transition: background .2s;
    overflow: hidden;
}
.metodo-btn.selected .metodo-icon { background: rgba(200,150,46,.2); }
.metodo-icon svg { width: 18px; height: 18px; stroke-width: 1.8; }
.metodo-img { width: 100%; height: 100%; object-fit: cover; }
.metodo-label { font-size: .72rem; font-weight: 600; color: var(--brown-md); }
.metodo-btn.selected .metodo-label { color: var(--gold); }

.btn-submit {
    width: 100%; padding: .95rem; border: none;
    border-radius: 10px; cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem; font-weight: 600;
    letter-spacing: .04em; text-transform: uppercase;
    color: #fff; background: var(--gold);
    box-shadow: 0 6px 20px rgba(200,150,46,.4);
    transition: background .2s, transform .2s;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    margin-top: .8rem;
}
.btn-submit svg { width: 18px; height: 18px; stroke-width: 2.2; }
.btn-submit:hover { background: var(--brown); transform: translateY(-1px); }

/* ── RESUMEN ── */
.resumen-card {
    background: #fff; border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 20px var(--shadow);
    animation: fadeUp .5s .2s both;
    position: sticky; top: 80px;
}
.resumen-header {
    background: var(--brown);
    padding: 1.1rem 1.4rem;
    display: flex; align-items: center; justify-content: space-between;
}
.resumen-header h3 {
    font-family: 'Merriweather', serif;
    font-size: 1.2rem; font-weight: 400; color: var(--cream);
    display: flex; align-items: center; gap: .5rem;
}
.resumen-header h3 svg { width: 18px; height: 18px; stroke-width: 1.7; color: var(--gold-lt); }
.resumen-count {
    background: var(--gold); color: #fff;
    font-size: .68rem; font-weight: 700;
    padding: .2rem .55rem; border-radius: 2rem;
}
.resumen-items { padding: .8rem 1.2rem; max-height: 320px; overflow-y: auto; }
.resumen-items::-webkit-scrollbar { width: 3px; }
.resumen-items::-webkit-scrollbar-thumb { background: var(--warm); border-radius: 2px; }

.resumen-item {
    display: grid;
    grid-template-columns: 40px minmax(0, 1fr) auto;
    align-items: center; gap: .8rem;
    padding: .7rem 0; border-bottom: 1px solid var(--warm);
    font-size: .88rem;
}
.resumen-item:last-child { border-bottom: none; }
.ri-img {
    width: 40px; height: 40px; border-radius: 8px;
    object-fit: cover; flex-shrink: 0;
    border: 1px solid var(--warm);
}
.ri-name {
    display: block; font-weight: 500; color: var(--brown);
    line-height: 1.25; white-space: normal;
    overflow-wrap: anywhere; word-break: break-word;
}
.ri-price {
    display: block; margin-top: .25rem;
    font-weight: 600; color: var(--gold); white-space: nowrap;
}
.ri-actions { display: flex; align-items: center; gap: .35rem; flex-shrink: 0; }
.ri-qty {
    min-width: 24px; text-align: center;
    color: var(--brown-md); font-size: .85rem; font-weight: 600;
}
.ri-qty-btn {
    width: 28px; height: 28px; border-radius: 50%;
    border: 1.5px solid var(--warm); background: #fff;
    color: var(--brown-md); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: border-color .2s, background .2s, color .2s;
}
.ri-qty-btn svg { width: 13px; height: 13px; stroke-width: 2.5; }
.ri-qty-btn:hover { border-color: var(--gold); background: rgba(200,150,46,.1); color: var(--gold); }
.ri-qty-btn.remove { color: var(--red); border-color: rgba(139,26,26,.25); }
.ri-qty-btn.remove:hover { background: rgba(139,26,26,.08); }

.resumen-footer {
    padding: 1rem 1.4rem;
    background: #faf6ee;
    border-top: 2px solid var(--warm);
}
.resumen-total-row { display: flex; justify-content: space-between; align-items: baseline; }
.resumen-total-label { font-size: .85rem; color: var(--brown-md); }
.resumen-total-price {
    font-family: 'Merriweather', serif;
    font-size: 1.8rem; font-weight: 600; color: var(--brown);
}
.resumen-total-price span { font-size: .85rem; color: var(--gold); }

/* EMPTY */
.carrito-vacio {
    grid-column: 1 / -1; text-align: center;
    padding: 4rem 1rem; animation: fadeUp .5s both;
}
.carrito-vacio .cv-icon {
    width: 80px; height: 80px; border-radius: 50%;
    background: rgba(200,150,46,.1); color: var(--gold);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
}
.carrito-vacio .cv-icon svg { width: 36px; height: 36px; stroke-width: 1.5; }
.carrito-vacio h3 {
    font-family: 'Merriweather', serif;
    font-size: 1.6rem; color: var(--brown-md); margin-bottom: .5rem;
}
.carrito-vacio p { color: #a08060; margin-bottom: 1.5rem; }
.btn-ir-carta {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--gold); color: #fff;
    padding: .75rem 1.8rem; border-radius: 3rem;
    text-decoration: none; font-weight: 600;
    box-shadow: 0 6px 20px rgba(200,150,46,.4);
    transition: background .2s, transform .2s;
}
.btn-ir-carta svg { width: 16px; height: 16px; stroke-width: 2; }
.btn-ir-carta:hover { background: var(--brown); transform: translateY(-2px); }

@media (max-width: 700px) {
    .content { grid-template-columns: 1fr; padding: 1.5rem 1rem 3rem; }
    .page-hero { padding: 2rem 1.4rem; }
    .resumen-card { position: static; }
    .metodos-grid { grid-template-columns: repeat(2, 1fr); }
    .resumen-item { grid-template-columns: 40px minmax(0, 1fr); }
    .ri-actions { grid-column: 2; justify-content: flex-start; }
}
</style>
</head>
<body>

<?php include '../includes/header_cliente.php'; ?>
<?php include '../includes/sidebar_cliente.php'; ?>

<main>
    <div class="page-hero">
        <div class="hero-inner">
            <h1>Hacer <em>Delivery</em></h1>
            <p>Completa tu dirección y método de pago para recibir tu pedido en casa</p>
        </div>
    </div>

    <div class="content">

        <?php if ($resultado): ?>
        <!-- RESULTADO -->
        <div class="resultado-wrap">
            <div class="resultado-card <?= $tipoResultado ?>">
                <div class="resultado-icon">
                    <?php if ($tipoResultado === 'ok'): ?>
                        <i data-lucide="bike"></i>
                    <?php else: ?>
                        <i data-lucide="circle-x"></i>
                    <?php endif; ?>
                </div>
                <div class="resultado-title">
                    <?= $tipoResultado === 'ok' ? '¡Pedido enviado!' : 'Pedido fallido' ?>
                </div>
                <p class="resultado-msg"><?= $resultado ?></p>
                <div class="resultado-btns">
                    <a href="platos_usuario.php" class="btn-resultado btn-volver">
                        <i data-lucide="arrow-left"></i> Volver a la carta
                    </a>
                    <?php if ($tipoResultado === 'ok'): ?>
                    <a href="mis_pedidos.php" class="btn-resultado btn-mispedidos">
                        Ver mis pedidos <i data-lucide="arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php else: ?>

        <!-- EMPTY -->
        <div id="emptyState" class="carrito-vacio" style="display:none;">
            <div class="cv-icon"><i data-lucide="shopping-cart"></i></div>
            <h3>Tu carrito está vacío</h3>
            <p>Agrega platos desde la carta antes de pedir delivery.</p>
            <a href="platos_usuario.php" class="btn-ir-carta">
                <i data-lucide="utensils"></i> Ir a la carta
            </a>
        </div>

        <!-- FORMULARIO -->
        <div id="formWrap" class="form-card" style="display:none;">
            <h2>
                <i data-lucide="bike"></i>
                Datos de entrega
            </h2>

            <form method="POST" id="pedidoForm">
                <input type="hidden" name="cartData"    id="cartDataInput">
                <input type="hidden" name="metodo_pago" id="metodoPagoInput">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i data-lucide="user"></i> Nombre completo *
                        </label>
                        <input type="text" name="nombre_cliente" class="form-input"
                               placeholder="Ej: Juan Pérez"
                               value="<?= htmlspecialchars($_SESSION['usuario'] ?? '') ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i data-lucide="phone"></i> Teléfono *
                        </label>
                        <input type="tel" name="telefono" class="form-input"
                               placeholder="Ej: 987654321" maxlength="9"
                               oninput="this.value=this.value.replace(/\D/g,'')"
                               title="Ingresa un número de teléfono válido"
                               value="<?= htmlspecialchars($userPhone) ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i data-lucide="map-pin"></i> Dirección de entrega *
                    </label>
                    <textarea name="direccion" class="form-textarea"
                              placeholder="Ej: Av. Lima 345, Miraflores, piso 3..."
                              required><?= htmlspecialchars($userAddress) ?></textarea>
                </div>

                <div class="form-group">
                    <div class="metodos-label">
                        <i data-lucide="credit-card"></i> Método de pago *
                    </div>
                    <div class="metodos-grid">
                        <label class="metodo-btn" onclick="seleccionarPago('EFECTIVO', this)">
                            <span class="metodo-icon">
                                <img src="../assets/img/efectivo.png" alt="Efectivo" class="metodo-img"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <i data-lucide="banknote" style="display:none"></i>
                            </span>
                            <span class="metodo-label">Efectivo</span>
                        </label>
                        <label class="metodo-btn" onclick="seleccionarPago('YAPE', this)">
                            <span class="metodo-icon">
                                <img src="../assets/img/yape.jpg" alt="Yape" class="metodo-img"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <i data-lucide="smartphone" style="display:none"></i>
                            </span>
                            <span class="metodo-label">Yape</span>
                        </label>
                        <label class="metodo-btn" onclick="seleccionarPago('PLIN', this)">
                            <span class="metodo-icon">
                                <img src="../assets/img/plin.png" alt="Plin" class="metodo-img"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <i data-lucide="zap" style="display:none"></i>
                            </span>
                            <span class="metodo-label">Plin</span>
                        </label>
                        <label class="metodo-btn" onclick="seleccionarPago('TARJETA', this)">
                            <span class="metodo-icon">
                                <img src="../assets/img/tarjeta.png" alt="Tarjeta" class="metodo-img"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <i data-lucide="credit-card" style="display:none"></i>
                            </span>
                            <span class="metodo-label">Tarjeta</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <i data-lucide="arrow-right"></i>
                    Confirmar delivery
                </button>
            </form>
        </div>

        <!-- RESUMEN -->
        <div class="resumen-card" id="resumenCard" style="display:none;">
            <div class="resumen-header">
                <h3>
                    <i data-lucide="shopping-bag"></i>
                    Tu pedido
                </h3>
                <span class="resumen-count" id="resumenCount">0 ítems</span>
            </div>
            <div class="resumen-items" id="resumenItems"></div>
            <div class="resumen-footer">
                <div class="resumen-total-row">
                    <span class="resumen-total-label">Total</span>
                    <div class="resumen-total-price">
                        <span>S/</span> <span id="resumenTotal">0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>

    </div>
</main>

<script>
function seleccionarPago(metodo, el) {
    document.querySelectorAll('.metodo-btn').forEach(function(b) {
        b.classList.remove('selected');
    });
    el.classList.add('selected');
    document.getElementById('metodoPagoInput').value = metodo;
}

document.addEventListener('DOMContentLoaded', function () {

    <?php if (!$resultado): ?>

    var CART_STORAGE_KEY = 'laDeliciaCart';
    var raw  = localStorage.getItem(CART_STORAGE_KEY) || sessionStorage.getItem('cartData');
    var cart = {};

    try { cart = raw ? JSON.parse(raw) : {}; } catch (e) { cart = {}; }

    function getIds() { return Object.keys(cart); }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
            .replace(/'/g,'&#039;');
    }

    function saveCart() {
        var ids = getIds();
        if (ids.length === 0) {
            localStorage.removeItem(CART_STORAGE_KEY);
            sessionStorage.removeItem('cartData');
            document.getElementById('formWrap').style.display   = 'none';
            document.getElementById('resumenCard').style.display = 'none';
            document.getElementById('emptyState').style.display  = 'block';
            document.getElementById('cartDataInput').value = '';
            return;
        }
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
        sessionStorage.setItem('cartData', JSON.stringify(cart));
        document.getElementById('cartDataInput').value = JSON.stringify(
            ids.map(function(id){ return cart[id]; })
        );
    }

    function renderResumen() {
        var ids = getIds();
        var totalQty = 0, totalPrice = 0;
        var container = document.getElementById('resumenItems');
        container.innerHTML = '';

        ids.forEach(function(id) {
            var item = cart[id];
            item.qty    = parseInt(item.qty, 10)    || 1;
            item.precio = parseFloat(item.precio)   || 0;
            totalQty   += item.qty;
            totalPrice += item.qty * item.precio;

            var div = document.createElement('div');
            div.className = 'resumen-item';
            div.innerHTML =
                '<img class="ri-img" src="' + escapeHtml(item.imgSrc || '../assets/img/default.jpg') + '" ' +
                     'onerror="this.src=\'../assets/img/default.jpg\'">' +
                '<div class="ri-info">' +
                    '<span class="ri-name">' + escapeHtml(item.nombre || 'Producto') + '</span>' +
                    '<span class="ri-price">S/ ' + (item.qty * item.precio).toFixed(2) + '</span>' +
                '</div>' +
                '<div class="ri-actions">' +
                    '<button type="button" class="ri-qty-btn remove" data-key="' + escapeHtml(id) + '" data-delta="-1" title="Restar">' +
                        '<i data-lucide="minus"></i>' +
                    '</button>' +
                    '<span class="ri-qty">× ' + item.qty + '</span>' +
                    '<button type="button" class="ri-qty-btn" data-key="' + escapeHtml(id) + '" data-delta="1" title="Sumar">' +
                        '<i data-lucide="plus"></i>' +
                    '</button>' +
                '</div>';
            container.appendChild(div);
        });

        document.getElementById('resumenCount').textContent =
            totalQty + ' ítem' + (totalQty !== 1 ? 's' : '');
        document.getElementById('resumenTotal').textContent = totalPrice.toFixed(2);

        lucide.createIcons(); // Renderizar nuevos iconos del resumen
        saveCart();
    }

    function cambiarCantidad(key, delta) {
        if (!cart[key]) return;
        cart[key].qty = (parseInt(cart[key].qty, 10) || 1) + delta;
        if (cart[key].qty <= 0) delete cart[key];
        renderResumen();
    }

    var ids = getIds();
    if (ids.length === 0) {
        document.getElementById('emptyState').style.display = 'block';
        lucide.createIcons();
        return;
    }

    document.getElementById('formWrap').style.display    = 'block';
    document.getElementById('resumenCard').style.display = 'block';
    renderResumen();

    document.getElementById('resumenItems').addEventListener('click', function(e) {
        var btn = e.target.closest('.ri-qty-btn');
        if (!btn) return;
        cambiarCantidad(btn.dataset.key, parseInt(btn.dataset.delta, 10));
    });

    document.getElementById('pedidoForm').addEventListener('submit', function(e) {
        saveCart();
        if (!document.getElementById('metodoPagoInput').value) {
            e.preventDefault();
            alert('Selecciona un método de pago.');
        }
    });

    <?php endif; ?>

    <?php if ($tipoResultado === 'ok'): ?>
    localStorage.removeItem('laDeliciaCart');
    sessionStorage.removeItem('cartData');
    <?php endif; ?>

    lucide.createIcons();
});
</script>

</body>
</html>