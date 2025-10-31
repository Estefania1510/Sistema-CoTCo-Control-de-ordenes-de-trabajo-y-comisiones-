<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
header('Content-Type: application/json');

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

try {
		$sql = "SELECT u.idUsuario, u.NombreUsuario, u.Usuario, GROUP_CONCAT(r.rol) AS Roles
		        FROM usuario u
		        INNER JOIN usuarioroles ur ON u.idUsuario = ur.idUsuario
		        INNER JOIN rol r ON ur.idRol = r.idRol
		        WHERE u.idUsuario = ?
		        GROUP BY u.idUsuario";


    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($usuario ?: ['status' => 'error', 'message' => 'Usuario no encontrado.']);

} catch (Exception $e) {
    $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('obtenerUsuario', :error)");
    $log->execute([':error' => $e->getMessage()]);

    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
