<?php
// api.php - Backend para gerenciar estações e rotas (CORRIGIDO)

// DESATIVAR TODOS OS ERROS E WARNINGS ANTES DO HEADER
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurações do banco de dados
$host = "localhost:3307";
$usuario = "root";
$senha = "";
$banco = "tsf"; // Usando o banco tsf

// Criar conexão MySQLi
$mysqli = new mysqli($host, $usuario, $senha, $banco);

// Verificar conexão
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $mysqli->connect_error]);
    exit;
}

// Inicializar cache
$cache_estacoes = null;
$cache_routes = null;

$notificationHelperPath = __DIR__ . '/../connections/notification_helper.php';
if (file_exists($notificationHelperPath)) {
    require_once($notificationHelperPath);
} else {
    // Se não existir, criar funções dummy
    function notificarCriacaoRota($nomeRota, $estacoesCount, $distancia) { 
        error_log("Notificação: Rota {$nomeRota} criada com {$estacoesCount} estações");
        return true; 
    }
    function notificarExclusaoRota($nomeRota) { 
        error_log("Notificação: Rota {$nomeRota} excluída");
        return true; 
    }
    function notificarCriacaoEstacao($nomeEstacao) { 
        error_log("Notificação: Estação {$nomeEstacao} criada");
        return true; 
    }
    function notificarExclusaoEstacao($nomeEstacao) { 
        error_log("Notificação: Estação {$nomeEstacao} excluída");
        return true; 
    }
    function notificarEdicaoEstacao($nomeEstacao) { 
        error_log("Notificação: Estação {$nomeEstacao} editada");
        return true; 
    }
    function notificarMovimentoEstacao($nomeEstacao) { 
        error_log("Notificação: Estação {$nomeEstacao} movida");
        return true; 
    }
}
$action = $_GET['action'] ?? '';

// Para requisições POST, obter os dados do corpo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $input = $_POST;
    }
} else {
    $input = $_GET;
}

// Função para invalidar cache
function invalidateCache() {
    global $cache_estacoes, $cache_routes;
    $cache_estacoes = null;
    $cache_routes = null;
}

switch ($action) {
    case 'get_stations':
        getStations($mysqli);
        break;
        
    case 'get_routes':
        getRoutes($mysqli);
        break;
        
    case 'save_station':
        saveStation($mysqli, $input);
        break;
        
    case 'delete_station':
        deleteStation($mysqli, $input);
        break;
        
    case 'save_route':
        saveRoute($mysqli, $input);
        break;
        
    case 'delete_route':
        deleteRoute($mysqli, $input);
        break;
        
    case 'update_station_position':
        updateStationPosition($mysqli, $input);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
        break;
}
function getStations($mysqli) {
    global $cache_estacoes;

    if ($cache_estacoes !== null) {
        echo json_encode($cache_estacoes);
        return;
    }

    try {
        $result = $mysqli->query("SELECT id, nome, latitude, longitude, endereco FROM estacoes ORDER BY nome");
        if ($result) {
            $stations = [];
            while ($row = $result->fetch_assoc()) {
                // Converter tipos
                $row['id'] = (int)$row['id'];
                $row['latitude'] = (float)$row['latitude'];
                $row['longitude'] = (float)$row['longitude'];
                $stations[] = $row;
            }
            $cache_estacoes = $stations;
            echo json_encode($stations);
        } else {
            throw new Exception($mysqli->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao obter estações: ' . $e->getMessage()]);
    }
}

function getRoutes($mysqli) {
    global $cache_routes;

    if ($cache_routes !== null) {
        echo json_encode($cache_routes);
        return;
    }

    try {
        // Consulta simplificada - primeiro pegar as rotas
        $result = $mysqli->query("SELECT id, nome, distancia_km, tempo_estimado_min FROM rotas ORDER BY nome");
        if (!$result) {
            throw new Exception($mysqli->error);
        }

        $routes = [];
        while ($row = $result->fetch_assoc()) {
            $route = [
                'id' => (int)$row['id'],
                'nome' => $row['nome'],
                'distancia_km' => (float)$row['distancia_km'],
                'tempo_estimado_min' => (int)$row['tempo_estimado_min'],
                'estacoes' => []
            ];
            
            // Buscar estações desta rota
            $stmt = $mysqli->prepare("
                SELECT e.id, e.nome, e.latitude, e.longitude, e.endereco 
                FROM estacoes e 
                JOIN rota_estacoes re ON e.id = re.id_estacao 
                WHERE re.id_rota = ? 
                ORDER BY re.ordem
            ");
            $stmt->bind_param("i", $route['id']);
            $stmt->execute();
            $resultEstacoes = $stmt->get_result();
            
            while ($estacao = $resultEstacoes->fetch_assoc()) {
                $estacao['id'] = (int)$estacao['id'];
                $estacao['latitude'] = (float)$estacao['latitude'];
                $estacao['longitude'] = (float)$estacao['longitude'];
                $route['estacoes'][] = $estacao;
            }
            $stmt->close();
            
            $routes[] = $route;
        }

        $cache_routes = $routes;
        echo json_encode($routes);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao obter rotas: ' . $e->getMessage()]);
    }
}

function saveStation($mysqli, $input) {
    try {
        $id = $input['id'] ?? null;
        $nome = $input['nome'] ?? '';
        $endereco = $input['endereco'] ?? '';
        $latitude = $input['latitude'] ?? 0;
        $longitude = $input['longitude'] ?? 0;
        
        if (empty($nome) || empty($latitude) || empty($longitude)) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
            return;
        }
        
        if ($id) {
            // Atualizar estação existente
            $stmt = $mysqli->prepare("
                UPDATE estacoes 
                SET nome = ?, endereco = ?, latitude = ?, longitude = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("ssddi", $nome, $endereco, $latitude, $longitude, $id);
        } else {
            // Inserir nova estação
            $stmt = $mysqli->prepare("
                INSERT INTO estacoes (nome, endereco, latitude, longitude) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssdd", $nome, $endereco, $latitude, $longitude);
        }
        
        if ($stmt->execute()) {
            $newId = $id ?: $mysqli->insert_id;
            invalidateCache(); // Invalidar cache após mudança
            
            // Tentar notificar (se as funções existirem)
            if (function_exists('notificarEdicaoEstacao') && $id) {
                notificarEdicaoEstacao($nome);
            } elseif (function_exists('notificarCriacaoEstacao') && !$id) {
                notificarCriacaoEstacao($nome);
            }
            
            echo json_encode(['success' => true, 'id' => $newId]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar estação: ' . $e->getMessage()]);
    }
}

function deleteStation($mysqli, $input) {
    try {
        $id = $input['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
            return;
        }
        
        // Primeiro, obter o nome da estação
        $stmt = $mysqli->prepare("SELECT nome FROM estacoes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Estação não encontrada']);
            $stmt->close();
            return;
        }
        
        $estacao = $result->fetch_assoc();
        $nomeEstacao = $estacao['nome'];
        $stmt->close();
        
        // Verificar se a estação está sendo usada em alguma rota
        $stmt = $mysqli->prepare("
            SELECT COUNT(*) as count 
            FROM rota_estacoes 
            WHERE id_estacao = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Não é possível excluir a estação pois ela está sendo usada em uma ou mais rotas']);
            return;
        }
        
        // Excluir estação
        $stmt = $mysqli->prepare("DELETE FROM estacoes WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            invalidateCache(); // Invalidar cache após mudança
            
            // Tentar notificar
            if (function_exists('notificarExclusaoEstacao')) {
                notificarExclusaoEstacao($nomeEstacao);
            }
            
            echo json_encode(['success' => true]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir estação: ' . $e->getMessage()]);
    }
}

function saveRoute($mysqli, $input) {
    try {
        $nome = $input['nome'] ?? '';
        $estacoes_json = $input['estacoes'] ?? '[]';
        
        // Decodificar o JSON das estações
        $estacoes_ids = json_decode($estacoes_json, true);
        
        if (empty($nome) || !is_array($estacoes_ids) || count($estacoes_ids) < 2) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos: nome e pelo menos duas estações são obrigatórios']);
            return;
        }
        
        // Calcular distância total
        $distancia_total = 0;
        $stmt = $mysqli->prepare("SELECT latitude, longitude FROM estacoes WHERE id = ?");
        
        for ($i = 0; $i < count($estacoes_ids) - 1; $i++) {
            $stmt->bind_param("i", $estacoes_ids[$i]);
            $stmt->execute();
            $estacao1 = $stmt->get_result()->fetch_assoc();
            
            $stmt->bind_param("i", $estacoes_ids[$i + 1]);
            $stmt->execute();
            $estacao2 = $stmt->get_result()->fetch_assoc();
            
            if ($estacao1 && $estacao2) {
                // Fórmula de Haversine para calcular distância
                $lat1 = deg2rad((float)$estacao1['latitude']);
                $lon1 = deg2rad((float)$estacao1['longitude']);
                $lat2 = deg2rad((float)$estacao2['latitude']);
                $lon2 = deg2rad((float)$estacao2['longitude']);
                
                $dlat = $lat2 - $lat1;
                $dlon = $lon2 - $lon1;
                
                $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
                $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                $distancia = 6371 * $c; // Raio da Terra em km
                
                $distancia_total += $distancia;
            }
        }
        $stmt->close();
        
        // Calcular tempo estimado (60 km/h em média)
        $tempo_estimado = round(($distancia_total / 60) * 60);
        
        $mysqli->begin_transaction();
        
        // Inserir rota
        $stmt = $mysqli->prepare("INSERT INTO rotas (nome, distancia_km, tempo_estimado_min) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $nome, round($distancia_total, 2), $tempo_estimado);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id_rota = $mysqli->insert_id;
        $stmt->close();
        
        // Inserir estações da rota
        $stmt = $mysqli->prepare("INSERT INTO rota_estacoes (id_rota, id_estacao, ordem) VALUES (?, ?, ?)");
        
        foreach ($estacoes_ids as $index => $id_estacao) {
            $stmt->bind_param("iii", $id_rota, $id_estacao, $index);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
        }
        
        $stmt->close();
        $mysqli->commit();
        
        invalidateCache(); // Invalidar cache após mudança
        
        // NOTIFICAR CRIAÇÃO DA ROTA - AGORA COM USUÁRIO LOGADO
        notificarCriacaoRota($nome, count($estacoes_ids), round($distancia_total, 2));
        
        echo json_encode(['success' => true, 'id' => $id_rota, 'distancia' => round($distancia_total, 2)]);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar rota: ' . $e->getMessage()]);
    }
}
function deleteRoute($mysqli, $input) {
    try {
        $id = $input['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
            return;
        }
        
        // Primeiro, obter o nome da rota
        $stmt = $mysqli->prepare("SELECT nome FROM rotas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Rota não encontrada']);
            $stmt->close();
            return;
        }
        
        $rota = $result->fetch_assoc();
        $nomeRota = $rota['nome'];
        $stmt->close();
        
        // Excluir rota (as rota_estacoes serão excluídas em cascade)
        $stmt = $mysqli->prepare("DELETE FROM rotas WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            invalidateCache(); // Invalidar cache após mudança
            
            // Tentar notificar
            if (function_exists('notificarExclusaoRota')) {
                notificarExclusaoRota($nomeRota);
            }
            
            echo json_encode(['success' => true]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir rota: ' . $e->getMessage()]);
    }
}

function updateStationPosition($mysqli, $input) {
    try {
        $id = $input['id'] ?? null;
        $latitude = $input['latitude'] ?? 0;
        $longitude = $input['longitude'] ?? 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
            return;
        }
        
        // Obter nome da estação
        $stmt = $mysqli->prepare("SELECT nome FROM estacoes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Estação não encontrada']);
            $stmt->close();
            return;
        }
        
        $estacao = $result->fetch_assoc();
        $nomeEstacao = $estacao['nome'];
        $stmt->close();
        
        $stmt = $mysqli->prepare("UPDATE estacoes SET latitude = ?, longitude = ? WHERE id = ?");
        $stmt->bind_param("ddi", $latitude, $longitude, $id);
        
        if ($stmt->execute()) {
            invalidateCache(); // Invalidar cache após mudança
            
            // Tentar notificar
            if (function_exists('notificarMovimentoEstacao')) {
                notificarMovimentoEstacao($nomeEstacao);
            }
            
            echo json_encode(['success' => true]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar posição: ' . $e->getMessage()]);
    }
}

// Fechar conexão
$mysqli->close();
?>