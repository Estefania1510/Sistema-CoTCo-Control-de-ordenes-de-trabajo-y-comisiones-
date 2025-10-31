<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
session_start();

// === Validar rol administrador ===
if (!in_array('administrador', $_SESSION['roles'] ?? [])) {
  echo json_encode(["status" => "error", "message" => "No tienes permisos para registrar licencias."]);
  exit;
}

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_POST['idNota'] ?? null;
$idCliente = $_POST['idCliente'] ?? null;

$softwares = $_POST['software'] ?? [];
$licenciasSeleccionadas = $_POST['licenciaLibre'] ?? []; // ← select de licencias
$passwords = $_POST['password'] ?? [];
$equipos = $_POST['equipo'] ?? [];
$procesadores = $_POST['procesador'] ?? [];
$dispositivos = $_POST['dispositivo'] ?? [];
$productos = $_POST['producto'] ?? [];
$fechas = $_POST['fecha'] ?? [];

if (empty($softwares) || count(array_filter($softwares)) === 0) {
  echo json_encode(["status" => "error", "message" => "No se recibió ninguna licencia para registrar."]);
  exit;
}

try {
  $conn->beginTransaction();

  // Preparar consulta para actualizar licencias existentes
  $stmtUpdate = $conn->prepare("
    UPDATE licenciasoftware
    SET Estatus = 'Instalada',
        Password = ?,
        Equipo = ?,
        Procesador = ?,
        IdDispositivo = ?,
        IdProducto = ?,
        Fecha = ?,
        idCliente = ?,
        idNota = ?
    WHERE idLS = ?
  ");

  // Preparar consulta para insertar nuevas (en caso de no elegir una licencia existente)
  $stmtInsert = $conn->prepare("
    INSERT INTO licenciasoftware
    (Software, Estatus, Password, Equipo, Procesador, IdDispositivo, IdProducto, Fecha, idCliente, idNota)
    VALUES (?, 'Instalada', ?, ?, ?, ?, ?, ?, ?, ?)
  ");

  for ($i = 0; $i < count($softwares); $i++) {
    $software = trim($softwares[$i] ?? '');
    $licenciaExistente = $licenciasSeleccionadas[$i] ?? '';
    $password = trim($passwords[$i] ?? '');
    $equipo = trim($equipos[$i] ?? '');
    $procesador = trim($procesadores[$i] ?? '');
    $dispositivo = trim($dispositivos[$i] ?? '');
    $producto = trim($productos[$i] ?? '');
    $fecha = !empty($fechas[$i]) ? $fechas[$i] : null;

    if ($software === '' || $password === '' || $equipo === '' || $procesador === '' || $dispositivo === '' || $producto === '') {
      $conn->rollBack();
      echo json_encode([
        "status" => "error",
        "message" => "Todos los campos son obligatorios. Revisa los datos de la licencia #" . ($i + 1)
      ]);
      exit;
    }

    if (!empty($licenciaExistente)) {
      // === ACTUALIZAR LICENCIA EXISTENTE ===
      $stmtUpdate->execute([
        $password, $equipo, $procesador, $dispositivo, $producto,
        $fecha, $idCliente, $idNota, $licenciaExistente
      ]);
    } else {
      // === INSERTAR NUEVA ===
      $stmtInsert->execute([
        $software, $password, $equipo, $procesador, $dispositivo,
        $producto, $fecha, $idCliente, $idNota
      ]);
    }
  }

  $conn->commit();
  echo json_encode(["status" => "success", "message" => "Licencias registradas y actualizadas correctamente."]);

} catch (Exception $e) {
  $conn->rollBack();
  $log = $conn->prepare("INSERT INTO logerror (metodo, excepcion) VALUES ('procesarLicenciaOrden', ?)");
  $log->execute([$e->getMessage()]);
  echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
