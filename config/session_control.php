<?php
// Tiempo máximo de inactividad (1 hora = 3600 segundos)
$tiempoMaximo = 3600;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si existe la variable de última actividad
if (isset($_SESSION['ultima_actividad'])) {

    $tiempoInactivo = time() - $_SESSION['ultima_actividad'];

    if ($tiempoInactivo > $tiempoMaximo) {

        // Destruir sesión
        session_unset();
        session_destroy();

        header("Location: ../index.php?sesion=expirada");
        exit;
    }
}

// Actualizar tiempo de última actividad
$_SESSION['ultima_actividad'] = time();