<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$sql = $conexion->query("SELECT * FROM salas");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Salas</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
*,
*::before,
*::after{
    box-sizing:border-box;
}

body{
    margin:0;
    min-height:100vh;
    font-family:'Sora',sans-serif;
    background:#0f1117;
    overflow-x:hidden;
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

/* MENU */
#toggleBtn,
#toggleTNBtn{
    position:fixed;
    top:14px;
    left:14px;
    width:38px !important;
    height:38px !important;
    padding:0 !important;
    border:none;
    border-radius:8px !important;
    background:#1e2130;
    color:#94a3b8;
    font-size:18px !important;
    line-height:38px !important;
    text-align:center;
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
    flex-wrap:wrap;
    margin-bottom:22px;
}

.page-title{
    margin:0;
    color:#f1f5f9;
    font-size:20px;
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

.count-badge{
    font-size:12px;
    padding:5px 12px;
    border-radius:50px;
    color:#6ee7b7;
    background:rgba(110,231,183,.08);
    border:1px solid rgba(110,231,183,.2);
}

/* FORM */
.form-card{
    background:#171922;
    border:1px solid rgba(255,255,255,.07);
    border-radius:14px;
    padding:16px;
    margin-bottom:18px;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 220px 170px;
    gap:10px;
}

.form-card input{
    width:100%;
    height:42px;
    border:none;
    outline:none;
    border-radius:10px;
    background:#0f1117;
    color:#fff;
    padding:0 14px;
    border:1px solid rgba(255,255,255,.08);
}

.form-card input::placeholder{
    color:#64748b;
}

.btn-save{
    width:100%;
    height:42px;
    border:none;
    border-radius:10px;
    background:#6ee7b7;
    color:#064e3b;
    font-weight:600;
}

/* TABLA */
.table-card{
    background:#171922;
    border:1px solid rgba(255,255,255,.07);
    border-radius:14px;
    overflow:hidden;
}

.table-responsive{
    width:100%;
    overflow-x:auto;
}

.salas-table{
    width:100%;
    min-width:720px;
    border-collapse:collapse;
}

.salas-table thead th{
    background:#1e2130;
    color:#94a3b8;
    font-size:12px;
    font-weight:600;
    padding:14px;
    white-space:nowrap;
}

.salas-table tbody td{
    padding:14px;
    border-top:1px solid rgba(255,255,255,.04);
    color:#e2e8f0;
    vertical-align:middle;
}

.td-id{
    width:80px;
    color:#64748b;
    white-space:nowrap;
}

.td-name{
    font-weight:500;
}

.edit-input,
.mesas-input{
    width:100%;
    height:34px;
    border:none;
    outline:none;
    border-radius:8px;
    padding:0 10px;
    background:#0f1117;
    color:#fff;
    border:1px solid rgba(255,255,255,.08);
}

.edit-input{
    display:none;
}

.mesas-input{
    color:#6ee7b7;
}

.td-actions{
    width:220px;
}

.btn-actions{
    display:flex;
    gap:8px;
    justify-content:center;
    flex-wrap:wrap;
}

.btn-edit,
.btn-delete{
    height:32px;
    padding:0 14px;
    border:none;
    border-radius:8px;
    font-size:12px;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    white-space:nowrap;
}

.btn-edit{
    background:rgba(99,102,241,.12);
    color:#a5b4fc;
}

.btn-delete{
    background:rgba(239,68,68,.12);
    color:#fca5a5;
}

.btn-edit.saving{
    background:rgba(110,231,183,.12);
    color:#6ee7b7;
}

/* TABLET */
@media (max-width:992px){

    .content-area{
        margin-left:0;
        padding:16px;
    }

    .form-grid{
        grid-template-columns:1fr 1fr;
    }

    .full{
        grid-column:1 / -1;
    }
}

/* MOVIL */
@media (max-width:768px){

    .page-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .page-title{
        font-size:18px;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .salas-table{
        min-width:650px;
    }

    .btn-actions{
        flex-direction:column;
    }

    .btn-edit,
    .btn-delete{
        width:100%;
    }
}

/* PEQUEÑO */
@media (max-width:480px){

    .content-area{
        padding:12px;
    }

    .page-title{
        font-size:17px;
    }

    .count-badge{
        font-size:11px;
    }

    .salas-table{
        min-width:600px;
    }
}
</style>
</head>

<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

    <div class="page-header">
        <h2 class="page-title">Salas</h2>
        <span class="count-badge" id="count-badge">0 registros</span>
    </div>

    <!-- FORM -->
    <form action="../controllers/salasController.php" method="POST" class="form-card">
        <input type="hidden" name="accion" value="guardar">

        <div class="form-grid">
            <input type="text" name="nombre" placeholder="Nombre de sala" required>
            <input type="number" name="mesas" placeholder="Cantidad de mesas" required>
            <button class="btn-save full">Guardar</button>
        </div>
    </form>

    <!-- TABLA -->
    <div class="table-card">
        <div class="table-responsive">

            <table class="salas-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Mesas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                <?php while($row = $sql->fetch()): ?>
                <tr>

                    <td class="td-id">
                        <?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?>
                    </td>

                    <td class="td-name">

                        <span id="text-nombre-<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['nombre']) ?>
                        </span>

                        <input type="text"
                               class="edit-input"
                               id="input-nombre-<?= $row['id'] ?>"
                               value="<?= htmlspecialchars($row['nombre']) ?>">

                    </td>

                    <td>
                        <input type="number"
                               class="mesas-input"
                               value="<?= $row['mesas'] ?>">
                    </td>

                    <td class="td-actions">

                        <div class="btn-actions">

                            <button class="btn-edit guardar"
                                    data-id="<?= $row['id'] ?>">
                                Editar
                            </button>

                            <a href="../controllers/salasController.php?eliminar=<?= $row['id'] ?>"
                               class="btn-delete"
                               onclick="return confirm('¿Eliminar esta sala?')">
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
document.getElementById("count-badge").textContent =
document.querySelectorAll(".salas-table tbody tr").length + " registros";

document.querySelectorAll(".guardar").forEach(btn=>{

    btn.addEventListener("click",function(){

        let id=this.dataset.id;

        let text=document.getElementById("text-nombre-"+id);
        let input=document.getElementById("input-nombre-"+id);

        let fila=this.closest("tr");
        let mesas=fila.querySelector(".mesas-input").value;

        let editando=input.style.display==="block";

        if(!editando){
            input.style.display="block";
            text.style.display="none";
            this.innerText="Guardar";
            this.classList.add("saving");
            input.focus();
            return;
        }

        fetch("../controllers/salasController.php",{
            method:"POST",
            headers:{
                "Content-Type":"application/x-www-form-urlencoded"
            },
            body:`accion=editar&id=${id}&nombre=${encodeURIComponent(input.value)}&mesas=${encodeURIComponent(mesas)}`
        })
        .then(res=>res.text())
        .then(data=>{

            if(data.trim()==="ok"){

                text.innerText=input.value;
                input.style.display="none";
                text.style.display="inline";

                this.innerText="Editar";
                this.classList.remove("saving");

            }else{
                alert("Error al actualizar");
            }

        });

    });

});
</script>

</body>
</html>