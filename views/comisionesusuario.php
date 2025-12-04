<?php include 'includes/header.php'; ?>
<div class="container-fluid mt-4">
  <h1 class="text-dark fw-bold mb-4">Mis Comisiones</h1>

  <!--Cuadros informativos -->
  <div class="row mb-4">
        <div class="col-md-4">
      <div class="card mb-4 border-0 shadow-sm" style="background-color:#e6f0ff; color:#003366; ">
        <div class="card-body">
          <h5 class="card-title">Total de Notas Entregadas</h5>
          <h3 id="totalEntregadas">$0.00</h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card mb-4 border-0 shadow-sm" style="background-color:#fff8bd; color:#e2b808; ">
        <div class="card-body">
          <h5 class="card-title">Pendientes</h5>
          <h3 id="totalPendientes">$0.00</h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card mb-4 border-0 shadow-sm" style="background-color:#e6ffee; color:#004d26; ">
        <div class="card-body">
          <h5 class="card-title">Pagadas</h5>
          <h3 id="totalPagadas">$0.00</h3>
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
