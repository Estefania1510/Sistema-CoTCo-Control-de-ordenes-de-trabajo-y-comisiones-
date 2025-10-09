<?php
session_start();
session_destroy(); // elimina toda la sesión
header("Location: ../views/login.php"); 
exit;
