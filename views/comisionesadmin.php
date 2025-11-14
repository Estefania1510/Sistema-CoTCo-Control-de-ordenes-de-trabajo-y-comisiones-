<?php include 'includes/header.php'; 

require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();
$stmt = $conn->prepare("SELECT valor FROM configcomision WHERE nombreajuste = 'porcentaje'");
$stmt->execute();
$porcentajeActual = $stmt->fetchColumn() ?? 30;
?>

<div class="container-fluid mt-4">
  <h1 class="text-dark fw-bold mb-4">Gestión de Comisiones</h1>

    <div class="col-md-2">
      <label>% Comisión:</label>
      <div class="input-group">
        <input type="number" id="porcentaje" class="form-control"
               value="<?= $porcentajeActual ?>" min="1" max="100">
        <button class="btn btn-outline-success" id="btnActualizarPorcentaje" title="Actualizar porcentaje">
          <i class="fa-solid fa-square-check"></i>
        </button>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered" id="tablaComisiones">
        <thead class="table-dark">
          <tr>
            <th></th> 
            <th>Usuario</th>
            <th>Rol</th>
            <th>Trabajos</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
</div>

<script>
  window.__ROL_POWER__ = true;
</script>

<script src="../funciones/comisiones.js"></script>
<?php include 'includes/footer.php'; ?>
