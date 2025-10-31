<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

if (isset($_GET['tipo'])) {
    $idTipo = $_GET['tipo'];

    $sql = "SELECT idCatalogoMnt, Servicio 
            FROM catalogomnt 
            WHERE idTipoMnt = :idTipo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idTipo', $idTipo, PDO::PARAM_INT);
    $stmt->execute();
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($servicios);
    exit;
}

$sql = "SELECT idTipoMnt, NombreTipo FROM tipomantenimiento ORDER BY NombreTipo ASC";
$stmt = $conn->query($sql);
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($tipos);
