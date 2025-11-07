<?php
require_once('db.php');
session_start();
session_unset();
session_destroy();
// Limpar o cookie de sessão para garantir que seja expirado
setcookie(session_name(), '', time() - 3600, '/GabrielaPimentel/SyncRail-Main/');
header("Location: ../index.php");
?>
