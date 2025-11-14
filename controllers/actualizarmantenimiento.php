<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_POST['idNota'] ?? null;
$idMantenimiento = $_POST['idMantenimiento'] ?? null;
$estatus = $_POST['estatus'] ?? '';
$fechaEntrega = !empty($_POST['FechaEntrega']) ? $_POST['FechaEntrega'] : null;
$idTecnico = $_POST['idTecnico'] ?? null;

$equipo = trim($_POST['equipo'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$modelo = trim($_POST['modelo'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');
$accesorios = trim($_POST['accesorios'] ?? '');
$descEquipo = trim($_POST['descEquipo'] ?? '');
$descProblema = trim($_POST['descProblema'] ?? '');
$sugerencia = trim($_POST['sugerencia'] ?? '');
$total = floatval($_POST['total'] ?? 0);
$anticipo = floatval($_POST['anticipo'] ?? 0);
$resto = floatval($_POST['resto'] ?? 0);
$tipos = $_POST['tipo'] ?? [];
$servicios = $_POST['servicio'] ?? [];
$precios = $_POST['precio'] ?? [];

try {
  $conn->beginTransaction();

// REASIGNACIÓN DE COMISIÓN 
$idComision = null;
$idTecOriginal = $_POST['idTecnicoOriginal'] ?? null;
if (!empty($idTecnico) && $idTecnico !== $idTecOriginal) {

    // Buscar comisión existente
    $stmtC = $conn->prepare("
        SELECT idComisiones 
        FROM comisiones 
        WHERE idnota = ? AND tipo = 'Mantenimiento'
        LIMIT 1
    ");
    $stmtC->execute([$idNota]);
    $idComision = $stmtC->fetchColumn();

    // Si existe comisión - reasignar técnico
    if ($idComision) {
        $stmtUp = $conn->prepare("
            UPDATE comisiones 
            SET idUsuario = ? 
            WHERE idComisiones = ?
        ");
        $stmtUp->execute([$idTecnico, $idComision]);
    }
}

if (empty($idComision)) {
    $stmtC = $conn->prepare("
        SELECT idComisiones 
        FROM comisiones 
        WHERE idnota = ? AND tipo = 'Mantenimiento'
        LIMIT 1
    ");
    $stmtC->execute([$idNota]);
    $idComision = $stmtC->fetchColumn();
}

if ($idComision) {

    $stmtP = $conn->prepare("SELECT porcentaje FROM comisiones WHERE idComisiones = ?");
    $stmtP->execute([$idComision]);
    $porcentaje = (float)$stmtP->fetchColumn();

    if ($porcentaje <= 0) {
        $porcentaje = 10; // valor de seguridad
    }

    $nuevoMonto = ($total * $porcentaje) / 100;

    $stmtUpdCom = $conn->prepare("
        UPDATE comisiones
        SET monto = ?, porcentaje = ?
        WHERE idComisiones = ?
    ");
    $stmtUpdCom->execute([$nuevoMonto, $porcentaje, $idComision]);
}

$conn->commit();

echo json_encode([
    "status" => "success",
    "message" => "La orden de mantenimiento fue actualizada correctamente."
]);

} catch (Exception $e) {

$conn->rollBack();

$log = $conn->prepare("
    INSERT INTO logerror (metodo, excepcion) 
    VALUES ('actualizarmantenimiento', ?)
");
$log->execute([$e->getMessage()]);

echo json_encode([
    "status" => "error",
    "message" => "Error: ".$e->getMessage()
]);
}

