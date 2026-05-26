<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); exit;
}
if ($_SESSION['rol'] !== 'Administrador') {
    header("Location: login.php"); exit;
}

// Cambiar estado con motivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id'])) {
    $id     = (int)$_POST['id'];
    $accion = $_POST['accion'];
    $motivo = trim($_POST['motivo'] ?? '');

    $nuevoEstado = match($accion) {
        'confirmar' => 'CONFIRMADA',
        'cancelar'  => 'CANCELADA',
        'liberar'   => 'CANCELADA',
        default     => null
    };

    if ($nuevoEstado) {
        $conexion->prepare("UPDATE reservas SET estado = ?, motivo = ? WHERE id = ?")
                 ->execute([$nuevoEstado, $motivo ?: null, $id]);
    }

    header("Location: reservas.php"); exit;
}

$reservas = $conexion->query("
    SELECT r.*, s.nombre AS sala_nombre
    FROM reservas r
    JOIN salas s ON s.id = r.id_sala
    ORDER BY r.estado ASC, r.fecha ASC, r.hora ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reservas</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family:'Sora',sans-serif;
    overflow-x:hidden;
    min-height:100vh;
    position:relative;
}

body::before {
    content:"";
    position:fixed; inset:0;
background: url('../assets/img/fnd.jpg') no-repeat center center fixed;
background-size: cover;
    z-index:-2;
}
body::after {
    content:"";
    position:fixed; inset:0;
    background:rgba(0,0,0,.35);
    z-index:-1;
}

#sidebar {
    position:fixed; top:0; left:0;
    width:260px; height:100vh;
    background:#0d0f18;
    border-right:1px solid rgba(255,255,255,.05);
    z-index:200;
}

#toggleBtn, #toggleTNBtn {
    position:fixed; top:14px; left:14px;
    width:38px !important; height:38px !important;
    padding:0 !important; border:none;
    border-radius:8px !important;
    background:#1e2130; color:#94a3b8;
    font-size:18px !important; line-height:38px !important;
    text-align:center; cursor:pointer; z-index:1100;
}

.content-area { margin-left:260px; padding:28px; min-height:100vh; }

.page-header {
    display:flex; justify-content:space-between;
    align-items:center; margin-bottom:20px;
    gap:15px; flex-wrap:wrap;
}

.page-title {
    color:#fff; font-size:20px; font-weight:600;
    display:flex; align-items:center; gap:10px; margin:0;
}
.page-title-dot {
    width:8px; height:8px;
    border-radius:50%; background:#6ee7b7;
    flex-shrink:0;
}

.count-badge {
    font-size:11px; color:#6ee7b7;
    background:rgba(110,231,183,.10);
    border:1px solid rgba(110,231,183,.18);
    padding:5px 12px; border-radius:999px;
    display:flex; align-items:center; gap:6px;
}

/* FILTROS */
.filtros-wrap {
    display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px;
}
.filtro-btn {
    height:34px; padding:0 16px;
    border-radius:999px;
    font-family:'Sora',sans-serif;
    font-size:12px; font-weight:500;
    cursor:pointer;
    background:#1e2130; color:#94a3b8;
    border:1px solid rgba(255,255,255,.06);
    transition:all .2s;
    display:inline-flex; align-items:center; gap:6px;
}
.filtro-btn svg { width:13px; height:13px; flex-shrink:0; }
.filtro-btn:hover { background:#252836; color:#cbd5e1; }
.filtro-btn.active {
    background:#6ee7b7; color:#064e3b;
    border-color:#6ee7b7;
    box-shadow:0 4px 14px rgba(110,231,183,.25);
}
.filtro-btn.active svg { stroke:#064e3b; }

/* TABLA */
.table-card {
    background:#171922;
    border:1px solid rgba(255,255,255,.06);
    border-radius:18px; overflow:hidden;
    box-shadow:0 14px 35px rgba(0,0,0,.25);
}
.table-wrapper { overflow:auto; }
.table { margin:0; min-width:1020px; }

.table thead th {
    background:#1e2130 !important;
    color:#64748b !important;
    font-size:11px; text-transform:uppercase;
    letter-spacing:.06em;
    border:none !important;
    padding:14px 16px !important;
    white-space:nowrap;
}
.table tbody td {
    background:#171922 !important;
    color:#cbd5e1;
    border-top:1px solid rgba(255,255,255,.04) !important;
    padding:13px 16px !important;
    vertical-align:middle; white-space:nowrap;
}
.table tbody tr:hover td { background:#1b1e28 !important; }
.td-id { color:#64748b; font-family:monospace; }

/* BADGES */
.badge-modern {
    font-size:11px; font-weight:600;
    padding:5px 11px; border-radius:999px;
    letter-spacing:.02em; white-space:nowrap;
    display:inline-flex; align-items:center; gap:5px;
}
.badge-modern svg { width:10px; height:10px; flex-shrink:0; }

.pending   { background:rgba(251,191,36,.12); color:#facc15; border:1px solid rgba(251,191,36,.18); }
.confirmed { background:rgba(34,197,94,.12);  color:#4ade80; border:1px solid rgba(34,197,94,.18); }
.cancelled { background:rgba(239,68,68,.12);  color:#f87171; border:1px solid rgba(239,68,68,.18); }

.td-nota, .td-motivo {
    max-width:160px;
    overflow:hidden; text-overflow:ellipsis;
    white-space:nowrap; font-size:12px;
}
.td-nota   { color:#94a3b8; }
.td-motivo { color:#f87171; }

/* BOTONES ACCIÓN */
.btn-confirmar, .btn-cancelar, .btn-liberar {
    height:30px; padding:0 12px; border:none;
    border-radius:8px; font-size:11px; font-weight:600;
    font-family:'Sora',sans-serif;
    cursor:pointer; text-decoration:none;
    display:inline-flex; align-items:center; gap:5px;
    white-space:nowrap; transition:.2s;
}
.btn-confirmar svg, .btn-cancelar svg, .btn-liberar svg {
    width:12px; height:12px; flex-shrink:0;
}
.btn-confirmar { background:rgba(110,231,183,.12); color:#6ee7b7; border:1px solid rgba(110,231,183,.2); }
.btn-confirmar:hover { background:rgba(110,231,183,.22); color:#6ee7b7; }
.btn-cancelar  { background:rgba(239,68,68,.12);   color:#f87171; border:1px solid rgba(239,68,68,.2); }
.btn-cancelar:hover  { background:rgba(239,68,68,.22);   color:#f87171; }
.btn-liberar   { background:rgba(251,191,36,.1);   color:#facc15; border:1px solid rgba(251,191,36,.2); }
.btn-liberar:hover   { background:rgba(251,191,36,.2);   color:#facc15; }

.acciones-wrap { display:flex; gap:6px; justify-content:center; flex-wrap:wrap; }

.empty-row td {
    padding:3rem !important;
    text-align:center;
    color:#475569 !important;
    font-size:13px;
}
.empty-icon {
    display:flex; flex-direction:column;
    align-items:center; gap:10px;
}
.empty-icon svg { width:32px; height:32px; stroke:#334155; }

/* PERSONAS CELL */
.personas-cell {
    display:inline-flex; align-items:center; gap:5px;
}
.personas-cell svg { width:13px; height:13px; stroke:#64748b; }

/* NOTA CELL */
.nota-cell-inner {
    display:inline-flex; align-items:center; gap:5px;
}
.nota-cell-inner svg { width:12px; height:12px; stroke:#64748b; flex-shrink:0; }

/* MOTIVO CELL */
.motivo-cell-inner {
    display:inline-flex; align-items:center; gap:5px;
}
.motivo-cell-inner svg { width:12px; height:12px; stroke:#f87171; flex-shrink:0; }

/* ── MODAL ── */
.modal-overlay {
    display:none;
    position:fixed; inset:0;
    background:rgba(0,0,0,.65);
    backdrop-filter:blur(4px);
    z-index:2000;
    align-items:center;
    justify-content:center;
}
.modal-overlay.visible { display:flex; }

.modal-box {
    background:#171922;
    border:1px solid rgba(255,255,255,.08);
    border-radius:18px;
    padding:28px;
    width:100%;
    max-width:440px;
    margin:1rem;
    box-shadow:0 24px 60px rgba(0,0,0,.5);
    animation: modalIn .25s ease;
}

@keyframes modalIn {
    from { opacity:0; transform:translateY(16px) scale(.97); }
    to   { opacity:1; transform:translateY(0)    scale(1);   }
}

.modal-header {
    display:flex; align-items:center;
    justify-content:space-between;
    margin-bottom:18px;
}
.modal-title {
    color:#f1f5f9; font-size:16px; font-weight:600;
    display:flex; align-items:center; gap:8px;
}
.modal-title svg { width:18px; height:18px; flex-shrink:0; }
.modal-close {
    background:none; border:none; color:#64748b;
    cursor:pointer; line-height:1;
    transition:color .2s;
    display:flex; align-items:center; justify-content:center;
    width:28px; height:28px; border-radius:6px;
}
.modal-close:hover { color:#f1f5f9; background:rgba(255,255,255,.06); }
.modal-close svg { width:16px; height:16px; }

.modal-info {
    background:#1e2130;
    border-radius:10px; padding:12px 14px;
    margin-bottom:16px; font-size:13px; color:#94a3b8;
    line-height:1.6;
    display:flex; align-items:center; gap:10px;
}
.modal-info-icon { flex-shrink:0; }
.modal-info-icon svg { width:16px; height:16px; stroke:#64748b; }
.modal-info strong { color:#e2e8f0; }

.modal-label {
    font-size:11px; font-weight:600;
    text-transform:uppercase; letter-spacing:.08em;
    color:#64748b; margin-bottom:6px; display:flex;
    align-items:center; gap:5px;
}
.modal-label svg { width:12px; height:12px; }

.modal-textarea {
    width:100%; min-height:90px;
    background:#0f1117; color:#e2e8f0;
    border:1px solid rgba(255,255,255,.08);
    border-radius:10px; padding:10px 14px;
    font-family:'Sora',sans-serif; font-size:13px;
    resize:vertical; outline:none;
    transition:border-color .2s;
}
.modal-textarea:focus { border-color:#6ee7b7; }
.modal-textarea::placeholder { color:#475569; }

.modal-hint {
    font-size:11px; color:#475569; margin-top:6px;
    display:flex; align-items:center; gap:4px;
}
.modal-hint svg { width:11px; height:11px; stroke:#475569; }

.modal-footer {
    display:flex; gap:8px; justify-content:flex-end;
    margin-top:20px;
}
.modal-btn {
    height:36px; padding:0 18px; border:none;
    border-radius:10px; font-family:'Sora',sans-serif;
    font-size:13px; font-weight:600; cursor:pointer;
    transition:.2s; display:inline-flex; align-items:center; gap:6px;
}
.modal-btn svg { width:14px; height:14px; flex-shrink:0; }
.modal-btn-cancel {
    background:#1e2130; color:#94a3b8;
    border:1px solid rgba(255,255,255,.06);
}
.modal-btn-cancel:hover { background:#252836; color:#cbd5e1; }
.modal-btn-confirm {
    background:#6ee7b7; color:#064e3b;
}
.modal-btn-confirm:hover { opacity:.88; }
.modal-btn-danger {
    background:rgba(239,68,68,.2); color:#f87171;
    border:1px solid rgba(239,68,68,.25);
}
.modal-btn-danger:hover { background:rgba(239,68,68,.32); }

@media(max-width:768px) {
    .content-area { margin-left:0; padding:15px; }
    .page-header { flex-direction:column; align-items:flex-start; }
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
            Reservas
        </h2>
        <span class="count-badge" id="countBadge">
            <!-- layers icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="#6ee7b7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                <polyline points="2 17 12 22 22 17"/>
                <polyline points="2 12 12 17 22 12"/>
            </svg>
            <span><?= count($reservas) ?> registros</span>
        </span>
    </div>

    <!-- FILTROS -->
    <div class="filtros-wrap">
        <button class="filtro-btn active" onclick="filtrar('todos', this)">
            <!-- grid icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Todos
        </button>
        <button class="filtro-btn" onclick="filtrar('PENDIENTE', this)">
            <!-- clock icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            Pendientes
        </button>
        <button class="filtro-btn" onclick="filtrar('CONFIRMADA', this)">
            <!-- check-circle icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            Confirmadas
        </button>
        <button class="filtro-btn" onclick="filtrar('CANCELADA', this)">
            <!-- x-circle icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            Canceladas
        </button>
    </div>

    <div class="table-card">
        <div class="table-wrapper">
            <table class="table text-center align-middle" id="tablaReservas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Sala</th>
                        <th>Mesa</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Personas</th>
                        <th>Nota cliente</th>
                        <th>Motivo admin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (empty($reservas)): ?>
                <tr class="empty-row">
                    <td colspan="11">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8"  y1="2" x2="8"  y2="6"/>
                                <line x1="3"  y1="10" x2="21" y2="10"/>
                            </svg>
                            No hay reservas registradas aún
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($reservas as $r): ?>
                <tr data-estado="<?= $r['estado'] ?>">

                    <td class="td-id"><?= str_pad($r['id'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($r['usuario']) ?></td>
                    <td><?= htmlspecialchars($r['sala_nombre']) ?></td>
                    <td>Mesa <?= $r['num_mesa'] ?></td>
                    <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                    <td><?= substr($r['hora'], 0, 5) ?></td>

                    <td>
                        <span class="personas-cell">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <?= $r['personas'] ?>
                        </span>
                    </td>

                    <td class="td-nota" title="<?= htmlspecialchars($r['nota'] ?? '') ?>">
                        <?php if ($r['nota']): ?>
                        <span class="nota-cell-inner">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            <?= htmlspecialchars($r['nota']) ?>
                        </span>
                        <?php else: ?>
                            <span style="color:#334155">—</span>
                        <?php endif; ?>
                    </td>

                    <td class="td-motivo" title="<?= htmlspecialchars($r['motivo'] ?? '') ?>">
                        <?php if ($r['motivo']): ?>
                        <span class="motivo-cell-inner">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <?= htmlspecialchars($r['motivo']) ?>
                        </span>
                        <?php else: ?>
                            <span style="color:#334155">—</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($r['estado'] === 'PENDIENTE'): ?>
                            <span class="badge-modern pending">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                Pendiente
                            </span>
                        <?php elseif ($r['estado'] === 'CONFIRMADA'): ?>
                            <span class="badge-modern confirmed">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Confirmada
                            </span>
                        <?php else: ?>
                            <span class="badge-modern cancelled">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6"  y1="6" x2="18" y2="18"/>
                                </svg>
                                Cancelada
                            </span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="acciones-wrap">
                        <?php if ($r['estado'] === 'PENDIENTE'): ?>

                            <button class="btn-confirmar"
                                onclick="abrirModal('confirmar', <?= $r['id'] ?>,
                                    '<?= addslashes($r['sala_nombre']) ?>',
                                    <?= $r['num_mesa'] ?>,
                                    '<?= addslashes($r['usuario']) ?>')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Confirmar
                            </button>

                            <button class="btn-cancelar"
                                onclick="abrirModal('cancelar', <?= $r['id'] ?>,
                                    '<?= addslashes($r['sala_nombre']) ?>',
                                    <?= $r['num_mesa'] ?>,
                                    '<?= addslashes($r['usuario']) ?>')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6"  y1="6" x2="18" y2="18"/>
                                </svg>
                                Cancelar
                            </button>

                        <?php elseif ($r['estado'] === 'CONFIRMADA'): ?>

                            <button class="btn-liberar"
                                onclick="abrirModal('liberar', <?= $r['id'] ?>,
                                    '<?= addslashes($r['sala_nombre']) ?>',
                                    <?= $r['num_mesa'] ?>,
                                    '<?= addslashes($r['usuario']) ?>')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 9.9-1"/>
                                </svg>
                                Liberar mesa
                            </button>

                        <?php else: ?>
                            <span style="color:#334155;font-size:12px;">—</span>
                        <?php endif; ?>
                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>

                <tr id="emptyFiltro" style="display:none;" class="empty-row">
                    <td colspan="11">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                <line x1="8"  y1="11" x2="14" y2="11"/>
                            </svg>
                            No hay reservas en esta categoría
                        </div>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ══ MODAL ══ -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">

        <div class="modal-header">
            <span class="modal-title" id="modalTitle">Confirmar acción</span>
            <button class="modal-close" onclick="cerrarModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6"  y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="modal-info" id="modalInfo">
            <span class="modal-info-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </span>
            <span id="modalInfoText"></span>
        </div>

        <form method="POST" id="modalForm">
            <input type="hidden" name="accion" id="modalAccion">
            <input type="hidden" name="id"     id="modalId">

            <label class="modal-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Mensaje para el cliente
            </label>
            <textarea
                name="motivo"
                id="modalMotivo"
                class="modal-textarea"
                placeholder="Ej: Su mesa está lista, lo esperamos. / Lo sentimos, no contamos con disponibilidad para esa cantidad de personas...">
            </textarea>
            <p class="modal-hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Opcional — el cliente verá este mensaje en sus reservas.
            </p>

            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="cerrarModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6"  y1="6" x2="18" y2="18"/>
                    </svg>
                    Cancelar
                </button>
                <button type="submit" class="modal-btn" id="modalBtnConfirm">
                    Confirmar
                </button>
            </div>
        </form>

    </div>
</div>

<script>
/* ── SVGs reutilizables para el modal ── */
var svgCheck = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
var svgX     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
var svgLock  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>';

var svgTitleCheck = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6ee7b7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
var svgTitleX     = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
var svgTitleLock  = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#facc15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>';

/* ── Filtrar ── */
function filtrar(estado, btn) {
    document.querySelectorAll('.filtro-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');

    var filas = document.querySelectorAll('#tablaReservas tbody tr[data-estado]');
    var visibles = 0;

    filas.forEach(function(fila) {
        var mostrar = estado === 'todos' || fila.dataset.estado === estado;
        fila.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
    });

    document.getElementById('emptyFiltro').style.display = visibles === 0 ? '' : 'none';
    document.getElementById('countBadge').querySelector('span').textContent = visibles + ' registros';
}

/* ── Config modal ── */
var textos = {
    confirmar: {
        icono:    svgTitleCheck,
        titulo:   'Confirmar reserva',
        btnClass: 'modal-btn-confirm',
        btnSvg:   svgCheck,
        btnText:  'Confirmar reserva'
    },
    cancelar: {
        icono:    svgTitleX,
        titulo:   'Cancelar reserva',
        btnClass: 'modal-btn-danger',
        btnSvg:   svgX,
        btnText:  'Cancelar reserva'
    },
    liberar: {
        icono:    svgTitleLock,
        titulo:   'Liberar mesa',
        btnClass: 'modal-btn-danger',
        btnSvg:   svgLock,
        btnText:  'Liberar mesa'
    }
};

function abrirModal(accion, id, sala, mesa, usuario) {
    var cfg = textos[accion];

    document.getElementById('modalTitle').innerHTML = cfg.icono + ' ' + cfg.titulo;
    document.getElementById('modalAccion').value    = accion;
    document.getElementById('modalId').value        = id;
    document.getElementById('modalMotivo').value    = '';

    document.getElementById('modalInfoText').innerHTML =
        '<strong>' + sala + '</strong> — Mesa ' + mesa +
        ' &nbsp;·&nbsp; Cliente: <strong>' + usuario + '</strong>';

    var btn = document.getElementById('modalBtnConfirm');
    btn.className = 'modal-btn ' + cfg.btnClass;
    btn.innerHTML = cfg.btnSvg + ' ' + cfg.btnText;

    document.getElementById('modalOverlay').classList.add('visible');
    document.getElementById('modalMotivo').focus();
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('visible');
}

// Cerrar con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModal();
});

// Cerrar al hacer clic fuera
document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>

</body>
</html>