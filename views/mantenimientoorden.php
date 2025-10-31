  <?php include 'includes/header.php'; 

  require_once __DIR__ . "/../config/Conexion.php";
  require_once __DIR__ . "/../config/ConnectData.php";

  $conexion = new Conexion($conData);
  $conn = $conexion->getConnection();


  $sql = "SELECT  u.idUsuario, u.NombreUsuario
          FROM usuario u
          INNER JOIN usuarioroles ur ON u.idUsuario = ur.idUsuario
          INNER JOIN rol r ON ur.idRol = r.idRol
          WHERE (r.rol = 'tecnico' OR r.rol = 'administrador')
            AND u.Estatus = 'Activo'";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ?>

    <div class="d-flex justify-content-between align-items-center mt-2">
      <h1 class="mt-2 text-dark fw-bold mb-0">Nueva Orden de Mantenimiento</h1>
      <div class="px-3 py-2 bg-primary text-white rounded-3 shadow-sm fw-bold text-center" 
           style="min-width: 130px; font-size: 1.1rem;">
        Folio: <span id="folio"></span>
      </div>
    </div>
    <ol class="breadcrumb mb-4 mt-2">
      <li class="breadcrumb-item active">Registro de pedido de diseño</li>
    </ol>


<form id="formMantenimiento" method="POST">

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
        <input type="text" name="telefono" id="telefono" class="form-control" maxlength="12" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Teléfono 2 (opcional)</label>
        <input type="text" name="telefono2" id="telefono2" maxlength="12" class="form-control">
      </div>
      <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" id="direccion"  class="form-control" required>
      </div>
    </div>
  </div>

  <!-- DATOS DEL EQUIPO-->
  <div class="card mb-4">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fa-solid fa-laptop"></i> Datos del Equipo</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Equipo</label>
          <input type="text" id="equipo" name="equipo" class="form-control" placeholder="Ejemplo: Laptop, CPU, Impresora" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Marca</label>
          <input type="text" id="marca" name="marca" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Modelo</label>
          <input type="text" id="modelo" name="modelo" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Contraseña</label>
          <input type="text" id="contrasena" name="contrasena" class="form-control" placeholder="Si aplica">
        </div>
        <div class="col-md-4">
          <label class="form-label">Accesorios</label>
          <input type="text" id="accesorios" name="accesorios" class="form-control" placeholder="Ejemplo: cargador, mouse, maletín">
        </div>
        <div class="col-md-4">
          <label class="form-label">Descripcion del Equipo</label>
          <textarea type="text" id="descEquipo" name="descEquipo" class="form-control" 
          placeholder="Ejemplo: Color, algun rayon etc." required></textarea>
        </div>

      </div>
    </div>
  </div>

  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-tools me-2"></i> Problema del Equipo</h5>

        <div class="col-md-4">
          <label class="form-label">Descripcion del Problema</label>
          <textarea type="text" id="descProblema" name="descProblema" class="form-control" 
          placeholder="Ejemplo: No enciende, esta lenta"></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label">Sugerencia Técnica</label>
          <textarea type="text" id="sugerencia" name="sugerencia" class="form-control"
           placeholder="Recomendacion del Tecnico"></textarea>
        </div>
  </div>

    <!-- CHECKBOX -->
      <div class="card-body d-flex flex-wrap justify-content-start gap-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="agregarProblema">
          <label class="form-check-label fw-semibold" for="agregarProblema">
            Agregar problema desde catálogo
          </label>
        </div>
      </div>

      <!-- BLOQUE CATÁLOGO -->
      <div id="bloqueCatalogo" style="display:none;">
        <div class="card-body pt-3">
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-bold">Tipo</label>
              <select id="tipoServicio" class="form-select">
                <option value="">Selecciona tipo</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Servicios</label>
              <select id="servicioCatalogo" class="form-select">
                <option value="">Selecciona servicio</option>
              </select>
            </div>
            <div class="col-md-12 d-flex gap-2 mt-2">
              <button type="button" id="btnAgregarServicio"  class="btn btn-outline-primary btn-sm">
                Agregar
              </button>
            </div>
        </div>

        <!-- TABLA DE SERVICIOS -->
        <div class="card-body">
          <table id="tablaMnt" class="table table-bordered table-striped display nowrap" style="width:100%">
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
  
            </tbody>
          </table>
        </div>
      </div>
    </div>

  <!-- COSTOS  -->
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Total</label>
          <input type="number" step="0.01" id="total" name="total" class="form-control" value="0.00" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Anticipo</label>
          <input type="number" step="0.01" name="anticipo" id="anticipo" class="form-control" value="0.00">
          <div id="error-anticipo" class="text-danger mt-1" style="display:none; font-size: 0.9em;"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Resto</label>
          <input type="number" step="0.01" id="resto" name="resto" class="form-control"  value="0.00" readonly>
        </div>
      </div>
      <div class="col-md-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="cotizacionPendiente" id="cotizacionPendiente">
          <label class="form-check-label form-check-label fw-semibold text-danger" for="cotizacionPendiente">Cotización pendiente</label>
        </div>
        <div id="msgPendiente" class="text-muted mt-1 " style="display:none; font-size: 0.9em;">
               Los importes se llenarán cuando se haga la cotización.
      </div>
      </div>
    </div>
  </div>


  <!--ASIGNACIÓN  -->
  <div class="card mb-3 shadow-sm">
    <div class="card-body fw-bold">
      <h5 class="mb-3"><i class="fas fa-users-cog me-2"></i> Asignación</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Recepcionado por</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario); ?>" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Técnico asignado</label>
          <select id="tecnico" class="form-select">
            <option value="">En espera</option>
            <?php foreach ($tecnicos as $t): ?>
            <option value="<?= $t['idUsuario'] ?>"><?= htmlspecialchars($t['NombreUsuario']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!--  BOTÓN GUARDAR -->
  <div class="text-center mb-5">
    <button type="submit" class="btn btn-primary btn-lg fw-bold px-4">
      <i class="bi bi-save"></i> Guardar Orden
    </button>
    <button type="button" class="btn btn-danger btn-lg fw-bold px-4" onclick="history.back()">
      <i class="fas fa-times"></i> Cancelar
    </button>
</div>
     
      <!--  GUARDAR -->
    <script src="../funciones/alertas.js"></script>

    <script>
    document.getElementById("formMantenimiento").addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
     formData.append("idUsuario", <?= $_SESSION['idUsuario'] ?>);
      formData.append("tecnico", document.getElementById("tecnico").value);

      try {
        const res = await fetch("../controllers/procesarMantenimiento.php", {
          method: "POST",
          body: formData
        });

        const data = await res.json();

          if (data.status === "success") {
              alertaGuardadoExito(data.folio);
              e.target.reset();

              // Ocultar bloque de catálogo 
              $('#bloqueCatalogo').slideUp();
              $('#agregarProblema').prop('checked', false);
              $('#tipoServicio').val('');
              $('#servicioCatalogo').val('');
              $('#tablaMnt').DataTable().clear().draw();

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
                window.open(`../controllers/TicketMantenimiento.php?idNota=${data.idNota}`, "_blank");
              }, 1600);
          }

       else {
          alertaError(data.error || data.message);
        }
      } catch (err) {
        alertaError(err.message);
      }
    });
    </script>

</form>

<?php include 'includes/footer.php'; ?>

<script src="../funciones/mantenimientoorden.js"></script>



