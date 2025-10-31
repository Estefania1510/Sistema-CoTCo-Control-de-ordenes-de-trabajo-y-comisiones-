<?php 
include 'includes/header.php'; 
require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

$idNota = $_GET['id'] ?? null;
if (!$idNota) {
  die("ID de nota no especificado.");
}


// CONSULTA DE DISEÑO
$sql = "SELECT n.idNota, n.Total, n.Anticipo, n.Resto, n.FechaEntrega, 
               n.Descripcion AS DescTrabajo, n.Comentario,
               c.NombreCliente, c.Direccion, c.Telefono, c.Telefono2,
               nd.idDiseño, nd.CostoDiseño, nd.Estatus,
               u.NombreUsuario AS Diseñador,
               us.NombreUsuario AS RecepcionadoPor
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        INNER JOIN notadiseño nd ON n.idNota = nd.idNota
        INNER JOIN usuario us ON n.idUsuario = us.idUsuario
        LEFT JOIN usuario u ON nd.idDiseñador = u.idUsuario
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$diseno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$diseno) {
  die("No se encontró la orden de diseño.");
}


// CONSULTA MATERIALES
$sqlMat = "SELECT Material, Cantidad, Precio, Subtotal 
           FROM material 
           WHERE idDiseño = ?";
$stmt = $conn->prepare($sqlMat);
$stmt->execute([$diseno['idDiseño'] ?? 0]);
$materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 mt-3">
  <div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark fw-bold mb-0">Detalles de la Orden de Diseño</h1>
    <div class="px-3 py-2 bg-primary text-white rounded-3 shadow-sm fw-bold text-center" 
         style="min-width: 130px; font-size: 1.1rem;">
      Folio: <?= htmlspecialchars($diseno['idNota']) ?>
    </div>
  </div>

  <ol class="breadcrumb mb-4 mt-2">
    <li class="breadcrumb-item active">Vista de Diseño</li>
  </ol>

    <div class="card mb-4 shadow-sm">
      <div class="card-body row g-3">
          <div class="col-md-6">
            <label class="form-label">Recepcionado por</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($diseno['RecepcionadoPor']) ?>" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Diseñador asignado</label>
            <input type="text" class="form-control" 
                   value="<?= htmlspecialchars($diseno['Diseñador'] ?? 'En espera') ?>" readonly>
          </div>
      </div>
    </div>

  <!-- DATOS DEL CLIENTE -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-user me-2"></i> Datos del Cliente</h5>

      <div class="col-md-6">
        <label class="form-label">Nombre del Cliente</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($diseno['NombreCliente']) ?>" readonly>
      </div>

      <div class="col-md-3">
        <label class="form-label">Teléfono</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($diseno['Telefono']) ?>" readonly>
      </div>

      <div class="col-md-3">
        <label class="form-label">Teléfono 2</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($diseno['Telefono2'] ?? '') ?>" readonly>
      </div>

      <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($diseno['Direccion']) ?>" readonly>
      </div>
    </div>
  </div>

  <!-- DATOS DEL DISEÑO -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-paint-brush me-2"></i> Datos del Diseño</h5>

      <div class="col-md-12">
        <label class="form-label">Descripción del trabajo</label>
        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($diseno['DescTrabajo'] ?? '') ?></textarea>
      </div>

    </div>
  </div>

     <!-- TABLA DE MATERIALES -->
      <div class="card mb-4 shadow-sm">
       <div class="card-body">
         <h5 class="mb-3"><i class="fas fa-layer-group me-2"></i> Materiales utilizados</h5>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="table-light text-center">
                <tr>
                  <th>Material</th>
                  <th>Cantidad</th>
                  <th>Precio</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($materiales)): ?>
                  <?php foreach ($materiales as $m): ?>
                    <tr>
                      <td class="text-center"><?= htmlspecialchars($m['Material']) ?></td>
                      <td class="text-center"><?= htmlspecialchars($m['Cantidad']) ?></td>
                      <td class="text-center">$<?= number_format($m['Precio'], 2) ?></td>
                      <td class="text-center">$<?= number_format($m['Subtotal'], 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                 <tr><td colspan="4" class="text-center">No hay materiales registrados.</td></tr>
               <?php endif; ?>
             </tbody>
            </table>
          </div>
       </div>
      </div>



  <!-- COSTOS -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-money-bill me-2"></i> Costos</h5>

      <div class="col-md-4">
        <label class="form-label">Total</label>
        <input type="text" class="form-control" value="$<?= number_format($diseno['Total'], 2) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Anticipo</label>
        <input type="text" class="form-control" value="$<?= number_format($diseno['Anticipo'], 2) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Resto</label>
        <input type="text" class="form-control" value="$<?= number_format($diseno['Resto'], 2) ?>" readonly>
      </div>
      <div class="col-md-12">
        <label class="form-label">Comentario</label>
        <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($diseno['Comentario'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- BOTONES -->
  <div class="text-center mb-4">
    <a href="javascript:history.back()" class="btn btn-secondary px-4">
      <i class="fas fa-arrow-left"></i> Regresar
    </a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
