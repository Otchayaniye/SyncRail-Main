<?php
require_once('db.php');

$_SESSION["user_name"] = "";
$_SESSION["user_id"] = "";
$_SESSION["conected"] = false;

header("Location: ../index.php");
