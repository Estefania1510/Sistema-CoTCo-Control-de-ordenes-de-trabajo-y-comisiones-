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


// === TÍTULO ===
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(0,5,'ORDEN DE DISEÑO',0,1,'C');
$pdf->SetFont('DejaVu','B',12);
$pdf->Cell(0,6,'FOLIO: '.$nota['idNota'],0,1,'C');
$pdf->Ln(2);

$margenIzq = 5.5;

// Fecha y recepcionado
$pdf->SetFont('DejaVu','',8.5);

$pdf->SetX($margenIzq);
$pdf->Cell(0,4,'Fecha: '.$nota['FechaRecepcion'],0,1);

$pdf->SetX($margenIzq);
$pdf->Cell(0,4,'Recepcionado por: '.$nota['RecepcionadoPor'],0,1);

$pdf->Ln(2);

// Cliente
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

$pdf->Ln(2);

// Descripción
$pdf->SetFont('DejaVu','B',8.5);
$pdf->SetX($margenIzq);
$pdf->Cell(0,5,'Descripción:',0,1);

$pdf->SetFont('DejaVu','',8.5);
$pdf->SetX($margenIzq);
$pdf->MultiCell(0,4,$nota['Descripcion']);

$pdf->Ln(2);

// Comentarios
if (!empty($nota['Comentario'])) {
    $pdf->SetFont('DejaVu','B',8.5);
    $pdf->SetX($margenIzq);
    $pdf->Cell(0,5,'Comentarios:',0,1);

    $pdf->SetFont('DejaVu','',8.5);
    $pdf->SetX($margenIzq);
    $pdf->MultiCell(0,4,trim($nota['Comentario']));

    $pdf->Ln(2);
}

// ================= MATERIALES (TABLA AJUSTADA) =================
if ($materiales) {

    $margenIzq = 5.5; // margen izquierdo
    $pdf->SetX($margenIzq);

    $pdf->SetFont('DejaVu','B',8.5);
    $pdf->Cell(0,5,'Materiales:',0,1);
    $pdf->Ln(1);

    // Encabezado
    $pdf->SetFont('DejaVu','B',8);
    $pdf->SetX($margenIzq);
    $pdf->Cell(36,4,'Material',0,0);
    $pdf->Cell(12,4,'Cant',0,0,'C');
    $pdf->Cell(18,4,'Total',0,1,'R');

    $pdf->Ln(1);
    $pdf->SetFont('DejaVu','',8);

    foreach ($materiales as $m) {

        $totalMaterial = ($nota['Total'] > 0)
            ? $m['Subtotal']
            : 0;

        $pdf->SetX($margenIzq);
        $pdf->Cell(36,4,substr($m['Material'],0,26),0,0);
        $pdf->Cell(12,4,$m['Cantidad'],0,0,'C');

        if ($nota['Total'] > 0) {
            $pdf->Cell(18,4,'$'.number_format($totalMaterial,2),0,1,'R');
        } else {
            $pdf->Cell(18,4,'',0,1,'R');
        }
    }

    $pdf->Ln(2);
}

// ================= COSTOS (ALINEADOS CON TABLA) =================
$pdf->Ln(1);
$pdf->Cell(0,0,str_repeat('-',32),0,1,'C');
$pdf->Ln(3);

$margenIzq = 5.5;
$anchoTexto = 48;   // Material + Cant
$anchoTotal = 18;   // MISMO ancho que columna Total

$pdf->SetFont('DejaVu','',8.5);

// Costo Diseño
if (!empty($diseno['CostoDiseño'])) {
    $pdf->SetX($margenIzq);
    $pdf->Cell($anchoTexto,4,'Costo Diseño:',0,0);
    $pdf->Cell($anchoTotal,4,'$'.number_format($diseno['CostoDiseño'],2),0,1,'R');
    $pdf->Ln(1);
}

// Total
$pdf->SetFont('DejaVu','B',8.5);   // 👉 activar negrita
$pdf->SetX($margenIzq);
$pdf->Cell($anchoTexto,4,'Total:',0,0);
$pdf->Cell($anchoTotal,4,'$'.number_format($nota['Total'],2),0,1,'R');
$pdf->Ln(1);
$pdf->SetFont('DejaVu','',8.5);    // 👉 regresar a normal

$pdf->Ln(2.5); // 👈 espacio extra entre costos y pagos


// Anticipo
$pdf->SetX($margenIzq);
$pdf->Cell($anchoTexto,4,'Anticipo:',0,0);
$pdf->Cell($anchoTotal,4,'$'.number_format($nota['Anticipo'],2),0,1,'R');
$pdf->Ln(1);

// Restante
$pdf->SetFont('DejaVu','B',8.5); // destacar restante
$pdf->SetX($margenIzq);
$pdf->Cell($anchoTexto,4,'Restante:',0,0);
$pdf->Cell($anchoTotal,4,'$'.number_format($nota['Resto'],2),0,1,'R');
$pdf->SetFont('DejaVu','B',8.5);





$pdf->Ln(4);
$pdf->Cell(0,0,'--------------------------------------',0,1,'C');
$pdf->Ln(3);
$pdf->SetFont('DejaVu','I',8.5);
$pdf->Cell(0,4,'Gracias por su preferencia.',0,1,'C');
$pdf->Cell(0,4,'Por favor conserve este ticket.',0,1,'C');


$pdf->Output('I','Ticket_Diseno_'.$nota['idNota'].'.pdf');
exit;
