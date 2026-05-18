<?php
require "../config/conexion.php";
require "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$id = (int)($_GET['id'] ?? 0);

$stmt = $conexion->prepare("SELECT * FROM pedidos_web WHERE id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

$items = $conexion->prepare("SELECT * FROM detalle_pedidos_web WHERE id_pedido = ?");
$items->execute([$id]);
$detalle = $items->fetchAll(PDO::FETCH_ASSOC);

$config = $conexion->query("SELECT * FROM config LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$iconoMetodo = ['EFECTIVO'=>'Efectivo','YAPE'=>'Yape','PLIN'=>'Plin','TARJETA'=>'Tarjeta'][$pedido['metodo_pago']] ?? $pedido['metodo_pago'];

$filas = '';
foreach ($detalle as $item) {
    $subtotal = number_format($item['precio'] * $item['cantidad'], 2);
    $filas .= "
    <tr>
        <td>{$item['nombre']}</td>
        <td style='text-align:center'>{$item['cantidad']}</td>
        <td style='text-align:right'>S/ " . number_format($item['precio'], 2) . "</td>
        <td style='text-align:right'>S/ {$subtotal}</td>
    </tr>";
}

$igv     = number_format($pedido['total'] * 0.18, 2);
$subtotalFinal = number_format($pedido['total'] * 0.82, 2);
$total   = number_format($pedido['total'], 2);
$numBoleta = str_pad($pedido['id'], 6, '0', STR_PAD_LEFT);

$html = "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; padding: 30px; }

    .header { text-align:center; margin-bottom:20px; border-bottom: 2px solid #92400e; padding-bottom:16px; }
    .header h1 { font-size:22px; color:#92400e; }
    .header p  { font-size:11px; color:#555; margin-top:3px; }

    .boleta-num {
        text-align:center; margin:14px 0;
        font-size:13px; font-weight:bold; color:#444;
        background:#fef3c7; padding:8px; border-radius:6px;
        border:1px solid #fcd34d;
    }

    .section { margin-bottom:14px; }
    .section-title {
        font-size:10px; text-transform:uppercase; letter-spacing:.06em;
        color:#92400e; font-weight:bold; margin-bottom:6px;
        border-bottom:1px solid #e5e7eb; padding-bottom:4px;
    }
    .info-row { display:flex; justify-content:space-between; margin-bottom:3px; font-size:11px; }
    .info-row span:last-child { font-weight:600; }

    table { width:100%; border-collapse:collapse; margin-top:6px; }
    thead th {
        background:#92400e; color:#fff;
        padding:7px 8px; font-size:11px; text-align:left;
    }
    tbody td { padding:7px 8px; border-bottom:1px solid #f0ece4; font-size:11px; }
    tbody tr:last-child td { border-bottom:none; }

    .totales { margin-top:12px; }
    .total-row { display:flex; justify-content:space-between; padding:3px 0; font-size:11px; color:#555; }
    .total-final { display:flex; justify-content:space-between; padding:8px 0 0; font-size:15px; font-weight:bold; color:#92400e; border-top:2px solid #92400e; margin-top:6px; }

    .footer { text-align:center; margin-top:24px; font-size:10px; color:#888; border-top:1px dashed #ccc; padding-top:12px; }
    .footer strong { color:#92400e; }
</style>
</head>
<body>

<div class='header'>
    <h1>{$config['nombre']}</h1>
    <p>RUC: {$config['ruc']} | Tel: {$config['telefono']}</p>
    <p>{$config['direccion']}</p>
</div>

<div class='boleta-num'>BOLETA ELECTRÓNICA N° B001-{$numBoleta}</div>

<div class='section'>
    <div class='section-title'>Datos del cliente</div>
    <div class='info-row'><span>Nombre</span><span>{$pedido['nombre_cliente']}</span></div>
    <div class='info-row'><span>Teléfono</span><span>{$pedido['telefono']}</span></div>
    <div class='info-row'><span>Dirección</span><span>{$pedido['direccion']}</span></div>
</div>

<div class='section'>
    <div class='section-title'>Datos del pedido</div>
    <div class='info-row'><span>Fecha</span><span>{$pedido['fecha']}</span></div>
    <div class='info-row'><span>Hora</span><span>" . substr($pedido['hora'],0,5) . "</span></div>
    <div class='info-row'><span>Método de pago</span><span>{$iconoMetodo}</span></div>
</div>

<div class='section'>
    <div class='section-title'>Detalle</div>
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th style='text-align:center'>Cant.</th>
                <th style='text-align:right'>P. Unit.</th>
                <th style='text-align:right'>Subtotal</th>
            </tr>
        </thead>
        <tbody>{$filas}</tbody>
    </table>
</div>

<div class='totales'>
    <div class='total-row'><span>Subtotal (sin IGV)</span><span>S/ {$subtotalFinal}</span></div>
    <div class='total-row'><span>IGV (18%)</span><span>S/ {$igv}</span></div>
    <div class='total-final'><span>TOTAL</span><span>S/ {$total}</span></div>
</div>

<div class='footer'>
    <strong>{$config['mensaje']}</strong><br>
    Documento generado el " . date('d/m/Y H:i') . "
</div>

</body>
</html>";

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("boleta_{$numBoleta}.pdf", ['Attachment' => false]);