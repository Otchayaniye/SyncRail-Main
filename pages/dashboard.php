<?php
session_start();

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    header("Location: ../index.php");
    exit;
}

require_once('../connections/db.php');
include('../lay/menu.php');

// Validar e sanitizar
$id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: ../index.php");
    exit;
}

$stmt = $conn->prepare("SELECT user_adm FROM usuario WHERE pk_user = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: ../index.php");
    exit;
}

$admin = $resultado->fetch_assoc();
$_SESSION['admin'] = (int) $admin['user_adm'];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/status.css">
    <link rel="stylesheet" href="../css/train.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Dashboard</title>
</head>

<body class="bg2 min-vh-100 d-flex flex-column justify-content-center">
    <div class="container d-flex justify-content-evenly align-items-stretch bgcont rounded p-0 gap-4">
        <div class="lbox d-flex flex-column gap-4">
            <div class="w-100 bg4 h-100 rounded p-2">
                <div class="bg6 w-100 h-100 rounded">
                    <div class="train-content">
                        <div class="train-scene rounded">
                            <div class="train-background"></div>
                            <div class="train-mountains"></div>

                            <!-- PRIMEIRO: Trilhos (fundo) -->
                            <div class="train-track">
                                <div class="train-rail"></div>
                                <div class="train-sleepers">
                                    <!-- Os dormentes serão adicionados via JavaScript -->
                                </div>
                            </div>

                            <!-- DEPOIS: Trem (sobre os trilhos) -->
                            <div class="train-container">
                                <div class="train">
                                    <div class="train-locomotive">
                                        <div class="train-chimney"></div>
                                        <div class="train-smoke"></div>
                                        <div class="train-smoke"></div>
                                        <div class="train-smoke"></div>
                                    </div>
                                    <div class="train-wagon"></div>
                                    <div class="train-wagon train-wagon-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="train-controls">
                            <button class="train-btn" id="goLeft">
                                <i class="bi bi-caret-left-fill train-btn-left"></i>
                            </button>
                            <button class="train-btn" id="stop">
                                <i class="bi bi-pause-fill train-btn-stop"></i>
                            </button>
                            <button class="train-btn" id="goRight">
                                <i class="bi bi-caret-right-fill train-btn-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="w-100 h-100 d-flex gap-4">
                <div class="w-100 bg3 h-100 rounded p-2 text-center">
                    <div class="bg6 w-100 h-100 rounded pe-4 ps-4">
                        <!-- <canvas id="temperaturaGauge"></canvas> -->
                        <h5>Umidade</h5>
                    </div>
                </div>
                <div class="w-100 bg1 h-100 rounded p-2 text-center">
                    <div class="bg6 w-100 h-100 rounded pe-4 ps-4">
                        <!-- <canvas id="temperaturaGauge"></canvas> -->
                        <h5>Temperatura</h5>
                    </div>
                </div>
            </div>
        </div>


        <div class="rbox d-flex flex-column align-items-center bg3 rounded">
            <div class=" d-flex w-100 justify-content-between mb-3" id="boxtituloalerta">
                <h4 class="alertat" id="tituloAlerta">Alertas</h4>

                <button class="btn p-0 iconplus ps-2 pe-1" onclick="abrircriaralerta()" id="adminonly"
                    data-is-admin="<?= htmlspecialchars($_SESSION['admin']) ?>">
                    <i class="bi bi-plus-circle"></i>
                </button>
                <div id="popcriaralerta" class="popup bg6 rounded">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="m-0 p-0">Novo Aviso</h3>
                        <button class="btn btf" onclick="fecharcriaralerta()"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <form id="formCriarAlerta" class="d-flex flex-column align-items-center gap-2">
                        <input type="text" id="titulo" name="alerta_titulo" placeholder="Título"
                            class="form-control fontc text-center" autocomplete="off" required>
                        <input type="text" id="descr" name="descr" placeholder="Descrição"
                            class="form-control fontc text-center" autocomplete="off" required>
                        <button type="submit" class="btn border bg3 w-50 mt-2 text-light">Enviar</button>
                    </form>

                    <div id="alertaMensagem" class="mt-2 text-center" style="display: none;"></div>

                    <?php if (!empty($error)): ?>
                        <div class="error w-100 text-center"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div id="criaralertaoverlay" class="overlay"></div>

            <div class="w-100 p-2 d-flex flex-column gap-2 alertacorpo scrolly h-100 bg2 rounded">
                <?php
                include("../connections/warndisplay.php");
                if (!empty($alerta)) {
                    foreach ($alerta as $linha) {
                        $tipoClasse = 'alerta-' . ($linha['alerta_tipo'] ?? 'sistema');
                        echo '<a class="linkveralerta p-2 rounded bg6 ' . $tipoClasse . '" data-alerta-id="' . htmlspecialchars($linha['pk_alerta']) . '">
                <div><strong>' . htmlspecialchars($linha['alerta_titulo']) . '</strong></div>
                <div>' . htmlspecialchars($linha['fk_user_name']) . '</div>
                <div class="w-100 text-end fs-6"><span><em>' . htmlspecialchars($linha['alerta_data_formatada']) . '</em></span></div>
              </a>';
                    }
                }
                ?>
            </div>

            <div class="popupveralerta rounded bg6 p-2" id="popmostraralerta">
                <div class="d-flex justify-content-between align-items-center mb-2 ">
                    <h3 class="m-0 p-2" id="alertaTitulo">Carregando...</h3>
                    <button class="btn btf p-2" onclick="fecharveralerta()"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="alertacorpo p-2" id="alertaContent">
                    <div class="scrolly textoalerta">
                        <p class="mb-1" id="alertaTexto">Carregando conteúdo do alerta...</p>
                    </div>
                    <div class="w-100 text-end">
                        <strong>Criado por:</strong>
                        <p class="mb-1" id="alertaUser">Carregando usuário...</p>
                        <p class="mb-1" id="alertaUserEmail">Carregando email...</p>
                    </div>
                    <div class="w-100 text-end fs-6">
                        <em id="alertaInfo">Carregando informações...</em>
                    </div>
                </div>
                <div class="w-100 d-flex p-2 justify-content-center">
                    <button class="btn btnexcluiralerta btn-outline-danger w-50">Excluir</button>
                </div>

            </div>
            <div id="mostraralertaoverlay" class="overlayveralerta"></div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
        <script src="../js/dashborad.js"></script>
        <script src="../js/train.js"></script>
        <script>
            const isAdmin = document.getElementById('adminonly').dataset.isAdmin === '1';
            document.getElementById("adminonly").style.display = isAdmin ? "block" : "none";

            $(document).on('click', '.linkveralerta', function (event) {
                event.preventDefault();
                var alertaID = $(this).data('alerta-id');
                const alertaIdint = parseInt(alertaID, 10);

                $.ajax({
                    url: '../connections/get_alerta.php',
                    type: 'POST',
                    data: {
                        alertaId: alertaIdint
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Preencher o popup com os dados do alerta
                            $('#alertaTitulo').text(response.alerta_titulo);
                            $('#alertaTexto').html(response.alerta_texto.replace(/\n/g, '<br>'));
                            $('#alertaUser').html(response.fk_user_name);
                            $('#alertaUserEmail').html(response.fk_user_mail);
                            $('#alertaInfo').html(response.alerta_data);
                            abrirveralerta();
                        } else {
                            alert('Erro ao carregar o alerta: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro AJAX:", error);
                        alert('Erro ao carregar o alerta. Tente novamente.');
                    }
                });
            });

            let currentAlertaId = null;

            $(document).on('click', '.linkveralerta', function (event) {
                event.preventDefault();
                var alertaID = $(this).data('alerta-id');
                currentAlertaId = parseInt(alertaID, 10);

                $.ajax({
                    url: '../connections/get_alerta.php',
                    type: 'POST',
                    data: {
                        alertaId: currentAlertaId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Preencher o popup com os dados do alerta
                            $('#alertaTitulo').text(response.alerta_titulo);
                            $('#alertaTexto').html(response.alerta_texto.replace(/\n/g, '<br>'));
                            $('#alertaUser').html(response.fk_user_name);
                            $('#alertaUserEmail').html(response.fk_user_mail);
                            $('#alertaInfo').html(response.alerta_data);

                            // Mostrar/ocultar botão de excluir baseado no admin
                            if (isAdmin) {
                                $('.btnexcluiralerta').show();
                            } else {
                                $('.btnexcluiralerta').hide();
                            }

                            abrirveralerta();
                        } else {
                            alert('Erro ao carregar o alerta: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro AJAX:", error);
                        alert('Erro ao carregar o alerta. Tente novamente.');
                    }
                });
            });

            $(document).on('click', '.btnexcluiralerta', function (event) {
                event.preventDefault();

                if (!currentAlertaId) {
                    alert('Nenhum alerta selecionado');
                    return;
                }

                if (!confirm('Tem certeza que deseja excluir este alerta?')) {
                    return;
                }

                $.ajax({
                    url: '../connections/deletewarning.php',
                    type: 'POST',
                    data: {
                        alertaId: currentAlertaId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            fecharveralerta();
                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        } else {
                            alert('Erro ao tentar excluir alerta: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro AJAX:", error);
                        alert('Erro ao tentar excluir o alerta. Tente novamente.');
                    }
                });
            });
            $(document).ready(function () {
                $('#formCriarAlerta').on('submit', function (e) {
                    e.preventDefault();

                    $('#alertaMensagem').hide().removeClass('alert-success alert-danger');
                    $('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Enviando...');

                    var formData = $(this).serialize();

                    $.ajax({
                        url: '../connections/createwarning.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {

                                $('#formCriarAlerta')[0].reset();

                                setTimeout(function () {
                                    location.reload();
                                }, 500);

                            } else {
                                $('#alertaMensagem')
                                    .html('<i class="bi bi-exclamation-triangle"></i> ' + response.message)
                                    .addClass('alert-danger')
                                    .show();
                            }
                        },
                        error: function (xhr, status, error) {
                            $('#alertaMensagem')
                                .html('<i class="bi bi-exclamation-triangle"></i> Erro de conexão. Tente novamente.')
                                .addClass('alert-danger')
                                .show();
                            console.error('Erro AJAX:', error);
                        },
                        complete: function () {
                            $('button[type="submit"]').prop('disabled', false).html('Enviar');
                        }
                    });
                });

                $(document).on('keyup', function (e) {
                    if (e.key === 'Escape') {
                        fecharcriaralerta();
                    }
                });

                $(document).on('click', '#btn-add-alert', function () {
                    setTimeout(function () {
                        $('#titulo').focus();
                    }, 300);
                });
            });

            var temperatura = 0;

            const ctx = document.getElementById('temperaturaGauge').getContext('2d');
            const gauge = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [temperatura, 50 - temperatura],
                        backgroundColor: [
                            getCorTemperatura(temperatura),
                            '#f0f0f0'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    circumference: 270,
                    rotation: 225,
                    cutout: '80%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });

            function getCorTemperatura(temp) {
                if (temp < 20) return '#3498db';
                if (temp < 30) return '#f39c12';
                return '#e74c3c';
            }

            function set_Temperature() {

                fetch('get_messages.php')
                    .then(r => r.text())
                    .then(data => {
                        console.log("Recebido:", data);
                        if (data.trim() != "") {
                            temperatura.textContent = data.trim();
                        }
                    })
                    .catch(err => console.error(err));
            }
            setInterval(set_Temperature, 1000);

        </script>
</body>

</html>