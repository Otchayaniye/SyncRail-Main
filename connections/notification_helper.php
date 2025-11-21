<?php
// Arquivo: connections/notification_helper.php
session_start();
require_once('db.php');

function criarAlerta($titulo, $texto, $tipo = 'sistema') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO alertas(alerta_titulo, alerta_texto, alerta_tipo) VALUES (?, ?, ?)");
        $stmt->bind_param("ssisss", $titulo, $texto, $tipo);
        
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Erro ao criar alerta: " . $e->getMessage());
        return false;
    }
}

// Funções específicas para cada tipo de ação
function notificarCriacaoRota($nomeRota, $estacoesCount, $distancia) {
    $titulo = "Nova Rota Criada";
    $texto = "Rota '{$nomeRota}' criada com {$estacoesCount} estações ({$distancia} km)";
    return criarAlerta($titulo, $texto, 'rota');
}

function notificarExclusaoRota($nomeRota) {
    $titulo = "Rota Excluída";
    $texto = "Rota '{$nomeRota}' foi excluída do sistema";
    return criarAlerta($titulo, $texto, 'rota');
}

function notificarCriacaoEstacao($nomeEstacao) {
    $titulo = "Nova Estação Criada";
    $texto = "Estação '{$nomeEstacao}' adicionada ao sistema";
    return criarAlerta($titulo, $texto, 'estacao');
}

function notificarExclusaoEstacao($nomeEstacao) {
    $titulo = "Estação Excluída";
    $texto = "Estação '{$nomeEstacao}' foi removida do sistema";
    return criarAlerta($titulo, $texto, 'estacao');
}

function notificarEdicaoEstacao($nomeEstacao) {
    $titulo = "Estação Editada";
    $texto = "Estação '{$nomeEstacao}' foi atualizada";
    return criarAlerta($titulo, $texto, 'estacao');
}

function notificarMovimentoEstacao($nomeEstacao) {
    $titulo = "Estação Movida";
    $texto = "Posição da estação '{$nomeEstacao}' foi ajustada no mapa";
    return criarAlerta($titulo, $texto, 'estacao');
}
?>