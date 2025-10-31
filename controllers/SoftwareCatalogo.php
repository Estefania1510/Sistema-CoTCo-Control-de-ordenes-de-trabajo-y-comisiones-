<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

try {
    $conexion = new Conexion($conData);
    $conn = $conexion->getConnection();

    // Consulta para obtener los software
    $sql = "
        SELECT c.idCatalogoMnt, c.Servicio
        FROM catalogomnt c
        INNER JOIN tipomantenimiento t ON c.idTipoMnt = t.idTipoMnt
        WHERE LOWER(t.NombreTipo) = 'software'
        ORDER BY c.Servicio ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $softwares = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Forzar salida como JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($softwares);

} catch (Exception $e) {
    $conn->rollBack();

    $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('SoftwareCatalogo', :error)");
    $log->execute([':error' => $e->getMessage()]);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
