<?php
session_start();
require_once('db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['sensor_value']) || !isset($data['sensor_type'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$sensor_value = floatval($data['sensor_value']);
$sensor_type = $conn->real_escape_string($data['sensor_type']);
$sensor_topic = isset($data['sensor_topic']) ? $conn->real_escape_string($data['sensor_topic']) : '';

try {
    // Inserir dados na tabela
    $stmt = $conn->prepare("INSERT INTO sensordata (sensor_value, sensor_type, sensor_topic) VALUES (?, ?, ?)");
    $stmt->bind_param("dss", $sensor_value, $sensor_type, $sensor_topic);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Dados salvos com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar dados: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

$conn->close();
?>