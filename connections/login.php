<?php
require "db.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // Busca o usuário pelo email
    $stmt = $conn->prepare("SELECT pk_user, user_name, user_password FROM usuario WHERE user_mail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $dados = $resultado->fetch_assoc();

        // Verifica se a senha está correta usando password_verify
        if (password_verify($password, $dados["user_password"])) {
            $_SESSION["user_name"] = $dados["user_name"];
            $_SESSION["user_id"] = $dados["pk_user"];
            $_SESSION["conected"] = true;
            header("Location: ../pages/dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "E-mail ou senha inválidos.";
        }
    } else {
        $_SESSION['error'] = "E-mail ou senha inválidos.";
    }
}
