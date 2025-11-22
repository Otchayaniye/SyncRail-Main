<?php
session_start();
require_once('db.php');

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    // Verificar se é uma requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método não permitido']);
        exit;
    }

    $stmt = $conn->prepare("SELECT user_name, user_mail FROM usuario WHERE pk_user = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $alerta_titulo = $_POST["alerta_titulo"] ?? '';
        $descr = $_POST["descr"] ?? '';
        
        // Validar dados
        if (empty($alerta_titulo) || empty($descr)) {
            echo json_encode(['success' => false, 'message' => 'Título e descrição são obrigatórios']);
            exit;
        }
        
        $row = $resultado->fetch_assoc();
        
        // Inserir alerta
        $sql = "INSERT INTO alertas(alerta_titulo, alerta_texto, fk_user_id, fk_user_name, fk_user_mail) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiss", $alerta_titulo, $descr, $_SESSION['user_id'], $row['user_name'], $row['user_mail']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Alerta criado com sucesso!',
                'alerta' => [
                    'titulo' => $alerta_titulo,
                    'texto' => $descr,
                    'usuario' => $row['user_name'],
                    'data' => date('d-m-Y H:i')
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar alerta no banco de dados']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

$conn->close();
?>