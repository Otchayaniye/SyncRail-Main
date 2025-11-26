<?php
// Arquivo: connections/notification_helper.php
session_start();
require_once('db.php');

function criarAlerta($titulo, $texto, $tipo = 'sistema')
{
    // Criar uma nova conexão para as notificações
    $host = "localhost";
    $usuario = "root";
    $senha = "root";
    $banco = "tsf";
    // $host = "localhost:3307";
    // $usuario = "root";
    // $senha = "";
    // $banco = "tsf";

    $conn_notificacao = new mysqli($host, $usuario, $senha, $banco);

    if ($conn_notificacao->connect_error) {
        error_log("Erro de conexão nas notificações: " . $conn_notificacao->connect_error);
        return false;
    }

    // Usar informações da sessão do usuário logado
    $user_id = $_SESSION["user_id"] ?? 0;
    $user_name = $_SESSION["user_name"] ?? 'Sistema';
    $user_mail = $_SESSION["user_mail"] ?? 'sistema@syncrail.com';

    // Se não tem user_id, tentar buscar o usuário da sessão atual
    if ($user_id == 0 && isset($_SESSION["conected"]) && $_SESSION["conected"] == true) {
        // Se estiver conectado mas não tem user_id na sessão, buscar do banco
        if (isset($_SESSION["user_mail"])) {
            $stmt = $conn_notificacao->prepare("SELECT pk_user, user_name FROM usuario WHERE user_mail = ?");
            $stmt->bind_param("s", $_SESSION["user_mail"]);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                $user_id = $user_data['pk_user'];
                $user_name = $user_data['user_name'];
            }
            $stmt->close();
        }
    }

    try {
        $stmt = $conn_notificacao->prepare("INSERT INTO alertas(alerta_titulo, alerta_texto, fk_user_id, fk_user_name, fk_user_mail, alerta_tipo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $titulo, $texto, $user_id, $user_name, $user_mail, $tipo);

        $result = $stmt->execute();
        $stmt->close();
        $conn_notificacao->close();

        return $result;
    } catch (Exception $e) {
        error_log("Erro ao criar alerta: " . $e->getMessage());
        $conn_notificacao->close();
        return false;
    }
}

// Funções específicas para cada tipo de ação
function notificarCriacaoRota($nomeRota, $estacoesCount, $distancia)
{
    $titulo = "Nova Rota Criada";
    $texto = "Rota '{$nomeRota}' foi criada, unindo {$estacoesCount} estações (" . number_format($distancia, 2) . " km)";
    $result = criarAlerta($titulo, $texto, 'rota');

    if (!$result) {
        error_log("FALHA ao notificar criação da rota: {$nomeRota}");
    }

    return $result;
}

function notificarExclusaoRota($nomeRota)
{
    $titulo = "Rota Excluída";
    $texto = "Rota '{$nomeRota}' foi excluída do sistema";
    $result = criarAlerta($titulo, $texto, 'rota');

    if (!$result) {
        error_log("FALHA ao notificar exclusão da rota: {$nomeRota}");
    }

    return $result;
}

function notificarCriacaoEstacao($nomeEstacao)
{
    $titulo = "Nova Estação Criada";
    $texto = "Estação '{$nomeEstacao}' adicionada ao sistema";
    $result = criarAlerta($titulo, $texto, 'estacao');

    if (!$result) {
        error_log("FALHA ao notificar criação da estação: {$nomeEstacao}");
    }

    return $result;
}

function notificarExclusaoEstacao($nomeEstacao)
{
    $titulo = "Estação Excluída";
    $texto = "Estação '{$nomeEstacao}' foi removida do sistema";
    $result = criarAlerta($titulo, $texto, 'estacao');

    if (!$result) {
        error_log("FALHA ao notificar exclusão da estação: {$nomeEstacao}");
    }

    return $result;
}

function notificarEdicaoEstacao($nomeEstacao)
{
    $titulo = "Estação Editada";
    $texto = "Estação '{$nomeEstacao}' foi atualizada";
    $result = criarAlerta($titulo, $texto, 'estacao');

    if (!$result) {
        error_log("FALHA ao notificar edição da estação: {$nomeEstacao}");
    }

    return $result;
}

function notificarMovimentoEstacao($nomeEstacao)
{
    $titulo = "Estação Movida";
    $texto = "Posição da estação '{$nomeEstacao}' foi ajustada no mapa";
    $result = criarAlerta($titulo, $texto, 'estacao');

    if (!$result) {
        error_log("FALHA ao notificar movimento da estação: {$nomeEstacao}");
    }

    return $result;
}

?>