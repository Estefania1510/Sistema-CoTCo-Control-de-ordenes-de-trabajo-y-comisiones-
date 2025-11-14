<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-4">
  <h1 class="text-dark fw-bold mb-3">Clientes</h1>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <div class="row g-3 align-items-center">
        <div class="col-md-4">
          <label class="form-label fw-bold">Buscar cliente</label>
          <input type="text" id="buscarCliente" class="form-control" placeholder="Ej. Juan">
        </div>
      </div>
    </div>
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

<script src="../funciones/clientes.js"></script>

<?php include 'includes/footer.php'; ?>
