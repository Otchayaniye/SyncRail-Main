<?php
session_start();
require_once('db.php');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $prioridade = $_POST['prioridade'];
    $user_id = $_SESSION['user_id'];
    
    // Validar dados
    if (empty($titulo) || empty($descricao)) {
        $_SESSION['error'] = "Todos os campos são obrigatórios!";
        header("Location: ../pages/repair.php");
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO chamados (titulo, descricao, prioridade, user_id, status) VALUES (?, ?, ?, ?, 'aberto')");
    $stmt->bind_param("sssi", $titulo, $descricao, $prioridade, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Chamado criado com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao criar chamado: " . $conn->error;
    }
    
    header("Location: ../pages/repair.php");
    exit;
}
?>