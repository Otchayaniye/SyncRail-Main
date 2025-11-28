<?php
require("phpMQTT.php");

$server = "7f3337e189e34386a91cdc05ae8a6d85.s1.eu.hivemq.cloud";
$port = 8883;
$topics = [
    'SyncRail/S1/Iluminacao',
    'SyncRail/S1/Umidade',
    'SyncRail/S1/Presenca',
    'SyncRail/S1/Temperatura',
    'SyncRail/S4/Velocidade'
];

$client_id = "phpmqtt-" . rand();
$username = "SyncRail";
$password = "SyncRail123";
$cafile = __DIR__ . "/cacert.pem";
$messages = [];

$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);
$mqtt->cafile = $cafile;
if (!$mqtt->connect(true, NULL, $username, $password)) {
    echo json_encode(["error" => "Não foi possível conectar ao broker"]);
    exit;
}

foreach ($topics as $topic) {
    $mqtt->subscribe([
        $topic => [
            "qos" => 0,
            "function" => function ($topic, $msg) use (&$messages) {
                $messages[$topic] = $msg;
            }
        ]
    ], 0);
}

$start = time();
while (time() - $start < 3) {
    $mqtt->proc();
}

$mqtt->close();

echo json_encode($messages);
?>

