<?php
include 'includes/header.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

// Validar rol administrador
if (!in_array('administrador', $_SESSION['roles'] ?? [])) {
  header('Location: ../index.php');
  exit;
}
?>

<div class="container-fluid mt-4">
  <h1 class="text-dark fw-bold mb-3"><i class="fas fa-key me-2"></i> Licencias de Software</h1>

  <!-- Formulario -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Licencia</label>
          <input type="text" id="licencia" class="form-control" placeholder="Clave o serial" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Software</label>
          <select id="software" class="form-select" required>
            <option value="">Seleccionar software...</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Estatus</label>
          <input type="text" id="estatus" class="form-control" value="Libre" readonly>
        </div>
        <div class="col-md-2 text-end">
          <button id="btnAgregar" class="btn btn-primary fw-bold w-100">
            <i class="fas fa-plus"></i> Agregar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered display nowrap tabla-responsiva w-100" id="tablaLicencias">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Licencia</th>
            <th>Software</th>
            <th>Estatus</th>
            <th>ID Instalación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>


<script src="../funciones/licenciasoftware.js"></script>

<?php include 'includes/footer.php'; ?>
