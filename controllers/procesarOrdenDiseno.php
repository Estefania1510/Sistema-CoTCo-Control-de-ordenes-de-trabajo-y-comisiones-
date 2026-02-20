<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/session_control.php';
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

function normalizarNombre(string $s): string {
    $s = trim($s);
    $s = mb_strtoupper($s, 'UTF-8');
    $s = preg_replace('/\s+/', ' ', $s); // múltiples espacios a 1
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

    // normalizar campos opcionales
    $dirNorm  = trim($direccion);
    $telNorm  = trim($telefono);
    $tel2Norm = trim($telefono2);

    // ========= 1) Si viene ID (autocomplete) =========
    if (!empty($idClienteForm)) {

        // Actualiza datos SOLO si vienen (no pisa con vacío)
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

    // ========= 2) No viene ID: buscar por nombre =========
    $existe = obtenerIdClienteExistente($conn, $nombreNorm);

    if ($existe) {
        // Si existe, actualiza datos SOLO si vienen (evita duplicados + mantiene info al día)
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

    // ========= 3) No existe: crear =========
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
    if ($valor === false || $valor === null) return 30.0;
    return (float)$valor;
}

function registrarComisionAutomatica($conn, $idNota, $idUsuario, $tipo, $montoBase) {
    if (!$idUsuario) return;

    $check = $conn->prepare("SELECT idComisiones FROM comisiones WHERE idnota = ? AND tipo = ?");
    $check->execute([$idNota, $tipo]);
    if ($check->fetch()) return;

    $sqlRol = "SELECT r.rol 
               FROM usuarioroles ur 
               INNER JOIN rol r ON ur.idRol = r.idRol
               WHERE ur.idUsuario = ?";
    $stmtRol = $conn->prepare($sqlRol);
    $stmtRol->execute([$idUsuario]);
    $roles = $stmtRol->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('administrador', $roles)) return;

    $porcentaje = obtenerPorcentajeComision($conn);
    $montoComision = round($montoBase * ($porcentaje / 100), 2);

    $sql = "INSERT INTO comisiones (tipo, porcentaje, monto, estado, idUsuario, idnota)
            VALUES (?, ?, ?, 'Orden no Entregada', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tipo, $porcentaje, $montoComision, $idUsuario, $idNota]);
}

try {
    $conn->beginTransaction();

    // ===================== DATOS FORMULARIO =====================
    $nombreCliente = trim($_POST['nombreCliente'] ?? '');
    $telefono      = trim($_POST['telefono'] ?? '');
    $telefono2     = trim($_POST['telefono2'] ?? '');
    $direccion     = trim($_POST['direccion'] ?? '');

    $descripcion   = trim($_POST['descripcion'] ?? '');
    $trabajo       = trim($_POST['trabajo'] ?? '');
    $comentarios   = $_POST['comentarios'] ?? null;

    $cotPendiente  = isset($_POST['cotPendiente']) ? 1 : 0;
    $esDigital     = isset($_POST['esDigital']) ? 1 : 0;

    $medioEntrega = null;
    if ($esDigital) {
        $medioEntrega = trim($_POST['medioEntrega'] ?? '');
        if ($medioEntrega === '') $medioEntrega = null;
    }

    $diseno        = (float)($_POST['diseño'] ?? 0);
    $anticipo      = (float)($_POST['anticipo'] ?? 0);

    $idDiseñador   = !empty($_POST['idDiseñador']) ? $_POST['idDiseñador'] : null;
    $idUsuario     = $_SESSION['idUsuario'];
    
        if (empty($_SESSION['idUsuario'])) {
        throw new Exception("Sesión sin idUsuario. Cierra sesión e inicia sesión de nuevo.");
    }

    $idUsuario = (int)$_SESSION['idUsuario'];

    $chk = $conn->prepare("SELECT 1 FROM usuario WHERE idUsuario = ?");
    $chk->execute([$idUsuario]);
    if (!$chk->fetchColumn()) {
        throw new Exception("idUsuario en sesión ($idUsuario) NO existe en tabla usuario. Cierra sesión e inicia sesión otra vez.");
    }

    // ===================== CLIENTE =====================
    $nombreCliente = normalizarNombre($nombreCliente);
    $idClienteForm = !empty($_POST['idCliente']) ? (int)$_POST['idCliente'] : null;

    // ✅ Obtener o crear cliente (sin duplicar por tecleo rápido)
    $idCliente = obtenerOCrearCliente($conn, $idClienteForm, $nombreCliente, $direccion, $telefono, $telefono2);


    // ===================== SUBTOTAL DESDE MATERIALES =====================
    $subtotalCalc = 0;

    if (!$cotPendiente && isset($_POST['material']) && is_array($_POST['material'])) {
        $materiales = $_POST['material'];
        $cantidades = $_POST['cantidad'] ?? [];
        $precios    = $_POST['precio'] ?? [];

        for ($i = 0; $i < count($materiales); $i++) {
            $mat  = trim($materiales[$i] ?? '');
            if ($mat === '') continue;

            $cant = (float)($cantidades[$i] ?? 0);
            $prec = (float)($precios[$i] ?? 0);

            $subtotalCalc += ($cant * $prec);
        }
    }

    // ===================== TOTALES =====================
    $fechaActual = date('Y-m-d');

    if ($cotPendiente) {
        // Cotización pendiente: importes en 0 (puede haber anticipo)
        $subtotal = 0;
        $diseno   = 0;
        $total    = 0;
        $resto    = 0;
        if ($anticipo < 0) $anticipo = 0;
    } else {
        $subtotal = $subtotalCalc;           // ✅ puede ser 0 (digital puro) o >0 (calcas, lona, etc.)
        $total    = $subtotal + $diseno;
        $resto    = $total - $anticipo;      // ✅ puede ser negativo y está bien
    }

    // ===================== INSERT NOTA =====================
    $sqlNota = "INSERT INTO nota 
        (FechaRecepcion, FechaEntrega, Total, Anticipo, Resto, Trabajo, Descripcion, Comentario, idUsuario, idCliente)
        VALUES (:frecep, NULL, :total, :anticipo, :resto, :trabajo, :desc, :coment, :idUser, :idCli)";
    $stmt = $conn->prepare($sqlNota);
    $stmt->execute([
        ':frecep'   => $fechaActual,
        ':total'    => $total,
        ':anticipo' => $anticipo,
        ':resto'    => $resto,
        ':trabajo'  => $trabajo,
        ':desc'     => $descripcion,
        ':coment'   => $comentarios,
        ':idUser'   => $idUsuario,
        ':idCli'    => $idCliente
    ]);

    $idNota = $conn->lastInsertId();

    // ===================== INSERT NOTADISEÑO =====================
    $sqlDiseno = "INSERT INTO notadiseño (estatus, CostoDiseño, EsDigital, MedioEntrega, idNota, idDiseñador) 
                  VALUES ('Proceso', :costoDiseno, :esDigital, :medioEntrega, :idNota, :idDisenador )";
    $stmt = $conn->prepare($sqlDiseno);
    $stmt->execute([
        ':costoDiseno' => $diseno,
        ':esDigital'     => $esDigital,
        ':medioEntrega'  => $medioEntrega,
        ':idNota'      => $idNota,
        ':idDisenador' => $idDiseñador,
    ]);

    $idDiseno = $conn->lastInsertId();
    
    if (!empty($idDiseñador)) {
        registrarComisionAutomatica($conn, $idNota, $idDiseñador, 'Diseño', $diseno);
    }


    // ===================== INSERT MATERIALES =====================
    // ✅ Se insertan siempre que NO sea cotización pendiente (aunque sea digital)
    if (!$cotPendiente && isset($_POST['material']) && is_array($_POST['material'])) {

        $stmtMat = $conn->prepare("
            INSERT INTO material (Material, Cantidad, Precio, Subtotal, idDiseño)
            VALUES (?, ?, ?, ?, ?)
        ");

        $materiales = $_POST['material'];
        $cantidades = $_POST['cantidad'] ?? [];
        $precios    = $_POST['precio'] ?? [];

        for ($i = 0; $i < count($materiales); $i++) {
            $mat = trim($materiales[$i] ?? '');
            if ($mat === '') continue;

            $cant = (float)($cantidades[$i] ?? 0);
            $prec = (float)($precios[$i] ?? 0);
            $sub  = $cant * $prec;

            $stmtMat->execute([$mat, $cant, $prec, $sub, $idDiseno]);
        }
    }

    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'folio'  => $idNota
    ]);

} catch (Exception $e) {
    $conn->rollBack();

    $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('procesarOrdenDiseno', :error)");
    $log->execute([':error' => $e->getMessage()]);

    header('Content-Type: application/json');
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
