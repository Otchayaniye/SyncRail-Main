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
    // Buscar os dados do alerta
    $sql = "DELETE FROM alertas WHERE pk_alerta = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $alertaId);
    $stmt->execute();

    $sql = "SELECT pk_alerta FROM alertas WHERE pk_alerta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $alertaId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows === 0) {
        echo json_encode(['success' => true,]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Alerta não encontrado']);
    }
    if ($resultado) {
        $resultado->free();
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>