<?php 
require "db.php";

session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $email = trim($_POST["email"] ?? "");
    if (preg_match('/^.{6,26}$/', (trim($_POST["password"] ?? "")))) {
        $password = password_hash(trim($_POST["password"] ?? ""), PASSWORD_BCRYPT);

        $checkEmailStmt = $conn->prepare("SELECT user_mail FROM usuario WHERE user_mail = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();

        if ($checkEmailStmt->num_rows > 0) {
            $error = "E-mail já cadastrado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO usuario(user_name, user_mail, user_password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);
            if ($stmt->execute()) {
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Erro ao cadastrar usuário.";
            }
        }
    } else {
        $error = "A senha deve conter entre 6 e 26 caracteres alfabéticos.";
    }
}
?>