<?php
session_start();
require_once('../connections/db.php');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT user_adm FROM usuario WHERE pk_user = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$admin = $resultado->fetch_assoc();
$_SESSION['admin'] = $admin['user_adm'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Rotas Ferroviárias - Brasil</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="../css/map.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="navbar">
        <a class="navbar-brand" href="#">SyncRail</a>
        <div class="" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li>
                    <a class="nav-link" href="dashboard.php">Status</a>
                </li>
                <li>
                    <a class="nav-link" href="map.php">Mapa</a>
                </li>
                <li>
                    <a class="nav-link" href="repair.php">Manutenção</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li>
                    <a href="../connections/exit.php">
                        <input type="button" value="Sair" event="../connections/exit.php" class="buttonexitmenu" />
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <div id="adminonly">
                <div class="control-group " id="">
                    <button id="btn-add-station" class="btn"><i class="fas fa-plus-circle"></i> Estação</button>
                    <button id="btn-start-route" class="btn btn-warning"><i class="fas fa-route"></i> Nova Rota</button>
                </div>

                <div class="control-group " id="">
                    <button id="btn-edit-mode" class="btn"><i class="fas fa-edit"></i> Editar</button>
                    <button id="btn-save" class="btn btn-success"><i class="fas fa-save"></i> Salvar</button>
                </div>
            </div>


            <div class="station-list">
                <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Estações</h3>
                <div id="stations-container"></div>
            </div>

            <div class="route-list">
                <h3 class="section-title"><i class="fas fa-train"></i> Rotas</h3>
                <div id="routes-container"></div>
            </div>

        </div>

        <div class="main-content">
            <div class="toolbar">
                <h3>Mapa Ferroviário do Brasil</h3>
                <div>
                    <span id="mode-indicator" class="mode-indicator">Modo Visualização</span>
                </div>
            </div>
            <div id="map"></div>
            <div class="status-bar">
                <span id="status-message">Sistema de gerenciamento de rotas ferroviárias</span>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar/editar estação -->
    <div id="station-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title"><i class="fas fa-train-station"></i> Adicionar Estação</h3>
                <span class="close">&times;</span>
            </div>
            <form id="station-form">
                <input type="hidden" id="station-id">
                <div class="form-group">
                    <label for="station-name">Nome da Estação:</label>
                    <input type="text" id="station-name" required>
                </div>
                <div class="form-group">
                    <label for="station-address">Endereço:</label>
                    <input type="text" id="station-address">
                </div>
                <div class="form-group">
                    <label for="station-lat">Latitude:</label>
                    <input type="text" id="station-lat" required>
                </div>
                <div class="form-group">
                    <label for="station-lng">Longitude:</label>
                    <input type="text" id="station-lng" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Estação</button>
                    <button type="button" class="btn btn-danger" id="btn-delete-station"><i class="fas fa-trash"></i> Excluir Estação</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Painel de criação de rotas -->
    <div id="route-creator" class="route-creator">
        <h3><i class="fas fa-route"></i> Criando Nova Rota</h3>
        <p>Clique nas estações para adicioná-las à rota</p>
        <div class="form-group">
            <label for="route-name">Nome da Rota:</label>
            <input type="text" id="route-name" placeholder="Ex: Rota Sul-Norte">
        </div>
        <div class="control-group">
            <button id="btn-finish-route" class="btn btn-success"><i class="fas fa-check"></i> Finalizar Rota</button>
            <button id="btn-cancel-route" class="btn btn-danger"><i class="fas fa-times"></i> Cancelar</button>
        </div>
        <div id="route-stations-list" style="margin-top: 15px; max-height: 150px; overflow-y: auto;"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        if (<?php echo $_SESSION['admin'] ?> === 0) {
            document.getElementById("adminonly").style.display = "none";
        }


        // Variáveis globais
        let map;
        let stations = [];
        let routes = [];
        let stationMarkers = [];
        let routeLines = [];
        let editMode = false;
        let selectedStation = null;
        let tempMarker = null;
        let creatingRoute = false;
        let currentRoute = [];
        let currentRouteLine = null;

        // Inicialização do mapa
        function initMap() {
            // Coordenadas do centro do Brasil
            const centerLat = -14.2350;
            const centerLng = -51.9253;

            // Criar o mapa
            map = L.map('map').setView([centerLat, centerLng], 5);

            // Adicionar camada do mapa
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Carregar dados do servidor
            loadStations();
            loadRoutes();

            // Evento de clique no mapa
            map.on('click', function(e) {
                if (editMode && !tempMarker && !creatingRoute) {
                    createTempStation(e.latlng);
                }
            });
        }

        // Carregar estações do servidor
        function loadStations() {
            fetch('api.php?action=get_stations')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    stations = data;
                    renderStations();
                    updateStatus("Estações carregadas com sucesso");
                })
                .catch(error => {
                    console.error('Erro ao carregar estações:', error);
                    updateStatus("Erro ao carregar estações");
                });
        }

        // Carregar rotas do servidor
        function loadRoutes() {
            fetch('api.php?action=get_routes')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    routes = data;
                    renderRoutes();
                    updateStatus("Rotas carregadas com sucesso");
                })
                .catch(error => {
                    console.error('Erro ao carregar rotas:', error);
                    updateStatus("Erro ao carregar rotas");
                });
        }

        // Criar estação temporária no mapa
        function createTempStation(latlng) {
            tempMarker = L.marker(latlng, {
                draggable: true,
                icon: L.divIcon({
                    className: 'temp-marker',
                    html: '<div style="background-color: #3498db; width: 18px; height: 18px; border-radius: 50%; border: 3px solid white;"></div>',
                    iconSize: [24, 24]
                })
            }).addTo(map);

            // Preencher coordenadas no formulário
            document.getElementById('station-lat').value = latlng.lat.toFixed(6);
            document.getElementById('station-lng').value = latlng.lng.toFixed(6);

            // Abrir modal para adicionar estação
            openStationModal();
        }

        // Renderizar estações no mapa e na lista
        function renderStations() {
            // Limpar marcadores existentes
            stationMarkers.forEach(marker => map.removeLayer(marker));
            stationMarkers = [];

            // Limpar lista de estações
            const container = document.getElementById('stations-container');
            container.innerHTML = '';

            // Adicionar cada estação
            stations.forEach(station => {
                // Criar marcador no mapa
                const marker = L.marker([station.latitude, station.longitude], {
                    draggable: editMode,
                    icon: L.divIcon({
                        className: 'station-marker',
                        html: '<div style="background-color: #e74c3c; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white;"></div>',
                        iconSize: [26, 26]
                    })
                }).addTo(map);

                // Adicionar popup com informações
                marker.bindPopup(`
                    <div>
                        <h3>${station.nome}</h3>
                        <p>${station.endereco || 'Sem endereço'}</p>
                        <button onclick="editStation(${station.id})" class="btn" style="margin-top: 10px;">Editar</button>
                    </div>
                `);

                // Evento de arrastar (apenas no modo edição)
                if (editMode) {
                    marker.on('dragend', function(e) {
                        const newLat = e.target.getLatLng().lat;
                        const newLng = e.target.getLatLng().lng;
                        updateStationPosition(station.id, newLat, newLng);
                    });
                }

                // Evento de clique
                marker.on('click', function() {
                    if (creatingRoute) {
                        addStationToRoute(station);
                    } else if (editMode) {
                        selectStation(station.id);
                    } else {
                        map.setView([station.latitude, station.longitude], 10);
                        marker.openPopup();
                    }
                });

                stationMarkers.push(marker);

                // Adicionar à lista lateral
                const stationItem = document.createElement('div');
                stationItem.className = 'station-item';
                stationItem.innerHTML = `
                    <strong>${station.nome}</strong>
                    <div style="font-size: 12px; margin-top: 5px;">${station.endereco || ''}</div>
                `;
                stationItem.dataset.id = station.id;

                stationItem.addEventListener('click', function() {
                    if (creatingRoute) {
                        addStationToRoute(station);
                    } else if (editMode) {
                        selectStation(station.id);
                    } else {
                        map.setView([station.latitude, station.longitude], 10);
                        marker.openPopup();
                    }
                });

                container.appendChild(stationItem);
            });
        }

        // Renderizar rotas no mapa
        // Função renderRoutes - CORRIGIDA
        function renderRoutes() {
            // Limpar rotas existentes
            routeLines.forEach(line => {
                if (line && map.hasLayer(line)) {
                    map.removeLayer(line);
                }
            });
            routeLines = [];

            // Limpar lista de rotas
            const container = document.getElementById('routes-container');
            container.innerHTML = '';

            // Adicionar cada rota
            routes.forEach(route => {
                // Verificar se a rota tem estações
                if (!route.estacoes || route.estacoes.length === 0) {
                    console.warn(`Rota "${route.nome}" não tem estações`);
                    return;
                }

                // Obter coordenadas das estações da rota
                const coordinates = route.estacoes.map(estacao => {
                    // Garantir que as coordenadas são números
                    const lat = parseFloat(estacao.latitude);
                    const lng = parseFloat(estacao.longitude);
                    return [lat, lng];
                });

                if (coordinates.length > 1) {
                    try {
                        // Criar linha principal da rota
                        const line = L.polyline(coordinates, {
                            color: '#e74c3c',
                            weight: 6,
                            opacity: 0.8,
                            lineCap: 'round'
                        }).addTo(map);

                        // Linha de sombra para efeito de trilho
                        const shadowLine = L.polyline(coordinates, {
                            color: '#2c3e50',
                            weight: 8,
                            opacity: 0.3,
                            lineCap: 'round'
                        }).addTo(map);

                        // Adicionar popup com informações
                        const popupContent = `
                    <div style="min-width: 200px;">
                        <h3 style="margin: 0 0 10px 0; color: #2c3e50;">${route.nome}</h3>
                        <div style="font-size: 14px; margin-bottom: 5px;">
                            <strong>Distância:</strong> ${typeof route.distancia_km === 'number' ? route.distancia_km.toFixed(2) : route.distancia_km} km
                        </div>
                        <div style="font-size: 14px; margin-bottom: 5px;">
                            <strong>Tempo estimado:</strong> ${Math.floor(route.tempo_estimado_min / 60)}h ${route.tempo_estimado_min % 60}min
                        </div>
                        <div style="font-size: 14px; margin-bottom: 10px;">
                            <strong>Estações:</strong> ${route.estacoes.length}
                        </div>
                        <button onclick="deleteRoute(${route.id})" class="btn btn-danger" style="width: 100%; padding: 5px;">
                            <i class="fas fa-trash"></i> Excluir Rota
                        </button>
                    </div>
                `;

                        line.bindPopup(popupContent);
                        shadowLine.bindPopup(popupContent);

                        routeLines.push(line, shadowLine);

                        // Adicionar marcadores para as estações da rota (opcional)
                        route.estacoes.forEach((estacao, index) => {
                            const marker = L.marker([estacao.latitude, estacao.longitude], {
                                icon: L.divIcon({
                                    className: 'route-station-marker',
                                    html: `<div style="background-color: #27ae60; color: white; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">${index + 1}</div>`,
                                    iconSize: [24, 24]
                                })
                            }).addTo(map);

                            marker.bindPopup(`
                        <div>
                            <h4>${estacao.nome}</h4>
                            <p>Ordem na rota: ${index + 1}</p>
                            <p>${estacao.endereco || 'Sem endereço'}</p>
                        </div>
                    `);

                            routeLines.push(marker);
                        });

                    } catch (error) {
                        console.error(`Erro ao renderizar rota "${route.nome}":`, error);
                    }
                }

                // Adicionar à lista lateral
                const routeItem = document.createElement('div');
                routeItem.className = 'route-item';
                routeItem.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="flex: 1;">
                    <strong>${route.nome}</strong>
                    <div style="font-size: 12px; margin-top: 5px; color: #666;">
                        <div>Distância: ${typeof route.distancia_km === 'number' ? route.distancia_km.toFixed(2) : route.distancia_km} km</div>
                        <div>Tempo: ${Math.floor(route.tempo_estimado_min / 60)}h ${route.tempo_estimado_min % 60}min</div>
                        <div>${route.estacoes ? route.estacoes.length : 0} estações</div>
                    </div>
                </div>
                <button onclick="event.stopPropagation(); deleteRoute(${route.id})" class="btn-delete-small" title="Excluir rota" style="margin-left: 10px;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

                routeItem.addEventListener('click', function() {
                    if (coordinates.length > 0) {
                        map.fitBounds(coordinates, {
                            padding: [20, 20]
                        });
                    }
                });

                container.appendChild(routeItem);
            });
        }
        // Alternar modo de edição
        function toggleEditMode() {
            editMode = !editMode;

            const indicator = document.getElementById('mode-indicator');
            const btn = document.getElementById('btn-edit-mode');

            if (editMode) {
                indicator.textContent = 'Modo Edição';
                indicator.style.backgroundColor = '#e74c3c';
                btn.innerHTML = '<i class="fas fa-eye"></i> Visualizar';
                updateStatus("Modo edição ativado - Você pode mover estações");
            } else {
                indicator.textContent = 'Modo Visualização';
                indicator.style.backgroundColor = '#f39c12';
                btn.innerHTML = '<i class="fas fa-edit"></i> Editar';
                updateStatus("Modo visualização ativado");
            }

            renderStations();
        }

        // Iniciar criação de rota
        function startRouteCreation() {
            if (editMode) {
                alert('Saia do modo edição para criar uma rota');
                return;
            }

            creatingRoute = true;
            currentRoute = [];

            document.getElementById('route-creator').style.display = 'block';
            document.getElementById('route-name').value = `${routes.length + 1}`;
            updateRouteStationsList();
            updateStatus("Criando nova rota - Clique nas estações para adicioná-las à rota");
            map.getContainer().style.cursor = 'crosshair';
        }

        // Finalizar criação de rota
        function finishRouteCreation() {
            const routeName = document.getElementById('route-name').value.trim();

            if (!routeName) {
                alert('Por favor, informe um nome para a rota');
                document.getElementById('route-name').focus();
                return;
            }

            if (currentRoute.length < 2) {
                alert('Uma rota precisa ter pelo menos duas estações');
                return;
            }

            // Verificar se já existe rota com mesmo nome
            if (routes.some(route => route.nome.toLowerCase() === routeName.toLowerCase())) {
                if (!confirm(`Já existe uma rota com o nome "${routeName}". Deseja continuar mesmo assim?`)) {
                    return;
                }
            }

            // Preparar dados para envio
            const data = {
                nome: routeName,
                estacoes: JSON.stringify(currentRoute.map(station => station.id))
            };

            // Mostrar loading
            updateStatus("Salvando rota...");

            // Enviar para o servidor
            fetch('api.php?action=save_route', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        cancelRouteCreation();
                        loadRoutes();
                        updateStatus(`Rota "${routeName}" criada com sucesso! Distância: ${data.distancia || 'N/A'} km`);
                    } else {
                        alert('Erro ao salvar rota: ' + data.message);
                        updateStatus("Erro ao salvar rota");
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao salvar rota: ' + error.message);
                    updateStatus("Erro de conexão ao salvar rota");
                });
        }

        // Cancelar criação de rota
        function cancelRouteCreation() {
            creatingRoute = false;
            currentRoute = [];

            document.getElementById('route-creator').style.display = 'none';
            map.getContainer().style.cursor = '';

            if (currentRouteLine) {
                map.removeLayer(currentRouteLine);
                currentRouteLine = null;
            }

            updateStatus("Criação de rota cancelada");
        }

        // Adicionar estação à rota em criação
        function addStationToRoute(station) {
            if (currentRoute.some(s => s.id === station.id)) {
                updateStatus(`Estação "${station.nome}" já está na rota`);
                return;
            }

            currentRoute.push(station);
            updateRouteStationsList();
            updateTempRouteLine();
            updateStatus(`Estação "${station.nome}" adicionada à rota (${currentRoute.length} estações)`);

            // Destacar a estação adicionada
            const marker = stationMarkers.find(m => {
                const latLng = m.getLatLng();
                return latLng.lat === station.latitude && latLng.lng === station.longitude;
            });
            if (marker) {
                marker.openPopup();
            }
        }

        // Atualizar lista de estações na rota em criação
        function updateRouteStationsList() {
            const container = document.getElementById('route-stations-list');
            container.innerHTML = '';

            if (currentRoute.length === 0) {
                const emptyMsg = document.createElement('div');
                emptyMsg.style.padding = '10px';
                emptyMsg.style.textAlign = 'center';
                emptyMsg.style.color = '#666';
                emptyMsg.innerHTML = 'Nenhuma estação adicionada';
                container.appendChild(emptyMsg);
                return;
            }

            currentRoute.forEach((station, index) => {
                const stationItem = document.createElement('div');
                stationItem.style.padding = '8px';
                stationItem.style.borderBottom = '1px solid #eee';
                stationItem.style.display = 'flex';
                stationItem.style.justifyContent = 'space-between';
                stationItem.style.alignItems = 'center';

                stationItem.innerHTML = `
            <div>
                <strong>${index + 1}.</strong> ${station.nome}
            </div>
            <button onclick="removeStationFromRoute(${index})" class="btn-delete-small" title="Remover estação">
                <i class="fas fa-times"></i>
            </button>
        `;
                container.appendChild(stationItem);
            });
        }

        function removeStationFromRoute(index) {
            if (index >= 0 && index < currentRoute.length) {
                const station = currentRoute[index];
                currentRoute.splice(index, 1);
                updateRouteStationsList();
                updateTempRouteLine();
                updateStatus(`Estação "${station.nome}" removida da rota`);
            }
        }

        // Atualizar linha temporária da rota em criação
        function updateTempRouteLine() {
            if (currentRouteLine) {
                map.removeLayer(currentRouteLine);
                currentRouteLine = null;
            }

            if (currentRoute.length > 1) {
                const coordinates = currentRoute.map(station => [station.latitude, station.longitude]);

                currentRouteLine = L.polyline(coordinates, {
                    color: '#3498db',
                    weight: 4,
                    opacity: 0.7,
                    dashArray: '5, 5'
                }).addTo(map);

                // Ajustar visualização para mostrar toda a rota
                map.fitBounds(currentRouteLine.getBounds());
            }
        }


        // Selecionar estação
        function selectStation(stationId) {
            document.querySelectorAll('.station-item').forEach(item => {
                item.classList.remove('active');
            });

            selectedStation = stationId;

            if (stationId) {
                const stationItem = document.querySelector(`.station-item[data-id="${stationId}"]`);
                if (stationItem) {
                    stationItem.classList.add('active');
                }
            }
        }

        // Abrir modal de estação
        function openStationModal(stationId = null) {
            const modal = document.getElementById('station-modal');
            const title = document.getElementById('modal-title');
            const form = document.getElementById('station-form');
            const deleteBtn = document.getElementById('btn-delete-station');

            if (stationId) {
                title.innerHTML = '<i class="fas fa-train-station"></i> Editar Estação';
                const station = stations.find(s => s.id == stationId);

                if (station) {
                    document.getElementById('station-id').value = station.id;
                    document.getElementById('station-name').value = station.nome;
                    document.getElementById('station-address').value = station.endereco || '';
                    document.getElementById('station-lat').value = station.latitude;
                    document.getElementById('station-lng').value = station.longitude;
                }

                deleteBtn.style.display = 'inline-block';
            } else {
                title.innerHTML = '<i class="fas fa-train-station"></i> Adicionar Estação';
                form.reset();
                document.getElementById('station-id').value = '';
                deleteBtn.style.display = 'none';

                if (!document.getElementById('station-lat').value) {
                    const center = map.getCenter();
                    document.getElementById('station-lat').value = center.lat.toFixed(6);
                    document.getElementById('station-lng').value = center.lng.toFixed(6);
                }
            }

            modal.style.display = 'flex';
        }

        // Editar estação
        function editStation(stationId) {
            openStationModal(stationId);
        }

        // Fechar modais
        function closeModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });

            if (tempMarker) {
                map.removeLayer(tempMarker);
                tempMarker = null;
            }
        }

        // Salvar estação
        function saveStation(event) {
            event.preventDefault();

            const data = {
                id: document.getElementById('station-id').value,
                nome: document.getElementById('station-name').value,
                endereco: document.getElementById('station-address').value,
                latitude: document.getElementById('station-lat').value,
                longitude: document.getElementById('station-lng').value
            };

            fetch('api.php?action=save_station', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        closeModals();
                        loadStations();
                        updateStatus(`Estação "${document.getElementById('station-name').value}" salva com sucesso`);
                    } else {
                        alert('Erro ao salvar estação: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao salvar estação: ' + error.message);
                });
        }

        // Excluir estação
        function deleteStation() {
            const stationId = document.getElementById('station-id').value;

            if (!stationId || !confirm('Tem certeza que deseja excluir esta estação?')) {
                return;
            }

            const data = {
                id: stationId
            };

            fetch('api.php?action=delete_station', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        closeModals();
                        loadStations();
                        updateStatus("Estação excluída com sucesso");
                    } else {
                        alert('Erro ao excluir estação: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir estação: ' + error.message);
                });
        }

        // Excluir rota
        function deleteRoute(routeId) {
            console.log('Tentando excluir rota ID:', routeId, 'Tipo:', typeof routeId);

            // Garantir que routeId é um número
            routeId = parseInt(routeId);

            if (!routeId || isNaN(routeId)) {
                alert('ID da rota inválido');
                return;
            }

            // Encontrar a rota para mostrar o nome
            const route = routes.find(r => parseInt(r.id) === routeId);
            const routeName = route ? route.nome : `ID ${routeId}`;

            if (!confirm(`Tem certeza que deseja excluir a rota "${routeName}"?\nEsta ação não pode ser desfeita.`)) {
                return;
            }

            const data = {
                id: routeId
            };

            fetch('api.php?action=delete_route', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatus(`Rota "${routeName}" excluída com sucesso`);
                        // Recarregar as rotas
                        loadRoutes();
                    } else {
                        alert('Erro ao excluir rota: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir rota: ' + error.message);
                });
        }

        // Atualizar posição da estação
        function updateStationPosition(stationId, lat, lng) {
            const data = {
                id: stationId,
                latitude: lat,
                longitude: lng
            };

            fetch('api.php?action=update_station_position', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const station = stations.find(s => s.id == stationId);
                        if (station) {
                            updateStatus(`Posição da estação "${station.nome}" atualizada`);
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        }

        // Atualizar mensagem de status
        function updateStatus(message) {
            document.getElementById('status-message').textContent = message;
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar mapa
            initMap();

            // Botões
            document.getElementById('btn-add-station').addEventListener('click', function() {
                openStationModal();
            });

            document.getElementById('btn-start-route').addEventListener('click', startRouteCreation);
            document.getElementById('btn-finish-route').addEventListener('click', finishRouteCreation);
            document.getElementById('btn-cancel-route').addEventListener('click', cancelRouteCreation);

            document.getElementById('btn-edit-mode').addEventListener('click', toggleEditMode);

            document.getElementById('btn-save').addEventListener('click', function() {
                // Recarregar dados do servidor
                loadStations();
                loadRoutes();
                updateStatus("Dados atualizados do servidor");
            });

            // Fechar modais
            document.querySelectorAll('.close').forEach(closeBtn => {
                closeBtn.addEventListener('click', closeModals);
            });

            // Formulários
            document.getElementById('station-form').addEventListener('submit', saveStation);
            document.getElementById('btn-delete-station').addEventListener('click', deleteStation);

            // Fechar modal ao clicar fora
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    closeModals();
                }
            });
        });
    </script>
</body>

</html>