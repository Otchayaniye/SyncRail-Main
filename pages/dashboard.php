
<?php
include("../lay/menu.php");
session_start();
$error = "";

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
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
    <title>Dashboard</title>
</head>

<body class="backgroundf min-vh-100 d-flex flex-column justify-content-center">
    <div class="container d-flex justify-content-evenly align-items-stretch bgcont rounded p-4">
        <div class="p-3 lbox w-75 scrolly">yht</div>
        <div class="p-4 rbox w-25 d-flex flex-column align-items-center bg-success rounded" ">
            <div class=" d-flex w-100 justify-content-between mb-3" id="boxtituloalerta">
            <h3 class="alertat" id="tituloAlerta">Alertas</h3>

            <button class="btn p-0 iconplus ps-3 pe-3" onclick="abrirPopup()"><i class="bi bi-plus-circle"></i></button>

            <div id="meuPopup" class="popup rounded">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="m-0 p-0">Novo Aviso</h3>
                    <button class="btn btf" onclick="fecharPopup()"><i class="bi bi-x-lg"></i></button>
                </div>

                <form method="POST" action="../connections/createwarning.php" class="d-flex flex-column align-items-center gap-2">
                    <input type="text" id="titulo" name="alerta_titulo" placeholder="Título" class="form-control fontc text-center" autocomplete="off" required>
                    <input type="text" id="descr" name="descr" placeholder="Descrição" class="form-control fontc text-center" autocomplete="off" required>
                    <button type="submit" class="btn border bg-danger w-50 mt-2">Enviar</button>
                </form>
                <?php if ($error): ?>
                    <div class="error w-100 text-center"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
            </div>
            <div id="meuOverlay" class="overlay"></div>

        </div>
        <div class="w-100 p-2 d-flex flex-column gap-2 alertacorpo scrolly h-100 bg-danger rounded">

            <?php
            include("../connections/warndisplay.php");
            if (!empty($alerta)) {
                foreach ($alerta as $linha) {
                    echo '<div class="p-2 rounded bg-light"><tr>
                            <td><strong>' . htmlspecialchars($linha['alerta_titulo']) . '</strong></td><br>
                            <td>' . htmlspecialchars($linha['fk_user_name']) . '</em></td><br> 
                            <div class="w-100 text-end fs-6"><td><em>' . htmlspecialchars($linha['alerta_data']) . '</em></td><br></div>
                        </tr></div>';
                }
            }
            ?>

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