<?php
$host     = "localhost";
$usuario  = "root";
$senha    = "root";
$banco    = "tsf";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$stmt=$conn->prepare("ALTER TABLE usuario AUTO_INCREMENT = 1");
$stmt->execute();
$stmt=$conn->prepare("ALTER TABLE alertas AUTO_INCREMENT = 1");
$stmt->execute();

$conn->set_charset("utf8");

date_default_timezone_set("America/Sao_Paulo");