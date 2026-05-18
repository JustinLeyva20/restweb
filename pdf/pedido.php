<?php
require("../config/conexion.php");
require("fpdf/fpdf.php");

$id = $_GET['id'];

# PEDIDO
$pedido = $conexion->prepare("
    SELECT p.*, s.nombre AS sala
    FROM pedidos p
    INNER JOIN salas s ON p.id_sala = s.id
    WHERE p.id=?
");
$pedido->execute([$id]);
$p = $pedido->fetch();

# DETALLE
$detalle = $conexion->prepare("
    SELECT * FROM detalle_pedidos WHERE id_pedido=?
");
$detalle->execute([$id]);
$detalles = $detalle->fetchAll();

$pdf = new FPDF();
$pdf->AddPage();

# =========================
# EMPRESA
# =========================
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,6,'RESTAURANTE LA DELICIA',0,1,'C');

$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,'RUC: 65479877',0,1,'C');
$pdf->Cell(0,5,'Telefono: 957847894',0,1,'C');
$pdf->Cell(0,5,'Direccion: Lima - Peru',0,1,'C');

$pdf->Ln(3);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,6,'BOLETA ELECTRONICA',0,1,'C');

$pdf->Ln(5);

# =========================
# DATOS DEL CLIENTE / PEDIDO
# =========================
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,'Pedido #: '.$id,0,1);
$pdf->Cell(0,5,'Fecha: '.$p['fecha'],0,1);
$pdf->Cell(0,5,'Sala: '.$p['sala'],0,1);
$pdf->Cell(0,5,'Mesa: '.$p['num_mesa'],0,1);
$pdf->Cell(0,5,'Estado: '.$p['estado'],0,1);

$pdf->Ln(3);

# =========================
# TABLA ENCABEZADO
# =========================
$pdf->SetFillColor(0,102,204);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',9);

$pdf->Cell(20,6,'Cant',1,0,'C',true);
$pdf->Cell(100,6,'Descripcion',1,0,'C',true);
$pdf->Cell(35,6,'P. Unit',1,0,'C',true);
$pdf->Cell(35,6,'Importe',1,1,'C',true);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',9);

# =========================
# DETALLE
# =========================
$total = 0;

foreach ($detalles as $d) {

    $sub = $d['precio'] * $d['cantidad'];
    $total += $sub;

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $pdf->Cell(20,6,$d['cantidad'],1);

    $pdf->MultiCell(100,6,$d['nombre'],1);

    $y2 = $pdf->GetY();
    $alto = $y2 - $y;

    $pdf->SetXY($x + 120, $y);

    $pdf->Cell(35,$alto,'S/ '.$d['precio'],1);
    $pdf->Cell(35,$alto,'S/ '.number_format($sub,2),1);

    $pdf->Ln();
}

$pdf->Ln(3);

# =========================
# TOTALES
# =========================
$pdf->SetFont('Arial','B',10);
$pdf->Cell(150,6,'TOTAL:',0,0,'R');
$pdf->Cell(35,6,'S/ '.number_format($total,2),0,1,'R');

$pdf->Ln(5);

$pdf->Output("D","boleta_".$id.".pdf");
?>