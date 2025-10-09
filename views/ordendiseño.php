  <?php include 'includes/header.php'; 

  require_once __DIR__ . "/../config/Conexion.php";
  require_once __DIR__ . "/../config/ConnectData.php";

  $conexion = new Conexion($conData);
  $conn = $conexion->getConnection();


  $sql = "SELECT u.idUsuario, u.NombreUsuario 
          FROM usuario u
          INNER JOIN usuarioroles ur ON u.idUsuario = ur.idUsuario
          INNER JOIN rol r ON ur.idRol = r.idRol
          WHERE r.rol = 'diseñador' AND u.Estatus = 'Activo'";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $diseñadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>


<h1 class="mt-2">Nueva Orden de Diseño</h5>
<ol class="breadcrumb mb-4">
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
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" id="telefono" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono 2 (opcional)</label>
        <input type="text" name="telefono2" id="telefono2" class="form-control">
      </div>
      <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" id="direccion"  class="form-control">
      </div>
    </div>
  </div>

  <!-- Descripción diseño -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="mb-3"><i class="fas fa-paint-brush me-2"></i> Descripción del Diseño</h5>
      <textarea name="descripcion" class="form-control" rows="3" required></textarea>
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
            <td><input type="text" name="material[]" class="form-control" ></td>
            <td><input type="number" name="cantidad[]" class="form-control" min="1" ></td>
            <td><input type="number" name="precio[]" class="form-control" step="0.01"></td>
            <td><button type="button" class="btn btn-danger btn-sm" data-del="row">X</button></td>
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
      <input type="number" step="0.01" name="subtotal" class="form-control" readonly>
    </div>
    <div>
      <label class="form-label">Diseño</label>
      <input type="number" step="0.01" name="diseño" class="form-control">
    </div>
     <div>
      <h5><label class="form-label">Total</label></h6>
      <input type="number" step="0.01" name="total" class="form-control" readonly>
    </div>
  </div>

  <div class="col-md-6 d-flex flex-column gap-3 align-items-end">
 
    <div style="width: 100%;">
      <label class="form-label">Anticipo</label>
      <input type="number" step="0.01" name="anticipo" class="form-control">
       <div id="error-anticipo" class="text-danger mt-1" style="display:none; font-size: 0.9em;"></div>
    </div>
    <div style="width: 100%;">
      <label class="form-label">Resto</label>
      <input type="number" step="0.01" name="resto" class="form-control" readonly>
    </div>
    <div style="width: 100%;">
      <label class="form-label">Comentarios</label>
      <input type="text" name="comentarios" class="form-control">
    </div>
  </div>
      <div class="col-md-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="cotPendiente" id="cotPendiente">
          <label class="form-check-label" for="cotPendiente">Cotización pendiente</label>
        </div>
        <div id="msgPendiente" class="text-muted mt-1" style="display:none; font-size: 0.9em;">
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


  <div class="text-center">
    <button type="submit" class="btn btn-primary btn-lg me-2">
      <i class="fas fa-save"></i> Guardar Orden
    </button>
    <button type="button" class="btn btn-danger btn-lg" onclick="history.back()">
      <i class="fas fa-times"></i> Cancelar
    </button>


      <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Tu archivo de alertas -->
  <script src="../funciones/alertas.js"></script>

  <script>
  document.getElementById("formOrdenDiseno").addEventListener("submit", async (e) => {
    e.preventDefault(); // Evita que el form se recargue

    const formData = new FormData(e.target);

    try {
      const res = await fetch("../controllers/procesarOrdenDiseno.php", {
        method: "POST",
        body: formData
      });
      const data = await res.json();

      if (data.status === "success") {
        alertaGuardadoExito(); // llama a la función del archivo alertas.js
        e.target.reset(); // limpia el formulario
      } else {
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