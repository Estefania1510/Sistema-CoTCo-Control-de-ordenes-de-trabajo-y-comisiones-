<?php include 'includes/header.php'; 

require_once __DIR__ . "/../config/Conexion.php";
require_once __DIR__ . "/../config/ConnectData.php";

$conexion = new Conexion($conData);
$conn = $conexion->getConnection();

// Validar rol administrador
if (!in_array('administrador', $_SESSION['roles'] ?? [])) {
  header('Location: ../index.php');
  exit;
}

//CONSULTA DE USUARIOS Y ROLES 
$sql = "SELECT 
            u.idUsuario, 
            u.NombreUsuario, 
            u.Usuario, 
            GROUP_CONCAT(r.rol ORDER BY r.rol SEPARATOR ', ') AS Roles,
            u.Estatus
        FROM usuario u
        INNER JOIN usuarioroles ur ON u.idUsuario = ur.idUsuario
        INNER JOIN rol r ON ur.idRol = r.idRol
        GROUP BY u.idUsuario
        ORDER BY u.NombreUsuario ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


// CONSULTA DE ROLES PARA FILTRO 
$roles = $conn->query("SELECT rol FROM rol WHERE estatus='Activo'")->fetchAll(PDO::FETCH_ASSOC);

?>                                      

<div class="d-flex justify-content-between align-items-center mt-2">
  <h1 class="mt-2 text-dark fw-bold mb-0">Administración de Usuarios</h1>
  <button class="btn btn-primary fw-bold shadow-sm" id="btnAgregar">
    <i class="fas fa-user-plus me-2"></i>Agregar Usuario
  </button>
</div>

<ol class="breadcrumb mb-4 mt-2">
  <li class="breadcrumb-item active">Gestión de usuarios del sistema</li>
</ol>

<div class="card mb-4">
  <div class="card-body">

    <!-- Filtros -->
    <div class="row g-3 mb-3">
      <div class="col-md-6 col-lg-4">
        <label class="form-label fw-semibold">Buscar por nombre:</label>
        <input type="text" id="buscarNombre" class="form-control" placeholder="Ingrese el nombre del usuario">
      </div>

      <div class="col-md-6 col-lg-3">
        <label class="form-label fw-semibold">Filtrar por rol:</label>
        <select id="filtroRol" class="form-select">
          <option value="">Todos</option>
          <?php foreach ($roles as $r): ?>
            <option value="<?= htmlspecialchars($r['rol']) ?>"><?= htmlspecialchars(ucfirst($r['rol'])) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="table-responsive">
      <table class="table table-bordered display nowrap tabla-responsiva" id="tablaUsuarios" style="width:100%">
        <thead class="table-dark">
          <tr>
            <th></th>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Estatus</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td></td>
              <td><?= htmlspecialchars($u['NombreUsuario']) ?></td>
              <td><?= htmlspecialchars($u['Usuario']) ?></td>
              <td><?= htmlspecialchars(ucwords($u['Roles'])) ?></td>
              <td>
                <span class="badge <?= $u['Estatus'] === 'Activo' ? 'bg-success' : 'bg-danger' ?>">
                  <?= htmlspecialchars($u['Estatus']) ?>
                </span>
              </td>
              <td>
                <button class="btn btn-sm btn-outline-success editarUsuario" data-id="<?= $u['idUsuario'] ?>">
                  <i class="fas fa-pen"></i>
                </button>

                 <?php if ($u['Estatus'] === 'Activo'): ?>
                    <button class="btn btn-sm btn-outline-danger cambiarEstadoUsuario" data-id="<?= $u['idUsuario'] ?>" data-accion="baja">
                      <i class="fas fa-trash"></i>
                    </button>
                  <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary cambiarEstadoUsuario" data-id="<?= $u['idUsuario'] ?>" data-accion="reactivar">
                      <i class="fas fa-undo"></i>
                    </button>
                  <?php endif; ?>

              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Modal para agregar/editar usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="tituloModal">Agregar Usuario</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formUsuario">
          <input type="hidden" name="idUsuario" id="idUsuario">
          <div class="mb-3">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="NombreUsuario" id="NombreUsuario" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="Usuario" id="Usuario" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="Contraseña" id="Contraseña" class="form-control" placeholder="••••••••">
          </div>

				<div class="mb-3">
				  <label class="form-label fw-semibold">Roles</label>
				  <div class="row">
				    <?php foreach ($roles as $r): ?>
				      <div class="col-6 col-md-4">
				        <div class="form-check">
				          <input class="form-check-input" type="checkbox" 
				                 name="Rol[]" 
				                 id="rol_<?= htmlspecialchars($r['rol']) ?>" 
				                 value="<?= htmlspecialchars($r['rol']) ?>">
				          <label class="form-check-label" for="rol_<?= htmlspecialchars($r['rol']) ?>">
				            <?= ucfirst($r['rol']) ?>
				          </label>
				        </div>
				      </div>
				    <?php endforeach; ?>
				  </div>
				</div>


          <div class="text-center">
            <button type="submit" class="btn btn-primary px-4 fw-bold">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="../funciones/usuarios.js"></script>
<?php include 'includes/footer.php'; ?>
