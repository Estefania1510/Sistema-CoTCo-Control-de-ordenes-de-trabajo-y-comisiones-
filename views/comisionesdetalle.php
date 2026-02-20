<?php include 'includes/header.php'; 

$idUsuario = $_GET['id'] ?? null;
$nombre = $_GET['nombre'] ?? '';

if (!$idUsuario) {
  echo "<div class='alert alert-danger m-4'>No se especificó el usuario.</div>";
  include 'includes/footer.php';
  exit;
}
?>
 <div class="d-flex justify-content-between align-items-center mb-3">
<div class="mb-3">
  <h1 class="text-dark fw-bold mb-1">Detalle de Comisiones</h1>
  <p class="text-primary fw-semibold fs-3 mb-0"><?= htmlspecialchars($nombre) ?></p>
</div>
    <a href="comisionesadmin.php" class="btn btn-info">
      <i class="fas fa-arrow-left me-1"></i> Regresar a Gestión de Comisiones
    </a>
</div>

<!-- Tarjeta Total por pagar + Pagar todo (ADMIN DETALLE) -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5 class="card-title mb-1">Total de comisiones por pagar</h5>
          <h2 class="mb-0 text-danger" id="totalPorPagar">$0.00</h2>
          <small class="text-muted">Suma de comisiones NO pagadas</small>
        </div>

        <button id="btnPagarTodo" class="btn btn-success btn-lg">
          <i class="fas fa-check-circle me-2"></i> Pagar todo
        </button>
      </div>
    </div>
  </div>
</div>

  
  <!-- Filtros -->
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
          <option value="Orden Cancelada">Orden Cancelada</option>
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
  <table class="table table-bordered" id="tablaDetalle">
    <thead class="table-dark">
      <tr>
        <th></th> 
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
</div>

<script>
  window.__idUsuarioActivo = <?= (int)$idUsuario ?>;
  window.__ROL_POWER__ = <?= json_encode(in_array('administrador', $_SESSION['roles'] ?? [])) ?>;
</script>

<script src="../funciones/comisionesdetalle.js"></script>
<?php include 'includes/footer.php'; ?>
