<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="text-dark fw-bold mb-0">Clientes</h1>

    <button class="btn btn-success" id="btnNuevoCliente">
      <i class="fas fa-plus me-1"></i> Nuevo Cliente
    </button>
  </div>

  <!-- Tabla de clientes -->
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="tablaClientes" style="width:100%">
          <thead class="table-dark">
            <tr>
              <th></th>
              <th>ID</th>
              <th>Nombre</th>
              <th>Teléfono</th>
              <th>Teléfono 2</th>
              <th>Dirección</th>
              <th>Órdenes</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Crear/Editar -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalClienteTitle">Nuevo Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="idCliente">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Nombre</label>
            <input type="text" id="NombreCliente" class="form-control" maxlength="50" required>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-bold">Teléfono</label>
            <input type="text" id="Telefono" class="form-control" maxlength="12">
          </div>

          <div class="col-md-3">
            <label class="form-label fw-bold">Teléfono 2</label>
            <input type="text" id="Telefono2" class="form-control" maxlength="12">
          </div>

          <div class="col-md-12">
            <label class="form-label fw-bold">Dirección</label>
            <input type="text" id="Direccion" class="form-control" maxlength="50">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnGuardarCliente">
          <i class="fas fa-save me-1"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<script src="../funciones/clientes.js"></script>
<?php include 'includes/footer.php'; ?>
