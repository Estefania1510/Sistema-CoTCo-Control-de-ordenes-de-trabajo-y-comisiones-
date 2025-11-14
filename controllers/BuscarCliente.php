<?php
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$term = $_GET['term'] ?? '';

$sql = "SELECT idCliente, NombreCliente, Direccion, Telefono, Telefono2
        FROM cliente
        WHERE NombreCliente LIKE :term
        ORDER BY NombreCliente ASC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->execute([":term" => "%$term%"]);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($clientes);
