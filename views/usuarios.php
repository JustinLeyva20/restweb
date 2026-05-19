<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$sql  = $conexion->query("SELECT * FROM usuarios");
$rows = $sql->fetchAll(PDO::FETCH_ASSOC);
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
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Sora', sans-serif;
    overflow-x: hidden;
    min-height: 100vh;
    position: relative;
}

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

#toggleBtn, #toggleTNBtn {
    position: fixed; top: 14px; left: 14px;
    width: 38px !important; height: 38px !important;
    padding: 0 !important; border: none;
    border-radius: 8px !important;
    background: #1e2130; color: #94a3b8;
    font-size: 18px !important; line-height: 38px !important;
    text-align: center; cursor: pointer; z-index: 1100;
}

/* CONTENIDO */
.content-area { margin-left: 260px; padding: 28px; min-height: 100vh; }

/* HEADER */
.page-header {
    display: flex; justify-content: space-between;
    align-items: center; gap: 12px;
    margin-bottom: 22px; flex-wrap: wrap;
}

.page-title {
    color: #fff; font-size: 20px; font-weight: 600;
    display: flex; align-items: center; gap: 10px; margin: 0;
}
.page-title-dot {
    width: 8px; height: 8px;
    border-radius: 50%; background: #6ee7b7; flex-shrink: 0;
}

.badge-count {
    font-size: 11px; color: #6ee7b7;
    background: rgba(110,231,183,.10);
    border: 1px solid rgba(110,231,183,.18);
    padding: 5px 12px; border-radius: 999px;
    display: inline-flex; align-items: center; gap: 6px;
}
.badge-count svg { width: 13px; height: 13px; }

/* FORM CARD */
.form-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 18px; padding: 18px;
    box-shadow: 0 14px 35px rgba(0,0,0,.25);
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
    grid-template-columns: 1fr 1fr 1fr 180px auto;
    gap: 10px;
    align-items: end;
}

/* Input con ícono */
.input-wrap {
    position: relative; display: flex; align-items: center;
}
.input-wrap svg {
    position: absolute; left: 12px;
    width: 15px; height: 15px;
    stroke: #475569; pointer-events: none; flex-shrink: 0;
}

.form-card input[type="text"],
.form-card input[type="email"],
.form-card input[type="password"] {
    width: 100%; height: 42px;
    border: none; outline: none;
    border-radius: 10px; background: #0f1117;
    color: #f1f5f9; padding: 0 14px 0 38px;
    border: 1px solid rgba(255,255,255,.08);
    font-family: 'Sora', sans-serif; font-size: 13px;
    transition: border-color .2s;
}
.form-card input::placeholder { color: #475569; }
.form-card input:focus { border-color: #6ee7b7; outline: none; }

/* Select con ícono */
.select-wrap {
    position: relative; display: flex; align-items: center;
}
.select-wrap > svg {
    position: absolute; left: 12px;
    width: 15px; height: 15px;
    stroke: #475569; pointer-events: none; z-index: 1;
}

.form-card select,
.rol-select {
    width: 100%; height: 42px;
    border: none; outline: none;
    border-radius: 10px; background: #0f1117;
    color: #f1f5f9; padding: 0 14px 0 38px;
    border: 1px solid rgba(255,255,255,.08);
    font-family: 'Sora', sans-serif; font-size: 13px;
    appearance: none; cursor: pointer;
    transition: border-color .2s;
}
.form-card select:focus,
.rol-select:focus { border-color: #6ee7b7; outline: none; }
.form-card select option,
.rol-select option { background: #1e2130; color: #f1f5f9; }

/* Inline edit inputs (tabla) */
.inline-input {
    width: 100%; height: 34px;
    border: none; outline: none;
    border-radius: 8px; background: #0f1117;
    color: #f1f5f9; padding: 0 10px;
    border: 1px solid rgba(255,255,255,.08);
    font-family: 'Sora', sans-serif; font-size: 13px;
    transition: border-color .2s;
}
.inline-input:focus { border-color: #6ee7b7; }

/* BTN GUARDAR FORM */
.btn-save {
    width: 100%; height: 42px;
    border: none; border-radius: 10px;
    background: #6ee7b7; color: #064e3b;
    font-weight: 600; font-family: 'Sora', sans-serif;
    font-size: 13px; cursor: pointer;
    display: inline-flex; align-items: center;
    justify-content: center; gap: 7px;
    transition: opacity .2s; white-space: nowrap;
}
.btn-save svg { width: 15px; height: 15px; stroke: #064e3b; }
.btn-save:hover { opacity: .88; }

/* ROL BADGE (display) */
.rol-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 11px; border-radius: 999px;
    font-size: 11px; font-weight: 600;
}
.rol-badge svg { width: 11px; height: 11px; }
.rol-admin {
    background: rgba(99,102,241,.12); color: #a5b4fc;
    border: 1px solid rgba(99,102,241,.18);
}
.rol-admin svg { stroke: #a5b4fc; }
.rol-user {
    background: rgba(110,231,183,.10); color: #6ee7b7;
    border: 1px solid rgba(110,231,183,.18);
}
.rol-user svg { stroke: #6ee7b7; }

/* AVATAR */
.user-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: inline-flex; align-items: center;
    justify-content: center;
    font-size: 13px; font-weight: 600;
    flex-shrink: 0;
    background: rgba(110,231,183,.10);
    color: #6ee7b7;
    border: 1px solid rgba(110,231,183,.18);
}

.user-name-cell {
    display: flex; align-items: center; gap: 10px;
}

/* TABLE */
.table-card {
    background: #171922;
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 18px; overflow: hidden;
    box-shadow: 0 14px 35px rgba(0,0,0,.25);
}
.table-wrap { overflow: auto; }
.table { margin: 0; min-width: 900px; }

.table thead th {
    background: #1e2130 !important;
    color: #64748b !important;
    border: none !important;
    font-size: 11px; text-transform: uppercase;
    letter-spacing: .06em;
    padding: 14px 16px !important;
    white-space: nowrap;
}

.table tbody td {
    background: #171922 !important;
    color: #cbd5e1;
    border-top: 1px solid rgba(255,255,255,.04) !important;
    padding: 13px 16px !important;
    vertical-align: middle;
}
.table tbody tr:hover td { background: #1b1e28 !important; }

.td-id { color: #64748b; font-family: monospace; white-space: nowrap; }

/* BOTONES ACCIÓN */
.actions { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }

.btn-edit-row, .btn-delete-row {
    height: 32px; padding: 0 14px;
    border: none; border-radius: 8px;
    font-size: 11px; font-weight: 600;
    font-family: 'Sora', sans-serif;
    text-decoration: none;
    display: inline-flex; align-items: center;
    justify-content: center; gap: 5px;
    white-space: nowrap; cursor: pointer; transition: .2s;
}
.btn-edit-row svg, .btn-delete-row svg { width: 12px; height: 12px; }

.btn-edit-row {
    background: rgba(99,102,241,.12); color: #a5b4fc;
    border: 1px solid rgba(99,102,241,.18);
}
.btn-edit-row:hover { background: rgba(99,102,241,.22); color: #a5b4fc; }
.btn-edit-row.saving {
    background: rgba(110,231,183,.12); color: #6ee7b7;
    border-color: rgba(110,231,183,.2);
}
.btn-edit-row.ok {
    background: rgba(34,197,94,.12); color: #4ade80;
    border-color: rgba(34,197,94,.2);
}

.btn-delete-row {
    background: rgba(239,68,68,.12); color: #fca5a5;
    border: 1px solid rgba(239,68,68,.18);
}
.btn-delete-row:hover { background: rgba(239,68,68,.22); color: #fca5a5; }

/* EMPTY */
.empty-row td {
    padding: 3rem !important;
    text-align: center; color: #475569 !important; font-size: 13px;
}
.empty-icon { display: flex; flex-direction: column; align-items: center; gap: 10px; }
.empty-icon svg { width: 32px; height: 32px; stroke: #334155; }

/* TOAST */
.toast-msg {
    position: fixed; bottom: 24px; right: 24px;
    background: #1e2130;
    border: 1px solid rgba(110,231,183,.25);
    color: #6ee7b7; padding: 10px 18px;
    border-radius: 12px; font-size: 13px; font-weight: 500;
    display: flex; align-items: center; gap: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
    z-index: 9999; opacity: 0; transform: translateY(10px);
    transition: all .3s; pointer-events: none;
}
.toast-msg svg { width: 15px; height: 15px; }
.toast-msg.show { opacity: 1; transform: translateY(0); }

/* RESPONSIVE */
@media (max-width: 1200px) {
    .form-grid { grid-template-columns: 1fr 1fr 1fr; }
    .form-grid .btn-save { grid-column: 1 / -1; }
}
@media (max-width: 768px) {
    .content-area { margin-left: 0; padding: 15px; }
    .form-grid { grid-template-columns: 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; }
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
            Usuarios
        </h2>
        <span class="badge-count">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                <polyline points="2 17 12 22 22 17"/>
                <polyline points="2 12 12 17 22 12"/>
            </svg>
            <span><?= count($rows) ?> registros</span>
        </span>
    </div>

    <!-- FORMULARIO -->
    <form action="../controllers/usuariosController.php" method="POST" class="form-card">
        <input type="hidden" name="accion" value="guardar">

        <p class="form-card-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8"  y1="12" x2="16" y2="12"/>
            </svg>
            Nuevo usuario
        </p>

        <div class="form-grid">

            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <input type="text" name="nombre" placeholder="Nombre completo" required>
            </div>

            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input type="email" name="correo" placeholder="Correo electrónico" required>
            </div>

            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" name="pass" placeholder="Contraseña" required>
            </div>

            <div class="select-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <select name="rol" class="rol-select">
                    <option>Administrador</option>
                    <option>Usuario</option>
                </select>
            </div>

            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Guardar
            </button>

        </div>
    </form>

    <!-- TABLA -->
    <div class="table-card">
        <div class="table-wrap">
            <table class="table text-center align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($rows)): ?>
                <tr class="empty-row">
                    <td colspan="5">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            No hay usuarios registrados aún
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($rows as $row): ?>
                <tr>

                    <td class="td-id">
                        <?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?>
                    </td>

                    <!-- NOMBRE -->
                    <td>
                        <div class="user-name-cell" style="justify-content:center;">
                            <span class="user-avatar">
                                <?= mb_strtoupper(mb_substr($row['nombre'], 0, 1)) ?>
                            </span>
                            <input type="text"
                                   class="inline-input nombre"
                                   value="<?= htmlspecialchars($row['nombre']) ?>">
                        </div>
                    </td>

                    <!-- CORREO -->
                    <td>
                        <input type="email"
                               class="inline-input correo"
                               value="<?= htmlspecialchars($row['correo']) ?>">
                    </td>

                    <!-- ROL -->
                    <td>
                        <div class="select-wrap" style="justify-content:center;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <select class="rol-select rol" style="max-width:180px;">
                                <option <?= $row['rol'] === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                                <option <?= $row['rol'] === 'Usuario'       ? 'selected' : '' ?>>Usuario</option>
                            </select>
                        </div>
                    </td>

                    <!-- ACCIONES -->
                    <td>
                        <div class="actions">

                            <button class="btn-edit-row guardar" data-id="<?= $row['id'] ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Editar
                            </button>

                            <a href="../controllers/usuariosController.php?eliminar=<?= $row['id'] ?>"
                               class="btn-delete-row"
                               onclick="return confirm('¿Eliminar este usuario?')">
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
    <span id="toast-text">Usuario actualizado</span>
</div>

<script>
/* ── SVGs dinámicos ── */
var svgEdit = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
var svgCheck = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

/* ── Toast ── */
function showToast(msg, isError) {
    var t = document.getElementById("toast");
    document.getElementById("toast-text").textContent = msg || "Usuario actualizado";
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

/* ── Editar usuario ── */
document.querySelectorAll(".guardar").forEach(function(btn) {

    btn.addEventListener("click", function() {

        var self  = this;
        var fila  = this.closest("tr");
        var id     = this.dataset.id;
        var nombre = fila.querySelector(".nombre").value;
        var correo = fila.querySelector(".correo").value;
        var rol    = fila.querySelector(".rol").value;

        /* Feedback visual mientras guarda */
        self.innerHTML = svgCheck + " Guardando...";
        self.classList.add("saving");
        self.disabled = true;

        fetch("../controllers/usuariosController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "accion=editar&id=" + id +
                  "&nombre=" + encodeURIComponent(nombre) +
                  "&correo=" + encodeURIComponent(correo) +
                  "&rol="    + encodeURIComponent(rol)
        })
        .then(function(res) { return res.text(); })
        .then(function(data) {

            if (data.trim() === "ok") {

                /* Actualizar avatar con primera letra del nuevo nombre */
                var avatar = fila.querySelector(".user-avatar");
                if (avatar) {
                    avatar.textContent = nombre.charAt(0).toUpperCase();
                }

                self.innerHTML = svgCheck + " Guardado";
                self.classList.remove("saving");
                self.classList.add("ok");

                showToast("Usuario actualizado correctamente");

                setTimeout(function() {
                    self.innerHTML = svgEdit + " Editar";
                    self.classList.remove("ok");
                    self.disabled = false;
                }, 1800);

            } else {
                showToast("Error al actualizar", true);
                self.innerHTML = svgEdit + " Editar";
                self.classList.remove("saving");
                self.disabled = false;
            }
        })
        .catch(function() {
            showToast("Error de conexión", true);
            self.innerHTML = svgEdit + " Editar";
            self.classList.remove("saving");
            self.disabled = false;
        });

    });

});
</script>

</body>
</html>