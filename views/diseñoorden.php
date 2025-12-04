  <?php include 'includes/header.php'; 

  require_once __DIR__ . "/../config/Conexion.php";
  require_once __DIR__ . "/../config/ConnectData.php";

  $conexion = new Conexion($conData);
  $conn = $conexion->getConnection();

  $sql = "SELECT  u.idUsuario, u.NombreUsuario
          FROM usuario u
          INNER JOIN usuarioroles ur ON u.idUsuario = ur.idUsuario
          INNER JOIN rol r ON ur.idRol = r.idRol
          WHERE (r.rol = 'diseñador' OR r.rol = 'administrador')
            AND u.Estatus = 'Activo'";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $diseñadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ?>                                      

    <div class="d-flex justify-content-between align-items-center mt-2">
      <h1 class="mt-2 text-dark fw-bold mb-0">Nueva Orden de Diseño</h1>
      <div class="px-3 py-2 bg-primary text-white rounded-3 shadow-sm fw-bold text-center" 
           style="min-width: 130px; font-size: 1.1rem;">
        Folio: <span id="folio"></span>
      </div>
    </div>
    <ol class="breadcrumb mb-4 mt-2">
      <li class="breadcrumb-item active">Registro de pedido de diseño</li>
    </ol>
<form id="formOrdenDiseno" method="POST">

  <!-- Cliente -->
  <div class="card mb-4">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-user me-2"></i> Datos del Cliente</h5>
      <div class="col-md-6">
        <label class="form-label">Nombre del Cliente</label>
        <div class="input-group">
          <input type="text" name="nombreCliente" id="nombreCliente" class="form-control" required>
        </div>
          <input type="hidden" name="idCliente" id="idCliente">
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" id="telefono" class="form-control" maxlength="12" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono 2 (opcional)</label>
        <input type="text" name="telefono2" id="telefono2" class="form-control" maxlength="12">
      </div>
      <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" id="direccion"  class="form-control" required>
      </div>
    </div>
  </div>

  <!-- Descripción diseño -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="mb-3"><i class="fas fa-paint-brush me-2"></i> Descripción del Diseño</h5>
      <textarea name="descripcion" class="form-control" rows="3" required></textarea>
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

  <!-- Materiales -->
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
          <tr>
            <td></td>
            <td><input type="text" name="material[]" class="form-control" required ></td>
            <td><input type="text" name="cantidad[]" class="form-control" min="1" ></td>
            <td><input type="text" name="precio[]" class="form-control" step="0.01"></td>
            <td><button type="button" class="btn btn-danger btn-sm" data-del="row"><i class="fa-solid fa-trash-can"></i></button></td>
          </tr>
        </tbody>
      </table>
      <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addRow">Agregar Material</button>
    </div>

  <!-- Costos -->
  <div class="card-body row g-3">
  <div class="col-md-6 d-flex flex-column gap-3">
    <div>
      <label class="form-label">Subtotal</label>
      <input type="number" step="0.01" name="subtotal" class="form-control" value="0.00" readonly>
    </div>
    <div>
      <label class="form-label">Diseño</label>
      <input type="number" step="0.01" name="diseño" class="form-control" value="0.00">
    </div>
     <div>
      <h5><label class="form-label">Total</label></h6>
      <input type="number" step="0.01" name="total" class="form-control" value="0.00" readonly>
    </div>
  </div>
  <div class="col-md-6 d-flex flex-column gap-3 align-items-end">
    <div style="width: 100%;">
      <label class="form-label">Anticipo</label>
      <input type="number" step="0.01" name="anticipo" class="form-control" value="0.00">
       <div id="error-anticipo" class="text-danger mt-1" style="display:none; font-size: 0.9em;"></div>
    </div>
    <div style="width: 100%;">
      <label class="form-label">Resto</label>
      <input type="number" step="0.01" name="resto" class="form-control" value="0.00" readonly>
    </div>
    <div style="width: 100%;">
      <label class="form-label">Comentarios</label>
      <input type="text" name="comentarios" class="form-control">
    </div>
  </div>
      <div class="col-md-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="cotPendiente" id="cotPendiente">
          <label class="form-check-label form-check-label fw-semibold text-danger" for="cotPendiente">Cotización pendiente</label>
        </div>
        <div id="msgPendiente" class="text-muted mt-1 " style="display:none; font-size: 0.9em;">
               Los importes se llenarán cuando se haga la cotización.
      </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-users-cog me-2"></i> Asignación</h5>
      <div class="col-md-6">
        <label class="form-label">Recepcionado por</label>
        <input type="text" class="form-control" value="<?= $usuario ?>" readonly>
      </div>
      <div class="col-md-6">
        <label class="form-label">Diseñador</label>
      <select name="idDiseñador" class="form-select">
        <option value="">En espera</option>
        <?php foreach ($diseñadores as $d): ?>
          <option value="<?= $d['idUsuario'] ?>"><?= htmlspecialchars($d['NombreUsuario']) ?></option>
        <?php endforeach; ?>
      </select>
      </div>
    </div>
  </div>

  <div class="text-center mb-5">
    <button type="submit" class="btn btn-primary btn-lg fw-bold px-4">
      <i class="bi bi-save"></i> Guardar Orden
    </button>
    <button type="button" class="btn btn-danger btn-lg fw-bold px-4" onclick="history.back()">
      <i class="fas fa-times"></i> Cancelar
    </button>
</div>

  <!-- ALERTAS -->
  <script src="../funciones/alertas.js"></script>

  <script>
  document.getElementById("formOrdenDiseno").addEventListener("submit", async (e) => {
    e.preventDefault(); 

     console.log("ID ENVIADO:", document.getElementById("idCliente").value); // ← PRUEBA

    const formData = new FormData(e.target);

    try {
      const res = await fetch("../controllers/procesarOrdenDiseno.php", {
        method: "POST",
        body: formData
      });
      const data = await res.json();

      if (data.status === "success") {
        alertaGuardadoExito(data.folio);
        e.target.reset();

        //Actualizar el folio automáticamente
        $.ajax({
          url: "../controllers/obtenerFolio.php",
          method: "GET",
          dataType: "json",
          success: function (data) {
            $("#folio").text(data.folio);
          }
        });
        setTimeout(() => {
          window.open(`../controllers/TicketDiseno.php?idNota=${data.folio}`, '_blank');
        }, 1600);
      }
       else {
        alertaError(data.message);
      }
    } catch (err) {
      alertaError(err.message);
    }
  });
  </script>

  </div>
</form>
<?php include 'includes/footer.php'; ?>
<script src="../funciones/diseñoorden.js"></script>

