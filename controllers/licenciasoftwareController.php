<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();
require_once __DIR__ . '/../config/session_control.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$accion = $_POST['accion'] ?? null;
if (!$accion) {
  $data = json_decode(file_get_contents("php://input"), true);
  $accion = $data['accion'] ?? null;
}

switch ($accion) {

  case 'listar':
    $stmt = $conn->query("SELECT * FROM licenciasoftware ORDER BY idLS DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

  case 'agregar':
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("INSERT INTO licenciasoftware (Licencia, Software, Estatus) VALUES (?, ?, 'Libre')");
    $stmt->execute([$data['licencia'], $data['software']]);
    echo json_encode(["status" => "success", "message" => "Licencia agregada correctamente."]);
    break;

  case 'obtener':
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("SELECT * FROM licenciasoftware WHERE idLS = ?");
    $stmt->execute([$data['idLS']]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    break;

  case 'editar':
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("UPDATE licenciasoftware SET Licencia=?, Software=? WHERE idLS=?");
    $stmt->execute([$data['licencia'], $data['software'], $data['idLS']]);
    echo json_encode(["status" => "success", "message" => "Licencia actualizada correctamente."]);
    break;

  case 'baja':
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("UPDATE licenciasoftware SET Estatus='Baja' WHERE idLS=?");
    $stmt->execute([$data['idLS']]);
    echo json_encode(["status" => "success"]);
    break;

  case 'reactivar':
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("UPDATE licenciasoftware SET Estatus='Libre', idNota=NULL WHERE idLS=?");
    $stmt->execute([$data['idLS']]);
    echo json_encode(["status" => "success"]);
    break;

    case 'listar_instaladas':
  $stmt = $conn->query("
    SELECT
      n.idNota AS FolioOrden,
      c.NombreCliente AS Cliente,
      COALESCE(ls.Fecha, n.FechaRecepcion) AS Fecha,
      ls.Software,
      ls.Licencia,
      ls.Password,
      ls.Equipo,
      ls.Procesador,
      ls.IdDispositivo,
      ls.IdProducto
    FROM licenciasoftware ls
    LEFT JOIN nota n ON n.idNota = ls.idNota
    LEFT JOIN cliente c ON c.idCliente = n.idCliente
    WHERE ls.idNota IS NOT NULL
    ORDER BY n.idNota DESC, ls.idLS DESC
  ");
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  break;

  default:
    echo json_encode(["status" => "error", "message" => "Acción no reconocida"]);
    break;

}
