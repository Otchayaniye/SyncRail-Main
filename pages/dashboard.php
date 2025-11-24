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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Dashboard</title>
</head>

<body class="backgroundf min-vh-100 d-flex flex-column justify-content-center">
    <div class="container d-flex justify-content-evenly align-items-stretch bgcont rounded p-3">
        <div class="p-2 lbox scrolly">yht</div>





        <div class="rbox d-flex flex-column align-items-center bg-success rounded">
            <div class=" d-flex w-100 justify-content-between mb-3" id="boxtituloalerta">
                <h3 class="alertat" id="tituloAlerta">Alertas</h3>

                <button class="btn p-0 iconplus ps-3 pe-3" onclick="abrircriaralerta()" id="adminonly"
                    data-is-admin="<?= htmlspecialchars($_SESSION['admin']) ?>">
                    <i class="bi bi-plus-circle"></i>
                </button>
                <!-- No dashboard.php, substitua o formulário atual por este: -->
                <div id="popcriaralerta" class="popup rounded">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="m-0 p-0">Novo Aviso</h3>
                        <button class="btn btf" onclick="fecharcriaralerta()"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <form id="formCriarAlerta" class="d-flex flex-column align-items-center gap-2">
                        <input type="text" id="titulo" name="alerta_titulo" placeholder="Título"
                            class="form-control fontc text-center" autocomplete="off" required>
                        <input type="text" id="descr" name="descr" placeholder="Descrição"
                            class="form-control fontc text-center" autocomplete="off" required>
                        <button type="submit" class="btn border bg-danger w-50 mt-2">Enviar</button>
                    </form>

                    <!-- Mensagem de status -->
                    <div id="alertaMensagem" class="mt-2 text-center" style="display: none;"></div>

                    <?php if (!empty($error)): ?>
                        <div class="error w-100 text-center"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div id="criaralertaoverlay" class="overlay"></div>

            <div class="w-100 p-2 d-flex flex-column gap-2 alertacorpo scrolly h-100 bg-danger rounded">

                <?php
                include("../connections/warndisplay.php");
                if (!empty($alerta)) {
                    foreach ($alerta as $linha) {
                        $tipoClasse = 'alerta-' . ($linha['alerta_tipo'] ?? 'sistema');
                        echo '<a class="linkveralerta p-2 rounded bg-light ' . $tipoClasse . '" data-alerta-id="' . htmlspecialchars($linha['pk_alerta']) . '">
                <div><strong>' . htmlspecialchars($linha['alerta_titulo']) . '</strong></div>
                <div>' . htmlspecialchars($linha['fk_user_name']) . '</div>
                <div class="w-100 text-end fs-6"><span><em>' . htmlspecialchars($linha['alerta_data_formatada']) . '</em></span></div>
              </a>';
                    }
                }
                ?>
            </div>

            <!-- Popup para mostrar alerta -->
            <div class="popupveralerta rounded p-2" id="popmostraralerta">
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
                    <button class="btn btnexcluiralerta bg-danger w-75">Excluir</button>

                </div>

            </div>
            <div id="mostraralertaoverlay" class="overlayveralerta"></div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
        <script src="../js/dashborad.js"></script>
        <script>
            const isAdmin = document.getElementById('adminonly').dataset.isAdmin === '1';
            document.getElementById("adminonly").style.display = isAdmin ? "block" : "none";

            $(document).on('click', '.linkveralerta', function (event) {
                event.preventDefault();
                var alertaID = $(this).data('alerta-id');
                const alertaIdint = parseInt(alertaID, 10);

                // Fazer requisição AJAX para buscar os dados do alerta
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

            // Variável global para armazenar o ID do alerta atual
            let currentAlertaId = null;

            $(document).on('click', '.linkveralerta', function (event) {
                event.preventDefault();
                var alertaID = $(this).data('alerta-id');
                currentAlertaId = parseInt(alertaID, 10); // Armazena o ID globalmente

                // Fazer requisição AJAX para buscar os dados do alerta
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
                // Submissão do formulário de criar alerta
                $('#formCriarAlerta').on('submit', function (e) {
                    e.preventDefault();

                    // Mostrar loading
                    $('#alertaMensagem').hide().removeClass('alert-success alert-danger');
                    $('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Enviando...');

                    // Coletar dados do formulário
                    var formData = $(this).serialize();

                    $.ajax({
                        url: '../connections/createwarning.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {

                                // Limpar formulário
                                $('#formCriarAlerta')[0].reset();

                                // Recarregar a lista de alertas após 1 segundo
                                setTimeout(function () {
                                    location.reload();
                                }, 500);

                            } else {
                                // Erro
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
                            // Reativar botão
                            $('button[type="submit"]').prop('disabled', false).html('Enviar');
                        }
                    });
                });

                // Fechar popup ao pressionar ESC
                $(document).on('keyup', function (e) {
                    if (e.key === 'Escape') {
                        fecharcriaralerta();
                    }
                });

                // Focar no primeiro campo quando abrir o popup
                $(document).on('click', '#btn-add-alert', function () {
                    setTimeout(function () {
                        $('#titulo').focus();
                    }, 300);
                });
            });
        </script>
</body>

</html>