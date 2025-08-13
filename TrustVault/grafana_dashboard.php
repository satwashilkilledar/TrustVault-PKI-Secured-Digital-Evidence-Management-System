<?php
// Your actual Grafana API key (Bearer format)
$apiKey = 'Bearer';

// UID from your dashboard
$uid = 'noj8h7g';

// API endpoint to fetch dashboard info (optional, for debug or metadata)
$url = "https://nokegi8556.grafana.net/api/dashboards/uid/$uid";

// Optional: Fetch dashboard info from Grafana API
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $apiKey"
]);
$response = curl_exec($ch);
curl_close($ch);

// You can use this if you want to print dashboard info (optional)
// $data = json_decode($response, true);
// echo "<pre>"; print_r($data); echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>System Monitoring - Grafana Dashboard</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #f9f9f9;
    }
    iframe {
      width: 100%;
      height: 100vh;
      border: none;
    }
  </style>
</head>
<body>

<!-- Embedded Grafana Dashboard -->
<iframe 
  src="https://nokegi8556.grafana.net/d/noj8h7g/new-dashboard?orgId=1&from=now-6h&to=now&theme=light&kiosk"
  allowfullscreen>
</iframe>

</body>
</html>
