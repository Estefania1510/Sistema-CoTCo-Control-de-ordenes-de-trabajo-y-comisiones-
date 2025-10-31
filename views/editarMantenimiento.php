<?php 
include 'includes/header.php'; 
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_GET['id'] ?? null;

$sql = "SELECT 
          n.idNota, n.FechaRecepcion, n.FechaEntrega, n.Total, n.Anticipo, n.Resto, 
          n.Descripcion AS DescProblema, n.Comentario AS Sugerencia,
          c.NombreCliente, c.Telefono, c.Telefono2, c.Direccion,
          u.NombreUsuario AS RecepcionadoPor,
          m.idMantenimiento, m.Equipo, m.Marca, m.Model, m.Contraseña,
          m.Accesorios, m.SugerenciaTecn, m.Estatus, m.DescripcionEquipo, m.idTecnico
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        INNER JOIN usuario u ON n.idUsuario = u.idUsuario
        INNER JOIN notamantenimiento m ON m.idNota = n.idNota
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
  echo "<div class='alert alert-warning'>Orden no encontrada.</div>";
  exit;
}

// Técnicos activos
$sql2 = "SELECT u.idUsuario, u.NombreUsuario 
         FROM usuario u
         INNER JOIN usuarioroles ur ON u.idUsuario = ur.idUsuario
         INNER JOIN rol r ON ur.idRol = r.idRol
         WHERE (r.rol = 'tecnico' OR r.rol = 'administrador') 
           AND u.Estatus = 'Activo'";
$stmt2 = $conn->prepare($sql2);
$stmt2->execute();
$tecnicos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Servicios del catálogo asociados
$sql3 = "SELECT t.NombreTipo, c.Servicio, a.Precio
         FROM auxservicios a
         INNER JOIN catalogomnt c ON a.idCatalogoMnt = c.idCatalogoMnt
         INNER JOIN tipomantenimiento t ON c.idTipoMnt = t.idTipoMnt
         WHERE a.idMantenimiento = ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->execute([$orden['idMantenimiento']]);
$servicios = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Datos de sesión
$idUsuario = $_SESSION['idUsuario'];
$roles = $_SESSION['roles'] ?? [];
$rol = implode(',', $roles);
$puedeEditar = str_contains($rol, 'administrador') || str_contains($rol, 'encargado') ||
               (str_contains($rol, 'tecnico') && $orden['idTecnico'] == $idUsuario);
$puedeCambiarTecnico = str_contains($rol, 'administrador') || str_contains($rol, 'encargado');
?>

<div class="d-flex justify-content-between align-items-end mt-2">
  <h1 class="mt-2 text-dark fw-bold mb-0">Editar Orden de Mantenimiento</h1>
  <span class="badge bg-primary fs-5 me-5" style="min-width: 130px; font-size: 1.1rem;">Folio: <?= $orden['idNota'] ?></span>
</div>

<ol class="breadcrumb mb-4">
  <li class="breadcrumb-item active"></li>
</ol>

<form id="formEditarMantenimiento" method="POST">
  <input type="hidden" name="idNota" value="<?= $orden['idNota'] ?>">
  <input type="hidden" name="idMantenimiento" value="<?= $orden['idMantenimiento'] ?>">

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

  <!-- Datos del Equipo -->
  <div class="card mb-4">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fa-solid fa-laptop me-2"></i> Datos del Equipo</h5>
      <div class="col-md-4">
        <label class="form-label">Equipo</label>
        <input type="text" name="equipo" class="form-control" value="<?= htmlspecialchars($orden['Equipo']) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Marca</label>
        <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($orden['Marca']) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Modelo</label>
        <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($orden['Model']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Contraseña</label>
        <input type="text" name="contrasena" class="form-control" value="<?= htmlspecialchars($orden['Contraseña']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Accesorios</label>
        <input type="text" name="accesorios" class="form-control" value="<?= htmlspecialchars($orden['Accesorios']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Descripción del Equipo</label>
        <textarea name="descEquipo" class="form-control" rows="2"><?= htmlspecialchars($orden['DescripcionEquipo']) ?></textarea>
      </div>
    </div>
  </div>

  <!-- Problema -->
  <div class="card mb-4">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-tools me-2"></i> Problema y Sugerencia Técnica</h5>
      <div class="col-md-6">
        <label class="form-label">Descripción del Problema</label>
        <textarea name="descProblema" class="form-control" rows="3"><?= htmlspecialchars($orden['DescProblema']) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Sugerencia Técnica</label>
        <textarea name="sugerencia" class="form-control" rows="3"><?= htmlspecialchars($orden['SugerenciaTecn']) ?></textarea>
      </div>
    </div>
  </div>

  <!-- Menús desplegables para agregar servicio -->
  <?php if ($puedeEditar): ?>
  <div class="card mb-4">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fa-solid fa-list me-2"></i> Agregar Servicios</h5>
      <div class="col-md-6">
        <label class="form-label fw-bold">Tipo</label>
        <select id="tipoServicio" class="form-select">
          <option value="">Selecciona tipo</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Servicio</label>
        <select id="servicioCatalogo" class="form-select">
          <option value="">Selecciona servicio</option>
        </select>
      </div>
      <div class="col-md-12 d-flex gap-2 mt-2">
        <button type="button" id="btnAgregarServicio" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-plus me-1"></i> Agregar
        </button>
      </div>


  <?php endif; ?>

  <!-- Tabla de servicios -->
      <table class="table table-bordered display nowrap" id="tablaServicios" style="width:100%">
        <thead class="table-light">
          <tr>
            <th></th>
            <th>Tipo</th>
            <th>Servicio</th>
            <th>Precio</th>
            <th>Acción</th>
          </tr>
        </thead>

          <tbody>
            <?php 
            // Bloquear edición si NO es administrador ni encargada
            $bloquearCampos = !(
              str_contains($rol, 'administrador') || 
              str_contains($rol, 'encargado')
            );
            ?>

            <?php foreach ($servicios as $s): ?>
              <tr>
                <td></td>
                <td>
                  <input type="text" name="tipo[]" class="form-control" 
                         value="<?= htmlspecialchars($s['NombreTipo']) ?>" readonly>
                </td>
                <td>
                  <input type="text" name="servicio[]" class="form-control" 
                         value="<?= htmlspecialchars($s['Servicio']) ?>" readonly>
                </td>
                <td>
                  <input type="number" step="0.01" name="precio[]" class="form-control" 
                         value="<?= $s['Precio'] ?>" <?= $bloquearCampos ? 'readonly' : '' ?>>
                </td>
                <td>

                    <button type="button" class="btn btn-danger btn-sm fa-solid fa-trash-can" data-del="row"></button>

                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>

      </table>
    </div>
  </div>

  <!-- Costos -->
  <div class="card-body row g-3">
    <div class="col-md-4">
      <label class="form-label">Total</label>
      <input type="number" step="0.01" name="total" class="form-control" value="<?= $orden['Total'] ?>" readonly>
    </div>
    <div class="col-md-4">
      <label class="form-label">Anticipo</label>
      <input type="number" step="0.01" name="anticipo" class="form-control" value="<?= $orden['Anticipo'] ?>">
      <div id="error-anticipo" class="text-danger mt-1" style="display:none; font-size: 0.9em;"></div>
    </div>
    <div class="col-md-4">
      <label class="form-label">Resto</label>
      <input type="number" step="0.01" name="resto" class="form-control" value="<?= $orden['Resto'] ?>" readonly>
    </div>
    <div class="col-md-12 mt-2">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="cotizacionPendiente">
        <label class="form-check-label text-danger fw-semibold" for="cotizacionPendiente">
          Cotización pendiente
        </label>
      </div>
      <div id="msgPendiente" class="text-muted mt-1" style="display:none;">
        Los importes se llenarán cuando se haga la cotización.
      </div>
    </div>
  </div>

  <!-- Estatus y técnico -->
  <div class="card mb-4">
    <div class="card-body row g-3">
      <div class="col-md-4">
        <label class="form-label">Estatus</label>
        <select name="estatus" id="estatus" class="form-select">
          <?php
            $estatuses = ['Proceso','Espera','Avisado','Entregado','Cancelado'];
            foreach ($estatuses as $e) {
              $selected = $orden['Estatus'] == $e ? 'selected' : '';
              echo "<option value='$e' $selected>$e</option>";
            }
          ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Fecha de Entrega</label>
        <input type="date" name="FechaEntrega" id="FechaEntrega" class="form-control" 
               value="<?= $orden['FechaEntrega'] ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Técnico Asignado</label>
        <select name="idTecnico" class="form-select" <?= !$puedeCambiarTecnico ? 'disabled' : '' ?>>
          <option value="">En espera</option>
          <?php foreach ($tecnicos as $t): ?>
            <option value="<?= $t['idUsuario'] ?>" <?= $orden['idTecnico'] == $t['idUsuario'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($t['NombreUsuario']) ?>
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
<script src="../funciones/editarmantenimiento.js"></script>

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
