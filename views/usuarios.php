<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$sql = $conexion->query("SELECT * FROM usuarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Usuarios</title>

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

/* HEADER */
.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:22px;
    flex-wrap:wrap;
}

.page-title{
    color:#fff;
    font-size:21px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:10px;
    margin:0;
}

.page-title::before{
    content:"";
    width:8px;
    height:8px;
    border-radius:50%;
    background:#6ee7b7;
}

.badge-count{
    font-size:11px;
    color:#6ee7b7;
    background:rgba(110,231,183,.10);
    border:1px solid rgba(110,231,183,.18);
    padding:5px 12px;
    border-radius:999px;
}

/* FORM CARD */
.form-card{
    background:#171922;
    border:1px solid rgba(255,255,255,.06);
    border-radius:18px;
    padding:18px;
    box-shadow:0 14px 35px rgba(0,0,0,.25);
    margin-bottom:18px;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:12px;
}

.form-control{
    height:46px;
    border:none !important;
    border-radius:12px !important;
    background:#0f1117 !important;
    color:#fff !important;
    font-size:14px;
    padding:0 14px;
}

.form-control:focus{
    box-shadow:0 0 0 2px rgba(110,231,183,.14) !important;
}

select.form-control option{
    color:#000;
}

/* BUTTONS */
.btn-modern{
    height:46px;
    border:none;
    border-radius:12px;
    font-size:13px;
    font-weight:600;
    padding:0 16px;
    cursor:pointer;
}

.btn-save{
    background:#6ee7b7;
    color:#064e3b;
}

.btn-edit{
    background:#f59e0b;
    color:#fff;
    height:34px;
    padding:0 14px;
    border-radius:10px;
}

.btn-delete{
    background:#ef4444;
    color:#fff;
    height:34px;
    padding:0 14px;
    border-radius:10px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.btn-success-state{
    background:#22c55e !important;
    color:#fff !important;
}

/* TABLE */
.table-card{
    background:#171922;
    border:1px solid rgba(255,255,255,.06);
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 14px 35px rgba(0,0,0,.25);
}

.table-wrap{
    overflow:auto;
}

.table{
    margin:0;
    min-width:950px;
}

.table thead th{
    background:#1e2130 !important;
    color:#64748b !important;
    border:none !important;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.06em;
    padding:14px !important;
    white-space:nowrap;
}

.table tbody td{
    background:#171922 !important;
    color:#cbd5e1;
    border-top:1px solid rgba(255,255,255,.04) !important;
    padding:14px !important;
    vertical-align:middle;
}

.table tbody tr:hover td{
    background:#1b1e28 !important;
}

.td-id{
    color:#64748b;
    font-family:monospace;
    white-space:nowrap;
}

.actions{
    display:flex;
    justify-content:center;
    gap:8px;
    flex-wrap:wrap;
}

/* MOBILE */
@media(max-width:768px){

    .content-area{
        margin-left:0;
        padding:15px;
    }

    .form-grid{
        grid-template-columns:1fr;
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

<div class="page-header">
    <h2 class="page-title">Usuarios</h2>
    <span class="badge-count"><?= $sql->rowCount(); ?> registros</span>
</div>

<!-- FORMULARIO -->
<form action="../controllers/usuariosController.php" method="POST" class="form-card">
<input type="hidden" name="accion" value="guardar">

<div class="form-grid">

<input type="text" name="nombre" class="form-control" placeholder="Nombre" required>

<input type="email" name="correo" class="form-control" placeholder="Correo" required>

<input type="password" name="pass" class="form-control" placeholder="Contraseña" required>

<select name="rol" class="form-control">
<option>Administrador</option>
<option>Usuario</option>
</select>

<button class="btn-modern btn-save">
Guardar usuario
</button>

</div>
</form>

<!-- TABLA -->
<div class="table-card">
<div class="table-wrap">

<table class="table text-center align-middle">

<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Correo</th>
<th>Rol</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php while($row = $sql->fetch()): ?>

<tr>

<td class="td-id">
<?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?>
</td>

<td>
<input type="text"
value="<?= $row['nombre'] ?>"
class="form-control nombre">
</td>

<td>
<input type="email"
value="<?= $row['correo'] ?>"
class="form-control correo">
</td>

<td>
<select class="form-control rol">
<option <?= $row['rol']=='Administrador' ? 'selected' : '' ?>>
Administrador
</option>

<option <?= $row['rol']=='Usuario' ? 'selected' : '' ?>>
Usuario
</option>
</select>
</td>

<td>

<div class="actions">

<button class="btn-modern btn-edit guardar"
data-id="<?= $row['id'] ?>">
Editar
</button>

<a href="../controllers/usuariosController.php?eliminar=<?= $row['id'] ?>"
class="btn-delete"
onclick="return confirm('¿Eliminar usuario?')">
Eliminar
</a>

</div>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>
</div>

</div>

<script>
document.querySelectorAll(".guardar").forEach(btn=>{

btn.addEventListener("click",function(){

let fila=this.closest("tr");

let id=fila.querySelector(".guardar").dataset.id;
let nombre=fila.querySelector(".nombre").value;
let correo=fila.querySelector(".correo").value;
let rol=fila.querySelector(".rol").value;

fetch("../controllers/usuariosController.php",{
method:"POST",
headers:{
"Content-Type":"application/x-www-form-urlencoded"
},
body:`accion=editar&id=${id}&nombre=${encodeURIComponent(nombre)}&correo=${encodeURIComponent(correo)}&rol=${encodeURIComponent(rol)}`
})
.then(res=>res.text())
.then(data=>{

if(data.trim()==="ok"){

this.innerText="Guardado";
this.classList.add("btn-success-state");

setTimeout(()=>{
this.innerText="Editar";
this.classList.remove("btn-success-state");
},1500);

}else{
alert("Error al actualizar");
}

});

});

});
</script>

</body>
</html>