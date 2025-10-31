<?php
session_start();
include_once("../connections/db.php");
include("../lay/menu.php");

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = $_SESSION["pk_user"];
    $stmt = $conn->prepare("SELECT user_name, user_mail FROM usuario WHERE pk_user = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $alerta_titulo = $_POST["alerta_titulo"];
    $descr = ($_POST["descr"]);
    
    $stmt = $conn->prepare("INSERT INTO alertas(alerta_titulo, alerta_texto, fk_user_id, fk_user_name, fk_user_mail) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $alerta_titulo, $descr, $user, $, $);
    if ($stmt->execute()) {
        header("Location: ../index.php");
        exit;
    } else {
        $error = "Erro ao criar novo alerta. Por favor, tente novamente.";
    }
}
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
    <title>Status</title>
</head>

<body class="backgroundf min-vh-100 d-flex flex-column justify-content-center">
    <div class="container d-flex justify-content-evenly align-items-stretch bgcont rounded p-4">
        <div class="p-3 lbox w-50 scrolly">yht</div>
        <div class="p-4 rbox w-50 d-flex flex-column align-items-center bg-success rounded" ">
            <div class=" d-flex w-100 justify-content-between mb-3" id="boxtituloalerta">
            <h3 class="alertat" id="tituloAlerta">Alertas</h3>
            <button class="btn p-0 iconplus ps-3 pe-3" onclick="abrirPopup()"><i class="bi bi-plus-circle"></i></button>
            <div id="meuPopup" class="popup rounded">
                <div class="w-100 d-flex justify-content-between align-items-center mb-3">
                    <h2 class="m-0 p-0">Novo Aviso</h2>
                    <button class="btn" onclick="fecharPopup()">Fechar</button>
                </div>
                <form method="POST" action="">
                    <input type="text" id="titulo" name="alerta_titulo" placeholder="Título" class="form-control fontc text-center" autocomplete="off" required>
                    <input type="text" id="descr" name="descr" placeholder="Descrição" class="form-control fontc text-center" autocomplete="off" required>
                    <button type="submit" class="btf">Enviar</button>
                    <?php if ($error): ?>
                        <div class="error w-75"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
            </div>
            <div id="meuOverlay" class="overlay"></div>
        </div>
        <div class="w-100 p-4 d-flex flex-column align-items-center g-1 alertacorpo scrolly h-100 bg-danger rounded">
            <div class="">
                <?php
                include("../connections/warndisplay.php");
                if (!empty($alerta)) {
                    foreach ($alerta as $linha) {
                        echo '<tr>
                            <td><strong>' . htmlspecialchars($linha['alerta_titulo']) . '</strong></td><br>
                            <td>' . htmlspecialchars($linha['alerta_texto']) . '</td><br>
                            <td><em>' . htmlspecialchars($linha['alerta_data']) . '</em></td><br>
                            <td>' . htmlspecialchars($linha['user_name']) . '</em></td><br> 
                        </tr>';
                    }
                }
                ?>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script>
        const elementotituloalerta = document.getElementById('tituloAlerta');
        const elementoboxtituloalerta = document.getElementById('boxtituloalerta');
        const larguratituloalerta = elementotituloalerta.offsetWidth;
        const larguraboxtituloalerta = elementoboxtituloalerta.offsetWidth;
        elementotituloalerta.style.setProperty('--larguratituloalerta', larguratituloalerta + 'px');
        elementotituloalerta.style.setProperty('--larguraboxtituloalerta', larguraboxtituloalerta + 'px');

        function abrirPopup() {
            document.getElementById("meuPopup").style.display = "block";
            document.getElementById("meuOverlay").style.display = "block";
        }

        // Função para fechar o pop-up e a sobreposição
        function fecharPopup() {
            document.getElementById("meuPopup").style.display = "none";
            document.getElementById("meuOverlay").style.display = "none";
        }
    </script>
</body>

</html>