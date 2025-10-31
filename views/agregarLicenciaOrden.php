<?php
include 'includes/header.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';
 

// Solo administradora
if (!in_array('administrador', $_SESSION['roles'] ?? [])) {
  echo "<div class='alert alert-danger m-4'>No tienes permisos para acceder a esta sección.</div>";
  include 'includes/footer.php';
  exit;
}

$idNota = $_GET['idNota'] ?? null;
if (!$idNota) {
  echo "<div class='alert alert-warning m-4'>Folio no especificado.</div>";
  include 'includes/footer.php';
  exit;
}

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

// Obtener datos cliente + nota
$sql = "SELECT n.idNota, n.Descripcion, n.FechaRecepcion, c.idCliente, c.NombreCliente, c.Telefono, c.Direccion
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
  echo "<div class='alert alert-warning m-4'>Orden no encontrada.</div>";
  include 'includes/footer.php';
  exit;
}

// OBTENER SOLO LOS SERVICIOS DE TIPO SOFTWARE 
$sqlSoft = "SELECT cm.Servicio
             FROM catalogomnt cm
             INNER JOIN tipomantenimiento t ON cm.idTipoMnt = t.idTipoMnt
             INNER JOIN auxservicios a ON cm.idCatalogoMnt = a.idCatalogoMnt
             INNER JOIN notamantenimiento nm ON nm.idMantenimiento = a.idMantenimiento
             WHERE nm.idNota = ? AND LOWER(t.NombreTipo) = 'software'";
$stmtSoft = $conn->prepare($sqlSoft);
$stmtSoft->execute([$idNota]);
$softwaresNota = $stmtSoft->fetchAll(PDO::FETCH_COLUMN);

// === OBTENER LICENCIAS YA REGISTRADAS EN ESTA NOTA ===
$sqlLic = "SELECT idLS, Software, Licencia, Estatus, Password, Equipo, Procesador, IdDispositivo, IdProducto, Fecha
            FROM licenciasoftware
            WHERE idNota = ?";
$stmtLic = $conn->prepare($sqlLic);
$stmtLic->execute([$idNota]);
$licenciasGuardadas = $stmtLic->fetchAll(PDO::FETCH_ASSOC);



?>

<div class="d-flex justify-content-between align-items-end mt-2">
  <h1 class="mt-2 text-dark fw-bold mb-0">Registrar Licencias de Software</h1>
  <span class="badge bg-primary fs-5 me-5" style="min-width: 130px;">Folio: <?= $orden['idNota'] ?></span>
</div>


<ol class="breadcrumb mb-4 mt-2">
  <li class="breadcrumb-item active">Licencias de software instaladas</li>
</ol>

<!-- Información cliente -->
<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <h5 class="mb-3"><i class="fas fa-user me-2"></i> Información del Cliente</h5>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Cliente</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($orden['NombreCliente']) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($orden['Telefono']) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Dirección</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($orden['Direccion']) ?>" readonly>
      </div>
    </div>
  </div>
</div>

<form id="formLicencias" method="POST">
  <input type="hidden" name="idNota" value="<?= $orden['idNota'] ?>">
  <input type="hidden" name="idCliente" value="<?= $orden['idCliente'] ?>">

<!-- Licencias de software -->
<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <h5 class="mb-3"><i class="fa-solid fa-key me-2"></i> Licencias de Software</h5>

<div id="licenciasContainer">
  <?php if (!empty($licenciasGuardadas)): ?>
    <?php foreach ($licenciasGuardadas as $index => $lic): ?>
      <div class="card border p-3 mb-3 licencia-item">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="fw-bold mb-0">
            <i class="fas fa-key me-2"></i> Licencia #<?= $index + 1 ?>
          </h6>
          <button type="button" class="btn btn-danger btn-sm" data-del="licencia">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Software</label>
            <select name="software[]" class="form-select softwareNota" required>
              <option value="">Seleccionar software...</option>
              <?php foreach ($softwaresNota as $soft): ?>
                <option value="<?= htmlspecialchars($soft) ?>"
                  <?= $soft === $lic['Software'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($soft) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Licencia disponible</label>
            <select name="licenciaLibre[]" class="form-select licenciaLibre">
              <option value="<?= htmlspecialchars($lic['idLS']) ?>">
                <?= htmlspecialchars($lic['Licencia']) ?>
              </option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Password / Clave</label>
            <input type="text" name="password[]" class="form-control" 
                   value="<?= htmlspecialchars($lic['Password']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Equipo</label>
            <input type="text" name="equipo[]" class="form-control" 
                   value="<?= htmlspecialchars($lic['Equipo']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Procesador</label>
            <input type="text" name="procesador[]" class="form-control" 
                   value="<?= htmlspecialchars($lic['Procesador']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">ID Dispositivo</label>
            <input type="text" name="dispositivo[]" class="form-control"
                   value="<?= htmlspecialchars($lic['IdDispositivo']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">ID Producto</label>
            <input type="text" name="producto[]" class="form-control"
                   value="<?= htmlspecialchars($lic['IdProducto']) ?>" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha[]" class="form-control"
                   value="<?= htmlspecialchars($lic['Fecha']) ?>" required>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    
    <!-- Si no hay licencias guardadas, mostrar tarjeta vacía -->
    <div class="card border p-3 mb-3 licencia-item">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0"><i class="fas fa-key me-2"></i> Licencia #1</h6>
        <button type="button" class="btn btn-danger btn-sm" data-del="licencia">
          <i class="fa-solid fa-trash-can"></i>
        </button>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Software</label>
          <select name="software[]" class="form-select softwareNota" required>
            <option value="">Seleccionar software...</option>
            <?php foreach ($softwaresNota as $soft): ?>
              <option value="<?= htmlspecialchars($soft) ?>"><?= htmlspecialchars($soft) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Licencia disponible</label>
          <select name="licenciaLibre[]" class="form-select licenciaLibre">
            <option value="">Seleccionar licencia...</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Password / Clave</label>
          <input type="text" name="password[]" class="form-control" placeholder="Clave o serial" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Equipo</label>
          <input type="text" name="equipo[]" class="form-control" placeholder="Ej. CPU HP" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Procesador</label>
          <input type="text" name="procesador[]" class="form-control" placeholder="Ej. Intel i5" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">ID Dispositivo</label>
          <input type="text" name="dispositivo[]" class="form-control" placeholder="Ej. 10C-305280439733" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">ID Producto</label>
          <input type="text" name="producto[]" class="form-control" placeholder="Ej. I0-00000-AAOEM" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Fecha</label>
          <input type="date" name="fecha[]" class="form-control" required>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>


    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addLicencia">
      <i class="fa-solid fa-plus"></i> Agregar Licencia
    </button>
  </div>
</div>



  <div class="text-center mb-5">
    <button type="submit" class="btn btn-success btn-lg fw-bold px-4">
      <i class="bi bi-save"></i> Guardar Licencias
    </button>
    <a href="ordenestrabajo.php" class="btn btn-danger btn-lg fw-bold px-4">
      <i class="fas fa-times"></i> Cancelar
    </a>
  </div>
</form>

<?php include 'includes/footer.php'; ?>
<script src="../funciones/agregarLicenciaOrden.js"></script>
