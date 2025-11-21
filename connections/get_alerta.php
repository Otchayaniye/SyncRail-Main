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
    $sql = "SELECT pk_alerta, fk_user_id, fk_user_name, fk_user_mail, alerta_texto, 
            DATE_FORMAT(alerta_data, '%d-%m-%Y %H:%i') as alerta_data, alerta_titulo 
            FROM alertas WHERE pk_alerta = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $alertaId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows >= 1) {
        $alertacompleto = $resultado->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'alerta_titulo' => $alertacompleto['alerta_titulo'],
            'alerta_texto' => $alertacompleto['alerta_texto'],
            'fk_user_name' => $alertacompleto['fk_user_name'],
            'fk_user_mail' => $alertacompleto['fk_user_mail'],
            'alerta_data' => $alertacompleto['alerta_data']
        ]);
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