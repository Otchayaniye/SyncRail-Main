<?php
require("phpMQTT.php");

$server = "hivemq.com";
$port = 8883;
$t_iluminacao = "SyncRail/S1/Iluminacao";
$t_temperatura = "SyncRail/S1/Temperatura";
$t_umidade = "SyncRail/S1/Umidade";
$t_presenca = "SyncRail/S1/Presenca";
$t_velocidade = "SyncRail/S4/Velocidade";
$client_id = "phpmqtt-" . rand();

$username = "SyncRail";
$password = "SyncRail123";
$cafile = __DIR__ . "/cacert.pem";

$m_iluminacao = "";
$m_temperatura = "";
$m_umidade = "";
$m_presenca = "";
$m_velocidade = "";



$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);
$mqtt->cafile = $cafile;
if (!$mqtt->connect(true, NULL, $username, $password)) {
    echo "Não foi possível conectar ao broker";
    exit;
}

$mqtt->subscribe([
    $t_iluminacao => [
        "qos" => 0,
        "function" => function ($t_iluminacao, $msg) use (&$m_iluminacao) {
            if (!empty($msg)) {
                $m_iluminacao = $msg;
            }
        }
    ],
    $t_temperatura => [
        "qos" => 0,
        "function" => function ($t_temperatura, $msg) use (&$m_temperatura) {
            if (!empty($msg)) {
                $m_temperatura = $msg;
            }
        }
    ],
    $t_umidade => [
        "qos" => 0,
        "function" => function ($t_umidade, $msg) use (&$m_umidade) {
            if (!empty($msg)) {
                $m_umidade = $msg;
            }
        }
    ],
    $t_presenca => [
        "qos" => 0,
        "function" => function ($t_presenca, $msg) use (&$m_presenca) {
            if (!empty($msg)) {
                $m_presenca = $msg;
            }
        }
    ],
    $t_velocidade => [
        "qos" => 0,
        "function" => function ($t_velocidade, $msg) use (&$m_velocidade) {
            if (!empty($msg)) {
                $m_velocidade = $msg;
            }
        }
    ]
], 0);

echo json_encode([
    'success' => true,
    'iluminacao' => $m_iluminacao,
    'temperatura' => $m_temperatura,
    'umidade' => $m_umidade,
    'presenca' => $m_presenca,
    'velocidade' => $m_velocidade
]);

$start = time();
while (time() - $start < 5) {
    $mqtt->proc();
}

$mqtt->close();
?>

