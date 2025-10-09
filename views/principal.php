<?php include 'includes/header.php'; ?>

<h1 class="mt-4"><?= $usuario ?></h1>
<ol class="breadcrumb mb-4">
  <li class="breadcrumb-item active"><?= $roles ?></li>
</ol>

<!-- Tarjetas-->
<div class="row">
  <div class="col-md-4">
    <div class="card mb-4 border-0 shadow-sm" style="background-color:#e6f0ff; color:#003366;">
      <div class="card-body">
        <h5 class="card-title">Órdenes Activas</h5>
        <p class="display-6 fw-bold mb-0">12</p>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card mb-4 border-0 shadow-sm" style="background-color:#e6ffee; color:#004d26;">
      <div class="card-body">
        <h5 class="card-title">Órdenes Terminadas</h5>
        <p class="display-6 fw-bold mb-0">6</p>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card mb-4 border-0 shadow-sm" style="background-color:#ffe6e6; color:#802000;">
      <div class="card-body">
        <h5 class="card-title">Órdenes Atrasadas</h5>
        <p class="display-6 fw-bold mb-0">2</p>
      </div>
    </div>
  </div>
</div>


<div class="text-center mt-5">
  <h4 class="mb-4">Crear Orden:</h4>
  <div class="d-flex justify-content-center gap-3" style="max-width:600px; margin:0 auto;">
    <a href="ordenmantenimiento.php" class="btn btn-outline-primary btn-lg flex-fill">MANTENIMIENTO</a>
    <a href="ordendiseño.php" class="btn btn-outline-success btn-lg flex-fill">DISEÑO</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
