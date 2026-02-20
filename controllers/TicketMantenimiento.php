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

// ================= CONSULTA NOTA =================
$sql = "SELECT n.idNota, DATE_FORMAT(n.FechaRecepcion, '%d-%m-%Y') AS FechaRecepcion,
               n.Total, n.Anticipo, n.Resto, n.Descripcion, n.Trabajo,
               c.NombreCliente, c.Telefono, c.Direccion,
               u.NombreUsuario AS RecepcionadoPor
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        INNER JOIN usuario u ON n.idUsuario = u.idUsuario
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$nota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nota) die("No se encontró la nota.");

// ================= MANTENIMIENTO =================
$sqlMnt = "SELECT m.idMantenimiento, m.Equipo, m.Marca, m.Model,
                  m.Contraseña, m.Accesorios, m.SugerenciaTecn,
                  m.DescripcionEquipo
           FROM notamantenimiento m
           WHERE m.idNota = ?";
$stmt = $conn->prepare($sqlMnt);
$stmt->execute([$idNota]);
$mantenimiento = $stmt->fetch(PDO::FETCH_ASSOC);

// ================= SERVICIOS (CATÁLOGO + MANUAL) =================
$sqlServ = "
  SELECT
    a.idAuxServicios,
    a.Origen,
    a.Descripcion,
    a.Cantidad,
    a.Precio,
    a.Subtotal,
    tm.NombreTipo,
    c.Servicio AS ServicioCatalogo
  FROM auxservicios a
  LEFT JOIN catalogomnt c ON a.idCatalogoMnt = c.idCatalogoMnt
  LEFT JOIN tipomantenimiento tm ON c.idTipoMnt = tm.idTipoMnt
  WHERE a.idMantenimiento = ?
  ORDER BY
    CASE WHEN a.Origen='CATALOGO' THEN 0 ELSE 1 END,
    tm.NombreTipo,
    a.idAuxServicios
";
$stmt = $conn->prepare($sqlServ);
$stmt->execute([$mantenimiento['idMantenimiento'] ?? 0]);
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Normalizar filas para el ticket
$items = [];
foreach ($servicios as $s) {
  $origen = $s['Origen'] ?? 'CATALOGO';
  $tipo   = ($origen === 'MANUAL') ? 'Manual' : ($s['NombreTipo'] ?? 'Servicio');

  // Para catálogo usa el nombre del catálogo; para manual usa Descripcion
  $desc = ($origen === 'MANUAL')
    ? trim((string)($s['Descripcion'] ?? ''))
    : trim((string)($s['ServicioCatalogo'] ?? $s['Descripcion'] ?? ''));

  if ($desc === '') continue;

  $cant = (int)($s['Cantidad'] ?? 1);
  if ($cant <= 0) $cant = 1;

  $precio = (float)($s['Precio'] ?? 0);
  $subtotal = isset($s['Subtotal']) ? (float)$s['Subtotal'] : ($cant * $precio);

  $items[] = [
    'tipo' => $tipo,
    'desc' => $desc,
    'cant' => $cant,
    'precio' => $precio,
    'subtotal' => $subtotal
  ];
}

// ================= PDF =================
$pdf = new tFPDF('P','mm',[80,297]);
$pdf->AddPage();

$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
$pdf->AddFont('DejaVu','I','DejaVuSans-Oblique.ttf',true);

// LOGO
$logo = __DIR__ . '/../Image/ICT_NEGRO.png';
if (file_exists($logo)) {
  $x = (80 - 25) / 2;
  $pdf->Image($logo, $x, 5, 25);
  $pdf->Ln(28);
}

// EMPRESA
$pdf->SetFont('DejaVu','',8.5);
$pdf->Cell(0,4,'C. Iturbide Sur #6, Magdalena, Jal.',0,1,'C');
$pdf->Cell(0,4,'Tel. 3311901741',0,1,'C');
$pdf->SetFont('DejaVu','B',8.5);
$pdf->Cell(0,4,'Horario:',0,1,'C');
$pdf->SetFont('DejaVu','',8.5);
$pdf->Cell(0,4,'Lun-Vie: 8:00 am a 9:00 pm',0,1,'C');
$pdf->Cell(0,4,'Sab-Dom: 9:00 am a 3:00 pm',0,1,'C');
$pdf->Ln(3);
$pdf->Cell(0,0,'--------------------------------------',0,1,'C');
$pdf->Ln(2);

// ENCABEZADO
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(0,5,'ORDEN DE MANTENIMIENTO',0,1,'C');
$pdf->SetFont('DejaVu','B',12);
$pdf->Cell(0,6,'FOLIO: '.$nota['idNota'],0,1,'C');
$pdf->Ln(3);

$margenIzq = 5.5;

// FECHA
$pdf->SetFont('DejaVu','',8.5);
$pdf->SetX($margenIzq);
$pdf->Cell(0,4,'Fecha: '.$nota['FechaRecepcion'],0,1);

$pdf->SetX($margenIzq);
$pdf->Cell(0,4,'Recepcionado por: '.$nota['RecepcionadoPor'],0,1);

$pdf->Ln(3);

// CLIENTE
$pdf->SetFont('DejaVu','B',8.5);
$pdf->SetX($margenIzq);
$pdf->Cell(0,5,'Cliente:',0,1);

$pdf->SetFont('DejaVu','',8.5);
$pdf->SetX($margenIzq);
$pdf->Cell(0,4,$nota['NombreCliente'],0,1);

$pdf->SetX($margenIzq);
$pdf->Cell(0,4,'Tel: '.$nota['Telefono'],0,1);

$pdf->SetX($margenIzq);
$pdf->MultiCell(0,4,$nota['Direccion']);
$pdf->Ln(3);

// TRABAJO (opcional, pero útil)
if (!empty($nota['Trabajo'])) {
  $pdf->SetFont('DejaVu','B',8.5);
  $pdf->SetX($margenIzq);
  $pdf->Cell(0,5,'Trabajo:',0,1);

  $pdf->SetFont('DejaVu','',8.5);
  $pdf->SetX($margenIzq);
  $pdf->MultiCell(0,4,$nota['Trabajo']);
  $pdf->Ln(2);
}

// EQUIPO
$pdf->SetFont('DejaVu','B',8.5);
$pdf->SetX($margenIzq);
$pdf->Cell(0,5,'Equipo:',0,1);

$pdf->SetFont('DejaVu','',8.5);
$pdf->SetX($margenIzq);
$pdf->MultiCell(0,4,trim($mantenimiento['Equipo'].' - '.$mantenimiento['Marca'].' '.$mantenimiento['Model']));

if (!empty($mantenimiento['Contraseña'])) {
  $pdf->SetX($margenIzq);
  $pdf->Cell(0,4,'Contraseña: '.$mantenimiento['Contraseña'],0,1);
}

if (!empty($mantenimiento['Accesorios'])) {
  $pdf->SetX($margenIzq);
  $pdf->MultiCell(0,4,'Accesorios: '.$mantenimiento['Accesorios']);
}

$pdf->Ln(3);

// DESCRIPCIÓN DEL EQUIPO
if (!empty($mantenimiento['DescripcionEquipo'])) {
  $pdf->SetFont('DejaVu','B',8.5);
  $pdf->SetX($margenIzq);
  $pdf->Cell(0,5,'Descripción del Equipo:',0,1);

  $pdf->SetFont('DejaVu','',8);
  $pdf->SetX($margenIzq);
  $pdf->MultiCell(0,4,$mantenimiento['DescripcionEquipo']);
  $pdf->Ln(2);
}

// DESCRIPCIÓN DEL PROBLEMA
if (!empty($nota['Descripcion'])) {
  $pdf->SetFont('DejaVu','B',8.5);
  $pdf->SetX($margenIzq);
  $pdf->Cell(0,5,'Descripción del Problema:',0,1);

  $pdf->SetFont('DejaVu','',8);
  $pdf->SetX($margenIzq);
  $pdf->MultiCell(0,4,$nota['Descripcion']);
  $pdf->Ln(2);
}

// ================= SERVICIOS (CATÁLOGO + MANUAL) =================
// ================= SERVICIOS (SIN TIPO) =================
if (!empty($items)) {

  $margenIzq = 5.5;
  $indent = 3;
  $anchoTexto = 48;
  $anchoPrecio = 18;

  $pdf->SetFont('DejaVu','B',8.5);
  $pdf->SetX($margenIzq);
  $pdf->Cell(0,5,'Servicios:',0,1);
  $pdf->Ln(1);

  foreach ($items as $it) {

    $yInicio = $pdf->GetY();

    // Texto: "Servicio xCant" (si cant > 1)
    $texto = $it['desc'];
    if ((int)$it['cant'] > 1) $texto .= ' x'.$it['cant'];

    $pdf->SetFont('DejaVu','',8.5);
    $pdf->SetX($margenIzq + $indent);
    $pdf->MultiCell($anchoTexto - $indent, 4, $texto);

    $yFinal = $pdf->GetY();

    // Precio (subtotal)
    if ($it['subtotal'] > 0) {
      $pdf->SetXY($margenIzq + $anchoTexto, $yInicio);
      $pdf->Cell($anchoPrecio, 4, '$'.number_format($it['subtotal'],2), 0, 0, 'R');
    }

    $pdf->SetY($yFinal + 1);
  }

  $pdf->Ln(2);
}


// ================= COSTOS =================
$totalNum    = (float)$nota['Total'];
$anticipoNum = (float)$nota['Anticipo'];
$restoNum    = (float)$nota['Resto'];
$esCotPendiente = ($totalNum == 0 && $restoNum == 0);

$pdf->Ln(1);
$pdf->Cell(0,0,str_repeat('-',32),0,1,'C');
$pdf->Ln(3);

$margenIzq = 5.5;
$anchoTexto = 48;
$anchoTotal = 18;

if ($esCotPendiente) {

  if ($anticipoNum > 0) {
    $pdf->SetFont('DejaVu','',8.5);
    $pdf->SetX($margenIzq);
    $pdf->Cell($anchoTexto,4,'Anticipo:',0,0);
    $pdf->Cell($anchoTotal,4,'$'.number_format($anticipoNum,2),0,1,'R');
    $pdf->Ln(2);
  }

  $pdf->SetFont('DejaVu','B',9);
  $pdf->Cell(0,5,'*** COTIZACION PENDIENTE ***',0,1,'C');
  $pdf->SetFont('DejaVu','',8.5);

} else {

  $pdf->SetFont('DejaVu','B',8.5);
  $pdf->SetX($margenIzq);
  $pdf->Cell($anchoTexto,4,'Total:',0,0);
  $pdf->Cell($anchoTotal,4,'$'.number_format($totalNum,2),0,1,'R');
  $pdf->SetFont('DejaVu','',8.5);
  $pdf->Ln(2);

  $pdf->SetX($margenIzq);
  $pdf->Cell($anchoTexto,4,'Anticipo:',0,0);
  $pdf->Cell($anchoTotal,4,'$'.number_format($anticipoNum,2),0,1,'R');
  $pdf->Ln(1);

  $pdf->SetFont('DejaVu','B',8.5);
  $pdf->SetX($margenIzq);
  $pdf->Cell($anchoTexto,4,'Restante:',0,0);
  $pdf->Cell($anchoTotal,4,'$'.number_format($restoNum,2),0,1,'R');
  $pdf->SetFont('DejaVu','',8.5);
}

$pdf->Ln(4);
$pdf->Cell(0,0,str_repeat('-',32),0,1,'C');
$pdf->Ln(3);
$pdf->SetFont('DejaVu','I',8.5);
$pdf->Cell(0,4,'Gracias por su preferencia.',0,1,'C');
$pdf->Cell(0,4,'Por favor conserve este ticket.',0,1,'C');

$pdf->Output('I','Ticket_Mantenimiento_'.$nota['idNota'].'.pdf');
exit;
