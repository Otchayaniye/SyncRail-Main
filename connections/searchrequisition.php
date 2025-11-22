<?php
session_start();
require_once('db.php');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $chamado_id = $_GET['id'];
    
    // Verificar se o usuário tem permissão para ver este chamado
    if ($_SESSION['admin'] == 1) {
        $stmt = $conn->prepare("SELECT * FROM chamados WHERE id = ?");
        $stmt->bind_param("i", $chamado_id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM chamados WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $chamado_id, $_SESSION['user_id']);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $chamado = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($chamado);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Chamado não encontrado ou você não tem permissão para acessá-lo']);
    }
}
?>