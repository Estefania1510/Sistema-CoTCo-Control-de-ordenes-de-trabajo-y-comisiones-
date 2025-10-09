<?php
session_start();
if(!isset($_SESSION['idUsuario'])){
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['nombre'] ?? "Invitado";
$roles   = implode(", ", $_SESSION['roles'] ?? ["Sin rol"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>CoTCo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
  <link href="css/styles.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">

<!-- Navbar superior -->
<nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #004aad;">
  <a class="navbar-brand ps-3 d-flex align-items-center" href="principal.php" style="font-size: 1.8rem; font-weight: bold;">
    ICT
    <img src="../Image/monito.png" alt="Logo" style="height: 45px; width: auto; margin-left: 10px;">
    
  </a>
  <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Menú usuario -->
  <ul class="navbar-nav ms-auto me-3 me-lg-4">
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" data-bs-toggle="dropdown">
        <i class="fas fa-user fa-fw" style="color: #ffffff;" ></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
        <li><a class="dropdown-item" href="../controllers/logout.php">
          <i class="fas fa-right-from-bracket me-2"></i> Cerrar sesión
        </a></li>
      </ul>
    </li>
  </ul>
</nav>

<div id="layoutSidenav">
  

  <!-- Sidebar -->
  <div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" style="background-color: #004aad;">
      <div class="sb-sidenav-menu">
        <div class="nav">
          <div class="sb-sidenav-menu-heading" style="color: #ffffff; font-size: 1.2rem;">Menú</div>
          <a class="nav-link" href="ordenes.php">
            <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list" style="color: #ffffff;" ></i></div>
            Órdenes de Trabajo
          </a>
          <a class="nav-link" href="comisiones.php">
            <div class="sb-nav-link-icon"><i class="fas fa-dollar-sign" style="color: #ffffff;"></i></div>
            Comisiones
          </a>
          <a class="nav-link" href="clientes.php">
            <div class="sb-nav-link-icon"><i class="fas fa-users-rays" style="color: #ffffff;"></i></div>
            Clientes
          </a>
          <a class="nav-link" href="catalogomnt.php">
            <div class="sb-nav-link-icon"><i  class="fa-solid fa-file" style="color: #ffffff;"></i></div>
            Catalogo de Servicios
          </a> 
          <a class="nav-link" href="usuarios.php">
            <div class="sb-nav-link-icon"><i class="fas fa-users" style="color: #ffffff;"></i></div>
            Administración de Usuarios
          </a>                   
        </div>
      </div>
      <div class="sb-sidenav-footer" style="background-color: #2479df; color: #ffffff;">
        <div class="small">Conectado como:</div>
        <?= $usuario ?> (<?= $roles ?>)
      </div>
    </nav>
  </div>

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <div id="layoutSidenav_content">
    <main class="container-fluid px-4 mt-4">
