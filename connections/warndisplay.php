<?php
// Arquivo: connections/warndisplay.php
require_once('db.php');

$sql = "SELECT pk_alerta, fk_user_id, fk_user_name, fk_user_mail, alerta_texto, 
        DATE_FORMAT(alerta_data, '%d-%m-%Y %H:%i') as alerta_data_formatada, 
        alerta_titulo, alerta_tipo 
        FROM alertas 
        ORDER BY alerta_data DESC 
        LIMIT 50";

$resultado = $conn->query($sql);
if ($resultado && $resultado->num_rows >= 1) {
    $alerta = $resultado->fetch_all(MYSQLI_ASSOC);
} else {
    echo "<div class='w-100 p-3 text-center'> Não há alertas! </div>";
}

$resultado->free();
?>