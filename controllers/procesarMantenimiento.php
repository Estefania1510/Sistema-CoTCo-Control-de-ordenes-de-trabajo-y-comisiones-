<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/../config/session_control.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

function normalizarNombre(string $s): string {
    $s = trim($s);
    $s = mb_strtoupper($s, 'UTF-8');
    $s = preg_replace('/\s+/', ' ', $s);
    return $s;
}

function obtenerIdClienteExistente(PDO $conn, string $nombreNorm): ?int {
    $stmt = $conn->prepare("
        SELECT idCliente
        FROM cliente
        WHERE UPPER(TRIM(NombreCliente)) = :nom
        LIMIT 1
    ");
    $stmt->execute([':nom' => $nombreNorm]);
    $id = $stmt->fetchColumn();
    return $id ? (int)$id : null;
}

function obtenerOCrearCliente(
    PDO $conn,
    ?int $idClienteForm,
    string $nombreCliente,
    string $direccion,
    string $telefono,
    string $telefono2
): int {

    $nombreNorm = normalizarNombre($nombreCliente);
    if ($nombreNorm === '') {
        throw new Exception("El nombre del cliente es obligatorio.");
    }

    $dirNorm  = trim($direccion);
    $telNorm  = trim($telefono);
    $tel2Norm = trim($telefono2);

    // 1) Viene ID (autocomplete)
    if (!empty($idClienteForm)) {

        $upd = $conn->prepare("
            UPDATE cliente
            SET 
                NombreCliente = :nombre,
                Direccion     = CASE WHEN :dir  <> '' THEN :dir  ELSE Direccion END,
                Telefono      = CASE WHEN :tel  <> '' THEN :tel  ELSE Telefono END,
                Telefono2     = CASE WHEN :tel2 <> '' THEN :tel2 ELSE Telefono2 END
            WHERE idCliente = :id
        ");
        $upd->execute([
            ':nombre' => $nombreNorm,
            ':dir'    => $dirNorm,
            ':tel'    => $telNorm,
            ':tel2'   => $tel2Norm,
            ':id'     => (int)$idClienteForm
        ]);

        return (int)$idClienteForm;
    }

    // 2) No viene ID: buscar por nombre
    $existe = obtenerIdClienteExistente($conn, $nombreNorm);
    if ($existe) {

        $upd = $conn->prepare("
            UPDATE cliente
            SET
                Direccion = CASE WHEN :dir  <> '' THEN :dir  ELSE Direccion END,
                Telefono  = CASE WHEN :tel  <> '' THEN :tel  ELSE Telefono END,
                Telefono2 = CASE WHEN :tel2 <> '' THEN :tel2 ELSE Telefono2 END
            WHERE idCliente = :id
        ");
        $upd->execute([
            ':dir'  => $dirNorm,
            ':tel'  => $telNorm,
            ':tel2' => $tel2Norm,
            ':id'   => (int)$existe
        ]);

        return (int)$existe;
    }

    // 3) No existe: crear
    $ins = $conn->prepare("
        INSERT INTO cliente (NombreCliente, Direccion, Telefono, Telefono2)
        VALUES (:nombre, :dir, :tel, :tel2)
    ");
    $ins->execute([
        ':nombre' => $nombreNorm,
        ':dir'    => $dirNorm !== '' ? $dirNorm : 'Sin dirección',
        ':tel'    => $telNorm,
        ':tel2'   => $tel2Norm
    ]);

    return (int)$conn->lastInsertId();
}

function obtenerPorcentajeComision(PDO $conn): float {
    $stmt = $conn->prepare("
        SELECT valor 
        FROM configcomision 
        WHERE nombreajuste = 'porcentaje'
        LIMIT 1
    ");
    $stmt->execute();
    $valor = $stmt->fetchColumn();

    if ($valor === false || $valor === null) {
        return 30.0; 
    }

    return (float)$valor;
}


function registrarComisionAutomatica($conn, $idNota, $idUsuario, $tipo, $montoBase) {
    if (!$idUsuario) return;

    // Evitar duplicados
    $check = $conn->prepare("SELECT idComisiones FROM comisiones WHERE idnota = ? AND tipo = ?");
    $check->execute([$idNota, $tipo]);
    if ($check->fetch()) return;

    // Verificar que el usuario NO sea administrador
    $sqlRol = "SELECT r.rol 
               FROM usuarioroles ur 
               INNER JOIN rol r ON ur.idRol = r.idRol
               WHERE ur.idUsuario = ?";
    $stmtRol = $conn->prepare($sqlRol);
    $stmtRol->execute([$idUsuario]);
    $roles = $stmtRol->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('administrador', $roles)) return; // no registrar comisiones para administradora

    // 🔹 AHORA TOMAMOS EL PORCENTAJE DESDE configcomision
    $porcentaje = obtenerPorcentajeComision($conn);

    $montoComision = round($montoBase * ($porcentaje / 100), 2);

    $sql = "INSERT INTO comisiones (tipo, porcentaje, monto, estado, idUsuario, idnota)
            VALUES (?, ?, ?, 'Orden no Entregada', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tipo, $porcentaje, $montoComision, $idUsuario, $idNota]);
}

try {
    $conn->beginTransaction();
// ====== DATOS DEL CLIENTE ======
$nombre    = trim($_POST['nombreCliente'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$telefono2 = trim($_POST['telefono2'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

$idClienteForm = !empty($_POST['idCliente']) ? (int)$_POST['idCliente'] : null;

// ✅ obtiene o crea SIN duplicar
$idCliente = obtenerOCrearCliente($conn, $idClienteForm, $nombre, $direccion, $telefono, $telefono2);



    //CLIENTE
// $idClienteForm = $_POST['idCliente'] ?? null;

// if (!empty($idClienteForm)) {

//         // CLIENTE EXISTENTE = actualizar datos
//         $sqlUpdate = "UPDATE cliente 
//                       SET NombreCliente = :nombre,
//                           Direccion = :dir,
//                           Telefono = :tel,
//                           Telefono2 = :tel2
//                       WHERE idCliente = :id";

//         $stmtUpdate = $conn->prepare($sqlUpdate);
//         $stmtUpdate->execute([
//             ':nombre' => $nombre,
//             ':dir' => $direccion,
//             ':tel' => $telefono,
//             ':tel2' => $telefono2,
//             ':id' => $idClienteForm
//         ]);

//         $idCliente = $idClienteForm;

//     } else {

//         // CLIENTE NUEVO = insertar
//         $stmt = $conn->prepare("INSERT INTO cliente (NombreCliente, Direccion, Telefono, Telefono2)
//                                 VALUES (:nombre, :dir, :tel, :tel2)");

//         $stmt->execute([
//             ':nombre' => $nombre,
//             ':dir' => $direccion,
//             ':tel' => $telefono,
//             ':tel2' => $telefono2
//         ]);

//         $idCliente = $conn->lastInsertId();
//     }


    // ====== DATOS DE LA NOTA ======
    $fechaRecepcion = date('Y-m-d');
    $total = $_POST['total'] ?? 0;
    $anticipo = $_POST['anticipo'] ?? 0;
    $resto = $_POST['resto'] ?? 0;
    $trabajo = trim($_POST['trabajo'] ?? '');
    $descProblema = $_POST['descProblema'] ?? '';
    $sugerencia = $_POST['sugerencia'] ?? '';
    $idUsuario = $_POST['idUsuario'];
    $cotPendiente = isset($_POST['cotizacionPendiente']) ? 1 : 0;

    if ($cotPendiente) {
        $total = 0;
        $resto = 0;
        if ($anticipo < 0) { 
        $anticipo = 0;
    }
    }

    $stmt = $conn->prepare("INSERT INTO nota 
        (FechaRecepcion, FechaEntrega, Total, Anticipo, Resto, Trabajo, Descripcion, Comentario, idUsuario, idCliente)
        VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $fechaRecepcion,
        $total,
        $anticipo,
        $resto,
        $trabajo,
        $descProblema,
        $sugerencia,
        $idUsuario,
        $idCliente
    ]);
        
    $idNota = $conn->lastInsertId();

    // ====== DATOS DEL EQUIPO ======
    $equipo = $_POST['equipo'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $accesorios = $_POST['accesorios'] ?? '';
    $sugerenciaTec = $_POST['sugerencia'] ?? '';
    $descEquipo = $_POST['descEquipo'] ?? '';
    $tecnico = !empty($_POST['tecnico']) ? $_POST['tecnico'] : null;

    $stmt = $conn->prepare("INSERT INTO notamantenimiento 
        (Equipo, Marca, Model, Contraseña, Accesorios, SugerenciaTecn, Estatus, DescripcionEquipo, idNota, idTecnico)
        VALUES (?, ?, ?, ?, ?, ?, 'Proceso', ?, ?, ?)");
    $stmt->execute([$equipo, $marca, $modelo, $contrasena, $accesorios, $sugerenciaTec, $descEquipo, $idNota, $tecnico]);
    $idMantenimiento = $conn->lastInsertId();

    // === GENERAR COMISIÓN AUTOMÁTICA PARA TÉCNICO ===
    if (!empty($tecnico)) {
        $sqlTotal = $conn->prepare("SELECT Total FROM nota WHERE idNota = ?");
        $sqlTotal->execute([$idNota]);
        $total = $sqlTotal->fetchColumn() ?: 0;

        registrarComisionAutomatica($conn, $idNota, $tecnico, 'Mantenimiento', $total);
    }



    // ====== CONCEPTOS (CATÁLOGO + MANUAL) ======
    $tipos     = $_POST['tipo'] ?? [];
    $servicios = $_POST['servicio'] ?? [];
    $precios   = $_POST['precio'] ?? [];
    $cantidades= $_POST['cantidad'] ?? [];
    $origenes  = $_POST['origen'] ?? [];

    $stmtCatalogo = $conn->prepare("
      SELECT c.idCatalogoMnt 
      FROM catalogomnt c
      INNER JOIN tipomantenimiento t ON c.idTipoMnt = t.idTipoMnt
      WHERE t.NombreTipo = :tipo AND c.Servicio = :servicio
      LIMIT 1
    ");

    $stmtAux = $conn->prepare("
      INSERT INTO auxservicios (idMantenimiento, idCatalogoMnt, Descripcion, Cantidad, Precio, Subtotal, Origen)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $max = count($servicios);
    for ($i = 0; $i < $max; $i++) {

      $origen = trim($origenes[$i] ?? 'CATALOGO');
      $tipo   = trim($tipos[$i] ?? '');
      $serv   = trim($servicios[$i] ?? '');
      $precio = floatval($precios[$i] ?? 0);
      $cant   = floatval($cantidades[$i] ?? 1);
      if ($cant <= 0) $cant = 1;

      if ($serv === '') continue;

      $subtotal = $cant * $precio;

      if ($origen === 'MANUAL') {
        // Manual: NO catálogo
        $stmtAux->execute([$idMantenimiento, null, $serv, $cant, $precio, $subtotal, 'MANUAL']);
      } else {
        // Catálogo: buscar idCatalogoMnt
        if ($tipo === '') continue;

        $stmtCatalogo->execute([':tipo' => $tipo, ':servicio' => $serv]);
        $catId = $stmtCatalogo->fetchColumn();

        if ($catId) {
          $stmtAux->execute([$idMantenimiento, $catId, $serv, $cant, $precio, $subtotal, 'CATALOGO']);
        }
      }
    }


    // ====== CONFIRMAR ======
    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Orden guardada correctamente",
        "idNota" => $idNota
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('procesarOrdenMantenimiento', ?)");
    $log->execute([$e->getMessage()]);

    echo json_encode([
        "status" => "error",
        "message" => "Error al guardar la orden.",
        "error" => $e->getMessage()
    ]);
}
?>
