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
    <link rel="stylesheet" href="../css/about.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Dashboard</title>
</head>

<body class="backgroundf min-vh-100 d-flex flex-column justify-content-center">
    <div class="container bgcont rounded p-2">
        <div class="text-center d-flex flex-column justify-content-evenly align-items-center h-100 scrolly">
            <h1 class="">Sobre o SyncRail</h1>
            <p class="fs-5 textobase">O SyncRail é uma aplicação desenvolvida para monitorar e gerenciar frotas de veículos
                    ferroviários em tempo real. Com uma interface intuitiva, o SyncRail oferece
                    soluções eficientes para otimizar operações, melhorar a segurança e aumentar a produtividade das
                    frotas.
                <p class="fs-5 textobase">O sistema oferece diversas ferramentas para
                    monitoramento de trens e rotas, além de
                    ter opções para atualizar e administrar o monitoramento dos que estão em funcionamento ou em
                    conserto, é
                    possível também emitir alertas para os usuários. O aplicativo oferece um mapa em tempo real dos
                    trens e suas rotas, é possível ver as
                    rotas e paradas dos trens e quais trens passam por certas rotas. Além das funções já mencionadas é
                    possível acompanhar trens e rotas em manutenção.</p>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
        <script src="../js/dashborad.js"></script>
        <script>
            const isAdmin = document.getElementById('adminonly').dataset.isAdmin === '1';
            document.getElementById("adminonly").style.display = isAdmin ? "block" : "none";
        </script>
</body>

</html>