<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }

$nombre_usuario = $_SESSION['usuario'] ?? 'Usuario';

// Obtener datos del usuario
$stmt = $conexion->prepare("SELECT id, nombre, correo, telefono, direccion, created_at FROM usuarios WHERE nombre = ? LIMIT 1");
$stmt->execute([$nombre_usuario]);
$mi_cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

$msg      = null;
$msg_tipo = 'ok';

// Actualizar datos personales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar') {
    $nombre    = trim($_POST['nombre']    ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($nombre)) {
        $msg      = "El nombre no puede estar vacío.";
        $msg_tipo = 'error';
    } else {
        $conexion->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?")
                 ->execute([$nombre, $telefono, $direccion, $mi_cuenta['id']]);
        $_SESSION['usuario']    = $nombre;
        $mi_cuenta['nombre']    = $nombre;
        $mi_cuenta['telefono']  = $telefono;
        $mi_cuenta['direccion'] = $direccion;
        $nombre_usuario         = $nombre;
        $msg = "Datos actualizados correctamente.";
    }
}

// Cambiar contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_pass') {
    $pass_actual  = $_POST['pass_actual']  ?? '';
    $pass_nueva   = $_POST['pass_nueva']   ?? '';
    $pass_repetir = $_POST['pass_repetir'] ?? '';

    $check = $conexion->prepare("SELECT pass FROM usuarios WHERE id = ?");
    $check->execute([$mi_cuenta['id']]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($pass_actual, $row['pass'])) {
        $msg      = "La contraseña actual no es correcta.";
        $msg_tipo = 'error';
    } elseif ($pass_nueva !== $pass_repetir) {
        $msg      = "Las contraseñas nuevas no coinciden.";
        $msg_tipo = 'error';
    } elseif (strlen($pass_nueva) < 6) {
        $msg      = "La nueva contraseña debe tener al menos 6 caracteres.";
        $msg_tipo = 'error';
    } else {
        $conexion->prepare("UPDATE usuarios SET pass = ? WHERE id = ?")
                 ->execute([password_hash($pass_nueva, PASSWORD_DEFAULT), $mi_cuenta['id']]);
        $msg = "Contraseña actualizada correctamente.";
    }
}

// Iniciales del avatar
$partes   = explode(' ', trim($mi_cuenta['nombre'] ?? 'U'));
$iniciales = strtoupper(
    (isset($partes[0]) ? mb_substr($partes[0], 0, 1) : '') .
    (isset($partes[1]) ? mb_substr($partes[1], 0, 1) : '')
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Cuenta</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Sora', sans-serif;
        overflow-x: hidden;
        min-height: 100vh;
        position: relative;
    }

    /* FONDO igual que usuarios.php */
    body::before {
        content: "";
        position: fixed; inset: 0;
background: url('../assets/img/fnd.jpg') no-repeat center center fixed;
background-size: cover;
        z-index: -2;
    }
    body::after {
        content: "";
        position: fixed; inset: 0;
        background: rgba(0,0,0,.35);
        z-index: -1;
    }

    /* SIDEBAR */
    #sidebar {
        position: fixed; top: 0; left: 0;
        width: 260px; height: 100vh;
        background: #0d0f18;
        border-right: 1px solid rgba(255,255,255,.05);
        z-index: 200;
    }

    /* CONTENT */
.content-area {
    margin-left: 260px;
    padding: 28px;
    min-height: 50vh;

    display: flex;
    flex-direction: column;
    align-items: center;   /* centra horizontal */
    justify-content: center; /* centra vertical */
}

    /* HEADER */
    .page-header {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 26px;
    }
    .page-title {
        color: #fff;
        font-size: 21px; font-weight: 600;
        display: flex; align-items: center; gap: 10px;
        margin: 0;
    }
    .page-title::before {
        content: "";
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #6ee7b7;
    }

    /* GRID DE 2 COLUMNAS */
.cards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    width: 100%;
    max-width: 900px; /* importante para que no se estire */
}

    /* CARD */
    .mc-card {
        background: #171922;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 14px 35px rgba(0,0,0,.25);
    }

    /* CARD HEADER */
    .mc-card-header {
        background: linear-gradient(135deg, #000000 0%, #0d0f18 60%, #1a1d2e 100%);
        padding: 16px 20px;
        display: flex; align-items: center; gap: 12px;
        position: relative; overflow: hidden;
    }
    .mc-card-header::before {
        content: '';
        position: absolute; top: -40px; right: -30px;
        width: 130px; height: 130px; border-radius: 50%;
        background: radial-gradient(circle, rgba(59,130,246,.2), transparent 65%);
        pointer-events: none;
    }
    .mc-card-header-icon {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 14px rgba(59,130,246,.4);
        position: relative; z-index: 1;
        color: #fff;
    }
    .mc-card-header-icon svg { width: 20px; height: 20px; stroke-width: 1.8; }
    .mc-card-header-title {
        font-size: 15px; font-weight: 600;
        color: #f5efe0;
        position: relative; z-index: 1;
    }

    /* AVATAR */
    .avatar-section {
        display: flex; flex-direction: column; align-items: center;
        padding: 22px 20px 14px;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .avatar-circle {
        width: 76px; height: 76px; border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.9rem; font-weight: 700; color: #fff;
        font-family: 'Sora', sans-serif;
        box-shadow: 0 8px 24px rgba(0,0,0,.5);
        margin-bottom: 10px;
    }
    .avatar-name {
        font-size: 15px; font-weight: 600; color: #f1f5f9;
    }
    .avatar-rol {
        font-size: 11px; font-weight: 600; letter-spacing: .8px;
        text-transform: uppercase; color: #3b82f6;
        margin-top: 3px;
    }

    /* FILAS SOLO LECTURA */
    .data-row {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid rgba(255,255,255,.05);
        transition: background .15s;
    }
    .data-row:hover { background: rgba(255,255,255,.03); }
    .data-icon {
        width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .data-icon svg { width: 17px; height: 17px; stroke-width: 1.8; }
    .icon-correo { background: rgba(99,102,241,.15); color: #818cf8; }
    .icon-fecha  { background: rgba(236,72,153,.12);  color: #f472b6; }
    .data-label {
        font-size: 10px; font-weight: 600;
        letter-spacing: .6px; text-transform: uppercase;
        color: #64748b; margin-bottom: 2px;
        display: flex; align-items: center; gap: 6px;
    }
    .data-value { color: #cbd5e1; font-size: 13px; font-weight: 500; }
    .readonly-badge {
        font-size: 9px; font-weight: 700; letter-spacing: .3px;
        text-transform: uppercase;
        background: rgba(100,116,139,.15);
        color: #64748b;
        padding: 1px 7px; border-radius: 99px;
        display: inline-flex; align-items: center; gap: 3px;
    }
    .readonly-badge svg { width: 9px; height: 9px; stroke-width: 2.5; }

    /* SEPARADOR */
    .mc-divider {
        height: 1px;
        background: rgba(255,255,255,.05);
    }

    /* MENSAJE */
    .mc-msg {
        margin: 14px 20px 0;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 8px;
    }
    .mc-msg svg { width: 15px; height: 15px; stroke-width: 2.2; flex-shrink: 0; }
    .mc-msg.ok {
        background: rgba(110,231,183,.1);
        border: 1px solid rgba(110,231,183,.2);
        color: #6ee7b7;
    }
    .mc-msg.error {
        background: rgba(239,68,68,.1);
        border: 1px solid rgba(239,68,68,.2);
        color: #fca5a5;
    }

    /* FORMULARIO */
    .mc-form { padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; }

    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-label-mc {
        font-size: 10px; font-weight: 600;
        letter-spacing: .6px; text-transform: uppercase;
        color: #94a3b8;
        display: flex; align-items: center; gap: 5px;
    }
    .form-label-mc svg { width: 12px; height: 12px; stroke-width: 2; }

    .mc-input {
        height: 44px;
        border: none !important;
        border-radius: 11px !important;
        background: #0f1117 !important;
        color: #f1f5f9 !important;
        font-family: 'Sora', sans-serif;
        font-size: 13px;
        padding: 0 14px;
        outline: none;
        transition: box-shadow .2s;
        width: 100%;
    }
    .mc-input:focus {
        box-shadow: 0 0 0 2px rgba(59,130,246,.3) !important;
    }
    .mc-input::placeholder { color: #475569; }

    .btn-mc {
        width: 100%; height: 46px;
        border: none; border-radius: 12px; cursor: pointer;
        font-family: 'Sora', sans-serif;
        font-size: 12px; font-weight: 700;
        letter-spacing: .8px; text-transform: uppercase;
        background: #3b82f6; color: #fff;
        box-shadow: 0 6px 20px rgba(59,130,246,.35);
        transition: background .2s, transform .15s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-mc svg { width: 15px; height: 15px; stroke-width: 2.2; }
    .btn-mc:hover { background: #1d4ed8; transform: translateY(-1px); }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .cards-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .content-area { margin-left: 0; padding: 15px; }
    }
    </style>
</head>
<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

    <div class="page-header">
        <h2 class="page-title">Mi Cuenta</h2>
    </div>

    <div class="cards-grid">

        <!-- ── CARD: INFORMACIÓN PERSONAL ── -->
        <div class="mc-card">

            <div class="mc-card-header">
                <div class="mc-card-header-icon">
                    <i data-lucide="circle-user-round"></i>
                </div>
                <span class="mc-card-header-title">Información personal</span>
            </div>

            <!-- Avatar -->
            <div class="avatar-section">
                <div class="avatar-circle"><?= htmlspecialchars($iniciales ?: '?') ?></div>
                <div class="avatar-name"><?= htmlspecialchars($mi_cuenta['nombre']) ?></div>
                <div class="avatar-rol"><?= htmlspecialchars($_SESSION['rol'] ?? 'Administrador') ?></div>
            </div>

            <!-- Solo lectura: correo -->
            <div class="data-row">
                <div class="data-icon icon-correo"><i data-lucide="mail"></i></div>
                <div>
                    <div class="data-label">
                        Correo electrónico
                    </div>
                    <div class="data-value"><?= htmlspecialchars($mi_cuenta['correo']) ?></div>
                </div>
            </div>

            <!-- Solo lectura: miembro desde -->
            <div class="data-row">
                <div class="data-icon icon-fecha"><i data-lucide="calendar"></i></div>
                <div>
                    <div class="data-label">
                        Miembro desde
                    </div>
                    <div class="data-value">
                        <?= $mi_cuenta['created_at'] ? date('d/m/Y', strtotime($mi_cuenta['created_at'])) : 'No disponible' ?>
                    </div>
                </div>
            </div>

            <div class="mc-divider"></div>

            <!-- Mensaje -->
            <?php if ($msg && ($_POST['accion'] ?? '') === 'actualizar'): ?>
            <div class="mc-msg <?= $msg_tipo ?>">
                <i data-lucide="<?= $msg_tipo === 'ok' ? 'circle-check' : 'circle-alert' ?>"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Formulario editable -->
            <form method="POST" class="mc-form">
                <input type="hidden" name="accion" value="actualizar">

                <div class="form-group">
                    <label class="form-label-mc">
                        <i data-lucide="user"></i> Nombre completo
                    </label>
                    <input type="text" name="nombre" class="mc-input"
                           placeholder="Tu nombre completo"
                           value="<?= htmlspecialchars($mi_cuenta['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label-mc">
                        <i data-lucide="phone"></i> Teléfono
                    </label>
                    <input type="tel" name="telefono" class="mc-input"
                           placeholder="Ej: 987654321"
                           value="<?= htmlspecialchars($mi_cuenta['telefono'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label-mc">
                        <i data-lucide="map-pin"></i> Dirección
                    </label>
                    <input type="text" name="direccion" class="mc-input"
                           placeholder="Ej: Av. Los Olivos 123, Lima"
                           value="<?= htmlspecialchars($mi_cuenta['direccion'] ?? '') ?>">
                </div>

                <button type="submit" class="btn-mc">
                    <i data-lucide="save"></i> Guardar cambios
                </button>
            </form>

        </div>

        <!-- ── CARD: CAMBIAR CONTRASEÑA ── -->
        <div class="mc-card">

            <div class="mc-card-header">
                <div class="mc-card-header-icon">
                    <i data-lucide="lock-keyhole"></i>
                </div>
                <span class="mc-card-header-title">Cambiar contraseña</span>
            </div>

            <!-- Mensaje -->
            <?php if ($msg && ($_POST['accion'] ?? '') === 'cambiar_pass'): ?>
            <div class="mc-msg <?= $msg_tipo ?>" style="margin-bottom:0;">
                <i data-lucide="<?= $msg_tipo === 'ok' ? 'circle-check' : 'circle-alert' ?>"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="mc-form">
                <input type="hidden" name="accion" value="cambiar_pass">

                <div class="form-group">
                    <label class="form-label-mc">
                        <i data-lucide="key-round"></i> Contraseña actual
                    </label>
                    <input type="password" name="pass_actual" class="mc-input"
                           placeholder="Tu contraseña actual" required>
                </div>

                <div class="form-group">
                    <label class="form-label-mc">
                        <i data-lucide="lock"></i> Nueva contraseña
                    </label>
                    <input type="password" name="pass_nueva" class="mc-input"
                           placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label class="form-label-mc">
                        <i data-lucide="lock-keyhole"></i> Repetir nueva contraseña
                    </label>
                    <input type="password" name="pass_repetir" class="mc-input"
                           placeholder="Repite la nueva contraseña" required>
                </div>

                <button type="submit" class="btn-mc">
                    <i data-lucide="shield-check"></i> Cambiar contraseña
                </button>
            </form>

        </div>

    </div><!-- /cards-grid -->

</div><!-- /content-area -->

<script>
    lucide.createIcons();
</script>

</body>
</html>