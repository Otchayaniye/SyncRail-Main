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

$username = "";
$password = "";
$cafile = __DIR__ . "/cacert.pem";
$message = "";



$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);
$mqtt->cafile = $cafile;
if (!$mqtt->connect(true, NULL, $username, $password)) {
    echo "Não foi possível conectar ao broker";
    exit;
}

$mqtt->subscribe([
    $t_iluminacao => [
        "qos" => 0,
        "function" => function ($topic, $msg) use (&$message) {
            if (!empty($msg)) {
                $message = $msg;
            }
        }
    ],
    $t_temperatura => [
        "qos" => 0,
        "function" => function ($topic, $msg) use (&$message) {
            if (!empty($msg)) {
                $message = $msg;
            }
        }
    ],
    $t_umidade => [
        "qos" => 0,
        "function" => function ($topic, $msg) use (&$message) {
            if (!empty($msg)) {
                $message = $msg;
            }
        }
    ],
    $t_presenca => [
        "qos" => 0,
        "function" => function ($topic, $msg) use (&$message) {
            if (!empty($msg)) {
                $message = $msg;
            }
        }
    ],
    $t_velocidade => [
        "qos" => 0,
        "function" => function ($topic, $msg) use (&$message) {
            if (!empty($msg)) {
                $message = $msg;
            }
        }
    ]
], 0);

$start = time();
while (time() - $start < 2) {
    $mqtt->proc();
}

$mqtt->close();

echo $message;
