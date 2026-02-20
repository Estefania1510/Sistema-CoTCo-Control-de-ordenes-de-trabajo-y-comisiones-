<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

header('Content-Type: application/json; charset=utf-8');

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_GET['idNota'] ?? null;
if (!$idNota) {
  echo json_encode(["status" => "error", "message" => "Falta idNota"]);
  exit;
}

function normalizarTelefonoWhats($tel) {
  $digits = preg_replace('/\D+/', '', (string)$tel);
  if (strlen($digits) === 10) $digits = "52" . $digits; // MX
  if (strlen($digits) < 10) return null;
  return $digits;
}

// ====== NOTA + CLIENTE ======
$sql = "SELECT
          n.idNota,
          DATE_FORMAT(n.FechaRecepcion, '%d-%m-%Y') AS FechaRecepcion,
          n.Trabajo,
          n.Comentario,
          n.Total, n.Anticipo, n.Resto,
          c.NombreCliente, c.Telefono, c.Telefono2
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$nota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nota) {
  echo json_encode(["status" => "error", "message" => "No se encontró la nota"]);
  exit;
}

// ====== DISEÑO ======
$sqlDis = "SELECT idDiseño, CostoDiseño, EsDigital, MedioEntrega
           FROM notadiseño
           WHERE idNota = ?";
$stmt = $conn->prepare($sqlDis);
$stmt->execute([$idNota]);
$dis = $stmt->fetch(PDO::FETCH_ASSOC);

$idDis = $dis['idDiseño'] ?? 0;

// ====== MATERIALES ======
$materiales = [];
if ($idDis) {
  $sqlMat = "SELECT Material, Cantidad, Precio, Subtotal
             FROM material
             WHERE idDiseño = ?";
  $stmt = $conn->prepare($sqlMat);
  $stmt->execute([$idDis]);
  $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // limpia vacíos
  $materiales = array_values(array_filter($materiales, function($m){
    return trim($m['Material'] ?? '') !== '';
  }));
}

// ====== Mensaje ======
$total    = (float)$nota['Total'];
$anticipo = (float)$nota['Anticipo'];
$resto    = (float)$nota['Resto'];
$esPend   = ($total == 0 && $resto == 0);

$esDigital = ((int)($dis['EsDigital'] ?? 0) === 1);
$medioEntrega = trim($dis['MedioEntrega'] ?? '');

$lineas = [];
$lineas[] = "*ICT*";
$lineas[] = "━━━━━━━━━━━━━━━━━━";
$lineas[] = "*ORDEN DE DISEÑO*";
$lineas[] = "*Folio:* #{$nota['idNota']}";
$lineas[] = "*Fecha:* {$nota['FechaRecepcion']}";
$lineas[] = "━━━━━━━━━━━━━━━━━━";
$lineas[] = "*Cliente:* {$nota['NombreCliente']}";
$lineas[] = "*Trabajo:* " . ($nota['Trabajo'] ?? '');
$lineas[] = "*Tipo:* " . ($esDigital ? "Trabajo digital" : "Trabajo físico");

if ($esDigital && $medioEntrega !== '') {
  $lineas[] = "*Medio de entrega:* {$medioEntrega}";
}

if (!empty($nota['Comentario'])) {
  $lineas[] = "*Comentarios:* _" . trim($nota['Comentario']) . "_";
}

$lineas[] = "━━━━━━━━━━━━━━━━━━";

if (!empty($materiales)) {
  $lineas[] = "*Materiales:*";
  foreach ($materiales as $m) {
    $mat = trim((string)$m['Material']);
    $cant = (int)($m['Cantidad'] ?? 1);
    if ($cant <= 0) $cant = 1;

    $sub = (float)($m['Subtotal'] ?? 0);
    $txt = "🔹 {$mat}";
    if ($cant > 1) $txt .= " (x{$cant})";

    if (!$esPend && $sub > 0) {
      $txt .= "  *$" . number_format($sub, 2) . "*";
    }
    $lineas[] = $txt;
  }
  $lineas[] = "━━━━━━━━━━━━━━━━━━";
}

// Costos
if ($esPend) {
  if ($anticipo > 0) $lineas[] = " *Anticipo:* $" . number_format($anticipo, 2);
  $lineas[] = "*COTIZACIÓN PENDIENTE*";
} else {
  if (!empty($dis['CostoDiseño'])) {
    $lineas[] = "*Costo diseño:* $" . number_format((float)$dis['CostoDiseño'], 2);
  }
  $lineas[] = "*Total:* $" . number_format($total, 2);
  if ($anticipo > 0) $lineas[] = "*Anticipo:* $" . number_format($anticipo, 2);
  $lineas[] = "*Restante:* *$" . number_format($resto, 2) . "*";
}

$lineas[] = "━━━━━━━━━━━━━━━━━━";
$lineas[] = "_¡Gracias por su preferencia!_";
$lineas[] = "*AVISO:* Si otra persona pasará a recoger, por favor *reenvíale este mensaje* para identificar su orden al llegar.";

$mensaje = implode("\n", $lineas);

// Teléfono (por defecto usa Teléfono 1; el selector en JS decidirá cuál usar)
$tel = normalizarTelefonoWhats($nota['Telefono'] ?? '');
if (!$tel) {
  echo json_encode(["status" => "error", "message" => "Teléfono inválido del cliente"]);
  exit;
}

$url = "https://wa.me/{$tel}?text=" . urlencode($mensaje);

echo json_encode([
  "status" => "success",
  "telefono" => $tel,
  "mensaje" => $mensaje,
  "url" => $url
]);
