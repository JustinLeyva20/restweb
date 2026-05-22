<?php
require "../config/conexion.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| REGISTRAR / REEMPLAZAR PEDIDO DESDE JSON
|--------------------------------------------------------------------------
*/
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {

    $sala       = $data['sala'];
    $mesa       = $data['mesa'];
    $carrito    = $data['carrito'];
    $comentario = $data['comentario'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | BUSCAR PEDIDO PENDIENTE
    |--------------------------------------------------------------------------
    */
    $buscar = $conexion->prepare("
        SELECT * FROM pedidos
        WHERE id_sala=?
        AND num_mesa=?
        AND estado='PENDIENTE'
        LIMIT 1
    ");
    $buscar->execute([$sala, $mesa]);

    $pedido = $buscar->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | SI NO EXISTE -> CREAR
    |--------------------------------------------------------------------------
    */
    if (!$pedido) {

        $crear = $conexion->prepare("
            INSERT INTO pedidos
            (id_sala,num_mesa,total,usuario,comentario,estado,fecha)
            VALUES (?,?,0,?,?,'PENDIENTE',NOW())
            RETURNING id
        ");
        $crear->execute([
            $sala,
            $mesa,
            "ADMIN",
            $comentario
        ]);

        $idPedido = $crear->fetchColumn();

    } else {

        $idPedido = $pedido['id'];

        $upd = $conexion->prepare("
            UPDATE pedidos
            SET comentario=?
            WHERE id=?
        ");
        $upd->execute([$comentario, $idPedido]);
    }

    /*
    |--------------------------------------------------------------------------
    | LIMPIAR DETALLE ANTERIOR COMPLETO
    |--------------------------------------------------------------------------
    | Esto permite:
    | - eliminar productos borrados
    | - reemplazar cantidades
    | - dejar exactamente lo del carrito actual
    |--------------------------------------------------------------------------
    */
    $del = $conexion->prepare("
        DELETE FROM detalle_pedidos
        WHERE id_pedido=?
    ");
    $del->execute([$idPedido]);

    /*
    |--------------------------------------------------------------------------
    | INSERTAR NUEVO DETALLE
    |--------------------------------------------------------------------------
    */
    $total = 0;

    foreach ($carrito as $p) {

        $nombre   = $p['nombre'];
        $precio   = floatval($p['precio']);
        $cantidad = intval($p['cantidad']);

        $subtotal = $precio * $cantidad;
        $total += $subtotal;

        $ins = $conexion->prepare("
            INSERT INTO detalle_pedidos
            (nombre,precio,cantidad,id_pedido)
            VALUES (?,?,?,?)
        ");
        $ins->execute([
            $nombre,
            $precio,
            $cantidad,
            $idPedido
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR TOTAL
    |--------------------------------------------------------------------------
    */
    $upTotal = $conexion->prepare("
        UPDATE pedidos
        SET total=?
        WHERE id=?
    ");
    $upTotal->execute([$total, $idPedido]);

    echo "ok";
    exit;
}

/*
|--------------------------------------------------------------------------
| PAGAR PEDIDO
|--------------------------------------------------------------------------
*/
if (isset($_POST['accion']) && $_POST['accion'] == "pagar") {

    $id = $_POST['id'];

    $sql = $conexion->prepare("
        UPDATE pedidos
        SET estado='FINALIZADO'
        WHERE id=?
    ");
    $sql->execute([$id]);

    echo "ok";
    exit;
}

/*
|--------------------------------------------------------------------------
| CANCELAR PEDIDO
|--------------------------------------------------------------------------
*/
if (isset($_POST['accion']) && $_POST['accion'] == "cancelar") {

    $id = $_POST['id'];

    $sql = $conexion->prepare("
        UPDATE pedidos
        SET estado='CANCELADO'
        WHERE id=?
    ");
    $sql->execute([$id]);

    echo "ok";
    exit;
}

echo "error";
?>