<?php
include 'includes/header.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$roles = $_SESSION['roles'] ?? [];
$rolesPermitidos = ['administrador', 'tecnico'];

if (empty(array_intersect($rolesPermitidos, $roles))) {
  echo "<div class='alert alert-danger m-4'>No tienes permisos para acceder a esta sección.</div>";
  include 'includes/footer.php';
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

    <!-- Acordeón: Historial / Licencias instaladas en órdenes -->
<div class="accordion mt-4" id="accLicencias">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingHistorial">
      <button class="accordion-button collapsed fw-bold" type="button"
        data-bs-toggle="collapse" data-bs-target="#collapseHistorial"
        aria-expanded="false" aria-controls="collapseHistorial">
        <i class="fas fa-clipboard-list me-2"></i> Licencias agregadas a órdenes (Historial)
      </button>
    </h2>
    <div id="collapseHistorial" class="accordion-collapse collapse"
      aria-labelledby="headingHistorial" data-bs-parent="#accLicencias">
      <div class="accordion-body">
        <div class="card shadow-sm">
          <div class="card-body">
            <table class="table table-bordered display nowrap tabla-responsiva w-100" id="tablaLicenciasOrden">
              <thead class="table-dark">
                <tr>
                  <th>Folio Orden</th>
                  <th>Cliente</th>
                  <th>Fecha</th>
                  <th>Software</th>
                  <th>Licencia</th>
                  <th>Password</th>
                  <th>Equipo</th>
                  <th>Procesador</th>
                  <th>ID Dispositivo</th>
                  <th>ID Producto</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

      </div>
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
