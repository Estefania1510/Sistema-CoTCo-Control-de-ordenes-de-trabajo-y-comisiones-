<?php
session_start();
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idUsuario = $_SESSION['idUsuario'];
$roles = $_SESSION['roles'] ?? [];

$nombre = $_GET['nombre'] ?? '';
$estado = $_GET['estado'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$fecha = $_GET['fecha'] ?? '';
$misOrdenes = $_GET['misOrdenes'] ?? 0;
$OrdenesTrabajadas = $_GET['OrdenesTrabajadas'] ?? 0;

$query = "SELECT 
            n.idNota AS folio,
            c.NombreCliente AS cliente,
            IF(nd.idDiseño IS NOT NULL, 'Diseño', 'Mantenimiento') AS tipo,
            DATE_FORMAT (n.FechaRecepcion, '%d-%m-%Y') AS fechaRecepcion,
            IFNULL(NULLIF(DATE_FORMAT(n.FechaEntrega, '%d-%m-%Y'), ''), 'Pendiente') AS fechaEntrega,
            COALESCE(nd.estatus, nm.Estatus) AS estado,

            CASE 
                WHEN COALESCE(nd.estatus, nm.Estatus) = 'Proceso' 
                     AND DATEDIFF(CURDATE(), n.FechaRecepcion) >= 2 THEN 'Retrasado'
                ELSE COALESCE(nd.estatus, nm.Estatus)
              END AS estado,

            u.NombreUsuario AS usuario,
            nd.idDiseñador,
            nm.idTecnico
          FROM nota n
          INNER JOIN cliente c ON n.idCliente = c.idCliente
          INNER JOIN usuario u ON n.idUsuario = u.idUsuario
          LEFT JOIN notadiseño nd ON n.idNota = nd.idNota
          LEFT JOIN notamantenimiento nm ON n.idNota = nm.idNota
          WHERE 1=1";

if ($nombre !== '') {
  // Buscar por cliente o por folio
  $query .= " AND (c.NombreCliente LIKE :nombre OR CAST(n.idNota AS CHAR) LIKE :folio)";
}
if ($estado !== '') {
  $query .= " AND (nd.estatus = :estado OR nm.Estatus = :estado)";
}
if ($tipo === 'Diseño') {
  $query .= " AND nd.idDiseño IS NOT NULL";
} elseif ($tipo === 'Mantenimiento') {
  $query .= " AND nm.idMantenimiento IS NOT NULL";
}
if ($fecha !== '') {
  $query .= " AND n.FechaRecepcion = :fecha";
}
if ($misOrdenes == 1) {
  $query .= " AND n.idUsuario = :idUsuario";
}
if ($OrdenesTrabajadas == 1) {
  $query .= " AND (nd.idDiseñador = :idUsuario OR nm.idTecnico = :idUsuario)";
}

$stmt = $conn->prepare($query);


if ($nombre !== '') {
  $stmt->bindValue(':nombre', "%$nombre%");
  $stmt->bindValue(':folio',  "%$nombre%");
}
if ($estado !== '') { $stmt->bindValue(':estado', $estado); }
if ($fecha  !== '') { $stmt->bindValue(':fecha',  $fecha);  }

if ($misOrdenes == 1 || $OrdenesTrabajadas == 1) {
  $stmt->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

//ACTUALIZAR  A RETRASADO
foreach ($result as $row) {
  if ($row['estado'] === 'Retrasado') {

    if ($row['tipo'] === 'Diseño') {
      $update = $conn->prepare("
        UPDATE notadiseño nd
        INNER JOIN nota n ON nd.idNota = n.idNota
        SET nd.estatus = 'Retrasado'
        WHERE n.idNota = ?
      ");
      $update->execute([$row['folio']]);
    }

    if ($row['tipo'] === 'Mantenimiento') {
      $update = $conn->prepare("
        UPDATE notamantenimiento nm
        INNER JOIN nota n ON nm.idNota = n.idNota
        SET nm.Estatus = 'Retrasado'
        WHERE n.idNota = ?
      ");
      $update->execute([$row['folio']]);
    }

  }
}


// Permisos para el botón editar
foreach ($result as &$row) {
  $puedeEditar =
    in_array('administrador', $roles) ||
    in_array('encargado', $roles) ||
    (in_array('diseñador', $roles) && $row['idDiseñador'] == $idUsuario) ||
    (in_array('tecnico', $roles) && $row['idTecnico'] == $idUsuario);

  $row['puedeEditar'] = $puedeEditar;
}

// Verificar si la orden tiene servicios de tipo "Software" y si ya hay licencias registradas 
foreach ($result as &$row) {
  
    // Verificar si es orden de mantenimiento
    if ($row['tipo'] === 'Mantenimiento') {

        // Verificar si tiene servicios tipo Software
        $stmtCheckSoftware = $conn->prepare("
            SELECT COUNT(*) 
            FROM auxservicios a
            INNER JOIN catalogomnt c ON a.idCatalogoMnt = c.idCatalogoMnt
            INNER JOIN tipomantenimiento t ON c.idTipoMnt = t.idTipoMnt
            INNER JOIN notamantenimiento nm ON a.idMantenimiento = nm.idMantenimiento
            WHERE t.NombreTipo = 'Software' AND nm.idNota = :idNota
        ");
        $stmtCheckSoftware->bindValue(':idNota', $row['folio']);
        $stmtCheckSoftware->execute();
        $row['tieneSoftware'] = $stmtCheckSoftware->fetchColumn() > 0;

        // Verificar si ya hay licencias registradas
        $stmtCheckLic = $conn->prepare("
            SELECT COUNT(*) FROM licenciasoftware
            WHERE idNota = :idNota
        ");
        $stmtCheckLic->bindValue(':idNota', $row['folio']);
        $stmtCheckLic->execute();
        $row['tieneLicencia'] = $stmtCheckLic->fetchColumn() > 0;
    } else {
        $row['tieneSoftware'] = false;
        $row['tieneLicencia'] = false;
    }
}


echo json_encode($result);
