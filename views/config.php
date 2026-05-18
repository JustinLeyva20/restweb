<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$sql = $conexion->query("SELECT * FROM config LIMIT 1");
$config = $sql->fetch(PDO::FETCH_ASSOC) ?? [];

$ruc       = $config['ruc'] ?? '';
$nombre    = $config['nombre'] ?? '';
$telefono  = $config['telefono'] ?? '';
$direccion = $config['direccion'] ?? '';
$mensaje   = $config['mensaje'] ?? '';
$id        = $config['id'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Datos de la empresa</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Sora',sans-serif;
    overflow-x:hidden;
    min-height:100vh;
    position:relative;
}

/* FONDO ORIGINAL */
body::before{
    content:"";
    position:fixed;
    inset:0;
    background:url('../assets/img/fnd2.jpg') no-repeat center center;
    background-size:cover;
    z-index:-2;
}

body::after{
    content:"";
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.35);
    z-index:-1;
}

/* SIDEBAR */
#sidebar{
    position:fixed;
    top:0;
    left:0;
    width:260px;
    height:100vh;
    background:#0d0f18;
    border-right:1px solid rgba(255,255,255,.05);
    z-index:200;
}

/* BOTON MENU */
#toggleBtn,
#toggleTNBtn{
    position:fixed;
    top:14px;
    left:14px;
    width:38px !important;
    height:38px !important;
    border:none;
    border-radius:8px !important;
    background:#1e2130;
    color:#94a3b8;
    font-size:18px !important;
    line-height:38px !important;
    text-align:center;
    cursor:pointer;
    z-index:1100;
}

/* CONTENIDO */
.content-area{
    margin-left:260px;
    padding:28px;
    min-height:100vh;
}

/* CARD */
.main-card{
    max-width:760px;
    margin:auto;
    background:#171922;
    border:1px solid rgba(255,255,255,.06);
    border-radius:22px;
    box-shadow:0 18px 45px rgba(0,0,0,.28);
    overflow:hidden;
}

/* HEADER */
.card-header-custom{
    padding:22px 24px;
    border-bottom:1px solid rgba(255,255,255,.05);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.page-title{
    margin:0;
    color:#fff;
    font-size:21px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:10px;
}

.page-title::before{
    content:"";
    width:8px;
    height:8px;
    border-radius:50%;
    background:#6ee7b7;
}

.status-badge{
    font-size:11px;
    color:#6ee7b7;
    background:rgba(110,231,183,.10);
    border:1px solid rgba(110,231,183,.18);
    padding:5px 12px;
    border-radius:999px;
}

/* BODY */
.card-body-custom{
    padding:24px;
}

/* GRID INFO */
.info-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:14px;
}

.info-item{
    background:#0f1117;
    border:1px solid rgba(255,255,255,.05);
    border-radius:14px;
    padding:15px;
}

.info-label{
    color:#64748b;
    font-size:11px;
    text-transform:uppercase;
    margin-bottom:6px;
}

.info-value{
    color:#f8fafc;
    font-size:14px;
    font-weight:500;
    word-break:break-word;
}

/* FORM */
.hidden{
    display:none;
}

.form-label{
    color:#94a3b8;
    font-size:13px;
    margin-bottom:7px;
}

.form-control{
    height:46px;
    border:none !important;
    border-radius:12px !important;
    background:#0f1117 !important;
    color:#fff !important;
    padding:0 14px;
    font-size:14px;
}

.form-control:focus{
    box-shadow:0 0 0 2px rgba(110,231,183,.15) !important;
}

textarea.form-control{
    height:auto;
    min-height:100px;
    padding-top:12px;
    resize:vertical;
}

/* BOTONES */
.actions{
    margin-top:24px;
    display:flex;
    justify-content:center;
    gap:10px;
    flex-wrap:wrap;
}

.btn-modern{
    height:42px;
    padding:0 18px;
    border:none;
    border-radius:12px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
}

.btn-edit{
    background:#f59e0b;
    color:#fff;
}

.btn-save{
    background:#22c55e;
    color:#fff;
}

.btn-cancel{
    background:#334155;
    color:#fff;
}

/* MOBILE */
@media(max-width:768px){

    .content-area{
        margin-left:0;
        padding:15px;
    }

    .card-header-custom,
    .card-body-custom{
        padding:18px;
    }

    .actions{
        flex-direction:column;
    }

    .btn-modern{
        width:100%;
    }
}

</style>
</head>

<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

<div class="main-card">

<div class="card-header-custom">
    <h2 class="page-title">Datos de la empresa</h2>
    <span class="status-badge">Configuración</span>
</div>

<div class="card-body-custom">

<!-- VISTA -->
<div id="modoVista">

<div class="info-grid">

<div class="info-item">
<div class="info-label">RUC</div>
<div class="info-value"><?= $ruc ?: 'No registrado' ?></div>
</div>

<div class="info-item">
<div class="info-label">Nombre</div>
<div class="info-value"><?= $nombre ?: 'No registrado' ?></div>
</div>

<div class="info-item">
<div class="info-label">Teléfono</div>
<div class="info-value"><?= $telefono ?: 'No registrado' ?></div>
</div>

<div class="info-item">
<div class="info-label">Dirección</div>
<div class="info-value"><?= $direccion ?: 'No registrado' ?></div>
</div>

<div class="info-item" style="grid-column:1/-1;">
<div class="info-label">Mensaje</div>
<div class="info-value"><?= $mensaje ?: 'No registrado' ?></div>
</div>

</div>

<div class="actions">
<button class="btn-modern btn-edit" onclick="activarEdicion()">
Editar datos
</button>
</div>

</div>

<!-- FORMULARIO -->
<form id="modoEdicion"
class="hidden"
action="../controllers/configController.php"
method="POST">

<input type="hidden" name="id" value="<?= $id ?>">

<div class="mb-3">
<label class="form-label">RUC</label>
<input type="text" name="ruc" class="form-control" value="<?= $ruc ?>">
</div>

<div class="mb-3">
<label class="form-label">Nombre</label>
<input type="text" name="nombre" class="form-control" value="<?= $nombre ?>">
</div>

<div class="mb-3">
<label class="form-label">Teléfono</label>
<input type="text" name="telefono" class="form-control" value="<?= $telefono ?>">
</div>

<div class="mb-3">
<label class="form-label">Dirección</label>
<textarea name="direccion" class="form-control"><?= $direccion ?></textarea>
</div>

<div class="mb-3">
<label class="form-label">Mensaje</label>
<input type="text" name="mensaje" class="form-control" value="<?= $mensaje ?>">
</div>

<div class="actions">
<button class="btn-modern btn-save">
Guardar cambios
</button>

<button type="button"
class="btn-modern btn-cancel"
onclick="cancelarEdicion()">
Cancelar
</button>
</div>

</form>

</div>
</div>

</div>

<script>
function activarEdicion(){
    document.getElementById("modoVista").classList.add("hidden");
    document.getElementById("modoEdicion").classList.remove("hidden");
}

function cancelarEdicion(){
    document.getElementById("modoEdicion").classList.add("hidden");
    document.getElementById("modoVista").classList.remove("hidden");
}
</script>

</body>
</html>