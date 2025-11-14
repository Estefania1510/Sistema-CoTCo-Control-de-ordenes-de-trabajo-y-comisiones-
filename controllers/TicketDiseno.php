<?php
require_once __DIR__ . '/../Librerias/tfpdf/tfpdf.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_GET['idNota'] ?? null;
if (!$idNota) {
  die("ID de nota no especificado.");
}

// CONSULTAS
$sql = "SELECT n.idNota, DATE_FORMAT(n.FechaRecepcion, '%d-%m-%Y') AS FechaRecepcion,
               n.Total, n.Anticipo, n.Resto, n.Descripcion,
               n.Comentario, c.NombreCliente, c.Telefono, c.Telefono2, c.Direccion,
               u.NombreUsuario AS RecepcionadoPor
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        INNER JOIN usuario u ON n.idUsuario = u.idUsuario
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$nota = $stmt->fetch(PDO::FETCH_ASSOC);


$sqlDis = "SELECT idDiseño, estatus, idDiseñador, CostoDiseño 
           FROM notadiseño 
           WHERE idNota = ?";
$stmt = $conn->prepare($sqlDis);
$stmt->execute([$idNota]);
$diseno = $stmt->fetch(PDO::FETCH_ASSOC);

$sqlMat = "SELECT Material, Cantidad, Precio, Subtotal 
           FROM material 
           WHERE idDiseño = ?";
$stmt = $conn->prepare($sqlMat);
$stmt->execute([$diseno['idDiseño']]);
$materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

//  PDF 
$pdf = new tFPDF('P','mm',[80,297]);
$pdf->AddPage();


$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
$pdf->AddFont('DejaVu','I','DejaVuSans-Oblique.ttf',true);
$pdf->AddFont('DejaVu','BI','DejaVuSans-BoldOblique.ttf',true);


$pdf->SetFont('DejaVu','',9);


// Encabezado 
$logo = __DIR__ . '/../Image/ICT_Negro.png';
if (file_exists($logo)) {
    $x = (80 - 25) / 2;
    $pdf->Image($logo, $x, 5, 25);
    $pdf->Ln(25);
} else {
    $pdf->SetFont('DejaVu','B',11);
    $pdf->Cell(0,5,'ICT',0,1,'C');
}

$pdf->SetFont('DejaVu','',8);
$pdf->Cell(0,4,'C. Iturbide Sur #6, Magdalena, Jal.',0,1,'C');
$pdf->Cell(0,4,'Tel. 3311901741',0,1,'C');
$pdf->Cell(0,4,'Horario: Lun-Vie 8:00 a 9:00 | Sab-Dom 9:00 a 3:00',0,1,'C');
$pdf->Ln(3);
$pdf->Cell(0,0,'--------------------------------------',0,1,'C');
$pdf->Ln(2);


// === TÍTULO ===
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(0,5,'ORDEN DE DISEÑO',0,1,'C');
$pdf->SetFont('DejaVu','B',12);
$pdf->Cell(0,6,'FOLIO: '.$nota['idNota'],0,1,'C');
$pdf->Ln(2);

$pdf->SetFont('DejaVu','',8);
$pdf->Cell(0,4,'Fecha: '.$nota['FechaRecepcion'],0,1);
$pdf->Cell(0,4,'Recepcionado por: '.$nota['RecepcionadoPor'],0,1);
$pdf->Ln(2);

// Cliente
$pdf->SetFont('DejaVu','B',8);
$pdf->Cell(0,5,'Cliente:',0,1);
$pdf->SetFont('DejaVu','',8);
$pdf->Cell(0,4,$nota['NombreCliente'],0,1);
$pdf->Cell(0,4,'Tel: '.$nota['Telefono'],0,1);
$pdf->MultiCell(0,4,$nota['Direccion']);
$pdf->Ln(2);

// Descripción 
$pdf->SetFont('DejaVu','B',8);
$pdf->Cell(0,5,'Descripción:',0,1);
$pdf->SetFont('DejaVu','',8);
$pdf->MultiCell(0,4,$nota['Descripcion']);
$pdf->Ln(2);

// Comentario 
if (!empty($nota['Comentario'])) {
    $pdf->SetFont('DejaVu','B',8);
    $pdf->Cell(0,5,'Comentarios:',0,1);
    $pdf->SetFont('DejaVu','',8);
    $pdf->MultiCell(0,4,trim($nota['Comentario']));
    $pdf->Ln(2);
}


// Materiales
if ($materiales) {
  $pdf->SetFont('DejaVu','B',8);
  $pdf->Cell(0,5,'Materiales:',0,1);
  $pdf->SetFont('DejaVu','',8);

  foreach ($materiales as $m) {
      if ($nota['Total'] == 0) {
          $line = sprintf("%s x%s", $m['Material'], $m['Cantidad']);
      } else {
          $line = sprintf("%s x%s  $%0.2f", $m['Material'], $m['Cantidad'], $m['Precio']);
      }
      $pdf->MultiCell(0,4,trim($line));
  }
  $pdf->Ln(2);
}

// Costos 
$pdf->Cell(0,0,'--------------------------------------',0,1,'C');
$pdf->Ln(2);

$sinCostos = ($nota['Total'] <= 0 && $diseno['CostoDiseño'] <= 0);

if ($sinCostos) {
    $pdf->SetFont('DejaVu','I',8);
    $pdf->Cell(0,5,'COTIZACIÓN PENDIENTE',0,1,'C');

    if ($nota['Anticipo'] > 0) {
        $pdf->SetFont('DejaVu','',8);
        $pdf->Cell(0,4,'Anticipo: $'.number_format($nota['Anticipo'],2),0,1,'C');
    }
} else {
    $pdf->SetFont('DejaVu','',8);

    if (!empty($diseno['CostoDiseño'])) {
        $pdf->Cell(0,4,'Costo Diseño: $'.number_format($diseno['CostoDiseño'],2),0,1);
    }

    $pdf->Cell(0,4,'Total: $'.number_format($nota['Total'],2),0,1);
    $pdf->Cell(0,4,'Anticipo: $'.number_format($nota['Anticipo'],2),0,1);
    $pdf->Cell(0,4,'Restante: $'.number_format($nota['Resto'],2),0,1);
}



$pdf->Ln(4);
$pdf->Cell(0,0,'--------------------------------------',0,1,'C');
$pdf->Ln(3);
$pdf->SetFont('DejaVu','I',8);
$pdf->Cell(0,4,'Gracias por su preferencia.',0,1,'C');
$pdf->Cell(0,4,'Por favor conserve este ticket.',0,1,'C');


$pdf->Output('I','Ticket_Diseno_'.$nota['idNota'].'.pdf');
exit;
