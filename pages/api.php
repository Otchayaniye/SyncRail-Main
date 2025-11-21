<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
session_start();
require_once('../connections/db.php');
require_once('../connections/notification_helper.php');
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $input = $_POST;
    }
} else {
    $input = $_GET;
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


// Função para obter estações
function getStations($mysqli)
{
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
                // Converter tipos uma vez só
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

// Função para obter rotas com suas estações
// Função para obter rotas com suas estações - CORRIGIDA
function getRoutes($mysqli)
{
    global $cache_routes;

    if ($cache_routes !== null) {
        echo json_encode($cache_routes);
        return;
    }

    try {
        // SINGLE QUERY com JOIN - muito mais eficiente
        $query = "
            SELECT 
                r.id as rota_id,
                r.nome as rota_nome,
                r.distancia_km,
                r.tempo_estimado_min,
                e.id as estacao_id,
                e.nome as estacao_nome,
                e.latitude,
                e.longitude,
                e.endereco,
                re.ordem
            FROM rotas r
            LEFT JOIN rota_estacoes re ON r.id = re.id_rota
            LEFT JOIN estacoes e ON re.id_estacao = e.id
            ORDER BY r.nome, re.ordem
        ";

        $result = $mysqli->query($query);
        if (!$result) {
            throw new Exception($mysqli->error);
        }

        $routes = [];
        $current_route_id = null;

        while ($row = $result->fetch_assoc()) {
            $route_id = (int)$row['rota_id'];

            // Nova rota
            if ($current_route_id !== $route_id) {
                if ($current_route_id !== null) {
                    $routes[] = $current_route;
                }

                $current_route = [
                    'id' => $route_id,
                    'nome' => $row['rota_nome'],
                    'distancia_km' => (float)$row['distancia_km'],
                    'tempo_estimado_min' => (int)$row['tempo_estimado_min'],
                    'estacoes' => []
                ];
                $current_route_id = $route_id;
            }

            // Adicionar estação se existir
            if ($row['estacao_id']) {
                $current_route['estacoes'][] = [
                    'id' => (int)$row['estacao_id'],
                    'nome' => $row['estacao_nome'],
                    'latitude' => (float)$row['latitude'],
                    'longitude' => (float)$row['longitude'],
                    'endereco' => $row['endereco']
                ];
            }
        }

        // Adicionar última rota
        if ($current_route_id !== null) {
            $routes[] = $current_route;
        }

        $cache_routes = $routes;
        echo json_encode($routes);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao obter rotas: ' . $e->getMessage()]);
    }
}

// Função para salvar estação
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
            
            if ($stmt->execute()) {
                // Notificar edição da estação
                notificarEdicaoEstacao($nome);
                $newId = $id;
                echo json_encode(['success' => true, 'id' => $newId]);
            } else {
                throw new Exception($stmt->error);
            }
        } else {
            // Inserir nova estação
            $stmt = $mysqli->prepare("
                INSERT INTO estacoes (nome, endereco, latitude, longitude) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssdd", $nome, $endereco, $latitude, $longitude);
            
            if ($stmt->execute()) {
                // Notificar criação da estação
                notificarCriacaoEstacao($nome);
                $newId = $mysqli->insert_id;
                echo json_encode(['success' => true, 'id' => $newId]);
            } else {
                throw new Exception($stmt->error);
            }
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar estação: ' . $e->getMessage()]);
    }
}

// Função para excluir estação
function deleteStation($mysqli, $input) {
    try {
        $id = $input['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
            return;
        }
        
        // Primeiro, obter o nome da estação para a notificação
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
            // Notificar exclusão da estação
            notificarExclusaoEstacao($nomeEstacao);
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
        
        // Calcular distância total (código existente mantido)
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
                $lat1 = deg2rad($estacao1['latitude']);
                $lon1 = deg2rad($estacao1['longitude']);
                $lat2 = deg2rad($estacao2['latitude']);
                $lon2 = deg2rad($estacao2['longitude']);
                
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
        
        // Notificar criação da rota
        notificarCriacaoRota($nome, count($estacoes_ids), round($distancia_total, 2));
        
        echo json_encode(['success' => true, 'id' => $id_rota]);
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
        
        // Primeiro, obter o nome da rota para a notificação
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
            // Notificar exclusão da rota
            notificarExclusaoRota($nomeRota);
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
        
        // Obter nome da estação para notificação
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
            // Notificar movimento da estação
            notificarMovimentoEstacao($nomeEstacao);
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
