<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$sql = $conexion->query("SELECT * FROM bebidas");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bebidas</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/style.css" rel="stylesheet">

<style>
*,*::before,*::after{box-sizing:border-box;}

body{
    margin:0;
    min-height:100vh;
    font-family:'Sora',sans-serif;
    background:#0f1117;
    overflow-x:hidden;
}

#sidebar{
    position:fixed;top:0;left:0;
    width:260px;height:100vh;
    background:#0d0f18;
    border-right:1px solid rgba(255,255,255,.05);
    z-index:200;
}

#toggleBtn,#toggleTNBtn{
    position:fixed;top:14px;left:14px;
    width:38px !important;height:38px !important;
    padding:0 !important;border:none;
    border-radius:8px !important;
    background:#1e2130;color:#94a3b8;
    font-size:18px !important;line-height:38px !important;
    text-align:center;z-index:1100;
}

.content-area{margin-left:260px;padding:28px;min-height:100vh;}

.page-header{
    display:flex;justify-content:space-between;
    align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px;
}

.page-title{
    margin:0;color:#f1f5f9;font-size:20px;font-weight:600;
    display:flex;align-items:center;gap:10px;
}

.page-title::before{
    content:"";width:8px;height:8px;
    border-radius:50%;background:#6ee7b7;
}

.count-badge{
    font-size:12px;padding:5px 12px;border-radius:50px;
    color:#6ee7b7;background:rgba(110,231,183,.08);
    border:1px solid rgba(110,231,183,.2);
}

/* FORM */
.form-card{
    background:#171922;border:1px solid rgba(255,255,255,.07);
    border-radius:14px;padding:16px;margin-bottom:18px;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 160px auto 160px;
    gap:10px;
    align-items:end;
}

.form-card input[type="text"],
.form-card input[type="number"]{
    width:100%;height:42px;border:none;outline:none;
    border-radius:10px;background:#0f1117;color:#fff;
    padding:0 14px;border:1px solid rgba(255,255,255,.08);
}

.form-card input::placeholder{color:#64748b;}

/* INPUT IMAGEN */
.img-upload-label{
    display:flex;align-items:center;justify-content:center;
    gap:8px;height:42px;border-radius:10px;cursor:pointer;
    background:#0f1117;color:#94a3b8;font-size:13px;
    border:1px dashed rgba(255,255,255,.15);
    padding:0 14px;white-space:nowrap;
    transition:border-color .2s,color .2s;
}

.img-upload-label:hover{border-color:#6ee7b7;color:#6ee7b7;}
.img-upload-label.has-file{border-color:#6ee7b7;color:#6ee7b7;}
.img-upload-label svg{flex-shrink:0;}

#img-input{display:none;}

.btn-agregar{
    height:42px;padding:0 20px;border:none;
    border-radius:10px;background:#6ee7b7;
    color:#064e3b;font-weight:600;white-space:nowrap;
    cursor:pointer;
}

/* TABLA */
.table-card{
    background:#171922;border:1px solid rgba(255,255,255,.07);
    border-radius:14px;overflow:hidden;
}

.table-responsive{width:100%;overflow-x:auto;}

.bebidas-table{
    width:100%;min-width:760px;border-collapse:collapse;
}

.bebidas-table thead th{
    background:#1e2130;color:#94a3b8;font-size:12px;
    font-weight:600;padding:14px;white-space:nowrap;
}

.bebidas-table tbody td{
    padding:12px 14px;
    border-top:1px solid rgba(255,255,255,.04);
    color:#e2e8f0;vertical-align:middle;
}

.td-id{width:70px;white-space:nowrap;color:#64748b;}
.td-img{width:64px;text-align:center;}

/* Imagen en tabla */
.bebida-thumb{
    width:48px;height:48px;border-radius:8px;
    object-fit:cover;border:1px solid rgba(255,255,255,.1);
}

.bebida-thumb-placeholder{
    width:48px;height:48px;border-radius:8px;
    background:#1e2130;border:1px dashed rgba(255,255,255,.1);
    display:flex;align-items:center;justify-content:center;
    color:#475569;font-size:18px;margin:auto;
}

.td-nombre{font-weight:500;}

.precio-input,.edit-input{
    width:100%;height:34px;border:none;outline:none;
    border-radius:8px;padding:0 10px;
    background:#0f1117;color:#fff;
    border:1px solid rgba(255,255,255,.08);
}

.edit-input{display:none;}

/* Botón cambiar imagen en tabla */
.img-edit-wrap{
    display:flex;flex-direction:column;align-items:center;gap:6px;
}

.btn-change-img{
    font-size:11px;padding:3px 8px;border-radius:6px;
    border:1px dashed rgba(255,255,255,.15);
    background:transparent;color:#64748b;cursor:pointer;
    white-space:nowrap;
    transition:border-color .2s,color .2s;
}

.btn-change-img:hover{border-color:#6ee7b7;color:#6ee7b7;}
input.img-row-input{display:none;}

.td-actions{width:210px;}

.btn-actions{
    display:flex;gap:8px;justify-content:center;flex-wrap:wrap;
}

.btn-edit,.btn-delete{
    height:32px;padding:0 14px;border:none;
    border-radius:8px;font-size:12px;
    text-decoration:none;display:flex;
    align-items:center;justify-content:center;white-space:nowrap;
    cursor:pointer;
}

.btn-edit{background:rgba(99,102,241,.12);color:#a5b4fc;}
.btn-delete{background:rgba(239,68,68,.12);color:#fca5a5;}

/* RESPONSIVE */
@media(max-width:992px){
    .content-area{margin-left:0;padding:16px;}
    .form-grid{grid-template-columns:1fr 1fr;}
    .form-grid .btn-agregar{grid-column:1/-1;}
}

@media(max-width:768px){
    .page-header{align-items:flex-start;flex-direction:column;}
    .page-title{font-size:18px;}
    .form-grid{grid-template-columns:1fr;}
    .bebidas-table{min-width:680px;}
    .btn-actions{flex-direction:column;}
    .btn-edit,.btn-delete{width:100%;}
}

@media(max-width:480px){
    .content-area{padding:12px;}
    .page-title{font-size:17px;}
    .count-badge{font-size:11px;}
    .bebidas-table{min-width:620px;}
}
</style>
</head>

<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

    <div class="page-header">
        <h2 class="page-title">Bebidas</h2>
        <span class="count-badge" id="count-badge">0 registros</span>
    </div>

    <!-- FORM AGREGAR -->
    <!-- enctype obligatorio para subir archivos -->
    <form action="../controllers/bebidasController.php"
          method="POST"
          enctype="multipart/form-data"
          class="form-card">

        <input type="hidden" name="accion" value="guardar">

        <div class="form-grid">

            <input type="text" name="nombre"
                   placeholder="Nombre de la bebida" required>

            <input type="number" name="precio"
                   placeholder="Precio (S/.)" step="0.01" required>

            <!-- Botón personalizado para elegir imagen -->
            <label class="img-upload-label" id="upload-label" for="img-input">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16l4-4a3 3 0 014.24 0L16 16m-2-2l1.59-1.59A3 3 0
                             014.24 0L20 16M14 8h.01M6 20h12a2 2 0 002-2V6a2
                             2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span id="upload-label-text">Imagen (opcional)</span>
            </label>
            <input type="file" name="imagen" id="img-input"
                   accept="image/*">

            <button type="submit" class="btn-agregar">Agregar bebida</button>

        </div>
    </form>

    <!-- TABLA -->
    <div class="table-card">
        <div class="table-responsive">

            <table class="bebidas-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                <?php while($row = $sql->fetch()): ?>
                    <tr>

                        <td class="td-id">
                            <?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?>
                        </td>

                        <!-- CELDA IMAGEN -->
                        <td class="td-img">
                            <div class="img-edit-wrap">

                                <?php if(!empty($row['imagen'])): ?>
                                    <img src="../uploads/bebidas/<?= htmlspecialchars($row['imagen']) ?>"
                                         class="bebida-thumb"
                                         id="thumb-<?= $row['id'] ?>"
                                         alt="<?= htmlspecialchars($row['nombre']) ?>">
                                <?php else: ?>
                                    <div class="bebida-thumb-placeholder"
                                         id="thumb-<?= $row['id'] ?>">🥤</div>
                                <?php endif; ?>

                                <label class="btn-change-img"
                                       for="img-row-<?= $row['id'] ?>">
                                    Cambiar
                                </label>
                                <input type="file"
                                       accept="image/*"
                                       class="img-row-input"
                                       id="img-row-<?= $row['id'] ?>"
                                       data-id="<?= $row['id'] ?>">
                            </div>
                        </td>

                        <!-- NOMBRE -->
                        <td class="td-nombre">
                            <span id="text-<?= $row['id'] ?>">
                                <?= htmlspecialchars($row['nombre']) ?>
                            </span>
                            <input type="text"
                                   class="edit-input"
                                   id="input-<?= $row['id'] ?>"
                                   value="<?= htmlspecialchars($row['nombre']) ?>">
                        </td>

                        <!-- PRECIO -->
                        <td>
                            <input type="number"
                                   step="0.01"
                                   class="precio-input"
                                   value="<?= $row['precio'] ?>">
                        </td>

                        <!-- ACCIONES -->
                        <td class="td-actions">
                            <div class="btn-actions">

                                <button class="btn-edit guardar"
                                        data-id="<?= $row['id'] ?>">
                                    Editar
                                </button>

                                <a href="../controllers/bebidasController.php?eliminar=<?= $row['id'] ?>"
                                   class="btn-delete"
                                   onclick="return confirm('¿Eliminar esta bebida?')">
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
/* ── Contador de registros ── */
document.getElementById("count-badge").textContent =
    document.querySelectorAll(".bebidas-table tbody tr").length + " registros";

/* ── Label del input de imagen del formulario ── */
document.getElementById("img-input").addEventListener("change", function(){
    const label = document.getElementById("upload-label");
    const span  = document.getElementById("upload-label-text");
    if(this.files.length){
        span.textContent = this.files[0].name;
        label.classList.add("has-file");
    } else {
        span.textContent = "Imagen (opcional)";
        label.classList.remove("has-file");
    }
});

/* ── Editar nombre/precio + imagen por fila ── */
document.querySelectorAll(".guardar").forEach(btn => {

    btn.addEventListener("click", function(){

        const id      = this.dataset.id;
        const input   = document.getElementById("input-" + id);
        const text    = document.getElementById("text-"  + id);
        const fila    = this.closest("tr");
        const precio  = fila.querySelector(".precio-input").value;
        const imgFile = document.getElementById("img-row-" + id).files[0];

        const editando = input.style.display === "block";

        /* Primera pulsación → activar modo edición */
        if(!editando){
            input.style.display = "block";
            text.style.display  = "none";
            this.innerText = "Guardar";
            return;
        }

        /* Segunda pulsación → enviar con FormData (soporta archivo) */
        const fd = new FormData();
        fd.append("accion",  "editar");
        fd.append("id",      id);
        fd.append("nombre",  input.value);
        fd.append("precio",  precio);
        if(imgFile) fd.append("imagen", imgFile);

        fetch("../controllers/bebidasController.php", {
            method: "POST",
            body: fd          // sin Content-Type: el browser lo pone solo
        })
        .then(r => r.text())
        .then(data => {

            if(data.trim() === "ok"){

                text.innerText      = input.value;
                input.style.display = "none";
                text.style.display  = "inline";
                this.innerText      = "Editar";

                /* Actualizar miniatura si se subió imagen */
                if(imgFile){
                    const thumbWrap = document.getElementById("thumb-" + id);
                    const url = URL.createObjectURL(imgFile);

                    if(thumbWrap.tagName === "IMG"){
                        thumbWrap.src = url;
                    } else {
                        /* Era el placeholder div → reemplazar por <img> */
                        const img = document.createElement("img");
                        img.src       = url;
                        img.className = "bebida-thumb";
                        img.id        = "thumb-" + id;
                        thumbWrap.replaceWith(img);
                    }
                }

            } else {
                alert("Error al actualizar");
            }
        });
    });
});

/* ── Preview rápido al elegir imagen en una fila ── */
document.querySelectorAll(".img-row-input").forEach(inp => {
    inp.addEventListener("change", function(){
        if(!this.files.length) return;
        const id   = this.dataset.id;
        const wrap = document.getElementById("thumb-" + id);
        const url  = URL.createObjectURL(this.files[0]);

        if(wrap.tagName === "IMG"){
            wrap.src = url;
        } else {
            const img     = document.createElement("img");
            img.src       = url;
            img.className = "bebida-thumb";
            img.id        = "thumb-" + id;
            wrap.replaceWith(img);
        }
    });
});
</script>

</body>
</html>
