<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

try {
    $conn->beginTransaction();

    // ====== DATOS DEL CLIENTE ======
    $nombre = trim($_POST['nombreCliente']);
    $telefono = trim($_POST['telefono'] ?? '');
    $telefono2 = trim($_POST['telefono2'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    // Buscar o insertar cliente
    $stmt = $conn->prepare("SELECT idCliente FROM cliente WHERE NombreCliente = :nombre AND Telefono = :tel");
    $stmt->execute([':nombre' => $nombre, ':tel' => $telefono]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $idCliente = $cliente['idCliente'];
    } else {
        $stmt = $conn->prepare("INSERT INTO cliente (NombreCliente, Direccion, Telefono, Telefono2)
                                VALUES (:nombre, :dir, :tel, :tel2)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':dir' => $direccion,
            ':tel' => $telefono,
            ':tel2' => $telefono2
        ]);
        $idCliente = $conn->lastInsertId();
    }

    // ====== DATOS DE LA NOTA ======
    $fechaRecepcion = date('Y-m-d');
    $total = $_POST['total'] ?? 0;
    $anticipo = $_POST['anticipo'] ?? 0;
    $resto = $_POST['resto'] ?? 0;
    $descProblema = $_POST['descProblema'] ?? '';
    $sugerencia = $_POST['sugerencia'] ?? '';
    $idUsuario = $_POST['idUsuario'];
    $cotPendiente = isset($_POST['cotizacionPendiente']) ? 1 : 0;

    if ($cotPendiente) {
        $total = 0;
        $anticipo = 0;
        $resto = 0;
    }

    $stmt = $conn->prepare("INSERT INTO nota 
        (FechaRecepcion, Total, Anticipo, Resto, Descripcion, Comentario, idUsuario, idCliente)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fechaRecepcion, $total, $anticipo, $resto, $descProblema, $sugerencia, $idUsuario, $idCliente]);
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

    // ====== SERVICIOS DEL CATÁLOGO ======
    if (isset($_POST['servicio'], $_POST['tipo'], $_POST['precio'])) {
        $tipos = $_POST['tipo'];
        $servicios = $_POST['servicio'];
        $precios = $_POST['precio'];

        $stmtCatalogo = $conn->prepare("
            SELECT c.idCatalogoMnt 
            FROM catalogomnt c
            INNER JOIN tipomantenimiento t ON c.idTipoMnt = t.idTipoMnt
            WHERE t.NombreTipo = :tipo AND c.Servicio = :servicio
        ");

        $stmtAux = $conn->prepare("
            INSERT INTO auxservicios (idMantenimiento, idCatalogoMnt, Precio)
            VALUES (?, ?, ?)
        ");

        for ($i = 0; $i < count($servicios); $i++) {
            $tipo = trim($tipos[$i]);
            $serv = trim($servicios[$i]);
            $precio = floatval($precios[$i] ?? 0);

            if (!empty($tipo) && !empty($serv)) {
                $stmtCatalogo->execute([':tipo' => $tipo, ':servicio' => $serv]);
                $cat = $stmtCatalogo->fetch(PDO::FETCH_ASSOC);
                if ($cat) {
                    $stmtAux->execute([$idMantenimiento, $cat['idCatalogoMnt'], $precio]);
                }
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
