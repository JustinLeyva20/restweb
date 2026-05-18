<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }
if ($_SESSION['rol'] === 'Administrador') { header("Location: dashboard.php"); exit; }

$nombre_usuario = htmlspecialchars($_SESSION['usuario'] ?? 'Cliente');

// Obtener datos del usuario
$stmt = $conexion->prepare("SELECT id, nombre, correo, telefono, created_at FROM usuarios WHERE nombre = ? LIMIT 1");
$stmt->execute([$nombre_usuario]);
$mi_cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

// Mensajes de feedback
$msg      = null;
$msg_tipo = 'ok';

// Actualizar teléfono
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $telefono = trim($_POST['telefono'] ?? '');
    $conexion->prepare("UPDATE usuarios SET telefono = ? WHERE id = ?")
             ->execute([$telefono, $mi_cuenta['id']]);
    $mi_cuenta['telefono'] = $telefono;
    $msg = "Teléfono actualizado correctamente.";
}

// Cambiar contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_pass') {
    $pass_actual  = $_POST['pass_actual']  ?? '';
    $pass_nueva   = $_POST['pass_nueva']   ?? '';
    $pass_repetir = $_POST['pass_repetir'] ?? '';

    $check = $conexion->prepare("SELECT pass FROM usuarios WHERE id = ?");
    $check->execute([$mi_cuenta['id']]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($pass_actual, $row['pass'])) {
        $msg = "La contraseña actual no es correcta.";
        $msg_tipo = 'error';
    } elseif ($pass_nueva !== $pass_repetir) {
        $msg = "Las contraseñas nuevas no coinciden.";
        $msg_tipo = 'error';
    } elseif (strlen($pass_nueva) < 6) {
        $msg = "La nueva contraseña debe tener al menos 6 caracteres.";
        $msg_tipo = 'error';
    } else {
       $conexion->prepare("UPDATE usuarios SET pass = ? WHERE id = ?")
         ->execute([password_hash($pass_nueva, PASSWORD_DEFAULT), $mi_cuenta['id']]);
        $msg = "Contraseña actualizada correctamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Delicia — Mi Cuenta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --cream:    #F5EFE0;
        --warm:     #EDE0C4;
        --gold:     #C8962E;
        --gold-lt:  #E4B84A;
        --brown:    #3B2710;
        --brown-md: #5C3D1E;
        --green:    #2C4A2E;
        --green-lt: #4a7c4e;
        --red:      #8B1A1A;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--cream);
        color: var(--brown);
        overflow-x: hidden;
    }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(18px); }
        to   { opacity:1; transform:translateY(0); }
    }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
    @keyframes popIn {
        0%   { opacity:0; transform:scale(.85) translateY(16px); }
        70%  { transform:scale(1.03) translateY(-2px); }
        100% { opacity:1; transform:scale(1) translateY(0); }
    }

    /* TOP BAR */
    .top-bar {
        position: fixed; top:0; left:0; right:0; z-index:900;
        height: 64px;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 2rem 0 4.5rem;
        background: rgba(245,239,224,.92);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(200,150,46,.22);
    }
    .top-logo {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem; font-weight: 600; letter-spacing: .04em;
        color: var(--brown); text-decoration: none;
    }
    .top-logo span { color: var(--gold); }
    .top-nav { display:flex; gap:1.2rem; align-items:center; }
    .top-nav a {
        font-size:.8rem; font-weight:500; letter-spacing:.08em;
        text-transform:uppercase; color:var(--brown-md);
        text-decoration:none; transition:color .2s;
    }
    .top-nav a:hover { color:var(--gold); }
    .top-nav .cta {
        background:var(--gold); color:#fff;
        padding:.4rem 1.1rem; border-radius:2rem;
        font-size:.76rem; transition:background .2s, transform .2s;
    }
    .top-nav .cta:hover { background:var(--brown); transform:translateY(-1px); }

    main { padding-top: 64px; min-height: 100vh; }

    /* HERO */
    .page-hero {
        background: linear-gradient(135deg, #1f0f04 0%, var(--brown) 50%, var(--brown-md) 100%);
        padding: 2.8rem 3rem 2.2rem;
        position: relative; overflow: hidden;
    }
    .page-hero::before {
        content:''; position:absolute; top:-90px; right:-70px;
        width:280px; height:280px; border-radius:50%;
        background: radial-gradient(circle, rgba(200,150,46,.28), transparent 65%);
        pointer-events:none;
    }
    .page-hero::after {
        content:''; position:absolute; bottom:-60px; left:20%;
        width:200px; height:200px; border-radius:50%;
        background: radial-gradient(circle, rgba(44,74,46,.2), transparent 65%);
        pointer-events:none;
    }
    .hero-inner { position:relative; z-index:1; }
    .hero-inner h1 {
        font-family:'Cormorant Garamond',serif;
        font-size:clamp(2rem,4vw,3rem);
        font-weight:300; color:var(--cream); line-height:1.1;
        animation: fadeUp .6s .1s both;
    }
    .hero-inner h1 em { font-style:italic; color:var(--gold-lt); }
    .hero-inner p {
        color:rgba(245,239,224,.6); font-size:.9rem;
        margin-top:.5rem; font-weight:300;
        animation: fadeUp .6s .25s both;
    }

    /* LAYOUT */
    .content-wrapper {
        padding: 2.5rem 3rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.6rem;
        max-width: 900px;
    }

    /* CARDS */
    .cuenta-card {
        background: #fff;
        border: 1px solid var(--warm);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(59,39,16,.08);
        animation: popIn .5s both;
    }

    .card-header {
        background: linear-gradient(135deg, #1f0f04 0%, var(--brown) 60%, var(--brown-md) 100%);
        padding: 1.4rem 1.8rem;
        display: flex; align-items: center; gap: 1rem;
        position: relative; overflow: hidden;
    }
    .card-header::before {
        content:''; position:absolute; top:-40px; right:-30px;
        width:140px; height:140px; border-radius:50%;
        background: radial-gradient(circle, rgba(200,150,46,.25), transparent 65%);
        pointer-events:none;
    }
    .card-header-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink:0;
        background: linear-gradient(135deg, var(--gold), #92400e);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        box-shadow: 0 6px 16px rgba(200,150,46,.4);
        position: relative; z-index:1;
    }
    .card-header-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem; font-weight: 600;
        color: var(--cream);
        position: relative; z-index:1;
    }

    /* AVATAR */
    .avatar-section {
        display: flex; flex-direction: column; align-items: center;
        padding: 1.8rem 1.8rem 1rem;
        border-bottom: 1px solid var(--warm);
    }
    .avatar-circle {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, var(--gold), #92400e);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 700; color: #fff;
        box-shadow: 0 8px 24px rgba(200,150,46,.3);
        margin-bottom: .8rem;
        font-family: 'Cormorant Garamond', serif;
    }
    .avatar-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.3rem; font-weight: 600; color: var(--brown);
    }
    .avatar-rol {
        font-size: .72rem; font-weight: 700; letter-spacing: .4px;
        text-transform: uppercase; color: var(--gold);
        margin-top: .2rem;
    }

    /* FILAS DE DATOS */
    .data-row {
        display: flex; align-items: center; gap: 1rem;
        padding: 1rem 1.8rem;
        border-bottom: 1px solid var(--warm);
        transition: background .15s;
    }
    .data-row:last-child { border-bottom: none; }
    .data-row:hover { background: rgba(200,150,46,.04); }

    .data-icon {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink:0;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px;
    }
    .icon-nombre  { background: rgba(200,150,46,.12); }
    .icon-correo  { background: rgba(99,102,241,.1);  }
    .icon-tel     { background: rgba(44,74,46,.1);    }
    .icon-fecha   { background: rgba(236,72,153,.1);  }

    .data-label {
        font-size: .68rem; font-weight: 700;
        letter-spacing: .5px; text-transform: uppercase;
        color: #a08060; margin-bottom: .2rem;
    }
    .data-value {
        color: var(--brown); font-size: .9rem; font-weight: 500;
    }
    .data-value.muted { color: #b0a090; font-style: italic; }

    /* FORMULARIO */
    .form-body { padding: 1.4rem 1.8rem; display: flex; flex-direction: column; gap: 1rem; }

    .form-group { display: flex; flex-direction: column; gap: .35rem; }
    .form-label {
        font-size: .72rem; font-weight: 700;
        letter-spacing: .5px; text-transform: uppercase; color: var(--brown-md);
    }
    .form-input {
        padding: .65rem .9rem;
        border: 1.5px solid var(--warm);
        border-radius: 9px;
        background: var(--cream);
        font-family: 'DM Sans', sans-serif;
        font-size: .88rem; color: var(--brown);
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-input:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(200,150,46,.15);
    }

    .btn-submit {
        width: 100%; padding: .8rem;
        background: var(--gold); color: #fff; border: none;
        border-radius: 10px; cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        font-size: .88rem; font-weight: 600; letter-spacing: .04em;
        text-transform: uppercase;
        box-shadow: 0 6px 18px rgba(200,150,46,.35);
        transition: background .2s, transform .2s;
    }
    .btn-submit:hover {
        background: var(--brown);
        transform: translateY(-1px);
    }

    /* MENSAJE */
    .msg {
        margin: 0 1.8rem 1rem;
        padding: .75rem 1rem;
        border-radius: 10px;
        font-size: .84rem; font-weight: 500;
        animation: fadeIn .4s;
    }
    .msg.ok {
        background: rgba(44,74,46,.1);
        border: 1px solid rgba(44,74,46,.25);
        color: var(--green);
    }
    .msg.error {
        background: rgba(139,26,26,.08);
        border: 1px solid rgba(139,26,26,.2);
        color: var(--red);
    }

    /* SPAN COMPLETO */
    .full-width { grid-column: 1 / -1; }

    /* RESPONSIVE */
    @media (max-width:900px) {
        .content-wrapper {
            grid-template-columns: 1fr;
            padding: 1.4rem;
        }
        .page-hero { padding: 2rem 1.4rem; }
    }
    </style>
</head>
<body>

<?php include '../includes/header_cliente.php'; ?>
<?php include '../includes/sidebar_cliente.php'; ?>

<header class="top-bar">
    <a href="inicio.php" class="top-logo">La <span>Delicia</span></a>
    <nav class="top-nav">
        <a href="platos_usuario.php">Carta</a>
        <a href="mis_reservas.php">Mis reservas</a>
        <a href="nuevo_pedido.php" class="cta">Nuevo pedido</a>
    </nav>
</header>

<main>

    <div class="page-hero">
        <div class="hero-inner">
            <h1>Mi <em>cuenta</em></h1>
            <p>Hola <?= $nombre_usuario ?> — gestiona tu información personal y tu contraseña</p>
        </div>
    </div>

    <div class="content-wrapper">

        <!-- CARD: DATOS DEL USUARIO -->
        <div class="cuenta-card" style="animation-delay:.1s">

            <div class="card-header">
                <div class="card-header-icon">👤</div>
                <div class="card-header-title">Información personal</div>
            </div>

            <!-- Avatar -->
            <div class="avatar-section">
                <div class="avatar-circle">
                    <?= mb_strtoupper(mb_substr($mi_cuenta['nombre'] , 0, 1)) ?>
                </div>
                <div class="avatar-name"><?= htmlspecialchars($mi_cuenta['nombre']) ?></div>
                <div class="avatar-rol"><?= htmlspecialchars($_SESSION['rol']) ?></div>
            </div>

            <!-- Datos -->
            <div class="data-row">
                <div class="data-icon icon-nombre">👤</div>
                <div>
                    <div class="data-label">Nombre completo</div>
                    <div class="data-value"><?= htmlspecialchars($mi_cuenta['nombre']) ?></div>
                </div>
            </div>

            <div class="data-row">
                <div class="data-icon icon-correo">✉️</div>
                <div>
                    <div class="data-label">Correo electrónico</div>
                    <div class="data-value"><?= htmlspecialchars($mi_cuenta['correo']) ?></div>
                </div>
            </div>

            <div class="data-row">
                <div class="data-icon icon-tel">📞</div>
                <div>
                    <div class="data-label">Teléfono</div>
                    <div class="data-value <?= $mi_cuenta['telefono'] ? '' : 'muted' ?>">
                        <?= $mi_cuenta['telefono'] ? htmlspecialchars($mi_cuenta['telefono']) : 'No registrado' ?>
                    </div>
                </div>
            </div>

            <div class="data-row">
                <div class="data-icon icon-fecha">📅</div>
                <div>
                    <div class="data-label">Miembro desde</div>
                    <div class="data-value">
                        <?= $mi_cuenta['created_at']
                            ? date('d/m/Y', strtotime($mi_cuenta['created_at']))
                            : 'No disponible' ?>
                    </div>
                </div>
            </div>

            <!-- Actualizar teléfono -->
            <?php if ($msg && ($_POST['accion'] ?? '') === 'actualizar'): ?>
            <div class="msg <?= $msg_tipo ?>"><?= $msg ?></div>
            <?php endif; ?>

            <form method="POST" class="form-body">
                <input type="hidden" name="accion" value="actualizar">
                <div class="form-group">
                    <label class="form-label">📞 Actualizar teléfono</label>
                    <input type="tel" name="telefono" class="form-input"
                           placeholder="Ej: 987654321"
                           value="<?= htmlspecialchars($mi_cuenta['telefono'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-submit">Guardar teléfono</button>
            </form>

        </div>

        <!-- CARD: CAMBIAR CONTRASEÑA -->
        <div class="cuenta-card" style="animation-delay:.2s">

            <div class="card-header">
                <div class="card-header-icon">🔐</div>
                <div class="card-header-title">Cambiar contraseña</div>
            </div>

            <?php if ($msg && ($_POST['accion'] ?? '') === 'cambiar_pass'): ?>
            <div class="msg <?= $msg_tipo ?>"><?= $msg ?></div>
            <?php endif; ?>

            <form method="POST" class="form-body">
                <input type="hidden" name="accion" value="cambiar_pass">

                <div class="form-group">
                    <label class="form-label">🔑 Contraseña actual</label>
                    <input type="password" name="pass_actual" class="form-input"
                           placeholder="Tu contraseña actual" required>
                </div>

                <div class="form-group">
                    <label class="form-label">🔒 Nueva contraseña</label>
                    <input type="password" name="pass_nueva" class="form-input"
                           placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label class="form-label">🔒 Repetir nueva contraseña</label>
                    <input type="password" name="pass_repetir" class="form-input"
                           placeholder="Repite la nueva contraseña" required>
                </div>

                <button type="submit" class="btn-submit">Cambiar contraseña</button>
            </form>

        </div>

    </div>

</main>

</body>
</html>