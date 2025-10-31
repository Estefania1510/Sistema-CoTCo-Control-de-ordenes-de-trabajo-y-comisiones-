<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

try {
    $conn->beginTransaction();

    // DATOS DEL FORMULARIO 
    $nombreCliente = trim($_POST['nombreCliente']);
    $telefono = trim($_POST['telefono']);
    $telefono2 = trim($_POST['telefono2']);
    $direccion = trim($_POST['direccion']);
    $descripcion = trim($_POST['descripcion']);
    $subtotal = $_POST['subtotal'] ?: 0;
    $diseno = $_POST['diseño'] ?: 0;
    $total = $_POST['total'] ?: 0;
    $anticipo = $_POST['anticipo'] ?: 0;
    $resto = $_POST['resto'] ?: 0;
    $comentarios = $_POST['comentarios'] ?? null;
    $cotPendiente = isset($_POST['cotPendiente']) ? 1 : 0;
    $idDiseñador = !empty($_POST['idDiseñador']) ? $_POST['idDiseñador'] : null;
    $idUsuario = $_SESSION['idUsuario'];


    // BUSCAR O INSERTAR CLIENTE 
    $sqlCliente = "SELECT idCliente FROM cliente WHERE NombreCliente = :nombre AND Telefono = :tel";
    $stmt = $conn->prepare($sqlCliente);
    $stmt->execute([':nombre' => $nombreCliente, ':tel' => $telefono]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $idCliente = $cliente['idCliente'];
    } else {
        $insertCliente = $conn->prepare("INSERT INTO cliente (NombreCliente, Direccion, Telefono, Telefono2)
                                         VALUES (:nombre, :dir, :tel, :tel2)");
        $insertCliente->execute([
            ':nombre' => $nombreCliente,
            ':dir' => $direccion,
            ':tel' => $telefono,
            ':tel2' => $telefono2
        ]);
        $idCliente = $conn->lastInsertId();
    }

    //  INSERTAR EN NOTA 
    $fechaActual = date('Y-m-d');

        if ($cotPendiente) {
            $subtotal = 0;
            $diseno   = 0;
            $total    = 0;
            $resto    = 0;      
            if ($anticipo < 0) { 
                $anticipo = 0;
            }
        }


    $sqlNota = "INSERT INTO nota 
                (FechaRecepcion, FechaEntrega, Total, Anticipo, Resto, Descripcion, Comentario, idUsuario, idCliente)
                VALUES (:frecep, NULL, :total, :anticipo, :resto, :desc, :coment, :idUser, :idCli)";
    $stmt = $conn->prepare($sqlNota);
    $stmt->execute([
        ':frecep' => $fechaActual,
        ':total' => $total,
        ':anticipo' => $anticipo,
        ':resto' => $resto,
        ':desc' => $descripcion,
        ':coment' => $comentarios,
        ':idUser' => $idUsuario,
        ':idCli' => $idCliente
    ]);

    $idNota = $conn->lastInsertId();

    // INSERTAR EN NOTADISEÑO 
        $sqlDiseno = "INSERT INTO notadiseño (estatus, CostoDiseño, idNota, idDiseñador) 
                      VALUES ('Proceso', :costoDiseno, :idNota, :idDisenador)";
        $stmt = $conn->prepare($sqlDiseno);
        $stmt->execute([
            ':costoDiseno' => $diseno, 
            ':idNota' => $idNota,
            ':idDisenador' => $idDiseñador
        ]);

        $idDiseno = $conn->lastInsertId();


    // INSERTAR MATERIALES 
    if (isset($_POST['material'])) {
        $sqlMat = "INSERT INTO material (Material, Cantidad, Precio, Subtotal, idDiseño) 
                   VALUES (:mat, :cant, :precio, :sub, :idDiseno)";
        $stmtMat = $conn->prepare($sqlMat);

        for ($i = 0; $i < count($_POST['material']); $i++) {
            $mat = $_POST['material'][$i];
            $cant = $_POST['cantidad'][$i] ?: 0;
            $precio = $_POST['precio'][$i] ?: 0;
            $sub = $cant * $precio;

            $stmtMat->execute([
                ':mat' => $mat,
                ':cant' => $cant,
                ':precio' => $precio,
                ':sub' => $sub,
                ':idDiseno' => $idDiseno
            ]);
        }
    }



    header('Content-Type: application/json');



    $conn->commit();

        echo json_encode([
        'status' => 'success',
        'folio' => $idNota
    ]);

} catch (Exception $e) {
    $conn->rollBack();

    $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('procesarOrdenDiseno', :error)");
    $log->execute([':error' => $e->getMessage()]);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}