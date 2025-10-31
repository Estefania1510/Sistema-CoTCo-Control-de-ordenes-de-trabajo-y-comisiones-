<?php 
include 'includes/header.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/ConnectData.php';

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();



$idUsuario = $_SESSION['idUsuario'];
$roles = $_SESSION['roles'] ?? [];
$rol = $roles[0] ?? '';
?>

<div class="container-fluid mt-4">

  <!-- Encabezado superior -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <h1 class="mt-4 text-dark fw-bold">Órdenes de Trabajo</h1>

    <div class="mt-2 mt-md-0">
      <div class="d-flex justify-content-end gap-2">
        <a href="mantenimientoorden.php" class="btn btn-outline-primary btn-lg">
          <i class="fas fa-tools me-2"></i> MANTENIMIENTO
        </a>
        <a href="diseñoorden.php" class="btn btn-outline-success btn-lg">
          <i class="fas fa-paint-brush me-2"></i> DISEÑO
        </a>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card shadow-sm mb-4">
    <div class="card-body row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Nombre del Cliente o Folio</label>
        <input type="text" id="filtroNombre" class="form-control" placeholder="Buscar cliente o folio...">
      </div>
      <div class="col-md-2">
        <label class="form-label">Estado</label>
        <select id="filtroEstado" class="form-select">
          <option value="">Todos</option>
          <option value="Proceso">Proceso</option>
          <option value="Espera">Espera</option>
          <option value="EnviadoTequila">EnviadoTequila</option>
          <option value="Avisado">Avisado</option>
          <option value="Entregado">Entregado</option>
          <option value="Cancelado">Cancelado</option>
          <option value="Retrasado">Retrasado</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Tipo</label>
        <select id="filtroTipo" class="form-select">
          <option value="">Todos</option>
          <option value="Diseño">Diseño</option>
          <option value="Mantenimiento">Mantenimiento</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Fecha</label>
        <input type="date" id="filtroFecha" class="form-control">
      </div>
        <div class="col-md-2 d-flex flex-column justify-content-start">
          <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" id="OrdenesTrabajadas">
            <label class="form-check-label" for="OrdenesTrabajadas">Ordenes Trabajadas</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="misOrdenes">
            <label class="form-check-label" for="misOrdenes" style="white-space: nowrap;">Ordenes Recepcionadas</label>
          </div>
        </div>

    </div>
  </div>



  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-body">
      <table id="tablaOrdenes" class="table table-bordered display nowrap tabla-responsiva w-100">
        <thead class="table-dark">
          <tr>
            <th></th>
            <th>Folio</th>
            <th>Cliente</th>
            <th>Tipo</th>
            <th>Fecha Recepción</th>
            <th>Fecha Entrega</th>
            <th>Estado</th>
            <th>Usuario</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

  <script>
  const usuarioActual = {
    id: <?php echo json_encode($idUsuario); ?>,
    rol: <?php echo json_encode($rol); ?>
  };
</script>

<script>
  window.rolesUsuario = "<?= implode(',', $_SESSION['roles'] ?? []); ?>";
</script>

<script src="../funciones/ordenestrabajo.js"></script>
<?php include 'includes/footer.php'; ?>
