<?php
session_start();
require_once __DIR__ . '/../config/session_control.php';
session_destroy(); 
header("Location: ../views/login.php"); 
exit;
