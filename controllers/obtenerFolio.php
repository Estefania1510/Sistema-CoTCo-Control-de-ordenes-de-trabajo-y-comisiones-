<?php
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$sql = "SELECT MAX(idNota) AS ultimoFolio FROM nota";
$stmt = $conn->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$folio = $row ? $row['ultimoFolio'] + 1 : 1;

echo json_encode(['folio' => $folio]);
?>
