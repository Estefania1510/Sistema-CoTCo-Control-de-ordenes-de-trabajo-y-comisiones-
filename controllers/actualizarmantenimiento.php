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

// servicios de catálogo
$tipos = $_POST['tipo'] ?? [];
$servicios = $_POST['servicio'] ?? [];
$precios = $_POST['precio'] ?? [];

try {
  $conn->beginTransaction();

  if ($idTecnico === '' || $idTecnico === 'null' || is_null($idTecnico)) {
    $stmtTec = $conn->prepare("SELECT idTecnico FROM notamantenimiento WHERE idMantenimiento = ?");
    $stmtTec->execute([$idMantenimiento]);
    $idTecnico = $stmtTec->fetchColumn();
  }

  // actualizar datos de nota
  $stmt1 = $conn->prepare("
    UPDATE nota 
    SET Descripcion = ?, Comentario = ?, Total = ?, Anticipo = ?, Resto = ?, FechaEntrega = ?
    WHERE idNota = ?
  ");
  $stmt1->execute([$descProblema, $sugerencia, $total, $anticipo, $resto, $fechaEntrega, $idNota]);

  // === actualizar datos del mantenimiento ===
  $stmt2 = $conn->prepare("
    UPDATE notamantenimiento
    SET Equipo = ?, Marca = ?, Model = ?, Contraseña = ?, Accesorios = ?, 
        SugerenciaTecn = ?, Estatus = ?, DescripcionEquipo = ?, idTecnico = ?
    WHERE idMantenimiento = ?
  ");
  $stmt2->bindValue(1, $equipo);
  $stmt2->bindValue(2, $marca);
  $stmt2->bindValue(3, $modelo);
  $stmt2->bindValue(4, $contrasena);
  $stmt2->bindValue(5, $accesorios);
  $stmt2->bindValue(6, $sugerencia);
  $stmt2->bindValue(7, $estatus);
  $stmt2->bindValue(8, $descEquipo);
  $stmt2->bindValue(9, $idTecnico, is_null($idTecnico) ? PDO::PARAM_NULL : PDO::PARAM_INT);
  $stmt2->bindValue(10, $idMantenimiento, PDO::PARAM_INT);
  $stmt2->execute();

  //eliminar servicios existentes
  $conn->prepare("DELETE FROM auxservicios WHERE idMantenimiento = ?")->execute([$idMantenimiento]);

  // insertar nuevos servicios 
  $stmtCatalogo = $conn->prepare("
    SELECT c.idCatalogoMnt 
    FROM catalogomnt c
    INNER JOIN tipomantenimiento t ON c.idTipoMnt = t.idTipoMnt
    WHERE t.NombreTipo = :tipo AND c.Servicio = :servicio
  ");

  $stmtInsert = $conn->prepare("
    INSERT INTO auxservicios (idMantenimiento, idCatalogoMnt, Precio)
    VALUES (?, ?, ?)
  ");

  for ($i = 0; $i < count($servicios); $i++) {
    $tipo = trim($tipos[$i]);
    $servicio = trim($servicios[$i]);
    $precio = floatval($precios[$i] ?? 0);

    if (!empty($tipo) && !empty($servicio)) {
      $stmtCatalogo->execute([':tipo' => $tipo, ':servicio' => $servicio]);
      $cat = $stmtCatalogo->fetch(PDO::FETCH_ASSOC);
      if ($cat) {
        $stmtInsert->execute([$idMantenimiento, $cat['idCatalogoMnt'], $precio]);
      }
    }
  }

  // recalcular totales 
  $sqlTotal = "
    SELECT 
      (SELECT COALESCE(SUM(a.Precio),0)
       FROM auxservicios a
       WHERE a.idMantenimiento = m.idMantenimiento) AS subtotal,
      COALESCE(n.Anticipo,0) AS Anticipo
    FROM nota n
    INNER JOIN notamantenimiento m ON m.idNota = n.idNota
    WHERE n.idNota = ?
    LIMIT 1
  ";
  $stmtTot = $conn->prepare($sqlTotal);
  $stmtTot->execute([$idNota]);
  $totales = $stmtTot->fetch(PDO::FETCH_ASSOC);

  $subtotal = (float)($totales['subtotal'] ?? 0);
  $anticipo = (float)($totales['Anticipo'] ?? 0);

  $total = $subtotal;
  $resto = $total - $anticipo;

  $stmtUpd = $conn->prepare("
    UPDATE nota 
    SET Total = ?, Resto = ?, FechaEntrega = ?
    WHERE idNota = ?
  ");
  $stmtUpd->execute([$total, $resto, $fechaEntrega, $idNota]);

  $conn->commit();

  echo json_encode([
    "status" => "success",
    "message" => "La orden de mantenimiento fue actualizada correctamente."
  ]);

} catch (Exception $e) {
  $conn->rollBack();
  $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('actualizarmantenimiento', ?)");
  $log->execute([$e->getMessage()]);

  echo json_encode([
    "status" => "error",
    "message" => "Error: " . $e->getMessage()
  ]);
}
