<?php
include("../lay/menu.php");
session_start();
$error = "";

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT user_adm FROM usuario WHERE pk_user = ?");
$stmt -> bind_param("i", $id);
$stmt -> execute();
$resultado = $stmt->get_result();
$admin = $resultado->fetch_assoc();
$_SESSION['admin'] = $admin['user_adm'];

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/rapair.css">
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
            

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
        <script src="../js/repair.js"></script>
        <script>

            if (<?php echo $_SESSION['admin'] ?> === 1){
                document.getElementById("adminonly").style.display = "block";
            } else if (<?php echo $_SESSION['admin'] ?> != 1){
                document.getElementById("adminonly").style.display = "none";
            }

        </script>
</body>

</html>