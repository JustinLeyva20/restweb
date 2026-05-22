<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$sql  = $conexion->query("SELECT * FROM bebidas");
$rows = $sql->fetchAll(PDO::FETCH_ASSOC);
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
*, *::before, *::after { box-sizing: border-box; }

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Sora', sans-serif;
    background: url('../assets/img/fnd.jpg') no-repeat center center fixed;
background-size: cover;
    overflow-x: hidden;
}

#sidebar {
    position: fixed; top: 0; left: 0;
    width: 260px; height: 100vh;
    background: #0d0f18;
    border-right: 1px solid rgba(255,255,255,.05);
    z-index: 200;
}

#toggleBtn, #toggleTNBtn {
    position: fixed; top: 14px; left: 14px;
    width: 38px !important; height: 38px !important;
    padding: 0 !important; border: none;
    border-radius: 8px !important;
    background: #1e2130; color: #94a3b8;
    font-size: 18px !important; line-height: 38px !important;
    text-align: center; z-index: 1100;
}

.content-area { margin-left: 260px; padding: 28px; min-height: 100vh; }

/* HEADER */
.page-header {
    display: flex; justify-content: space-between;
    align-items: center; gap: 12px;
    flex-wrap: wrap; margin-bottom: 22px;
}

.page-title {
    margin: 0; color: #f1f5f9;
    font-size: 20px; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
}

.page-title-dot {
    width: 8px; height: 8px;
    border-radius: 50%; background: #6ee7b7;
    flex-shrink: 0;
}

.count-badge {
    font-size: 12px; padding: 5px 12px;
    border-radius: 50px; color: #6ee7b7;
    background: rgba(110,231,183,.08);
    border: 1px solid rgba(110,231,183,.2);
    display: inline-flex; align-items: center; gap: 6px;
}
.count-badge svg { width: 13px; height: 13px; }

/* FORM CARD */
.form-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px; padding: 18px;
    margin-bottom: 18px;
}

.form-card-label {
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .07em;
    color: #475569; margin-bottom: 12px;
    display: flex; align-items: center; gap: 6px;
}
.form-card-label svg { width: 13px; height: 13px; stroke: #475569; }

.form-grid {
    display: grid;
    grid-template-columns: 1fr 160px auto 160px;
    gap: 10px;
    align-items: end;
}

.input-wrap {
    position: relative;
    display: flex; align-items: center;
}
.input-wrap svg {
    position: absolute; left: 12px;
    width: 15px; height: 15px;
    stroke: #475569; pointer-events: none; flex-shrink: 0;
}

.form-card input[type="text"],
.form-card input[type="number"] {
    width: 100%; height: 42px;
    border: none; outline: none;
    border-radius: 10px;
    background: #0f1117; color: #f1f5f9;
    padding: 0 14px 0 38px;
    border: 1px solid rgba(255,255,255,.08);
    font-family: 'Sora', sans-serif; font-size: 13px;
    transition: border-color .2s;
}
.form-card input::placeholder { color: #475569; }
.form-card input:focus { border-color: #6ee7b7; outline: none; }

/* Upload label */
.img-upload-label {
    display: flex; align-items: center;
    justify-content: center; gap: 8px;
    height: 42px; border-radius: 10px;
    cursor: pointer; background: #0f1117;
    color: #64748b; font-size: 13px;
    border: 1px dashed rgba(255,255,255,.15);
    padding: 0 14px; white-space: nowrap;
    transition: border-color .2s, color .2s;
    font-family: 'Sora', sans-serif;
}
.img-upload-label svg { flex-shrink: 0; width: 15px; height: 15px; }
.img-upload-label:hover { border-color: #6ee7b7; color: #6ee7b7; }
.img-upload-label.has-file { border-color: #6ee7b7; color: #6ee7b7; }
#img-input { display: none; }

.btn-agregar {
    height: 42px; padding: 0 20px; border: none;
    border-radius: 10px; background: #6ee7b7;
    color: #064e3b; font-weight: 600;
    font-family: 'Sora', sans-serif; font-size: 13px;
    white-space: nowrap; cursor: pointer;
    display: inline-flex; align-items: center;
    justify-content: center; gap: 7px;
    transition: opacity .2s;
}
.btn-agregar svg { width: 15px; height: 15px; stroke: #064e3b; }
.btn-agregar:hover { opacity: .88; }

/* TABLA */
.table-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 14px 35px rgba(0,0,0,.2);
}

.table-responsive { width: 100%; overflow-x: auto; }

.bebidas-table {
    width: 100%; min-width: 760px;
    border-collapse: collapse;
}

.bebidas-table thead th {
    background: #1e2130; color: #64748b;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .06em;
    padding: 14px 16px; white-space: nowrap;
}

.bebidas-table tbody td {
    padding: 12px 16px;
    border-top: 1px solid rgba(255,255,255,.04);
    color: #e2e8f0; vertical-align: middle;
}

.bebidas-table tbody tr:hover td { background: #1b1e28; }

.td-id { width: 70px; white-space: nowrap; color: #64748b; font-family: monospace; }
.td-img { width: 72px; text-align: center; }
.td-nombre { font-weight: 500; }

/* Thumbnail */
.bebida-thumb {
    width: 48px; height: 48px;
    border-radius: 10px; object-fit: cover;
    border: 1px solid rgba(255,255,255,.1);
}

.bebida-thumb-placeholder {
    width: 48px; height: 48px;
    border-radius: 10px;
    background: #1e2130;
    border: 1px dashed rgba(255,255,255,.1);
    display: flex; align-items: center;
    justify-content: center; margin: auto;
}
.bebida-thumb-placeholder svg { width: 20px; height: 20px; stroke: #334155; }

/* Precio display */
.precio-display {
    display: inline-flex; align-items: center;
    gap: 4px; color: #6ee7b7;
    font-size: 13px; font-weight: 500;
}
.precio-display span { color: #475569; font-size: 11px; }

/* Inputs inline */
.precio-input, .edit-input {
    width: 100%; height: 34px;
    border: none; outline: none;
    border-radius: 8px; padding: 0 10px;
    background: #0f1117; color: #f1f5f9;
    border: 1px solid rgba(255,255,255,.08);
    font-family: 'Sora', sans-serif; font-size: 13px;
    transition: border-color .2s;
}
.precio-input:focus, .edit-input:focus { border-color: #6ee7b7; }
.edit-input { display: none; }

/* Cambiar imagen en fila */
.img-edit-wrap {
    display: flex; flex-direction: column;
    align-items: center; gap: 6px;
}

.btn-change-img {
    font-size: 11px; padding: 4px 9px;
    border-radius: 6px;
    border: 1px dashed rgba(255,255,255,.12);
    background: transparent; color: #475569;
    cursor: pointer; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 4px;
    font-family: 'Sora', sans-serif;
    transition: border-color .2s, color .2s;
}
.btn-change-img svg { width: 10px; height: 10px; }
.btn-change-img:hover { border-color: #6ee7b7; color: #6ee7b7; }
input.img-row-input { display: none; }

/* Botones acción */
.td-actions { width: 210px; }

.btn-actions {
    display: flex; gap: 8px;
    justify-content: center; flex-wrap: wrap;
}

.btn-edit, .btn-delete {
    height: 32px; padding: 0 14px;
    border: none; border-radius: 8px;
    font-size: 11px; font-weight: 600;
    font-family: 'Sora', sans-serif;
    text-decoration: none;
    display: inline-flex; align-items: center;
    justify-content: center; gap: 5px;
    white-space: nowrap; cursor: pointer;
    transition: .2s;
}
.btn-edit svg, .btn-delete svg { width: 12px; height: 12px; }

.btn-edit {
    background: rgba(99,102,241,.12); color: #a5b4fc;
    border: 1px solid rgba(99,102,241,.18);
}
.btn-edit:hover { background: rgba(99,102,241,.22); color: #a5b4fc; }
.btn-edit.saving {
    background: rgba(110,231,183,.12); color: #6ee7b7;
    border-color: rgba(110,231,183,.2);
}

.btn-delete {
    background: rgba(239,68,68,.12); color: #fca5a5;
    border: 1px solid rgba(239,68,68,.18);
}
.btn-delete:hover { background: rgba(239,68,68,.22); color: #fca5a5; }

/* Empty */
.empty-row td {
    padding: 3rem !important;
    text-align: center;
    color: #475569 !important;
    font-size: 13px;
}
.empty-icon {
    display: flex; flex-direction: column;
    align-items: center; gap: 10px;
}
.empty-icon svg { width: 32px; height: 32px; stroke: #334155; }

/* Toast */
.toast-msg {
    position: fixed; bottom: 24px; right: 24px;
    background: #1e2130;
    border: 1px solid rgba(110,231,183,.25);
    color: #6ee7b7; padding: 10px 18px;
    border-radius: 12px;
    font-size: 13px; font-weight: 500;
    display: flex; align-items: center; gap: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
    z-index: 9999; opacity: 0;
    transform: translateY(10px);
    transition: all .3s; pointer-events: none;
}
.toast-msg svg { width: 15px; height: 15px; }
.toast-msg.show { opacity: 1; transform: translateY(0); }

/* RESPONSIVE */
@media (max-width: 992px) {
    .content-area { margin-left: 0; padding: 16px; }
    .form-grid { grid-template-columns: 1fr 1fr; }
    .form-grid .btn-agregar { grid-column: 1 / -1; }
}
@media (max-width: 768px) {
    .page-header { align-items: flex-start; flex-direction: column; }
    .page-title { font-size: 18px; }
    .form-grid { grid-template-columns: 1fr; }
    .bebidas-table { min-width: 680px; }
    .btn-actions { flex-direction: column; }
    .btn-edit, .btn-delete { width: 100%; }
}
@media (max-width: 480px) {
    .content-area { padding: 12px; }
    .page-title { font-size: 17px; }
    .count-badge { font-size: 11px; }
    .bebidas-table { min-width: 620px; }
}
</style>
</head>

<body>

<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>

<div class="content-area">

    <div class="page-header">
        <h2 class="page-title">
            <span class="page-title-dot"></span>
            Bebidas
        </h2>
        <span class="count-badge" id="count-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                <polyline points="2 17 12 22 22 17"/>
                <polyline points="2 12 12 17 22 12"/>
            </svg>
            <span>0 registros</span>
        </span>
    </div>

    <!-- FORM AGREGAR -->
    <form action="../controllers/bebidasController.php"
          method="POST"
          enctype="multipart/form-data"
          class="form-card">

        <input type="hidden" name="accion" value="guardar">

        <p class="form-card-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8"  y1="12" x2="16" y2="12"/>
            </svg>
            Nueva bebida
        </p>

        <div class="form-grid">

            <div class="input-wrap">
                <!-- cup icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8h1a4 4 0 0 1 0 8h-1"/>
                    <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/>
                    <line x1="6" y1="1" x2="6" y2="4"/>
                    <line x1="10" y1="1" x2="10" y2="4"/>
                    <line x1="14" y1="1" x2="14" y2="4"/>
                </svg>
                <input type="text" name="nombre"
                       placeholder="Nombre de la bebida" required>
            </div>

            <div class="input-wrap">
                <input type="number" name="precio"
                       placeholder="Precio (S/.)" step="0.01" required>
            </div>

            <label class="img-upload-label" id="upload-label" for="img-input">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span id="upload-label-text">Imagen (opcional)</span>
            </label>
            <input type="file" name="imagen" id="img-input" accept="image/*">

            <button type="submit" class="btn-agregar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Agregar bebida
            </button>

        </div>
    </form>

    <!-- TABLA -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="bebidas-table" id="bebidas-table">
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

                <?php if (empty($rows)): ?>
                <tr class="empty-row">
                    <td colspan="5">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 8h1a4 4 0 0 1 0 8h-1"/>
                                <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/>
                                <line x1="6" y1="1" x2="6" y2="4"/>
                                <line x1="10" y1="1" x2="10" y2="4"/>
                                <line x1="14" y1="1" x2="14" y2="4"/>
                            </svg>
                            No hay bebidas registradas aún
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($rows as $row): ?>
                <tr>

                    <td class="td-id">
                        <?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?>
                    </td>

                    <!-- IMAGEN -->
                    <td class="td-img">
                        <div class="img-edit-wrap">

                            <?php if (!empty($row['imagen'])): ?>
                                <img src="<?= htmlspecialchars($row['imagen']) ?>"
                                     class="bebida-thumb"
                                     id="thumb-<?= $row['id'] ?>"
                                     alt="<?= htmlspecialchars($row['nombre']) ?>">
                            <?php else: ?>
                                <div class="bebida-thumb-placeholder" id="thumb-<?= $row['id'] ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 8h1a4 4 0 0 1 0 8h-1"/>
                                        <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/>
                                        <line x1="6" y1="1" x2="6" y2="4"/>
                                        <line x1="10" y1="1" x2="10" y2="4"/>
                                        <line x1="14" y1="1" x2="14" y2="4"/>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <label class="btn-change-img" for="img-row-<?= $row['id'] ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                Cambiar
                            </label>
                            <input type="file" accept="image/*"
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
                        <span class="precio-display" id="precio-display-<?= $row['id'] ?>">
                            <span>S/.</span><?= number_format($row['precio'], 2) ?>
                        </span>
                        <input type="number" step="0.01"
                               class="precio-input"
                               id="precio-input-<?= $row['id'] ?>"
                               value="<?= $row['precio'] ?>"
                               style="display:none;">
                    </td>

                    <!-- ACCIONES -->
                    <td class="td-actions">
                        <div class="btn-actions">

                            <button class="btn-edit guardar" data-id="<?= $row['id'] ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Editar
                            </button>

                            <a href="../controllers/bebidasController.php?eliminar=<?= $row['id'] ?>"
                               class="btn-delete"
                               onclick="return confirm('¿Eliminar esta bebida?')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                    <path d="M10 11v6"/><path d="M14 11v6"/>
                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                </svg>
                                Eliminar
                            </a>

                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- TOAST -->
<div class="toast-msg" id="toast">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
    </svg>
    <span id="toast-text">Bebida actualizada</span>
</div>

<script>
/* ── Count badge ── */
document.getElementById("count-badge").querySelector("span").textContent =
    document.querySelectorAll(".bebidas-table tbody tr:not(.empty-row)").length + " registros";

/* ── SVG dinámicos para botón ── */
var svgEdit = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
var svgSave = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

/* ── Toast ── */
function showToast(msg, isError) {
    var t = document.getElementById("toast");
    document.getElementById("toast-text").textContent = msg || "Bebida actualizada";
    if (isError) {
        t.style.borderColor = "rgba(239,68,68,.25)";
        t.style.color = "#f87171";
        t.querySelector("svg").style.stroke = "#f87171";
    } else {
        t.style.borderColor = "rgba(110,231,183,.25)";
        t.style.color = "#6ee7b7";
        t.querySelector("svg").style.stroke = "#6ee7b7";
    }
    t.classList.add("show");
    setTimeout(function() { t.classList.remove("show"); }, 2500);
}

/* ── Label upload formulario ── */
document.getElementById("img-input").addEventListener("change", function() {
    var label = document.getElementById("upload-label");
    var span  = document.getElementById("upload-label-text");
    if (this.files.length) {
        span.textContent = this.files[0].name;
        label.classList.add("has-file");
    } else {
        span.textContent = "Imagen (opcional)";
        label.classList.remove("has-file");
    }
});

/* ── Editar fila ── */
document.querySelectorAll(".guardar").forEach(function(btn) {

    btn.addEventListener("click", function() {

        var id      = this.dataset.id;
        var input   = document.getElementById("input-"  + id);
        var text    = document.getElementById("text-"   + id);
        var pDisp   = document.getElementById("precio-display-" + id);
        var pInput  = document.getElementById("precio-input-"   + id);
        var imgFile = document.getElementById("img-row-" + id).files[0];

        var editando = input.style.display === "block";

        if (!editando) {
            input.style.display  = "block";
            text.style.display   = "none";
            pDisp.style.display  = "none";
            pInput.style.display = "block";
            this.innerHTML = svgSave + " Guardar";
            this.classList.add("saving");
            input.focus();
            return;
        }

        var self = this;
        var fd = new FormData();
        fd.append("accion",  "editar");
        fd.append("id",      id);
        fd.append("nombre",  input.value);
        fd.append("precio",  pInput.value);
        if (imgFile) fd.append("imagen", imgFile);

        fetch("../controllers/bebidasController.php", { method: "POST", body: fd })
        .then(function(r) { return r.text(); })
        .then(function(data) {

            if (data.trim() === "ok") {

                text.textContent    = input.value;
                input.style.display = "none";
                text.style.display  = "inline";

                var precio = parseFloat(pInput.value).toFixed(2);
                pDisp.innerHTML = '<span>S/.</span>' + precio;
                pInput.style.display = "none";
                pDisp.style.display  = "inline-flex";

                self.innerHTML = svgEdit + " Editar";
                self.classList.remove("saving");

                if (imgFile) {
                    var thumbWrap = document.getElementById("thumb-" + id);
                    var url = URL.createObjectURL(imgFile);
                    if (thumbWrap.tagName === "IMG") {
                        thumbWrap.src = url;
                    } else {
                        var img = document.createElement("img");
                        img.src = url; img.className = "bebida-thumb"; img.id = "thumb-" + id;
                        thumbWrap.replaceWith(img);
                    }
                }

                showToast("Bebida actualizada correctamente");

            } else {
                showToast("Error al actualizar", true);
            }
        })
        .catch(function() { showToast("Error de conexión", true); });

    });
});

/* ── Preview rápido imagen por fila ── */
document.querySelectorAll(".img-row-input").forEach(function(inp) {
    inp.addEventListener("change", function() {
        if (!this.files.length) return;
        var id   = this.dataset.id;
        var wrap = document.getElementById("thumb-" + id);
        var url  = URL.createObjectURL(this.files[0]);
        if (wrap.tagName === "IMG") {
            wrap.src = url;
        } else {
            var img = document.createElement("img");
            img.src = url; img.className = "bebida-thumb"; img.id = "thumb-" + id;
            wrap.replaceWith(img);
        }
    });
});
</script>

</body>
</html>