<?php
session_start();
require_once('db.php');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $chamado_id = $_GET['id'];
    
    // Verificar se o usuário é admin ou é o dono do chamado
    if ($_SESSION['admin'] == 1) {
        $stmt = $conn->prepare("DELETE FROM chamados WHERE id = ?");
        $stmt->bind_param("i", $chamado_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM chamados WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $chamado_id, $_SESSION['user_id']);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Chamado excluído com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao excluir chamado: " . $conn->error;
    }
    
    header("Location: ../pages/repair.php");
    exit;
}
?>