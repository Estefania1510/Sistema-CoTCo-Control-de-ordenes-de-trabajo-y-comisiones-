<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();
require_once __DIR__ . '/../config/session_control.php';
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

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

    COUNT(DISTINCT IF(c.estado <> 'Pagado' AND c.estado <> 'Orden Cancelada', c.idComisiones, NULL)) AS trabajos

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

    $stmtPend = $conn->prepare("
      SELECT COALESCE(SUM(monto),0)
      FROM comisiones
      WHERE idUsuario = :idUsuario
        AND estado <> 'Pagado'
        AND estado <> 'Orden Cancelada'
    ");
    $stmtPend->bindValue(':idUsuario', $usuarioDetalle, PDO::PARAM_INT);
    $stmtPend->execute();
    $pendienteGlobal = (float)$stmtPend->fetchColumn();

    echo json_encode([
      "status" => "ok",
      "data" => $detalle,
      "totales" => $totales,
      "pendiente" => $pendienteGlobal

    ]);
    exit;
  } //fin detalleusuario

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

      $idComision = $data['idComision'] ?? null;
      if (!$idComision) {
        echo json_encode(["status" => "error", "message" => "ID de comisión faltante"]);
        exit;
      }

      // Si NO es admin/encargado, solo puede marcar pagada SU propia comisión
      if (!$isPower) {
        $stmtOwner = $conn->prepare("SELECT idUsuario, estado FROM comisiones WHERE idComisiones = :id");
        $stmtOwner->bindValue(':id', $idComision, PDO::PARAM_INT);
        $stmtOwner->execute();
        $row = $stmtOwner->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
          echo json_encode(["status" => "error", "message" => "Comisión no encontrada"]);
          exit;
        }

        if ((int)$row['idUsuario'] !== (int)$idUsuario) {
          echo json_encode(["status" => "error", "message" => "No autorizado"]);
          exit;
        }

        // opcional: evitar pagar canceladas
        if ($row['estado'] === 'Orden Cancelada') {
          echo json_encode(["status" => "error", "message" => "No se puede marcar pagada una comisión cancelada"]);
          exit;
        }
      }

      // Marcar pagada (admin/encargado pueden pagar cualquiera; usuario solo la suya)
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

      // PAGAR TODO (usuario normal: solo sus comisiones; admin/encargado: también puede)
    if ($accion === 'pagarTodoUsuario') {

      // Si viene idUsuario en request y es power, podría pagar el de otro usuario.
      // Si NO es power, solo paga el suyo.
      $usuarioTarget = $data['idUsuario'] ?? $idUsuario;

      if (!$isPower) {
        $usuarioTarget = $idUsuario;
      }

      $fechaHoy = date('Y-m-d');

      // Marca como pagadas TODAS las comisiones NO pagadas del usuario (y no canceladas)
      $stmt = $conn->prepare("
        UPDATE comisiones
        SET estado = 'Pagado', fechapago = :fecha
        WHERE idUsuario = :idUsuario
          AND estado <> 'Pagado'
          AND estado <> 'Orden Cancelada'
      ");
      $stmt->bindValue(':fecha', $fechaHoy);
      $stmt->bindValue(':idUsuario', $usuarioTarget, PDO::PARAM_INT);
      $stmt->execute();

      echo json_encode([
        "status" => "ok",
        "message" => "Todas las comisiones fueron marcadas como pagadas"
      ]);
      exit;
    }



  echo json_encode(["status" => "error", "message" => "Acción no válida."]);
} catch (Throwable $e) {
  $stmtErr = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('comisionesController', :e)");
  $stmtErr->bindValue(':e', $e->getMessage());
  $stmtErr->execute();
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
