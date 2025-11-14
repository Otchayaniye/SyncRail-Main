<?php
require_once('db.php');
session_start();
$_SESSION["user_name"] = "";
$_SESSION["user_id"] = "";
$_SESSION["conected"] = false;
session_unset();
session_destroy();

header("Location: ../index.php");
?>
