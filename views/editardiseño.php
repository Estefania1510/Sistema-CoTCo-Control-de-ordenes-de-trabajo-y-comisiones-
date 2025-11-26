<?php 
include 'includes/header.php'; 
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_GET['id'] ?? null;

$sql = "SELECT 
          n.idNota, n.FechaRecepcion, n.FechaEntrega, n.Total, n.Anticipo, n.Resto,
          n.Descripcion, n.Comentario, c.NombreCliente, c.Direccion, c.Telefono, c.Telefono2,
          u.NombreUsuario AS RecepcionadoPor,
          d.idDiseño, d.estatus, d.CostoDiseño, d.idDiseñador
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        INNER JOIN usuario u ON n.idUsuario = u.idUsuario
        INNER JOIN notadiseño d ON d.idNota = n.idNota
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
  echo "<div class='alert alert-warning'>Orden no encontrada.</div>";
  exit;
}

$sql2 = "SELECT u.idUsuario, u.NombreUsuario 
          FROM usuario u
          INNER JOIN usuarioroles ur ON u.idUsuario = ur.idUsuario
          INNER JOIN rol r ON ur.idRol = r.idRol
          WHERE r.rol = 'diseñador' AND u.Estatus = 'Activo'";
$stmt2 = $conn->prepare($sql2);
$stmt2->execute();
$diseñadores = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Materiales
$sql3 = "SELECT idMaterial, Material, Cantidad, Precio, Subtotal 
         FROM material WHERE idDiseño = ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->execute([$orden['idDiseño']]);
$materiales = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Datos del usuario actual
$idUsuario = $_SESSION['idUsuario'];
$roles = $_SESSION['roles'] ?? [];
$rol = implode(',', $roles); 
$puedeEditar = in_array($rol, ['administrador', 'encargado']) || ($rol == 'diseñador' && $orden['idDiseñador'] == $idUsuario);
$puedeCambiarDiseñador = in_array('administrador', $roles) || in_array('encargado', $roles);
?>
    <div class="d-flex justify-content-between align-items-end mt-2">
      <h1 class="mt-2 text-dark fw-bold mb-0">Editar Orden de Diseño</h1>
      <span class="badge bg-success fs-5 me-5" style="min-width: 130px; font-size: 1.1rem;">Folio: <?= $orden['idNota'] ?></span>
    </div>
    <ol class="breadcrumb mb-4">
      <li class="breadcrumb-item active"></li>
    </ol>

<form id="formEditarDiseno" method="POST">

  <input type="hidden" name="idNota" value="<?= $orden['idNota'] ?>">
  <input type="hidden" name="idDiseño" value="<?= $orden['idDiseño'] ?>">
  <input type="hidden" name="idDiseñadorOriginal" value="<?= $orden['idDiseñador'] ?>">

  <!-- Cliente -->
  <div class="card mb-4">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-user me-2"></i> Datos del Cliente</h5>
      <div class="col-md-6">
        <label class="form-label">Nombre del Cliente</label>
        <input type="text" value="<?= htmlspecialchars($orden['NombreCliente']) ?>" class="form-control" readonly>
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono</label>
        <input type="text" value="<?= htmlspecialchars($orden['Telefono']) ?>" class="form-control" readonly>
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono 2</label>
        <input type="text" value="<?= htmlspecialchars($orden['Telefono2']) ?>" class="form-control" readonly>
      </div>
      <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input type="text" value="<?= htmlspecialchars($orden['Direccion']) ?>" class="form-control" readonly>
      </div>
    </div>
  </div>

  <!-- Descripcion -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="mb-3"><i class="fas fa-paint-brush me-2"></i> Descripción del Diseño</h5>
        <textarea name="Descripcion" class="form-control" rows="3"><?= htmlspecialchars($orden['Descripcion']) ?></textarea>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll("textarea").forEach(t => {
                t.style.overflowY = "hidden";
                t.style.height = "auto";
                t.style.height = t.scrollHeight + "px";
            });
        });

        document.addEventListener("input", function (e) {
            if (e.target.tagName.toLowerCase() === "textarea") {
                e.target.style.height = "auto";
                e.target.style.height = e.target.scrollHeight + "px";
            }
        });
        </script>
    </div>
  </div>

  <!-- Material -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="mb-3"><i class="fas fa-boxes me-2"></i> Material</h5>
      <table class="table table-bordered display nowrap tabla-responsiva" id="tablaMateriales" style="width:100%">
        <thead class="table-light">
          <tr>
            <th></th>
            <th>Material</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
            <?php 
            $bloquearCampos = !(
              str_contains($rol, 'administrador') || 
              str_contains($rol, 'encargado')
            );
            ?>

            <?php foreach ($materiales as $m): ?>
            <tr>
              <td></td>
              <td>
                <input type="text" name="material[]" class="form-control" 
                       value="<?= htmlspecialchars($m['Material']) ?>"
                       <?= $bloquearCampos ? 'readonly' : '' ?>>
              </td>
              <td>
                <input type="text" name="cantidad[]" class="form-control"
                       value="<?= $m['Cantidad'] ?>"
                       <?= $bloquearCampos ? 'readonly' : '' ?>>
              </td>
              <td>
                <input type="text" name="precio[]" class="form-control"
                       value="<?= $m['Precio'] ?>"
                       <?= $bloquearCampos ? 'readonly' : '' ?>>
              </td>
              <td>
              
                  <button type="button" class="btn btn-danger btn-sm fa-solid fa-trash-can" data-del="row"> </button>
              
              </td>
            </tr>
            <?php endforeach; ?>
            
        </tbody>
      </table>
      <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addRow">Agregar Material</button>
    </div>
  </div>

  <!-- Costos -->
  <div class="card-body row g-3">
    <div class="col-md-6 d-flex flex-column gap-3">
      <div>
        <label class="form-label">Subtotal</label>
        <input type="number" step="0.01" name="subtotal" class="form-control" readonly>
      </div>
      <div>
        <label class="form-label">Diseño</label>
        <input type="number" step="0.01" name="diseño" class="form-control" value="<?= $orden['CostoDiseño'] ?>">
      </div>
      <div>
        <label class="form-label">Total</label>
        <input type="number" step="0.01" name="total" class="form-control" readonly>
      </div>
    </div>

    <div class="col-md-6 d-flex flex-column gap-3 align-items-end">
      <div style="width: 100%;">
        <label class="form-label">Anticipo</label>
        <input type="number" step="0.01" name="anticipo" class="form-control" value="<?= $orden['Anticipo'] ?>">
        <div id="error-anticipo" class="text-danger mt-1" style="display:none; font-size: 0.9em;"></div>
      </div>
      <div style="width: 100%;">
        <label class="form-label">Resto</label>
        <input type="number" step="0.01" name="resto" class="form-control" readonly>
      </div>
      <div style="width: 100%;">
        <label class="form-label">Comentarios</label>
        <input type="text" name="comentarios" class="form-control" value="<?= htmlspecialchars($orden['Comentario']) ?>">
      </div>
    </div>
  </div>

  <!-- Estatus y diseñador -->
  <div class="card mb-4">
    <div class="card-body row g-3">
      <div class="col-md-4">
        <label class="form-label">Estatus</label>
        <select name="estatus" id="estatus" class="form-select">
          <?php
            $estatuses = ['Proceso', 'EnviadoTequila', 'Avisado', 'Entregado', 'Cancelado'];
            foreach ($estatuses as $e) {
              $selected = $orden['estatus'] == $e ? 'selected' : '';
              echo "<option value='$e' $selected>$e</option>";
            }
          ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Fecha de Entrega</label>
        <input type="date" name="FechaEntrega" id="FechaEntrega" class="form-control" value="<?= $orden['FechaEntrega'] ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Diseñador</label>
        <select name="idDiseñador" class="form-select" <?= !$puedeCambiarDiseñador ? 'disabled' : '' ?>>
          <option value="">En espera</option>
          <?php foreach ($diseñadores as $d): ?>
            <option value="<?= $d['idUsuario'] ?>" <?= $orden['idDiseñador'] == $d['idUsuario'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['NombreUsuario']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Botones -->
      <div class="text-center mb-5">
        <button type="submit" class="btn btn-success btn-lg fw-bold px-4">
          <i class="bi bi-save"></i> Guardar Cambios
        </button>
        <button type="button" class="btn btn-danger btn-lg fw-bold px-4" onclick="history.back()">
          <i class="fas fa-times"></i> Cancelar
        </button>
      </div>
</form>

<?php include 'includes/footer.php'; ?>
<script>
  const rolUsuario = "<?= $rol ?>";
</script>
<script src="../funciones/editardiseño.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const estatusSelect = document.getElementById("estatus");
  const fechaEntregaInput = document.getElementById("FechaEntrega");

  if (estatusSelect.value !== "Entregado") {
    fechaEntregaInput.setAttribute("readonly", true);
    fechaEntregaInput.classList.add("bg-light");
  }

  estatusSelect.addEventListener("change", function() {
    if (estatusSelect.value === "Entregado") {
  
      fechaEntregaInput.removeAttribute("readonly");
      fechaEntregaInput.classList.remove("bg-light");

      if (!fechaEntregaInput.value) {
        const hoy = new Date().toISOString().split("T")[0];
        fechaEntregaInput.value = hoy;
      }
    } else {
      fechaEntregaInput.setAttribute("readonly", true);
      fechaEntregaInput.classList.add("bg-light");
      fechaEntregaInput.value = ""; 
    }
  });
});


</script>


