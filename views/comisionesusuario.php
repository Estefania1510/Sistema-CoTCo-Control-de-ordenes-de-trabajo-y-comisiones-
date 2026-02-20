<?php include 'includes/header.php'; ?>
<div class="container-fluid mt-4">
  <h1 class="text-dark fw-bold mb-4">Mis Comisiones</h1>


<!-- Tarjeta Total por pagar + Pagar todo -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5 class="card-title mb-1">Total de comisiones por pagar</h5>
          <h2 class="mb-0 text-danger" id="totalPorPagar">$0.00</h2>
          <small class="text-muted">Suma de TODAS las comisiones pendientes de pagar</small>
        </div>

        <button id="btnPagarTodo" class="btn btn-success btn-lg">
          <i class="fas fa-check-circle me-2"></i> Pagar todo
        </button>
      </div>
    </div>
  </div>
</div>




  <div class="card shadow p-3 mb-4">
    <div class="row g-2">
      <div class="col-md-3">
        <label>Del:</label>
        <input type="date" id="fechaInicio" class="form-control">
      </div>
      <div class="col-md-3">
        <label>Hasta:</label>
        <input type="date" id="fechaFin" class="form-control">
      </div>
      <div class="col-md-3">
        <label>Estado:</label>
        <select id="filtroEstado" class="form-select">
          <option value="todas">Todas</option>
          <option value="Orden no Entregada">Orden no Entregada</option>
          <option value="Orden Entregada">Orden Entregada</option>
          <option value="Pagado">Pagado</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button id="btnBuscar" class="btn btn-primary w-100">
          <i class="bi bi-search"></i> Buscar
        </button>
      </div>
    </div>
  </div>
  
<div class="table-responsive">
  <table class="table table-bordered" id="tablaComisionesUser">
    <thead class="table-dark">
        <tr>
          <td></td>
          <th>Folio</th>
          <th>Cliente</th>
          <th>Tipo</th>
          <th>Recepción</th>
          <th>Entrega de orden</th>
          <th>Monto</th>
          <th>Fecha de pago</th>
          <th>Estado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
  window.__ROL_POWER__ = false; 
  window.__idUsuarioActivo = <?= json_encode($_SESSION['idUsuario']); ?>;
</script>

<script src="../funciones/comisiones.js"></script>
<?php include 'includes/footer.php'; ?>
