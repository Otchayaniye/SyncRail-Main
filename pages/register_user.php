<?php
session_start();
$erro = "";

if (isset($_SESSION['erro'])) {
    $erro = $_SESSION['erro'] ?? '';
    unset($_SESSION['erro']);
}

if (isset($_SESSION["user_name"]) && isset($_SESSION["conected"])) {
    if ($_SESSION["conected"] == true) {
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Cadastro</title>
</head>

<body class="backgroundf">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="d-flex flex-column align-items-center gap-3 p-4 rounded shadow bgform">
            <h2 class="">Cadastro</h2>
            <form method="post" action="../connections/register.php">
                <div class="d-flex flex-column align-items-center gap-3 fontc">
                    <input type="text" id="username" name="username" placeholder="Nome de Usuário" class="form-control fontc text-center" autocomplete="off" required>
                    <input type="email" id="email" name="email" placeholder="Email" class="form-control fontc text-center" autocomplete="off" required>
                    <input type="password" id="password" name="password" placeholder="Senha" class="form-control fontc text-center" autocomplete="off" required>
                    <a class="link" href="../index.php">Fazer Login</a>
                    <button type="submit" class="btf">Registrar</button>
                    <?php if ($erro): ?>
                        <div class="error w-75"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>