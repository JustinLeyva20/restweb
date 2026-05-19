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
*, *::before, *::after { box-sizing: border-box; }

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Sora', sans-serif;
    background: url('../assets/img/fnd.jpg') no-repeat center center fixed;
background-size: cover;
    overflow-x: hidden;
}

/* SIDEBAR */
#sidebar {
    position: fixed;
    top: 0; left: 0;
    width: 260px; height: 100vh;
    background: #0d0f18;
    border-right: 1px solid rgba(255,255,255,.05);
    z-index: 200;
}

#toggleBtn, #toggleTNBtn {
    position: fixed;
    top: 14px; left: 14px;
    width: 38px !important; height: 38px !important;
    padding: 0 !important; border: none;
    border-radius: 8px !important;
    background: #1e2130; color: #94a3b8;
    font-size: 18px !important; line-height: 38px !important;
    text-align: center; z-index: 1100;
}

/* CONTENIDO */
.content-area {
    margin-left: 260px;
    padding: 28px;
    min-height: 100vh;
}

/* HEADER */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px; flex-wrap: wrap;
    margin-bottom: 22px;
}

.page-title {
    margin: 0;
    color: #f1f5f9;
    font-size: 20px; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
}

.page-title-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #6ee7b7;
    flex-shrink: 0;
}

.count-badge {
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 50px;
    color: #6ee7b7;
    background: rgba(110,231,183,.08);
    border: 1px solid rgba(110,231,183,.2);
    display: inline-flex; align-items: center; gap: 6px;
}
.count-badge svg { width: 13px; height: 13px; }

/* FORM CARD */
.form-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 18px;
}

.form-card-label {
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .07em;
    color: #475569;
    margin-bottom: 12px;
    display: flex; align-items: center; gap: 6px;
}
.form-card-label svg { width: 13px; height: 13px; stroke: #475569; }

.form-grid {
    display: grid;
    grid-template-columns: 1fr 220px 170px;
    gap: 10px;
}

.input-wrap {
    position: relative;
    display: flex; align-items: center;
}
.input-wrap svg {
    position: absolute;
    left: 12px;
    width: 15px; height: 15px;
    stroke: #475569;
    pointer-events: none;
    flex-shrink: 0;
}

.form-card input[type="text"],
.form-card input[type="number"] {
    width: 100%;
    height: 42px;
    border: none; outline: none;
    border-radius: 10px;
    background: #0f1117;
    color: #f1f5f9;
    padding: 0 14px 0 38px;
    border: 1px solid rgba(255,255,255,.08);
    font-family: 'Sora', sans-serif;
    font-size: 13px;
    transition: border-color .2s;
}
.form-card input::placeholder { color: #475569; }
.form-card input:focus { border-color: #6ee7b7; outline: none; }

.btn-save {
    width: 100%;
    height: 42px;
    border: none;
    border-radius: 10px;
    background: #6ee7b7;
    color: #064e3b;
    font-weight: 600;
    font-family: 'Sora', sans-serif;
    font-size: 13px;
    cursor: pointer;
    display: inline-flex; align-items: center;
    justify-content: center; gap: 7px;
    transition: opacity .2s;
}
.btn-save svg { width: 15px; height: 15px; stroke: #064e3b; }
.btn-save:hover { opacity: .88; }

/* TABLA */
.table-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 14px 35px rgba(0,0,0,.2);
}

.table-responsive { width: 100%; overflow-x: auto; }

.salas-table {
    width: 100%;
    min-width: 720px;
    border-collapse: collapse;
}

.salas-table thead th {
    background: #1e2130;
    color: #64748b;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .06em;
    padding: 14px 16px;
    white-space: nowrap;
}

.salas-table tbody td {
    padding: 13px 16px;
    border-top: 1px solid rgba(255,255,255,.04);
    color: #e2e8f0;
    vertical-align: middle;
}

.salas-table tbody tr:hover td { background: #1b1e28; }

.td-id {
    width: 80px;
    color: #64748b;
    font-family: monospace;
    white-space: nowrap;
}

.td-name { font-weight: 500; }

/* SALA ICON CELL */
.sala-name-cell {
    display: flex; align-items: center; gap: 8px;
}
.sala-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: rgba(110,231,183,.08);
    border: 1px solid rgba(110,231,183,.14);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sala-icon svg { width: 14px; height: 14px; stroke: #6ee7b7; }

/* INPUTS INLINE */
.edit-input, .mesas-input {
    width: 100%;
    height: 34px;
    border: none; outline: none;
    border-radius: 8px;
    padding: 0 10px;
    background: #0f1117;
    color: #f1f5f9;
    border: 1px solid rgba(255,255,255,.08);
    font-family: 'Sora', sans-serif;
    font-size: 13px;
    transition: border-color .2s;
}
.edit-input:focus, .mesas-input:focus { border-color: #6ee7b7; }
.edit-input { display: none; }

/* MESAS DISPLAY */
.mesas-display {
    display: inline-flex; align-items: center; gap: 6px;
    color: #6ee7b7; font-size: 13px; font-weight: 500;
}
.mesas-display svg { width: 13px; height: 13px; stroke: #6ee7b7; }

.td-actions { width: 220px; }

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
    background: rgba(99,102,241,.12);
    color: #a5b4fc;
    border: 1px solid rgba(99,102,241,.18);
}
.btn-edit:hover { background: rgba(99,102,241,.22); color: #a5b4fc; }

.btn-edit.saving {
    background: rgba(110,231,183,.12);
    color: #6ee7b7;
    border-color: rgba(110,231,183,.2);
}

.btn-delete {
    background: rgba(239,68,68,.12);
    color: #fca5a5;
    border: 1px solid rgba(239,68,68,.18);
}
.btn-delete:hover { background: rgba(239,68,68,.22); color: #fca5a5; }

/* EMPTY */
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

/* TOAST */
.toast-msg {
    position: fixed;
    bottom: 24px; right: 24px;
    background: #1e2130;
    border: 1px solid rgba(110,231,183,.25);
    color: #6ee7b7;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 13px; font-weight: 500;
    display: flex; align-items: center; gap: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
    z-index: 9999;
    opacity: 0; transform: translateY(10px);
    transition: all .3s;
    pointer-events: none;
}
.toast-msg svg { width: 15px; height: 15px; stroke: #6ee7b7; }
.toast-msg.show { opacity: 1; transform: translateY(0); }

/* RESPONSIVE */
@media (max-width: 992px) {
    .content-area { margin-left: 0; padding: 16px; }
    .form-grid { grid-template-columns: 1fr 1fr; }
    .full { grid-column: 1 / -1; }
}
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-title { font-size: 18px; }
    .form-grid { grid-template-columns: 1fr; }
    .salas-table { min-width: 650px; }
    .btn-actions { flex-direction: column; }
    .btn-edit, .btn-delete { width: 100%; }
}
@media (max-width: 480px) {
    .content-area { padding: 12px; }
    .page-title { font-size: 17px; }
    .count-badge { font-size: 11px; }
    .salas-table { min-width: 600px; }
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
            Salas
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

    <!-- FORM -->
    <form action="../controllers/salasController.php" method="POST" class="form-card">
        <input type="hidden" name="accion" value="guardar">

        <p class="form-card-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8"  y1="12" x2="16" y2="12"/>
            </svg>
            Nueva sala
        </p>

        <div class="form-grid">

            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <input type="text" name="nombre" placeholder="Nombre de la sala" required>
            </div>

            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                <input type="number" name="mesas" placeholder="Cantidad de mesas" required min="1">
            </div>

            <button class="btn-save full" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Guardar sala
            </button>

        </div>
    </form>

    <!-- TABLA -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="salas-table" id="salas-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Mesas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
                if (empty($rows)):
                ?>
                <tr class="empty-row">
                    <td colspan="4">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            No hay salas registradas aún
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($rows as $row): ?>
                <tr>

                    <td class="td-id">
                        <?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?>
                    </td>

                    <td class="td-name">
                        <div class="sala-name-cell">
                            <span class="sala-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                            </span>

                            <span id="text-nombre-<?= $row['id'] ?>">
                                <?= htmlspecialchars($row['nombre']) ?>
                            </span>

                            <input type="text"
                                   class="edit-input"
                                   id="input-nombre-<?= $row['id'] ?>"
                                   value="<?= htmlspecialchars($row['nombre']) ?>">
                        </div>
                    </td>

                    <td>
                        <span class="mesas-display" id="mesas-display-<?= $row['id'] ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                            </svg>
                            <?= $row['mesas'] ?> mesas
                        </span>
                        <input type="number"
                               class="mesas-input"
                               id="mesas-input-<?= $row['id'] ?>"
                               value="<?= $row['mesas'] ?>"
                               min="1"
                               style="display:none;">
                    </td>

                    <td class="td-actions">
                        <div class="btn-actions">

                            <button class="btn-edit guardar" data-id="<?= $row['id'] ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Editar
                            </button>

                            <a href="../controllers/salasController.php?eliminar=<?= $row['id'] ?>"
                               class="btn-delete"
                               onclick="return confirm('¿Eliminar esta sala?')">
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
    <span id="toast-text">Sala actualizada</span>
</div>

<script>
/* ── Count badge ── */
document.getElementById("count-badge").querySelector("span").textContent =
    document.querySelectorAll(".salas-table tbody tr:not(.empty-row)").length + " registros";

/* ── SVG icons para botón dinámico ── */
var svgEdit = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
var svgSave = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

/* ── Toast ── */
function showToast(msg, isError) {
    var t = document.getElementById("toast");
    document.getElementById("toast-text").textContent = msg || "Sala actualizada";
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

/* ── Editar / Guardar ── */
document.querySelectorAll(".guardar").forEach(function(btn) {

    btn.addEventListener("click", function() {

        var id     = this.dataset.id;
        var text   = document.getElementById("text-nombre-" + id);
        var input  = document.getElementById("input-nombre-" + id);
        var mDisp  = document.getElementById("mesas-display-" + id);
        var mInput = document.getElementById("mesas-input-" + id);

        var editando = input.style.display === "block";

        if (!editando) {
            /* Activar edición */
            input.style.display  = "block";
            text.style.display   = "none";
            mInput.style.display = "block";
            mDisp.style.display  = "none";

            this.innerHTML  = svgSave + " Guardar";
            this.classList.add("saving");
            input.focus();
            return;
        }

        /* Enviar cambios */
        var self = this;
        fetch("../controllers/salasController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "accion=editar&id=" + id +
                  "&nombre=" + encodeURIComponent(input.value) +
                  "&mesas="  + encodeURIComponent(mInput.value)
        })
        .then(function(res) { return res.text(); })
        .then(function(data) {

            if (data.trim() === "ok") {

                text.textContent = input.value;
                input.style.display = "none";
                text.style.display  = "inline";

                /* Actualizar display de mesas */
                mDisp.innerHTML =
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;stroke:#6ee7b7">' +
                    '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>' +
                    '<rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>' +
                    '</svg> ' + mInput.value + ' mesas';
                mInput.style.display = "none";
                mDisp.style.display  = "inline-flex";

                self.innerHTML  = svgEdit + " Editar";
                self.classList.remove("saving");

                showToast("Sala actualizada correctamente");

            } else {
                showToast("Error al actualizar", true);
            }

        })
        .catch(function() {
            showToast("Error de conexión", true);
        });

    });

});
</script>

</body>
</html>