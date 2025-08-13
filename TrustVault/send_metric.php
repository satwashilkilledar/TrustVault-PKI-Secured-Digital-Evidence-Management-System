<?php
function sendToGrafana($metricName, $value, $tags = []) {
    $url = "https://graphite-prod-43-prod-ap-south-1.grafana.net/graphite/metrics";
    $apiKey = "";

    $timestamp = intval(microtime(true) * 1000000000);  // nanoseconds
    $postData = [[
        "name" => $metricName,
        "interval" => 10,
        "value" => $value,
        "tags" => $tags,
        "time" => $timestamp
    ]];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_POST, true);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    } elseif ($status !== 200) {
        echo "Error: HTTP $status\n$response";
    } else {
        echo "Metric sent successfully!";
    }

    curl_close($ch);
}

// Example call:
sendToGrafana("audit.approvals.count", 1, ["role=auditor", "source=php_dashboard"]);

