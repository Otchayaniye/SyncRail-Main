<?php
session_start();
$error = "";

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'] ?? '';
    unset($_SESSION['error']);
}

if (isset($_SESSION["user_name"]) && isset($_SESSION["conected"])) {
    if ($_SESSION["conected"] == true) {
        header("Location: pages/dashboard.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Login</title>
</head>

<body class="bg2">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="d-flex flex-column align-items-center gap-3 p-4 rounded shadow bgform">
            <h2>Login</h2>
            <form method="post" action="connections/login.php">
                <div class="d-flex flex-column align-items-center gap-3 fontc">
                    <input type="email" id="email" name="email" placeholder="Email" class="form-control fontc text-center" required>
                    <input type="password" id="password" name="password" placeholder="Senha" class="form-control fontc text-center" required>
                    <a class="link" href="pages/register_user.php">Criar conta</a>
                    <button type="submit" name="login" class="btf">Entrar</button>
                    <?php if ($error): ?>
                        <div class="error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>