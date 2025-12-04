<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['idUsuario'])) {
  header("Location: login.php");
  exit;
}

$isAdmin = in_array('administrador', $_SESSION['roles'] ?? []);

if ($isAdmin) {
  require __DIR__ . '/comisionesadmin.php';
} else {
  require __DIR__ . '/comisionesusuario.php';
}
