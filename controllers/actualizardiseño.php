<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_POST['idNota'] ?? null;
$idDiseño = $_POST['idDiseño'] ?? null;
$estatus = $_POST['estatus'] ?? '';
$costo = $_POST['diseño'] ?? 0;
$anticipo = $_POST['anticipo'] ?? 0;
$comentario = trim($_POST['comentarios'] ?? '');
$descripcion = trim($_POST['Descripcion'] ?? '');
$fechaEntrega = !empty($_POST['FechaEntrega']) ? $_POST['FechaEntrega'] : null;
$idDiseñador = $_POST['idDiseñador'] ?? null;

// Materiales
$materiales = $_POST['material'] ?? [];
$cantidades = $_POST['cantidad'] ?? [];
$precios = $_POST['precio'] ?? [];

try {
  $conn->beginTransaction();

 if ($idDiseñador === '' || $idDiseñador === 'null' || is_null($idDiseñador)) {
  $stmtDise = $conn->prepare("SELECT idDiseñador FROM notadiseño WHERE idDiseño = ?");
  $stmtDise->execute([$idDiseño]);
  $idDiseñador = $stmtDise->fetchColumn();
}

  $stmt1 = $conn->prepare("UPDATE nota SET Descripcion=?, Comentario=?, Anticipo=?, FechaEntrega=? WHERE idNota=?");
  $stmt1->execute([$descripcion, $comentario, $anticipo, $fechaEntrega, $idNota]);
  
  $stmt2 = $conn->prepare("UPDATE notadiseño SET estatus=?, CostoDiseño=?, idDiseñador=? WHERE idDiseño=?");
  $stmt2->bindValue(1, $estatus);
  $stmt2->bindValue(2, $costo);
  $stmt2->bindValue(3, $idDiseñador, is_null($idDiseñador) ? PDO::PARAM_NULL : PDO::PARAM_INT);
  $stmt2->bindValue(4, $idDiseño, PDO::PARAM_INT);
  $stmt2->execute();

  $conn->prepare("DELETE FROM material WHERE idDiseño=?")->execute([$idDiseño]);

  $stmtMat = $conn->prepare("INSERT INTO material (Material, Cantidad, Precio, Subtotal, idDiseño) VALUES (?, ?, ?, ?, ?)");
  for ($i = 0; $i < count($materiales); $i++) {
    $material = trim($materiales[$i]);
    $cantidad = floatval($cantidades[$i] ?? 0);
    $precio = floatval($precios[$i] ?? 0);
    $subtotal = $cantidad * $precio;
    if ($material !== '') {
      $stmtMat->execute([$material, $cantidad, $precio, $subtotal, $idDiseño]);
    }
  }

// Recalcular totales al actualizar
$sqlTotal = "
  SELECT
    (SELECT COALESCE(SUM(m.Subtotal),0)
     FROM material m
     WHERE m.idDiseño = d.idDiseño) AS subtotal,
    COALESCE(n.Anticipo,0) AS Anticipo,
    COALESCE(d.CostoDiseño,0) AS CostoDiseño
  FROM nota n
  INNER JOIN notadiseño d ON d.idNota = n.idNota
  WHERE n.idNota = ?
  LIMIT 1
";
$stmtTot = $conn->prepare($sqlTotal);
$stmtTot->execute([$idNota]);
$totales = $stmtTot->fetch(PDO::FETCH_ASSOC);

$subtotal = (float)($totales['subtotal'] ?? 0);
$diseno   = (float)($totales['CostoDiseño'] ?? 0);
$anticipo = (float)($totales['Anticipo'] ?? 0);


$total = $subtotal + $diseno;
$resto = $total - $anticipo;


$stmtUpd = $conn->prepare("
  UPDATE nota 
  SET Total = ?, Resto = ?, Anticipo = ?, FechaEntrega = ?
  WHERE idNota = ?
");
$stmtUpd->execute([$total, $resto, $anticipo, $fechaEntrega, $idNota]);


$conn->commit();

echo json_encode([
  "status" => "success",
  "message" => "La orden de diseño fue actualizada correctamente."
]);



} catch (Exception $e) {
  $conn->rollBack();
  echo json_encode([
    "status" => "error",
    "message" => "Error: " . $e->getMessage()
  ]);
}
