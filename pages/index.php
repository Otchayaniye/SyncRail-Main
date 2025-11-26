<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>MQTT Dashboard PHP</title>
    <script>
        function set_Temperature() {
            var temp = document.getElementById("temperature");

            fetch('get_messages.php')
                .then(r => r.text())
                .then(data => {
                    console.log("Recebido:", data);
                    if (data.trim() != "") {
                        temp.textContent = data.trim();
                    }
                })
                .catch(err => console.error(err));
        }
        setInterval(set_Temperature, 1000);
    </script>

</head>

<body>
    <span>Temperatura</span>
    <h1 id="temperature">xxxx</h1>
</body>