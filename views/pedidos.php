<?php
session_start();
require "../config/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
}

$platos = $conexion->query("SELECT * FROM platos")->fetchAll();
$salas = $conexion->query("SELECT * FROM salas")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container-fluid">
<div class="row">

<div class="col-md-2 p-0">
    <?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Nuevo Pedido</h2>

<!-- DATOS DEL PEDIDO -->
<div class="row mb-3">
    <div class="col-md-4">
        <select id="sala" class="form-control">
            <option value="">Seleccione sala</option>
            <?php foreach($salas as $s): ?>
                <option value="<?= $s['id'] ?>"><?= $s['nombre'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <input type="number" id="mesa" class="form-control" placeholder="Número de mesa">
    </div>
</div>

<!-- PLATOS -->
<div class="row">
<?php foreach($platos as $p): ?>
    <div class="col-md-3 mb-3">
        <div class="card p-2">
            <h5><?= $p['nombre'] ?></h5>
            <p>S/ <?= $p['precio'] ?></p>
            <button class="btn btn-primary"
                onclick="agregar(<?= $p['id'] ?>, '<?= $p['nombre'] ?>', <?= $p['precio'] ?>)">
                Agregar
            </button>
        </div>
    </div>
<?php endforeach; ?>
</div>

<hr>

<!-- CARRITO -->
<h4>Detalle del Pedido</h4>

<table class="table" id="tabla">
    <thead>
        <tr>
            <th>Plato</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Total</th>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<h4>Total: S/ <span id="total">0</span></h4>

<button class="btn btn-success" onclick="guardarPedido()">Guardar Pedido</button>

</div>
</div>
</div>

<script src="../assets/js/pedidos.js"></script>

</body>
</html>