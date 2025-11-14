<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();

header('Content-Type: application/json');

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

try {
  $data = json_decode(file_get_contents("php://input"), true);
  $accion = $data['accion'] ?? '';
  $fechaInicio = $data['fechaInicio'] ?? null;
  $fechaFin = $data['fechaFin'] ?? null;
  $filtroEstado = $data['filtroEstado'] ?? 'todas';
  $idUsuario = $_SESSION['idUsuario'] ?? null;
  $roles = $_SESSION['roles'] ?? [];

  $isAdmin = in_array('administrador', $roles);
  $isEncargado = in_array('encargado', $roles);
  $isPower = ($isAdmin || $isEncargado);
  

if ($accion === 'listar') {
  if (!$isPower) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
  }

  // Traer todos los usuarios
  $sql = "
    SELECT 
      u.idUsuario,
      u.NombreUsuario,
      GROUP_CONCAT(DISTINCT r.rol ORDER BY r.rol SEPARATOR ', ') AS rol,
      COUNT(DISTINCT c.idComisiones) AS trabajos
    FROM usuario u
    INNER JOIN usuarioroles ur ON u.idUsuario = ur.idUsuario
    INNER JOIN rol r ON ur.idRol = r.idRol
    LEFT JOIN comisiones c ON u.idUsuario = c.idUsuario
    WHERE u.Estatus = 'Activo'
      AND r.rol NOT IN ('administrador', 'encargado')
    GROUP BY u.idUsuario, u.NombreUsuario
    ORDER BY u.NombreUsuario ASC
  ";

  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // mostrar "Diseñador/Técnico")
  foreach ($usuarios as &$u) {
    $rol = $u['rol'];
    if (strpos($rol, 'diseñador') !== false && strpos($rol, 'tecnico') !== false) {
      $u['rol'] = 'Diseñador/Técnico';
    } elseif (strpos($rol, 'diseñador') !== false) {
      $u['rol'] = 'Diseñador';
    } elseif (strpos($rol, 'tecnico') !== false) {
      $u['rol'] = 'Técnico';
    } else {
      $u['rol'] = ucfirst($rol);
    }
  }

  echo json_encode(["status" => "ok", "data" => $usuarios]);
  exit;
}

  // DETALLE DE COMISIONES
  if ($accion === 'detalleUsuario') {
    if (!$isPower) {
      $data['idUsuario'] = $idUsuario; 
    }

    $usuarioDetalle = $data['idUsuario'] ?? $idUsuario;
    $filtroEstado = $data['filtroEstado'] ?? 'todas';

    $where = "WHERE c.idUsuario = :idUsuario";
    if ($filtroEstado !== 'todas') {
      $where .= " AND c.estado = :estado";
    }
    if ($fechaInicio && $fechaFin) {
      $where .= " AND n.FechaRecepcion BETWEEN :inicio AND :fin";
    }

    $sql = "
      SELECT 
        c.idComisiones,
        n.idNota AS folio,
        c.tipo,
        c.monto,
        c.estado,
        IFNULL(DATE_FORMAT(c.fechapago, '%d-%m-%Y'), '-') AS fechapago,
        DATE_FORMAT(n.FechaRecepcion, '%d-%m-%Y') AS FechaRecepcion,
        IFNULL(DATE_FORMAT(n.FechaEntrega, '%d-%m-%Y'), 'Pendiente') AS FechaEntrega,
        c.porcentaje,
        cli.NombreCliente
      FROM comisiones c
      INNER JOIN nota n ON c.idnota = n.idNota
      INNER JOIN cliente cli ON n.idCliente = cli.idCliente
      $where
      ORDER BY n.FechaRecepcion DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idUsuario', $usuarioDetalle);
    if ($filtroEstado !== 'todas') {
      $stmt->bindParam(':estado', $filtroEstado);
    }
    if ($fechaInicio && $fechaFin) {
      $stmt->bindParam(':inicio', $fechaInicio);
      $stmt->bindParam(':fin', $fechaFin);
    }
    $stmt->execute();
    $detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Resumen automático
    $totales = [
      "entregadas" => 0,
      "pendientes" => 0,
      "pagadas" => 0
    ];

    foreach ($detalle as $c) {
      switch ($c['estado']) {
        case 'Orden Entregada':
          $totales["entregadas"] += $c['monto'];
          break;
        case 'Orden no Entregada':
          $totales["pendientes"] += $c['monto'];
          break;
        case 'Pagado':
          $totales["pagadas"] += $c['monto'];
          break;

      }
    }

    echo json_encode([
      "status" => "ok",
      "data" => $detalle,
      "totales" => $totales
    ]);
    exit;
  }

  // Actualizar porcentaje
if ($accion === "actualizarPorcentaje") {

    if (!$isAdmin) {
        echo json_encode(["status" => "error", "message" => "No autorizado"]);
        exit;
    }

    $nuevo = $data['porcentaje'] ?? null;

    if (!$nuevo || $nuevo < 1 || $nuevo > 100) {
        echo json_encode(["status" => "error", "message" => "Porcentaje inválido"]);
        exit;
    }

    // 1️⃣ Actualizar porcentaje global
    $stmt = $conn->prepare("
        UPDATE configcomision 
        SET valor = :valor
        WHERE nombreajuste = 'porcentaje'
    ");
    $stmt->bindParam(":valor", $nuevo);
    $stmt->execute();

    // Recalcular todas las comisiones no pagadas
    $sqlPendientes = "
        SELECT idComisiones, monto, porcentaje, tipo, idnota
        FROM comisiones
        WHERE estado != 'Pagado'
    ";
    $stmtP = $conn->prepare($sqlPendientes);
    $stmtP->execute();
    $comisiones = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comisiones as $c) {

        $stmtTotal = $conn->prepare("SELECT Total FROM nota WHERE idNota = ?");
        $stmtTotal->execute([$c['idnota']]);
        $totalNota = (float)$stmtTotal->fetchColumn();

        // Calcular nuevo monto según el nuevo porcentaje
        $nuevoMonto = round($totalNota * ($nuevo / 100), 2);
        
        $stmtUpd = $conn->prepare("
            UPDATE comisiones
            SET porcentaje = ?, monto = ?
            WHERE idComisiones = ?
        ");
        $stmtUpd->execute([$nuevo, $nuevoMonto, $c['idComisiones']]);
    }

    echo json_encode(["status" => "ok", "message" => "Porcentaje actualizado y comisiones recalculadas"]);
    exit;
}

  // PAGAR O ADELANTAR COMISIÓN 
  if (in_array($accion, ['marcarPagada', 'adelantarComision'])) {
    if (!$isPower) {
      echo json_encode(["status" => "error", "message" => "No autorizado"]);
      exit;
    }

    $idComision = $data['idComision'] ?? null;
    if (!$idComision) {
      echo json_encode(["status" => "error", "message" => "ID de comisión faltante"]);
      exit;
    }

    $fechaHoy = date('Y-m-d');
    $stmt = $conn->prepare("
      UPDATE comisiones 
      SET estado = 'Pagado', fechapago = :fecha 
      WHERE idComisiones = :id
    ");
    $stmt->bindParam(':fecha', $fechaHoy);
    $stmt->bindParam(':id', $idComision);
    $stmt->execute();

    echo json_encode(["status" => "ok", "message" => "Comisión actualizada a pagada."]);
    exit;
  }

  echo json_encode(["status" => "error", "message" => "Acción no válida."]);
} catch (Throwable $e) {
  $stmtErr = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('comisionesController', :e)");
  $stmtErr->bindValue(':e', $e->getMessage());
  $stmtErr->execute();
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
