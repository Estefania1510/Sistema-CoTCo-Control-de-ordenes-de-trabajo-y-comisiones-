<?php include 'includes/header.php'; ?>

<h1 class="mt-4 text-dark fw-bold mb-0"><?= $usuario ?></h1>
<ol class="breadcrumb mb-4">
 <li class="text-primary fw-semibold fs-5 mb-0"><?= htmlspecialchars(implode(", ", $_SESSION['roles'])) ?></li>
</ol>

<!-- Tarjetas -->
<div class="container mt-4">
  <div class="row">

    <!-- En Proceso -->
    <div class="col-md-4">
      <div class="card mb-4 border-0 shadow-sm" style="background-color:#fff8bd; color:#e2b808; cursor:pointer;"
           onclick="window.location.href='ordenestrabajo.php?estado=Proceso'">
        <div class="card-body">
          <h5 class="card-title">Órdenes en Proceso</h5>
          <p class="display-6 fw-bold mb-0" id="countProceso">0</p>
        </div>
      </div>
    </div>

    <!-- Enviadas a Tequila -->
    <div class="col-md-4">
      <div class="card mb-4 border-0 shadow-sm" style="background-color:#e6f0ff; color:#003366; cursor:pointer;"
           onclick="window.location.href='ordenestrabajo.php?estado=EnviadoTequila'">
        <div class="card-body">
          <h5 class="card-title">Órdenes Enviadas a Tequila</h5>
          <p class="display-6 fw-bold mb-0" id="countTequila">0</p>
        </div>
      </div>
    </div>

    <!-- Entregadas -->
    <div class="col-md-4">
      <div class="card mb-4 border-0 shadow-sm" style="background-color:#e6ffee; color:#004d26; cursor:pointer;"
           onclick="window.location.href='ordenestrabajo.php?estado=Entregado'">
        <div class="card-body">
          <h5 class="card-title">Órdenes Entregadas</h5>
          <p class="display-6 fw-bold mb-0" id="countEntregado">0</p>
        </div>
      </div>
    </div>

    <!-- Retrasadas -->
    <div class="col-md-4">
      <div class="card mb-4 border-0 shadow-sm" style="background-color:#49444b; color:#ffffff; cursor:pointer;"
           onclick="window.location.href='ordenestrabajo.php?estado=Retrasado'">
        <div class="card-body">
          <h5 class="card-title">Órdenes Atrasadas</h5>
          <p class="display-6 fw-bold mb-0" id="countRetrasado">0</p>
        </div>
      </div>
    </div>

    <!-- Canceladas -->
    <div class="col-md-4">
      <div class="card mb-4 border-0 shadow-sm" style="background-color:#ffe6e6; color:#802000; cursor:pointer;"
           onclick="window.location.href='ordenestrabajo.php?estado=Cancelado'">
        <div class="card-body">
          <h5 class="card-title">Órdenes Canceladas</h5>
          <p class="display-6 fw-bold mb-0" id="countCancelado">0</p>
        </div>
      </div>
    </div>
  </div>
</div>


  <div class="text-center mb-4">
    <h4 class="mb-3">Crear Nueva Orden:</h4>
    <div class="d-flex justify-content-center gap-3" style="max-width:600px; margin:0 auto;">
      <a href="mantenimientoorden.php" class="btn btn-outline-primary btn-lg flex-fill">
        <i class="fas fa-tools me-2"></i> MANTENIMIENTO
      </a>
      <a href="diseñoorden.php" class="btn btn-outline-success btn-lg flex-fill">
        <i class="fas fa-paint-brush me-2"></i> DISEÑO
      </a>
    </div>
  </div>

  <script>
document.addEventListener("DOMContentLoaded", () => {
  fetch("../controllers/contarOrdenes.php")
    .then(res => res.json())
    .then(data => {
      document.getElementById("countProceso").textContent = data.Proceso ?? 0;
      document.getElementById("countTequila").textContent = data.EnviadoTequila ?? 0;
      document.getElementById("countEntregado").textContent = data.Entregado ?? 0;
      document.getElementById("countRetrasado").textContent = data.Retrasado ?? 0;
      document.getElementById("countCancelado").textContent = data.Cancelado ?? 0;
    })
    .catch(err => console.error("Error al contar órdenes:", err));
});
</script>



<?php include 'includes/footer.php'; ?>
