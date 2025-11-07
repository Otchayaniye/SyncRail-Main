<?php
require_once('db.php');

$stmt = $conn->prepare("SELECT user_name, user_mail FROM usuario WHERE pk_user = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $resultado = $stmt->get_result();
if ($resultado->num_rows === 1) {
    $alerta_titulo = $_POST["alerta_titulo"] ?? '';
    $descr = $_POST["descr"] ?? '';  
    $row = $resultado->fetch_assoc();
    $stmt = $conn->prepare("INSERT INTO alertas(alerta_titulo, alerta_texto, fk_user_id, fk_user_name, fk_user_mail) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $alerta_titulo, $descr, $_SESSION["user_id"], $row["user_name"], $row["user_mail"]);
}

if ($stmt->execute()) {
    header("Location: ../pages/status.php");
    exit;
} else {
    echo "Erro ao inserir: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>