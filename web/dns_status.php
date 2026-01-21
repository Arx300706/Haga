<?php
require_once 'config.php';
force_login();

function api_call($url) {
    $ctx = stream_context_create([
        "http" => [
            "timeout" => 2
        ]
    ]);
    $data = @file_get_contents($url, false, $ctx);
    return $data !== false ? trim($data) : "Erreur API";
}

$status = api_call(SERVER_BASE_URL . "/status");
$zones  = api_call(SERVER_BASE_URL . "/zones");
$logs   = api_call(SERVER_BASE_URL . "/logs");
?>

<h2>État BIND9 : <?= htmlspecialchars($status) ?></h2>

<h3>Zones DNS</h3>
<pre><?= htmlspecialchars($zones) ?></pre>

<h3>Logs DNS</h3>
<pre><?= htmlspecialchars($logs) ?></pre>
