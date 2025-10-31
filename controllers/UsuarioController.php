<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

header('Content-Type: application/json');

try {
    $accion = $_POST['accion'] ?? 'guardar';
    $idUsuario = $_POST['idUsuario'] ?? null;

    // GUARDAR O EDITAR USUARIO
    if ($accion === 'guardar') {

        $nombre = trim($_POST['NombreUsuario']);
        $usuario = trim($_POST['Usuario']);
        $password = $_POST['Contraseña'] ?? '';
        $roles = $_POST['Rol'] ?? []; 

        if (empty($nombre) || empty($usuario) || empty($roles)) {
            throw new Exception("Faltan campos obligatorios.");
        }

        // EDITAR
        if ($idUsuario) {
            $conn->beginTransaction(); 

            $sql = "UPDATE usuario SET NombreUsuario = :nombre, Usuario = :usuario";
            $params = [
                ':nombre' => $nombre,
                ':usuario' => $usuario,
                ':id' => $idUsuario
            ];

            if (!empty($password)) {
                $sql .= ", Contraseña = :pass";
                $params[':pass'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $sql .= " WHERE idUsuario = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            //Eliminar roles anteriores
            $conn->prepare("DELETE FROM usuarioroles WHERE idUsuario = :id")
                 ->execute([':id' => $idUsuario]);

            // Insertar nuevos roles seleccionados
            $stmtRol = $conn->prepare("INSERT INTO usuarioroles (idUsuario, idRol)
                                       VALUES (:id, (SELECT idRol FROM rol WHERE rol = :rol))");

            foreach ($roles as $rol) {
                $stmtRol->execute([':id' => $idUsuario, ':rol' => $rol]);
            }

            $conn->commit();

            echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
        }

        else {
            $conn->beginTransaction();

            $check = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE Usuario = :usuario");
            $check->execute([':usuario' => $usuario]);
            if ($check->fetchColumn() > 0) {
                throw new Exception("El nombre de usuario ya existe.");
            }

            $stmt = $conn->prepare("INSERT INTO usuario (NombreUsuario, Usuario, Contraseña, Estatus)
                                    VALUES (:nombre, :usuario, :pass, 'Activo')");
            $stmt->execute([
                ':nombre' => $nombre,
                ':usuario' => $usuario,
                ':pass' => password_hash($password, PASSWORD_BCRYPT)
            ]);

            $idNuevo = $conn->lastInsertId();

            //Insertar múltiples roles seleccionados
            $stmtRol = $conn->prepare("INSERT INTO usuarioroles (idUsuario, idRol)
                                       VALUES (:id, (SELECT idRol FROM rol WHERE rol = :rol))");
            foreach ($roles as $rol) {
                $stmtRol->execute([':id' => $idNuevo, ':rol' => $rol]);
            }

            $conn->commit();

            echo json_encode(['status' => 'success', 'message' => 'Usuario registrado correctamente.']);
        }
    }

    //INACTIVAR O REACTIVAR USUARIO 
    elseif ($accion === 'eliminar') {
        $id = $_POST['id'] ?? null;
        if (!$id) throw new Exception("ID no válido.");

        $sql = "UPDATE usuario 
                SET Estatus = IF(Estatus = 'Activo', 'Inactivo', 'Activo') 
                WHERE idUsuario = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Estado del usuario actualizado correctamente.'
        ]);
    }

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('UsuarioController', :error)");
    $log->execute([':error' => $e->getMessage()]);

    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
