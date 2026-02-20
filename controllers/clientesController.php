<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();
require_once __DIR__ . '/../config/session_control.php';
header('Content-Type: application/json');

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

try {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $accion = $data['accion'] ?? '';

    // LISTAR CLIENTES 
// LISTAR TODOS LOS CLIENTES (con total de notas)
if ($accion === 'listarClientes') {

    $sql = "
        SELECT 
            c.idCliente,
            c.NombreCliente,
            c.Telefono,
            c.Telefono2,
            c.Direccion,
            COUNT(n.idNota) AS totalNotas
        FROM cliente c
        LEFT JOIN nota n ON n.idCliente = c.idCliente
        GROUP BY c.idCliente, c.NombreCliente, c.Telefono, c.Telefono2, c.Direccion
        ORDER BY c.NombreCliente ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "ok", "data" => $clientes]);
    exit;
}

// OBTENER 1 CLIENTE
if ($accion === 'obtenerCliente') {
    $idCliente = $data['idCliente'] ?? null;
    if (!$idCliente) {
        echo json_encode(["status" => "error", "message" => "ID faltante"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT idCliente, NombreCliente, Direccion, Telefono, Telefono2 FROM cliente WHERE idCliente = ?");
    $stmt->execute([$idCliente]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        echo json_encode(["status" => "error", "message" => "Cliente no encontrado"]);
        exit;
    }

    echo json_encode(["status" => "ok", "data" => $cliente]);
    exit;
}

// CREAR CLIENTE
if ($accion === 'crearCliente') {
    $NombreCliente = trim($data['NombreCliente'] ?? '');
    $Direccion     = trim($data['Direccion'] ?? '');
    $Telefono      = trim($data['Telefono'] ?? '');
    $Telefono2     = trim($data['Telefono2'] ?? '');

    if ($NombreCliente === '') {
        echo json_encode(["status" => "error", "message" => "NombreCliente es obligatorio"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO cliente (NombreCliente, Direccion, Telefono, Telefono2) VALUES (?, ?, ?, ?)");
    $stmt->execute([$NombreCliente, $Direccion, $Telefono, $Telefono2]);

    echo json_encode(["status" => "ok", "message" => "Cliente creado correctamente"]);
    exit;
}

// ACTUALIZAR CLIENTE
if ($accion === 'actualizarCliente') {
    $idCliente     = $data['idCliente'] ?? null;
    $NombreCliente = trim($data['NombreCliente'] ?? '');
    $Direccion     = trim($data['Direccion'] ?? '');
    $Telefono      = trim($data['Telefono'] ?? '');
    $Telefono2     = trim($data['Telefono2'] ?? '');

    if (!$idCliente) {
        echo json_encode(["status" => "error", "message" => "ID faltante"]);
        exit;
    }
    if ($NombreCliente === '') {
        echo json_encode(["status" => "error", "message" => "NombreCliente es obligatorio"]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE cliente
        SET NombreCliente = ?, Direccion = ?, Telefono = ?, Telefono2 = ?
        WHERE idCliente = ?
    ");
    $stmt->execute([$NombreCliente, $Direccion, $Telefono, $Telefono2, $idCliente]);

    echo json_encode(["status" => "ok", "message" => "Cliente actualizado correctamente"]);
    exit;
}

// ELIMINAR CLIENTE (solo si NO tiene notas)
if ($accion === 'eliminarCliente') {
    $idCliente = $data['idCliente'] ?? null;
    if (!$idCliente) {
        echo json_encode(["status" => "error", "message" => "ID faltante"]);
        exit;
    }

    // Verificar si tiene notas
    $check = $conn->prepare("SELECT COUNT(*) FROM nota WHERE idCliente = ?");
    $check->execute([$idCliente]);
    $total = (int)$check->fetchColumn();

    if ($total > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "No se puede eliminar: el cliente ya tiene órdenes registradas."
        ]);
        exit;
    }

    $del = $conn->prepare("DELETE FROM cliente WHERE idCliente = ?");
    $del->execute([$idCliente]);

    echo json_encode(["status" => "ok", "message" => "Cliente eliminado correctamente"]);
    exit;
}


    // HISTORIAL DE UN CLIENTE
    if ($accion === 'historialCliente') {

        $idCliente   = $data['idCliente'] ?? null;
        $folio       = $data['folio'] ?? null;
        $estado      = $data['estado'] ?? 'todos';
        $tipo        = $data['tipo'] ?? 'todos';
        $fechaInicio = $data['fechaInicio'] ?? null;
        $fechaFin    = $data['fechaFin'] ?? null;

        if (!$idCliente) {
            echo json_encode(["status" => "error", "message" => "ID de cliente faltante"]);
            exit;
        }

        $sql = "
            SELECT *
			FROM (
			    -- DISEÑO
			    SELECT 
			        n.idNota AS folio,
			        'Diseño' AS tipo,
			        nd.Estatus AS estado,
			        n.FechaRecepcion AS FechaRecepcion,
			        n.FechaEntrega AS FechaEntrega,
			        COALESCE(uD.NombreUsuario,'En espera') AS UsuarioAsignado,
			        0 AS licencias  
			    FROM nota n
			    INNER JOIN notadiseño nd      ON nd.idNota = n.idNota
			    LEFT JOIN usuario uD          ON nd.idDiseñador = uD.idUsuario
			    WHERE n.idCliente = :idCliente1

			    UNION ALL

			    -- MANTENIMIENTO
			    SELECT 
			        n.idNota AS folio,
			        'Mantenimiento' AS tipo,
			        nm.Estatus AS estado,
			        n.FechaRecepcion AS FechaRecepcion,
			        n.FechaEntrega AS FechaEntrega,
			        COALESCE(uT.NombreUsuario,'En espera') AS UsuarioAsignado,
			        (SELECT COUNT(*) FROM licenciasoftware ls WHERE ls.idNota = n.idNota) AS licencias
			    FROM nota n
			    INNER JOIN notamantenimiento nm ON nm.idNota = n.idNota
			    LEFT JOIN usuario uT            ON nm.idTecnico = uT.idUsuario
			    WHERE n.idCliente = :idCliente2
			) AS t

            WHERE 1 = 1
        ";

        $params = [
            ':idCliente1' => $idCliente,
            ':idCliente2' => $idCliente,
        ];

        // Filtro por folio
        if (!empty($folio)) {
            $sql .= " AND t.folio = :folio";
            $params[':folio'] = $folio;
        }

        // Filtro por tipo
        if ($tipo !== 'todos') {
            $sql .= " AND t.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        // Filtro por estado
        if ($estado !== 'todos') {
            $sql .= " AND t.estado = :estado";
            $params[':estado'] = $estado;
        }

        // Filtro por fechas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $sql .= " AND t.FechaRecepcion BETWEEN :inicio AND :fin";
            $params[':inicio'] = $fechaInicio;
            $params[':fin']    = $fechaFin;
        }

        $sql .= " ORDER BY t.FechaRecepcion DESC";

        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "ok",
            "data"   => $historial
        ]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Throwable $e) {
    // Log de error, por si quieres
    $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('clientesController', ?)");
    $log->execute([$e->getMessage()]);

    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}
