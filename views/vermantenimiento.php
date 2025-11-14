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

//CONSULTA MANTENIMIENTO
$sql = "SELECT n.idNota, n.Total, n.Anticipo, n.Resto, n.FechaRecepcion, 
               n.Descripcion AS DescProblema, n.Comentario AS SugerenciaGeneral,
               c.NombreCliente, c.Direccion, c.Telefono, c.Telefono2,
               m.idMantenimiento, m.Equipo, m.Marca, m.Model, m.Contraseña, 
               m.Accesorios, m.SugerenciaTecn, m.DescripcionEquipo, m.Estatus,
               u.NombreUsuario AS Tecnico,
               us.NombreUsuario AS RecepcionadoPor
        FROM nota n
        INNER JOIN cliente c ON n.idCliente = c.idCliente
        INNER JOIN notamantenimiento m ON n.idNota = m.idNota
        INNER JOIN usuario us ON n.idUsuario = us.idUsuario
        LEFT JOIN usuario u ON m.idTecnico = u.idUsuario
        WHERE n.idNota = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idNota]);
$mantenimiento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mantenimiento) {
  die("No se encontró la orden de mantenimiento.");
}


$sqlServ = "SELECT tm.NombreTipo, c.Servicio, a.Precio
             FROM auxservicios a
             INNER JOIN catalogomnt c ON a.idCatalogoMnt = c.idCatalogoMnt
             INNER JOIN tipomantenimiento tm ON c.idTipoMnt = tm.idTipoMnt
             WHERE a.idMantenimiento = ?";
$stmt = $conn->prepare($sqlServ);
$stmt->execute([$mantenimiento['idMantenimiento'] ?? 0]);
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 mt-3">
  <div class="d-flex justify-content-between align-items-center">
    <h1 class="text-dark fw-bold mb-0">Detalles de la Orden de Mantenimiento</h1>
    <div class="px-3 py-2 bg-primary text-white rounded-3 shadow-sm fw-bold text-center" 
         style="min-width: 130px; font-size: 1.1rem;">
      Folio: <?= htmlspecialchars($mantenimiento['idNota']) ?>
    </div>
  </div>

  <ol class="breadcrumb mb-4 mt-2">
    <li class="breadcrumb-item active">Vista de Mantenimiento</li>
  </ol>

  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <div class="col-md-6">
        <label class="form-label fw-bold">Recepcionado por</label>
        <input type="text" class="form-control" 
               value="<?= htmlspecialchars($mantenimiento['RecepcionadoPor']) ?>" readonly>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-bold">Técnico asignado</label>
        <input type="text" class="form-control" 
               value="<?= htmlspecialchars($mantenimiento['Tecnico'] ?? 'En espera') ?>" readonly>
      </div>
    </div>
  </div>

  <!-- DATOS DEL CLIENTE -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-user me-2 " ></i> Datos del Cliente</h5>

      <div class="col-md-6">
        <label class="form-label">Nombre del Cliente</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['NombreCliente']) ?>" readonly>
      </div>

      <div class="col-md-3">
        <label class="form-label">Teléfono</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['Telefono']) ?>" readonly>
      </div>

      <div class="col-md-3">
        <label class="form-label">Teléfono 2</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['Telefono2'] ?? '') ?>" readonly>
      </div>

      <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['Direccion']) ?>" readonly>
      </div>
    </div>
  </div>

  <!-- DATOS DEL EQUIPO -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fa-solid fa-laptop me-2"></i> Datos del Equipo</h5>

      <div class="col-md-4">
        <label class="form-label">Equipo</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['Equipo']) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Marca</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['Marca']) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Modelo</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['Model'] ?? '') ?>" readonly>
      </div>

      <div class="col-md-4">
        <label class="form-label">Contraseña</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['Contraseña'] ?? '') ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Accesorios</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($mantenimiento['Accesorios'] ?? '') ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Descripción del Equipo</label>
        <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($mantenimiento['DescripcionEquipo'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- DESCRIPCIÓN DEL PROBLEMA Y SUGERENCIA -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body row g-3">
      <h5 class="mb-3"><i class="fas fa-tools me-2"></i> Diagnóstico y Sugerencias</h5>

      <div class="col-md-6">
        <label class="form-label">Descripción del Problema</label>
        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($mantenimiento['DescProblema'] ?? '') ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label">Sugerencia Técnica</label>
        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($mantenimiento['SugerenciaTecn'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- TABLA DE SERVICIOS -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="mb-3"><i class="fas fa-list me-2"></i> Servicios del Catálogo</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="table-light text-center">
            <tr>
              <th>Tipo</th>
              <th>Servicio</th>
              <th>Precio</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($servicios)): ?>
              <?php foreach ($servicios as $s): ?>
                <tr>
                  <td class="text-center"><?= htmlspecialchars($s['NombreTipo']) ?></td>
                  <td class="text-center"><?= htmlspecialchars($s['Servicio']) ?></td>
                  <td class="text-center">$<?= number_format($s['Precio'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="3" class="text-center">No hay servicios registrados.</td></tr>
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
        <input type="text" class="form-control" value="$<?= number_format($mantenimiento['Total'], 2) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Anticipo</label>
        <input type="text" class="form-control" value="$<?= number_format($mantenimiento['Anticipo'], 2) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Resto</label>
        <input type="text" class="form-control" value="$<?= number_format($mantenimiento['Resto'], 2) ?>" readonly>
      </div>
    </div>
  </div>

  <div class="text-center mb-4">
    <a href="javascript:history.back()" class="btn btn-secondary px-4">
      <i class="fas fa-arrow-left"></i> Regresar
    </a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
