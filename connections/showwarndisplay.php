<?php
require_once('db.php');

$_POST['alertaId'] = isset($_POST['alertaId']) ? $_POST['alertaId'] : null;
if ($_POST['alertaId'] === null) {
    // Se o alertaId não estiver definido, você pode definir um valor padrão ou lidar com isso de outra forma
    $_POST['alertaId'] = 0; // Exemplo de valor padrão
}

$id = $_POST['alertaId'];
echo $id;

$sql = "SELECT pk_alerta, fk_user_id, fk_user_name, fk_user_mail, alerta_texto, 
DATE_FORMAT(alerta_data, '%d-%m-%Y %H:%i') as alerta_data, alerta_titulo FROM alertas WHERE pk_alerta = $id";
$resultado = $conn->query($sql);
if ($resultado && $resultado->num_rows >= 1) {
    $alertacompleto = $resultado->fetch_all(MYSQLI_ASSOC);
}

$resultado->free();
$conn->close();
?>