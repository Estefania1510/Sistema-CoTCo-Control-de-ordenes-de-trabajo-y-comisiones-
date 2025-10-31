<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

try {
  $conexion = new Conexion($conData);
  $conn = $conexion->getConnection();

  $software = $_GET['software'] ?? '';
  $software = trim($software);

  if ($software === '') {
    echo json_encode([]);
    exit;
  }

  // Buscar licencias libres por software
  $sql = "SELECT idLS, Licencia 
          FROM licenciasoftware
          WHERE Software = ? AND Estatus = 'Libre'
          ORDER BY Licencia ASC";

  $stmt = $conn->prepare($sql);
  $stmt->execute([$software]);
  $licencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($licencias);

} catch (Exception $e) {

  echo json_encode(['error' => $e->getMessage()]);
}
