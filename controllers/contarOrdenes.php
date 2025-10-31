<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

// Contar órdenes por estado
$sql = "
SELECT estado, COUNT(*) AS total FROM (
  SELECT COALESCE(nd.estatus, nm.Estatus) AS estado
  FROM nota n
  LEFT JOIN notadiseño nd ON n.idNota = nd.idNota
  LEFT JOIN notamantenimiento nm ON n.idNota = nm.idNota
) AS todas
GROUP BY estado";

$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // estado => total

$estados = [
  'Proceso' => 0,
  'EnviadoTequila' => 0,
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
