<?php
session_start();
require_once('db.php');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chamado_id = $_POST['chamado_id'];
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $prioridade = $_POST['prioridade'];
    
    // Validar dados
    if (empty($titulo) || empty($descricao)) {
        $_SESSION['error'] = "Todos os campos são obrigatórios!";
        header("Location: ../pages/repair.php");
        exit;
    }
    
    // Verificar se o usuário tem permissão para editar este chamado
    if ($_SESSION['admin'] != 1) {
        // Para usuários não-admin, verificar se o chamado pertence a eles
        $check_stmt = $conn->prepare("SELECT id FROM chamados WHERE id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $chamado_id, $_SESSION['user_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            $_SESSION['error'] = "Você não tem permissão para editar este chamado!";
            header("Location: ../pages/repair.php");
            exit;
        }
    }
    
    // Verificar se o usuário é admin para permitir alterar status
    if ($_SESSION['admin'] == 1) {
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE chamados SET titulo = ?, descricao = ?, prioridade = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $titulo, $descricao, $prioridade, $status, $chamado_id);
    } else {
        // Usuário comum só pode editar título, descrição e prioridade
        $stmt = $conn->prepare("UPDATE chamados SET titulo = ?, descricao = ?, prioridade = ? WHERE id = ?");
        $stmt->bind_param("sssi", $titulo, $descricao, $prioridade, $chamado_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Chamado atualizado com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao atualizar chamado: " . $conn->error;
    }
    
    header("Location: ../pages/repair.php");
    exit;
}
?>