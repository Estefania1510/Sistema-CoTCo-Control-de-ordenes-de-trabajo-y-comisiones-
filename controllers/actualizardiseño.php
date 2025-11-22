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

    $stmt1 = $conn->prepare("
        UPDATE nota 
        SET Descripcion=?, Comentario=?, Anticipo=?, FechaEntrega=? 
        WHERE idNota=?
    ");
    $stmt1->execute([$descripcion, $comentario, $anticipo, $fechaEntrega, $idNota]);

    $stmt2 = $conn->prepare("
        UPDATE notadiseño 
        SET estatus=?, CostoDiseño=?, idDiseñador=? 
        WHERE idDiseño=?
    ");
    $stmt2->bindValue(1, $estatus);
    $stmt2->bindValue(2, $costo);
    $stmt2->bindValue(3, $idDiseñador, is_null($idDiseñador) ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt2->bindValue(4, $idDiseño, PDO::PARAM_INT);
    $stmt2->execute();

    $conn->prepare("DELETE FROM material WHERE idDiseño=?")
         ->execute([$idDiseño]);

    $stmtMat = $conn->prepare("
        INSERT INTO material (Material, Cantidad, Precio, Subtotal, idDiseño)
        VALUES (?, ?, ?, ?, ?)
    ");

    for ($i = 0; $i < count($materiales); $i++) {
        $mat  = trim($materiales[$i]);
        $cant = floatval($cantidades[$i] ?? 0);
        $prec = floatval($precios[$i] ?? 0);
        $sub  = $cant * $prec;

        if ($mat !== '') {
            $stmtMat->execute([$mat, $cant, $prec, $sub, $idDiseño]);
        }
    }

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

    $subtotal = (float)$totales['subtotal'];
    $diseno   = (float)$totales['CostoDiseño'];
    $anticipo = (float)$totales['Anticipo'];

    $total = $subtotal + $diseno;
    $resto = $total - $anticipo;

    $stmtUpd = $conn->prepare("
        UPDATE nota 
        SET Total=?, Resto=?, Anticipo=?, FechaEntrega=?
        WHERE idNota=?
    ");
    $stmtUpd->execute([$total, $resto, $anticipo, $fechaEntrega, $idNota]);

    $stmtC = $conn->prepare("
        SELECT idComisiones 
        FROM comisiones 
        WHERE idnota = ? AND tipo = 'Diseño'
        LIMIT 1
    ");
    $stmtC->execute([$idNota]);
    $idComision = $stmtC->fetchColumn();

    if (!$idComision && !empty($idDiseñador)) {

        $stmtPor = $conn->prepare("
            SELECT valor FROM configcomision 
            WHERE nombreajuste = 'porcentaje'
        ");
        $stmtPor->execute();
        $porcentaje = (float)$stmtPor->fetchColumn();
        if ($porcentaje <= 0) $porcentaje = 30;

        $montoInicial = ($diseno * $porcentaje) / 100;

        $estadoComision = 'Orden no Entregada';
        if ($estatus === 'Entregado')  $estadoComision = 'Orden Entregada';
        if ($estatus === 'Cancelado')  $estadoComision = 'Orden Cancelada';

        if ($estadoComision === 'Orden Cancelada') {
            $montoInicial = 0;
        }

        $stmtIns = $conn->prepare("
            INSERT INTO comisiones
            (tipo, porcentaje, fechapago, monto, estado, idUsuario, idnota)
            VALUES ('Diseño', ?, NULL, ?, ?, ?, ?)
        ");
        $stmtIns->execute([
            $porcentaje,
            $montoInicial,
            $estadoComision,
            $idDiseñador,
            $idNota
        ]);

        $idComision = $conn->lastInsertId();
    }


    if ($idComision) {

        if ($estatus === 'Cancelado') {

            $conn->prepare("
                UPDATE comisiones SET 
                estado='Orden Cancelada',
                monto=0,
                fechapago=NULL
                WHERE idComisiones=? AND estado!='Pagado'
            ")->execute([$idComision]);

        } elseif ($estatus === 'Entregado') {

            $conn->prepare("
                UPDATE comisiones SET 
                estado='Orden Entregada'
                WHERE idComisiones=? AND estado!='Pagado'
            ")->execute([$idComision]);

        } else {

            $conn->prepare("
                UPDATE comisiones SET 
                estado='Orden no Entregada'
                WHERE idComisiones=? AND estado!='Pagado'
            ")->execute([$idComision]);
        }
    }

    $idDiseOriginal = $_POST['idDiseñadorOriginal'] ?? null;

    if ($idComision && !empty($idDiseñador) && $idDiseñador !== $idDiseOriginal) {
        $conn->prepare("
            UPDATE comisiones 
            SET idUsuario = ?
            WHERE idComisiones = ?
        ")->execute([$idDiseñador, $idComision]);
    }

    if ($idComision) {

        $stmtEstado = $conn->prepare("
            SELECT estado FROM comisiones WHERE idComisiones = ?
        ");
        $stmtEstado->execute([$idComision]);
        $estadoActual = $stmtEstado->fetchColumn();

        if ($estadoActual !== 'Orden Cancelada') {

            $stmtP = $conn->prepare("
                SELECT porcentaje FROM comisiones WHERE idComisiones = ?
            ");
            $stmtP->execute([$idComision]);
            $porcentaje = (float)$stmtP->fetchColumn();
            if ($porcentaje <= 0) $porcentaje = 30;

            $nuevoMonto = ($diseno * $porcentaje) / 100;

            $conn->prepare("
                UPDATE comisiones 
                SET monto=?, porcentaje=?
                WHERE idComisiones=?
            ")->execute([$nuevoMonto, $porcentaje, $idComision]);
        }
    }

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
