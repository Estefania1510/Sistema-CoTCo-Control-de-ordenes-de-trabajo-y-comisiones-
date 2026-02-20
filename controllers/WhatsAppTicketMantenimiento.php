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

// Normaliza teléfono: deja solo dígitos y agrega +52 si viene en 10 dígitos (MX)
function normalizarTelefonoWhats($tel) {
  $digits = preg_replace('/\D+/', '', (string)$tel);

  // Si viene 10 dígitos, asumimos MX y agregamos 52
  if (strlen($digits) === 10) {
    $digits = "52" . $digits;
  }

  // Si viene 11 y empieza con 1 (caso US) lo dejamos, si viene 12+ igual
  // Si viene 0 o muy corto, lo marcamos inválido
  if (strlen($digits) < 10) return null;

  return $digits; // wa.me usa solo dígitos (sin +)
}

// ====== NOTA + CLIENTE ======
$sql = "SELECT
          n.idNota,
          DATE_FORMAT(n.FechaRecepcion, '%d-%m-%Y') AS FechaRecepcion,
          n.Descripcion AS DescProblema,
          n.Total, n.Anticipo, n.Resto,
          c.NombreCliente, c.Telefono
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

// ====== MANTENIMIENTO ======
$sqlMnt = "SELECT idMantenimiento, Equipo, Marca, Model
           FROM notamantenimiento
           WHERE idNota = ?";
$stmt = $conn->prepare($sqlMnt);
$stmt->execute([$idNota]);
$mnt = $stmt->fetch(PDO::FETCH_ASSOC);

$idMnt = $mnt['idMantenimiento'] ?? 0;

// ====== SERVICIOS (CATALOGO + MANUAL) ======
$sqlServ = "
  SELECT
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
  ORDER BY a.idAuxServicios ASC
";
$stmt = $conn->prepare($sqlServ);
$stmt->execute([$idMnt]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ====== Construir items solo con el NOMBRE del servicio (sin mostrar tipo) ======
$items = [];
foreach ($rows as $r) {
  $origen = $r['Origen'] ?? 'CATALOGO';

  $desc = ($origen === 'MANUAL')
    ? trim((string)($r['Descripcion'] ?? ''))
    : trim((string)($r['ServicioCatalogo'] ?? $r['Descripcion'] ?? ''));

  if ($desc === '') continue;

  $cant = (int)($r['Cantidad'] ?? 1);
  if ($cant <= 0) $cant = 1;

  $precio = (float)($r['Precio'] ?? 0);
  $subtotal = isset($r['Subtotal']) ? (float)$r['Subtotal'] : ($cant * $precio);

  $items[] = [
    'desc' => $desc,
    'cant' => $cant,
    'subtotal' => $subtotal
  ];
}

// ====== Armado del mensaje ======
$total    = (float)$nota['Total'];
$anticipo = (float)$nota['Anticipo'];
$resto    = (float)$nota['Resto'];
$esPend   = ($total == 0 && $resto == 0);

$lineas = [];
$lineas[] = "         *ICT*        ";
$lineas[] = "━━━━━━━━━━━━━━━━━━";
$lineas[] = "*ORDEN DE MANTENIMIENTO*";
$lineas[] = "*Folio:* {$nota['idNota']}";
$lineas[] = "*Fecha:* {$nota['FechaRecepcion']}";
$lineas[] = "━━━━━━━━━━━━━━━━━━";
$lineas[] = "*Cliente:* {$nota['NombreCliente']}";
if (!empty($mnt)) {
  $equipoTxt = trim(($mnt['Equipo'] ?? '').' - '.($mnt['Marca'] ?? '').' '.($mnt['Model'] ?? ''));
  if ($equipoTxt !== '-') $lineas[] = "*Equipo:* {$equipoTxt}";
}

if (!empty($nota['DescProblema'])) {
  $lineas[] = "*Problema:* " . $nota['DescProblema'];
}
$lineas[] = "━━━━━━━━━━━━━━━━━━";

if (!empty($items)) {
  $lineas[] = "*Servicios:*";
  foreach ($items as $it) {
    $txt = "- {$it['desc']}";
    if ((int)$it['cant'] > 1) $txt .= " x{$it['cant']}";
    if ($it['subtotal'] > 0)  $txt .= "  $" . number_format($it['subtotal'], 2);
    $lineas[] = $txt;
  }
  $lineas[] = "━━━━━━━━━━━━━━━━━━";
}

if ($esPend) {
  if ($anticipo > 0) $lineas[] = "Anticipo: $" . number_format($anticipo, 2);
  $lineas[] = "*** COTIZACION PENDIENTE ***";
} else {
  $lineas[] = "*Total:* $" . number_format($total, 2);
  $lineas[] = "*Anticipo:* $" . number_format($anticipo, 2);
  $lineas[] = "*Restante:* $" . number_format($resto, 2);
}

// ... después de los totales y el resto
$lineas[] = "━━━━━━━━━━━━━━━━━━";
$lineas[] = "_¡Gracias por su preferencia!_";
$lineas[] = "*AVISO:* Si otra persona pasará a recoger, por favor *reenvíale este mensaje* para identificar su orden al llegar.";

$mensaje = implode("\n", $lineas);

// ====== Teléfono ======
$tel = normalizarTelefonoWhats($nota['Telefono'] ?? '');
if (!$tel) {
  echo json_encode(["status" => "error", "message" => "Teléfono inválido del cliente"]);
  exit;
}

// URL WhatsApp
$url = "https://wa.me/{$tel}?text=" . urlencode($mensaje);

echo json_encode([
  "status" => "success",
  "telefono" => $tel,
  "mensaje" => $mensaje,
  "url" => $url
]);
