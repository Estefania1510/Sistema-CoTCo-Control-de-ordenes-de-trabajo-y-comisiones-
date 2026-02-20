<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();
require_once __DIR__ . '/../config/session_control.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_POST['idNota'] ?? null;
$idMantenimiento = $_POST['idMantenimiento'] ?? null;
$estatus = $_POST['estatus'] ?? '';
$fechaEntrega = !empty($_POST['FechaEntrega']) ? $_POST['FechaEntrega'] : null;
$idTecnico = $_POST['idTecnico'] ?? null;

$trabajo = trim($_POST['trabajo'] ?? '');
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
$cantidades = $_POST['cantidad'] ?? [];
$origenes = $_POST['origen'] ?? [];

try {
    $conn->beginTransaction();
    $stmtN = $conn->prepare("
        UPDATE nota
        SET Trabajo = ?, Descripcion = ?, Comentario = ?, Total = ?, Anticipo = ?, Resto = ?, FechaEntrega = ?
        WHERE idNota = ?
    ");
    $stmtN->execute([
        $trabajo,
        $descProblema,
        $sugerencia,
        $total,
        $anticipo,
        $resto,
        $fechaEntrega,
        $idNota
    ]);

    $stmtM = $conn->prepare("
        UPDATE notamantenimiento
        SET Equipo=?, Marca=?, Model=?, Contraseña=?, Accesorios=?, 
            SugerenciaTecn=?, Estatus=?, DescripcionEquipo=?, idTecnico=?
        WHERE idMantenimiento = ?
    ");
    $stmtM->execute([
        $equipo, $marca, $modelo, $contrasena, $accesorios,
        $sugerencia, $estatus, $descEquipo,
        $idTecnico ?: null,
        $idMantenimiento
    ]);


    $conn->prepare("DELETE FROM auxservicios WHERE idMantenimiento = ?")
         ->execute([$idMantenimiento]);

    $stmtCatalogo = $conn->prepare("
      SELECT c.idCatalogoMnt
      FROM catalogomnt c
      INNER JOIN tipomantenimiento t ON c.idTipoMnt = t.idTipoMnt
      WHERE t.NombreTipo = :tipo AND c.Servicio = :servicio
      LIMIT 1
    ");

    $stmtIns = $conn->prepare("
      INSERT INTO auxservicios (idMantenimiento, idCatalogoMnt, Descripcion, Cantidad, Precio, Subtotal, Origen)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $max = count($servicios);

    for ($i = 0; $i < $max; $i++) {

      $origen = trim(($origenes[$i] ?? 'CATALOGO'));
      $tipo   = trim(($tipos[$i] ?? ''));
      $desc   = trim(($servicios[$i] ?? ''));

      $precioStr = str_replace(',', '.', (string)($precios[$i] ?? '0'));
      $precio = floatval($precioStr);

      $cant = intval($cantidades[$i] ?? 1);
      if ($cant <= 0) $cant = 1;

      if ($desc === '') continue;

      $subtotal = $cant * $precio;

      if ($origen === 'MANUAL') {
        // MANUAL: idCatalogoMnt NULL
        $stmtIns->execute([$idMantenimiento, null, $desc, $cant, $precio, $subtotal, 'MANUAL']);
      } else {
        // CATALOGO: requiere tipo + servicio
        if ($tipo === '') continue;

        $stmtCatalogo->execute([':tipo' => $tipo, ':servicio' => $desc]);
        $idCat = $stmtCatalogo->fetchColumn();

        if ($idCat) {
          $stmtIns->execute([$idMantenimiento, $idCat, $desc, $cant, $precio, $subtotal, 'CATALOGO']);
        }
      }
    }



    $stmtC = $conn->prepare("
        SELECT idComisiones FROM comisiones
        WHERE idnota = ? AND tipo = 'Mantenimiento'
        LIMIT 1
    ");
    $stmtC->execute([$idNota]);
    $idComision = $stmtC->fetchColumn();

    if (!$idComision && !empty($idTecnico)) {

        $stmtPor = $conn->prepare("
            SELECT valor FROM configcomision WHERE nombreajuste = 'porcentaje'
        ");


        $stmtPor->execute();
        $porcentaje = (float)$stmtPor->fetchColumn() ?: 30;
        $montoInicial = ($total * $porcentaje) / 100;
        $estadoComision = 'Orden no Entregada';
        if ($estatus === 'Entregado') $estadoComision = 'Orden Entregada';
        if ($estatus === 'Cancelado') $estadoComision = 'Orden Cancelada';

        if ($estadoComision === 'Orden Cancelada') {
            $montoInicial = 0;
        }

        $stmtInsert = $conn->prepare("
            INSERT INTO comisiones (tipo, porcentaje, fechapago, monto, estado, idUsuario, idnota)
            VALUES ('Mantenimiento', ?, NULL, ?, ?, ?, ?)
        ");
        $stmtInsert->execute([
            $porcentaje,
            $montoInicial,
            $estadoComision,
            $idTecnico,
            $idNota
        ]);

        $idComision = $conn->lastInsertId();
    }

    $idTecOriginal = $_POST['idTecnicoOriginal'] ?? null;

    if ($idComision && !empty($idTecnico) && $idTecnico !== $idTecOriginal) {
        $conn->prepare("
            UPDATE comisiones 
            SET idUsuario = ? 
            WHERE idComisiones = ?
        ")->execute([$idTecnico, $idComision]);
    }

    if ($idComision) {

        if ($estatus === 'Cancelado') {

            $conn->prepare("
                UPDATE comisiones
                SET estado='Orden Cancelada', monto=0, fechapago=NULL
                WHERE idComisiones=? AND estado!='Pagado'
            ")->execute([$idComision]);

        } elseif ($estatus === 'Entregado') {

            $conn->prepare("
                UPDATE comisiones
                SET estado='Orden Entregada'
                WHERE idComisiones=? AND estado!='Pagado'
            ")->execute([$idComision]);

        } else {

            $conn->prepare("
                UPDATE comisiones
                SET estado='Orden no Entregada'
                WHERE idComisiones=? AND estado!='Pagado'
            ")->execute([$idComision]);
        }
    }

    if ($idComision) {

        $stmtEstado = $conn->prepare("
            SELECT estado FROM comisiones WHERE idComisiones=?
        ");
        $stmtEstado->execute([$idComision]);
        $estadoActual = $stmtEstado->fetchColumn();

        if ($estadoActual !== 'Orden Cancelada') {

            $stmtP = $conn->prepare("
                SELECT porcentaje FROM comisiones WHERE idComisiones=?
            ");
            $stmtP->execute([$idComision]);
            $porcentaje = (float)$stmtP->fetchColumn() ?: 30;

            $nuevoMonto = ($total * $porcentaje) / 100;

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
        "message" => "La orden de mantenimiento fue actualizada correctamente."
    ]);

} catch (Exception $e) {

    $conn->rollBack();

    $conn->prepare("
        INSERT INTO logerror (metodo, excepcion) 
        VALUES ('actualizarmantenimiento', ?)
    ")->execute([$e->getMessage()]);

    echo json_encode([
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ]);
}
