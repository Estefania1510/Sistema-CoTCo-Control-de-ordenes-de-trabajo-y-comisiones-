<?php 
include 'includes/header.php'; 

require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idCliente = $_GET['idCliente'] ?? null;

if (!$idCliente) {
  echo "<div class='alert alert-danger m-4'>No se especificó el cliente.</div>";
  include 'includes/footer.php';
  exit;
}

// Datos del cliente
$sql = "SELECT idCliente, NombreCliente, Direccion, Telefono, Telefono2
        FROM cliente
        WHERE idCliente = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idCliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
  echo "<div class='alert alert-warning m-4'>Cliente no encontrado.</div>";
  include 'includes/footer.php';
  exit;
}
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="text-dark fw-bold mb-0">Historial del Cliente</h1>
      <p class="text-primary fw-semibold fs-3 mb-0">
        <?= htmlspecialchars($cliente['NombreCliente']) ?> (ID: <?= (int)$cliente['idCliente'] ?>)
      </p>
    </div>
    <a href="clientes.php" class="btn btn-info">
      <i class="fas fa-arrow-left me-1"></i> Regresar a Clientes
    </a>
  </div>

  <!-- Datos del cliente -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <div class="col-md-4">
        <label class="form-label fw-bold">Teléfono</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($cliente['Telefono']) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">Teléfono 2</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($cliente['Telefono2'] ?? '') ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">Dirección</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($cliente['Direccion']) ?>" readonly>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="mb-3"><i class="fas fa-filter me-2"></i> Filtros</h5>
      <div class="row g-3">
        <div class="col-md-2">
          <label class="form-label">Folio</label>
          <input type="number" id="filtroFolio" class="form-control" placeholder="Folio">
        </div>
        <div class="col-md-3">
          <label class="form-label">Estado</label>
          <select id="filtroEstado" class="form-select">
            <option value="todos">Todos</option>
            <option value="Proceso">Proceso</option>
            <option value="Espera">Espera</option>
            <option value="Avisado">Avisado</option>
            <option value="Entregado">Entregado</option>
            <option value="Retrasado">Retrasado</option>
            <option value="Cancelado">Cancelado</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tipo</label>
          <select id="filtroTipo" class="form-select">
            <option value="todos">Todos</option>
            <option value="Diseño">Diseño</option>
            <option value="Mantenimiento">Mantenimiento</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Fecha desde</label>
          <input type="date" id="fechaInicio" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Fecha hasta</label>
          <input type="date" id="fechaFin" class="form-control">
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla historial -->
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="tablaHistorial" style="width:100%">
          <thead class="table-dark">
            <tr>
              <th></th>
              <th>Folio</th>
              <th>Tipo</th>
              <th>Usuario asignado</th>
              <th>Fecha recepción</th>
              <th>Fecha entrega</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  window.__idCliente = <?= (int)$cliente['idCliente']; ?>;
</script>
<script src="../funciones/clientes.js"></script>

<?php include 'includes/footer.php'; ?>
