<?php
session_start();
require_once('db.php');

header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar se o alertaId foi enviado
if (!isset($_POST['alertaId']) || empty($_POST['alertaId'])) {
    echo json_encode(['success' => false, 'message' => 'ID do alerta não fornecido']);
    exit;
}

$alertaId = intval($_POST['alertaId']);

try {
    // Verificar se o alerta existe antes de excluir
    $checkSql = "SELECT pk_alerta FROM alertas WHERE pk_alerta = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $alertaId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Alerta não encontrado']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();
    
    // Excluir o alerta
    $deleteSql = "DELETE FROM alertas WHERE pk_alerta = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $alertaId);
    
    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Alerta excluído com sucesso']);
    } else {
        throw new Exception("Erro ao excluir alerta: " . $deleteStmt->error);
    }
    
    $deleteStmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>