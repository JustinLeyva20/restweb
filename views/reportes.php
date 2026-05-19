<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Cambiar estado vía AJAX (POST JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $id    = (int)$_POST['id'];
    $nuevo = $_POST['estado'];
    $conexion->prepare("UPDATE reportes SET estado = ? WHERE id = ?")->execute([$nuevo, $id]);
    echo "ok"; exit;
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conexion->prepare("DELETE FROM reportes WHERE id = ?")->execute([$id]);
    header("Location: reportes.php"); exit;
}

$sql  = $conexion->query("SELECT * FROM reportes ORDER BY id DESC");
$rows = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reportes</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/style.css" rel="stylesheet">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Sora', sans-serif;
    overflow-x: hidden;
    min-height: 100vh;
    background: url('../assets/img/fnd.jpg') no-repeat center center fixed;
    background-size: cover;
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

.page-header {
    display: flex; justify-content: space-between;
    align-items: center; gap: 12px;
    margin-bottom: 22px; flex-wrap: wrap;
}

.page-title {
    margin: 0; color: #f1f5f9;
    font-size: 20px; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
}

.page-title-dot {
    width: 8px; height: 8px;
    border-radius: 50%; background: #f87171; flex-shrink: 0;
}

.count-badge {
    font-size: 12px; padding: 5px 12px;
    border-radius: 50px; color: #f87171;
    background: rgba(248,113,113,.08);
    border: 1px solid rgba(248,113,113,.2);
    display: inline-flex; align-items: center; gap: 6px;
}
.count-badge svg { width: 13px; height: 13px; }

/* TABLA */
.table-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 14px 35px rgba(0,0,0,.2);
}

.table-responsive { width: 100%; overflow-x: auto; }

.reportes-table {
    width: 100%; min-width: 860px;
    border-collapse: collapse;
}

.reportes-table thead th {
    background: #1e2130; color: #64748b;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .06em;
    padding: 14px 16px; white-space: nowrap;
}

.reportes-table tbody td {
    padding: 12px 16px;
    border-top: 1px solid rgba(255,255,255,.04);
    color: #e2e8f0; vertical-align: middle;
}

.reportes-table tbody tr:hover td { background: #1b1e28; }

.td-id    { width: 60px; white-space: nowrap; color: #64748b; font-family: monospace; }
.td-usuario { font-weight: 500; white-space: nowrap; }
.td-correo  { color: #94a3b8; }
.td-asunto  { font-weight: 500; min-width: 130px; }
.td-desc    { color: #94a3b8; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.td-fecha   { white-space: nowrap; color: #64748b; font-size: 12px; }
.td-estado  { width: 160px; }
.td-actions { width: 100px; text-align: center; }

/* ── ESTADO BADGE CLICKEABLE ── */
.estado-wrap {
    position: relative;
    display: inline-block;
}

.estado-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 11px; border-radius: 50px;
    font-size: 11px; font-weight: 600; white-space: nowrap;
    cursor: pointer; user-select: none;
    transition: opacity .2s, transform .15s;
}
.estado-badge:hover { opacity: .82; transform: scale(.97); }
.estado-badge svg { width: 11px; height: 11px; flex-shrink: 0; }

/* Pequeña flechita indicadora */
.estado-badge::after {
    content: "";
    width: 5px; height: 5px;
    border-right: 1.5px solid currentColor;
    border-bottom: 1.5px solid currentColor;
    transform: rotate(45deg) translateY(-1px);
    opacity: .6;
    flex-shrink: 0;
}

.est-PENDIENTE { background: rgba(251,191,36,.12);  color: #fbbf24; border: 1px solid rgba(251,191,36,.2); }
.est-REVISADO  { background: rgba(96,165,250,.12);   color: #93c5fd; border: 1px solid rgba(96,165,250,.2); }
.est-RESUELTO  { background: rgba(110,231,183,.12);  color: #6ee7b7; border: 1px solid rgba(110,231,183,.2); }

/* ── DROPDOWN GLOBAL (appended to body, position:fixed) ── */
#estado-dropdown-global {
    display: none;
    position: fixed;
    z-index: 9000;
    background: #1e2130;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 12px 32px rgba(0,0,0,.55);
    min-width: 155px;
    animation: ddIn .15s ease;
}
#estado-dropdown-global.open { display: block; }

@keyframes ddIn {
    from { opacity: 0; transform: translateY(-5px); }
    to   { opacity: 1; transform: translateY(0); }
}

.dd-option {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 14px; cursor: pointer;
    font-size: 12px; font-weight: 600;
    transition: background .15s;
    white-space: nowrap;
}
.dd-option:hover { background: rgba(255,255,255,.05); }
.dd-option svg { width: 12px; height: 12px; flex-shrink: 0; }

.dd-option.opt-PENDIENTE { color: #fbbf24; }
.dd-option.opt-REVISADO  { color: #93c5fd; }
.dd-option.opt-RESUELTO  { color: #6ee7b7; }
.dd-option.active { background: rgba(255,255,255,.06); }

/* Separador */
.dd-option + .dd-option { border-top: 1px solid rgba(255,255,255,.04); }

/* Dropdown en móvil — bottom-sheet */
@media (max-width: 576px) {
    #estado-dropdown-global {
        left: 10px !important;
        right: 10px !important;
        min-width: auto !important;
        border-radius: 12px;
        box-shadow: 0 -4px 32px rgba(0,0,0,.5);
    }
    .dd-option {
        padding: 12px 14px;
        font-size: 13px;
        justify-content: center;
    }
}

/* ── BOTÓN ELIMINAR ── */
.btn-delete {
    display: inline-flex; align-items: center; gap: 4px;
    height: 30px; padding: 0 12px; border: none;
    border-radius: 8px; cursor: pointer; text-decoration: none;
    font-family: 'Sora', sans-serif; font-size: 11px; font-weight: 600;
    background: rgba(239,68,68,.12); color: #fca5a5;
    border: 1px solid rgba(239,68,68,.18); transition: .2s;
}
.btn-delete:hover { background: rgba(239,68,68,.22); color: #fca5a5; }
.btn-delete svg { width: 12px; height: 12px; }

/* EMPTY */
.empty-row td { padding: 3rem !important; text-align: center; color: #475569 !important; font-size: 13px; }
.empty-icon { display: flex; flex-direction: column; align-items: center; gap: 10px; }
.empty-icon svg { width: 32px; height: 32px; stroke: #334155; }

/* TOAST */
.toast-msg {
    position: fixed; bottom: 24px; right: 24px;
    background: #1e2130; border: 1px solid rgba(110,231,183,.25);
    color: #6ee7b7; padding: 10px 18px; border-radius: 12px;
    font-size: 13px; font-weight: 500;
    display: flex; align-items: center; gap: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
    z-index: 9999; opacity: 0; transform: translateY(10px);
    transition: all .3s; pointer-events: none;
}
.toast-msg svg { width: 15px; height: 15px; }
.toast-msg.show { opacity: 1; transform: translateY(0); }

@media (max-width: 992px) {
    .content-area { margin-left: 0; padding: 16px; }
    .reportes-table { min-width: 0; }
    .reportes-table thead th,
    .reportes-table tbody td { padding: 10px 10px; }
    .td-desc { max-width: 120px; }
    .td-correo { display: none; }
}
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-title { font-size: 18px; }
    .reportes-table { min-width: 0; }
    .reportes-table tbody td { padding: 8px 6px; font-size: 12px; }
    .reportes-table thead th { padding: 10px 6px; font-size: 10px; }
    .td-correo { display: none; }
    .td-desc { max-width: 90px; }
    .td-usuario { font-size: 12px; }
    .td-asunto { font-size: 12px; min-width: 0; }
    .td-fecha { font-size: 11px; }
    .td-actions { width: auto; }
    .btn-delete { height: 26px; padding: 0 8px; font-size: 10px; }
    .btn-delete svg { width: 10px; height: 10px; }
    .estado-badge { padding: 4px 8px; font-size: 10px; }
    .estado-badge svg { width: 9px; height: 9px; }
    .table-card { border-radius: 12px; }
}
@media (max-width: 576px) {
    .reportes-table thead { display: none; }
    .reportes-table tbody tr {
        display: block;
        padding: 12px 14px;
        border-top: 1px solid rgba(255,255,255,.06);
    }
    .reportes-table tbody tr:hover td { background: transparent; }
    .reportes-table tbody td {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 5px 0 !important;
        border: none !important;
        font-size: 13px;
        width: 100% !important;
        max-width: none !important;
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
    }
    .reportes-table tbody td::before {
        content: attr(data-label);
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        min-width: 70px;
        flex-shrink: 0;
    }
    .td-id::before { content: "#"; }
    .td-usuario::before { content: "Usuario"; }
    .td-correo::before { content: "Correo"; }
    .td-correo { display: flex !important; }
    .td-asunto::before { content: "Asunto"; }
    .td-desc::before { content: "Descripción"; }
    .td-estado::before { content: "Estado"; }
    .td-fecha::before { content: "Fecha"; }
    .td-actions::before { content: "Acciones"; }
    .td-actions { justify-content: flex-start !important; }
    .td-estado { padding: 8px 0 !important; }
    .td-desc { -webkit-line-clamp: 2; display: -webkit-box !important; -webkit-box-orient: vertical; overflow: hidden !important; }
    .content-area { padding: 10px; }
    .page-header { gap: 8px; margin-bottom: 14px; }
    .page-title { font-size: 17px; }
    .count-badge { font-size: 11px; padding: 4px 10px; }
    .table-card { border-radius: 10px; }
}
@media (max-width: 400px) {
    .reportes-table tbody td { font-size: 12px; }
    .reportes-table tbody td::before { min-width: 60px; font-size: 9px; }
    .content-area { padding: 6px; }
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
            Reportes
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

    <div class="table-card">
        <div class="table-responsive">
            <table class="reportes-table" id="reportes-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Asunto</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($rows)): ?>
                <tr class="empty-row">
                    <td colspan="8">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            No hay reportes registrados
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($rows as $row): ?>
                <tr data-id="<?= $row['id'] ?>">

                    <td class="td-id" data-label="#">
                        <?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?>
                    </td>

                    <td class="td-usuario" data-label="Usuario">
                        <?= htmlspecialchars($row['usuario']) ?>
                    </td>

                    <td class="td-correo" data-label="Correo">
                        <?= htmlspecialchars($row['correo'] ?? '—') ?>
                    </td>

                    <td class="td-asunto" data-label="Asunto">
                        <?= htmlspecialchars($row['asunto']) ?>
                    </td>

                    <td class="td-desc" data-label="Descripción" title="<?= htmlspecialchars($row['descripcion']) ?>">
                        <?= htmlspecialchars($row['descripcion']) ?>
                    </td>

                    <!-- ESTADO INLINE CLICKEABLE -->
                    <td class="td-estado" data-label="Estado">
                        <span class="estado-badge est-<?= $row['estado'] ?>"
                              data-estado="<?= $row['estado'] ?>"
                              data-id="<?= $row['id'] ?>"
                              onclick="toggleDropdown(this)">

                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="estado-icon">
                                <?php if ($row['estado'] === 'PENDIENTE'): ?>
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                <?php elseif ($row['estado'] === 'REVISADO'): ?>
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                <?php else: ?>
                                    <polyline points="20 6 9 17 4 12"/>
                                <?php endif; ?>
                            </svg>

                            <span class="estado-label"><?= $row['estado'] ?></span>
                        </span>
                    </td>

                    <td class="td-fecha" data-label="Fecha">
                        <?= date('d/m/Y H:i', strtotime($row['fecha'])) ?>
                    </td>

                    <!-- ACCIONES: solo eliminar -->
                    <td class="td-actions" data-label="Acciones">
                        <a href="reportes.php?eliminar=<?= $row['id'] ?>"
                           class="btn-delete"
                           onclick="return confirm('¿Eliminar este reporte?')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6"/><path d="M14 11v6"/>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                            Eliminar
                        </a>
                    </td>

                </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- DROPDOWN GLOBAL — fuera de cualquier overflow:hidden -->
<div id="estado-dropdown-global">
    <div class="dd-option opt-PENDIENTE" data-val="PENDIENTE">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>
        Pendiente
    </div>
    <div class="dd-option opt-REVISADO" data-val="REVISADO">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
        Revisado
    </div>
    <div class="dd-option opt-RESUELTO" data-val="RESUELTO">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        Resuelto
    </div>
</div>

<!-- TOAST -->
<div class="toast-msg" id="toast">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
    </svg>
    <span id="toast-text">Estado actualizado</span>
</div>

<script>
/* ── Count ── */
document.getElementById("count-badge").querySelector("span").textContent =
    document.querySelectorAll(".reportes-table tbody tr:not(.empty-row)").length + " registros";

/* ── Toast ── */
function showToast(msg, isError) {
    var t = document.getElementById("toast");
    document.getElementById("toast-text").textContent = msg;
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

/* ── SVG por estado ── */
var svgEstado = {
    PENDIENTE: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    REVISADO:  '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
    RESUELTO:  '<polyline points="20 6 9 17 4 12"/>'
};

/* ── Dropdown global ── */
var ddGlobal    = document.getElementById("estado-dropdown-global");
var activeBadge = null;   /* badge actualmente abierto */

function toggleDropdown(badge) {
    /* Si ya está abierto para este badge, cerrar */
    if (activeBadge === badge && ddGlobal.classList.contains("open")) {
        closeDropdown();
        return;
    }

    activeBadge = badge;

    /* Marcar opción activa */
    var estado = badge.dataset.estado;
    ddGlobal.querySelectorAll(".dd-option").forEach(function(o) {
        o.classList.toggle("active", o.dataset.val === estado);
    });

    /* Posicionar con fixed justo debajo del badge */
    var rect = badge.getBoundingClientRect();
    var ddW  = 155;
    var isMobile = window.innerWidth < 576;

    if (isMobile) {
        /* En móvil: centrado horizontal, ancho casi completo */
        ddGlobal.style.left = "10px";
        ddGlobal.style.right = "10px";
        ddGlobal.style.top = (rect.bottom + 6) + "px";
        ddGlobal.style.minWidth = "auto";
    } else {
        ddGlobal.style.right = "auto";
        ddGlobal.style.minWidth = "155px";
        var left = rect.left;
        if (left + ddW > window.innerWidth - 8) {
            left = rect.right - ddW;
        }
        ddGlobal.style.left = left + "px";
        ddGlobal.style.top  = (rect.bottom + 6) + "px";
    }

    /* Forzar re-animación */
    ddGlobal.classList.remove("open");
    void ddGlobal.offsetWidth;
    ddGlobal.classList.add("open");
}

function closeDropdown() {
    ddGlobal.classList.remove("open");
    activeBadge = null;
}

/* Clic en opción del dropdown global */
ddGlobal.querySelectorAll(".dd-option").forEach(function(opt) {
    opt.addEventListener("click", function(e) {
        e.stopPropagation();
        if (!activeBadge) return;
        var id         = activeBadge.dataset.id;
        var nuevoEstado = this.dataset.val;
        cambiarEstado(id, nuevoEstado, this);
    });
});

/* Cerrar al hacer clic fuera */
document.addEventListener("click", function(e) {
    if (!e.target.closest(".estado-badge") && !e.target.closest("#estado-dropdown-global")) {
        closeDropdown();
    }
});

/* Cerrar al hacer scroll */
window.addEventListener("scroll", closeDropdown, true);

/* ── Cambiar estado vía fetch ── */
function cambiarEstado(id, nuevoEstado, optionEl) {
    var badge = document.querySelector(".estado-badge[data-id='" + id + "']");

    closeDropdown();

    if (!badge || badge.dataset.estado === nuevoEstado) return;

    var fd = new FormData();
    fd.append("cambiar_estado", "1");
    fd.append("id",     id);
    fd.append("estado", nuevoEstado);

    fetch("reportes.php", { method: "POST", body: fd })
    .then(function(r) { return r.text(); })
    .then(function(data) {

        if (data.trim() === "ok") {

            badge.className          = "estado-badge est-" + nuevoEstado;
            badge.dataset.estado     = nuevoEstado;
            badge.querySelector(".estado-label").textContent = nuevoEstado;
            badge.querySelector(".estado-icon").innerHTML    = svgEstado[nuevoEstado];

            showToast("Estado actualizado a " + nuevoEstado.toLowerCase());

        } else {
            showToast("Error al actualizar el estado", true);
        }
    })
    .catch(function() { showToast("Error de conexión", true); });
}
</script>

</body>
</html>