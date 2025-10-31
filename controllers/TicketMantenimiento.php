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

// CONSULTA NOTA 
$sql = "SELECT n.idNota, n.FechaRecepcion, n.Total, n.Anticipo, n.Resto, 
               n.Descripcion, n.Comentario, 
               c.NombreCliente, c.Telefono, c.Telefono2, c.Direccion,
               u.NombreUsuario AS RecepcionadoPor
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        INNER JOIN usuario u ON n.idUsuario = u.idUsuario
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$nota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nota) {
  die("No se encontró la nota.");
}

// CONSULTA MANTENIMIENTO 
$sqlMnt = "SELECT m.idMantenimiento, m.Equipo, m.Marca, m.Model, m.Contraseña, 
                  m.Accesorios, m.SugerenciaTecn, m.Estatus, 
                  t.NombreUsuario AS Tecnico
           FROM notamantenimiento m
           LEFT JOIN usuario t ON m.idTecnico = t.idUsuario
           WHERE m.idNota = ?";
$stmt = $conn->prepare($sqlMnt);
$stmt->execute([$idNota]);
$mantenimiento = $stmt->fetch(PDO::FETCH_ASSOC);

// CONSULTA SERVICIOS (ahora incluye precios)
$sqlServ = "SELECT tm.NombreTipo, c.Servicio, a.Precio
            FROM auxservicios a
            INNER JOIN catalogomnt c ON a.idCatalogoMnt = c.idCatalogoMnt
            INNER JOIN tipomantenimiento tm ON c.idTipoMnt = tm.idTipoMnt
            WHERE a.idMantenimiento = ?";
$stmt = $conn->prepare($sqlServ);
$stmt->execute([$mantenimiento['idMantenimiento'] ?? 0]);
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);


// === INICIO PDF ===
$pdf = new tFPDF('P','mm',[80,297]);
$pdf->AddPage();

$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
$pdf->AddFont('DejaVu','I','DejaVuSans-Oblique.ttf',true);
$pdf->SetFont('DejaVu','',9);

// LOGO
$logo = __DIR__ . '/../Image/ICT_NEGRO.png';
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

// ENCABEZADO
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(0,5,'ORDEN DE MANTENIMIENTO',0,1,'C');
$pdf->SetFont('DejaVu','B',12);
$pdf->Cell(0,6,'FOLIO: '.$nota['idNota'],0,1,'C');
$pdf->Ln(2);

$pdf->SetFont('DejaVu','',8);
$pdf->Cell(0,4,'Fecha: '.$nota['FechaRecepcion'],0,1);
$pdf->Cell(0,4,'Recepcionado por: '.$nota['RecepcionadoPor'],0,1);
$pdf->Ln(2);

// CLIENTE
$pdf->SetFont('DejaVu','B',8);
$pdf->Cell(0,5,'Cliente:',0,1);
$pdf->SetFont('DejaVu','',8);
$pdf->Cell(0,4,$nota['NombreCliente'],0,1);
$pdf->Cell(0,4,'Tel: '.$nota['Telefono'],0,1);
$pdf->MultiCell(0,4,$nota['Direccion']);
$pdf->Ln(2);

// EQUIPO
$pdf->SetFont('DejaVu','B',8);
$pdf->Cell(0,5,'Equipo:',0,1);
$pdf->SetFont('DejaVu','',8);
$pdf->MultiCell(0,4,trim($mantenimiento['Equipo'].' - '.$mantenimiento['Marca'].' '.$mantenimiento['Model']));
if (!empty($mantenimiento['Contraseña'])) $pdf->Cell(0,4,'Contraseña: '.$mantenimiento['Contraseña'],0,1);
if (!empty($mantenimiento['Accesorios'])) $pdf->MultiCell(0,4,'Accesorios: '.$mantenimiento['Accesorios']);
$pdf->Ln(2);

// DESCRIPCIÓN DEL PROBLEMA
if (!empty($nota['Descripcion'])) {
  $pdf->SetFont('DejaVu','B',8);
  $pdf->Cell(0,5,'Descripción del Problema:',0,1);
  $pdf->SetFont('DejaVu','',8);
  $pdf->MultiCell(0,4,trim($nota['Descripcion']));
  $pdf->Ln(1);
}

// SUGERENCIA TÉCNICA
if (!empty($mantenimiento['SugerenciaTecn'])) {
  $pdf->SetFont('DejaVu','B',8);
  $pdf->Cell(0,5,'Sugerencia Técnica:',0,1);
  $pdf->SetFont('DejaVu','',8);
  $pdf->MultiCell(0,4,trim($mantenimiento['SugerenciaTecn']));
  $pdf->Ln(1);
}

// SERVICIOS SELECCIONADOS
// SERVICIOS SELECCIONADOS
if ($servicios) {
  $pdf->SetFont('DejaVu','B',8);
  $pdf->Cell(0,5,'Servicios:',0,1);
  $pdf->Ln(1);

  foreach ($servicios as $s) {
    // Nombre del tipo en negrita
    $pdf->SetFont('DejaVu','B',8);
    $pdf->MultiCell(0,4,"• ".$s['NombreTipo'].":");

    // Descripción del servicio con sangría
    $pdf->SetFont('DejaVu','',8);
    $texto = "   ".$s['Servicio'];
    if ($s['Precio'] > 0) {
      $texto .= "  ($".number_format($s['Precio'],2).")";
    }

    $pdf->MultiCell(0,4,$texto);
    $pdf->Ln(2); 
  }
}


// COSTOS
$pdf->Cell(0,0,'--------------------------------------',0,1,'C');
$pdf->Ln(2);

$sinCostos = ($nota['Total'] <= 0);

if ($sinCostos) {
  $pdf->SetFont('DejaVu','I',8);
  $pdf->Cell(0,5,'COTIZACIÓN PENDIENTE',0,1,'C');
  if ($nota['Anticipo'] > 0) {
    $pdf->SetFont('DejaVu','',8);
    $pdf->Cell(0,4,'Anticipo: $'.number_format($nota['Anticipo'],2),0,1,'C');
  }
} else {
  $pdf->SetFont('DejaVu','',8);
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

$pdf->Output('I','Ticket_Mantenimiento_'.$nota['idNota'].'.pdf');
exit;
