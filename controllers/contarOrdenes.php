<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

// Contar órdenes por estado
$sql = "
SELECT estado_key, COUNT(*) AS total
FROM (
  SELECT
    CASE COALESCE(nd.estatus, nm.Estatus)
      WHEN 'Proceso' THEN 'Proceso'
      WHEN 'Enviado a Tequila' THEN 'EnviadoTequila'
      WHEN 'Listo para Entrega' THEN 'ListoEntregar'
      WHEN 'Entregado' THEN 'Entregado'
      WHEN 'Retrasado' THEN 'Retrasado'
      WHEN 'Cancelado' THEN 'Cancelado'
      -- Opcional: si algún registro trae 'Cliente Avisado' y no lo muestras en tarjetas,
      -- lo puedes ignorar o crear una tarjeta.
      ELSE 'OTRO'
    END AS estado_key
  FROM nota n
  LEFT JOIN notadiseño nd ON n.idNota = nd.idNota
  LEFT JOIN notamantenimiento nm ON n.idNota = nm.idNota
) AS todas
WHERE estado_key <> 'OTRO'
GROUP BY estado_key
";


$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // estado => total

$estados = [
  'Proceso' => 0,
  'EnviadoTequila' => 0,
  'ListoEntregar' => 0,
  'Entregado' => 0,
  'Retrasado' => 0,
  'Cancelado' => 0
];

foreach ($estados as $key => $val) {
  if (isset($rows[$key])) {
    $estados[$key] = (int)$rows[$key];
  }
}

echo json_encode($estados);
