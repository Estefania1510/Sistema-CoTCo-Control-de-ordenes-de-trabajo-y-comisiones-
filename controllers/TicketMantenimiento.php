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
               n.Total, n.Anticipo, n.Resto, n.Descripcion,
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

// ================= SERVICIOS =================
$sqlServ = "SELECT tm.NombreTipo, c.Servicio, a.Precio
            FROM auxservicios a
            INNER JOIN catalogomnt c ON a.idCatalogoMnt = c.idCatalogoMnt
            INNER JOIN tipomantenimiento tm ON c.idTipoMnt = tm.idTipoMnt
            WHERE a.idMantenimiento = ?";
$stmt = $conn->prepare($sqlServ);
$stmt->execute([$mantenimiento['idMantenimiento'] ?? 0]);
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// ================= FECHA =================
$pdf->SetFont('DejaVu','',8.5);

$pdf->SetX($margenIzq);
$pdf->Cell(0,4,'Fecha: '.$nota['FechaRecepcion'],0,1);

$pdf->SetX($margenIzq);
$pdf->Cell(0,4,'Recepcionado por: '.$nota['RecepcionadoPor'],0,1);

$pdf->Ln(3);

// ================= CLIENTE =================
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

// ================= EQUIPO =================
$pdf->SetFont('DejaVu','B',8.5);
$pdf->SetX($margenIzq);
$pdf->Cell(0,5,'Equipo:',0,1);

$pdf->SetFont('DejaVu','',8.5);
$pdf->SetX($margenIzq);
$pdf->MultiCell(0,4,$mantenimiento['Equipo'].' - '.$mantenimiento['Marca'].' '.$mantenimiento['Model']);

if (!empty($mantenimiento['Contraseña'])) {
    $pdf->SetX($margenIzq);
    $pdf->Cell(0,4,'Contraseña: '.$mantenimiento['Contraseña'],0,1);
}

if (!empty($mantenimiento['Accesorios'])) {
    $pdf->SetX($margenIzq);
    $pdf->MultiCell(0,4,'Accesorios: '.$mantenimiento['Accesorios']);
}

$pdf->Ln(3);

// ================= DESCRIPCIÓN DEL EQUIPO =================
if (!empty($mantenimiento['DescripcionEquipo'])) {
    $pdf->SetFont('DejaVu','B',8.5);
    $pdf->SetX($margenIzq);
    $pdf->Cell(0,5,'Descripción del Equipo:',0,1);

    $pdf->SetFont('DejaVu','',8);
    $pdf->SetX($margenIzq);
    $pdf->MultiCell(0,4,$mantenimiento['DescripcionEquipo']);

    $pdf->Ln(2);
}

// ================= DESCRIPCIÓN DEL PROBLEMA =================
if (!empty($nota['Descripcion'])) {
    $pdf->SetFont('DejaVu','B',8.5);
    $pdf->SetX($margenIzq);
    $pdf->Cell(0,5,'Descripción del Problema:',0,1);

    $pdf->SetFont('DejaVu','',8);
    $pdf->SetX($margenIzq);
    $pdf->MultiCell(0,4,$nota['Descripcion']);

    $pdf->Ln(2);
}


// ================= SERVICIOS (FORMATO AGRUPADO) =================
// ================= SERVICIOS (NOMBRES LARGOS, SIN ENCIMARSE) =================
// ================= SERVICIOS (PRECIO ALINEADO A LA PRIMERA LÍNEA) =================
if ($servicios) {

    $margenIzq = 5.5;
    $indentServicio = 3;
    $anchoTexto = 48;
    $anchoPrecio = 18;

    $pdf->SetFont('DejaVu','B',8.5);
    $pdf->SetX($margenIzq);
    $pdf->Cell(0,5,'Servicios:',0,1);
    $pdf->Ln(1);

    $tipoActual = '';

    foreach ($servicios as $s) {

        // Encabezado por tipo
        if ($tipoActual !== $s['NombreTipo']) {
            $tipoActual = $s['NombreTipo'];

            $pdf->SetFont('DejaVu','B',8.5);
            $pdf->SetX($margenIzq);
            $pdf->Cell(0,4,$tipoActual.':',0,1);
        }

        // Guardar posición inicial
        $yInicio = $pdf->GetY();

        // Nombre del servicio (con salto de línea)
        $pdf->SetFont('DejaVu','',8.5);
        $pdf->SetX($margenIzq + $indentServicio);
        $pdf->MultiCell(
            $anchoTexto - $indentServicio,
            4,
            $s['Servicio']
        );

        // Guardar posición final
        $yFinal = $pdf->GetY();

        // Precio alineado a la primera línea
        if ($s['Precio'] > 0) {
            $pdf->SetXY($margenIzq + $anchoTexto, $yInicio);
            $pdf->Cell(
                $anchoPrecio,
                4,
                '$'.number_format($s['Precio'],2),
                0,
                0,
                'R'
            );
        }

        // Volver al final del texto
        $pdf->SetY($yFinal + 1);
    }

    $pdf->Ln(2);
}



// COSTOS
// ================= COSTOS (MANTENIMIENTO - FORMATO UNIFICADO) =================
$pdf->Ln(1);
$pdf->Cell(0,0,str_repeat('-',32),0,1,'C');
$pdf->Ln(3);

$margenIzq = 5.5;
$anchoTexto = 48;
$anchoTotal = 18;

$pdf->SetFont('DejaVu','',8.5);

// Total (negrita)
$pdf->SetFont('DejaVu','B',8.5);
$pdf->SetX($margenIzq);
$pdf->Cell($anchoTexto,4,'Total:',0,0);
$pdf->Cell($anchoTotal,4,'$'.number_format($nota['Total'],2),0,1,'R');
$pdf->SetFont('DejaVu','',8.5);
$pdf->Ln(2);

// Anticipo
$pdf->SetX($margenIzq);
$pdf->Cell($anchoTexto,4,'Anticipo:',0,0);
$pdf->Cell($anchoTotal,4,'$'.number_format($nota['Anticipo'],2),0,1,'R');
$pdf->Ln(1);

// Restante (destacado)
$pdf->SetFont('DejaVu','B',8.5);
$pdf->SetX($margenIzq);
$pdf->Cell($anchoTexto,4,'Restante:',0,0);
$pdf->Cell($anchoTotal,4,'$'.number_format($nota['Resto'],2),0,1,'R');
$pdf->SetFont('DejaVu','',8.5);


$pdf->Ln(4);
$pdf->Cell(0,0,str_repeat('-',32),0,1,'C');
$pdf->Ln(3);
$pdf->SetFont('DejaVu','I',8.5);
$pdf->Cell(0,4,'Gracias por su preferencia.',0,1,'C');
$pdf->Cell(0,4,'Por favor conserve este ticket.',0,1,'C');

$pdf->Output('I','Ticket_Mantenimiento_'.$nota['idNota'].'.pdf');
exit;
