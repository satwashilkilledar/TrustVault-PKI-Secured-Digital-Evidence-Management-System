<?php
define('VT_API_KEY', '07367ba33abd8870c4effc2fafff9a34255982ab95f257354c7a8df59ffbe099');

$scan_result = null;
$error = null;

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

function vt_upload_file($file_path) {
    $curl = curl_init();
    $post_data = ['file' => new CURLFile($file_path)];

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://www.virustotal.com/api/v3/files",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["x-apikey: " . VT_API_KEY],
        CURLOPT_POSTFIELDS => $post_data,
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
        error_log("Upload Error: " . curl_error($curl));
    }
    curl_close($curl);
    return json_decode($response, true);
}

function vt_get_report($analysis_id) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://www.virustotal.com/api/v3/analyses/$analysis_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["x-apikey: " . VT_API_KEY],
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
        error_log("Report Error: " . curl_error($curl));
    }
    curl_close($curl);
    return json_decode($response, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $file = $_FILES['upload_file'];
    $filename = basename($file['name']);
    $upload_path = $upload_dir . $filename;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload error. Please try again.";
    } elseif (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $upload_response = vt_upload_file($upload_path);

        if (!isset($upload_response['data']['id'])) {
            $error = "Failed to scan the file.";
        } else {
            $analysis_id = $upload_response['data']['id'];

            $max_wait = 60;
            $interval = 5;
            $elapsed = 0;
            do {
                sleep($interval);
                $report = vt_get_report($analysis_id);
                $status = $report['data']['attributes']['status'] ?? '';
                if ($status === 'completed') {
                    $scan_result = $report;
                    break;
                }
                $elapsed += $interval;
            } while ($elapsed < $max_wait);

            if (!$scan_result) {
                $error = "Scan timed out.";
            }
        }
        // Delete after scan completes
        if (file_exists($upload_path)) unlink($upload_path);
    } else {
        $error = "Failed to move uploaded file.";
    }
}
?>

<!-- The HTML stays unchanged below this point, you can use your working popup + styles -->


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>VirusScan - Digital Evidence Portal</title>
<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
body {
    background-color: #0f213b;
    font-family: 'Arial', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    position: relative;
}
.dashboard-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: #1f2937;
    color: #ffffff;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: background-color 0.3s;
    cursor: pointer;
}
.dashboard-btn:hover {
    background-color: #374151;
}
.container {
    background-color: #1f2937;
    border-radius: 12px;
    padding: 2.5rem 2rem;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    position: relative;
}
h1 {
    color: #ffffff;
    font-weight: 700;
    text-align: center;
    margin-bottom: 1.5rem;
    font-size: 24px;
}
form {
    display: flex;
    flex-direction: column;
}
input[type="file"] {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #444;
    background-color: #2d3748;
    color: #fff;
    margin-bottom: 1.2rem;
}
button {
    padding: 14px;
    border: none;
    border-radius: 8px;
    background-color: #3b82f6;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}
button:hover {
    background-color: #2563eb;
}
.message {
    margin-top: 1rem;
    padding: 1rem;
    border-radius: 8px;
    font-weight: 600;
}
.error {
    background-color: #dc3545;
    color: #fff;
}
.success {
    background-color: #198754;
    color: #fff;
}
.results {
    background-color:  #d3d3d3ff;
    border-radius: 8px;
    margin-top: 1.5rem;
    padding: 1rem;
    max-height: 350px;
    overflow-y: auto;
    border: 1px solid #f6f6f6ff;
}
.results h3 {
    margin-bottom: 0.8rem;
    color: #fff;
    font-size: 20px;
}
.results ul {
    list-style: none;
}
.results li {
    margin-bottom: 0.6rem;
}

/* Overlay styles */
#loadingOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 33, 59, 0.85);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.popup-box {
    background-color: #1f2937;
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 10px;
    font-size: 18px;
    font-weight: bold;
    box-shadow: 0 0 20px rgba(0,0,0,0.4);
}
</style>
</head>
<body>

<a href="dashboard_user.php" class="dashboard-btn">Dashboard</a>

<!-- Loading Overlay -->
<div id="loadingOverlay">
  <div class="popup-box">
     <span id="loadingText"> 🔍 Please wait, your file is being securely scanned for threats...  
</span><span id="dots"></span> 🔄 
  </div>
</div>

<script>
function showLoading() {
    document.getElementById("loadingOverlay").style.display = "flex";
    animateDots();
}

function animateDots() {
    const dots = document.getElementById("dots");
    let count = 0;
    setInterval(() => {
        count = (count + 1) % 4;
        dots.textContent = '.'.repeat(count);
    }, 500);
}
</script>

<div class="container">
    <h1>VirusTotal File Scanner</h1>
    <form method="post" enctype="multipart/form-data" onsubmit="showLoading()">
        <input type="file" name="upload_file" required />
        <button type="submit">Upload & Scan</button>
    </form>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($scan_result): ?>
        <?php
            $stats = $scan_result['data']['attributes']['stats'] ?? [];
            $results = $scan_result['data']['attributes']['results'] ?? [];
            $malicious = $stats['malicious'] ?? 0;
            $harmless = $stats['harmless'] ?? 0;
        ?>
        <div class="message <?= $malicious > 0 ? 'error' : 'success' ?>">
            <?= $malicious > 0 ? '❌ Virus Detected! Do not use this file.' : '✅ No Virus Detected. Safe to use.' ?>
        </div>

        <div class="results">
            <h3>Scan Summary:</h3>
            <ul>
                <li><strong>Harmless:</strong> <?= intval($harmless) ?></li>
                <li><strong>Malicious:</strong> <?= intval($malicious) ?></li>
                <li><strong>Suspicious:</strong> <?= intval($stats['suspicious'] ?? 0) ?></li>
                <li><strong>Undetected:</strong> <?= intval($stats['undetected'] ?? 0) ?></li>
                <li><strong>Timeout:</strong> <?= intval($stats['timeout'] ?? 0) ?></li>
            </ul>

            <h3>Engine Detections:</h3>
            <ul>
                <?php foreach ($results as $engine => $data):
                    $category = $data['category'] ?? 'undetected';
                    $resultStr = $category !== 'undetected' ? htmlspecialchars($data['result']) : 'No threats';
                    $color = $category === 'malicious' ? '#d9534f' : ($category === 'suspicious' ? '#fd7e14' : '#198754');
                ?>
                    <li><strong><?= htmlspecialchars($engine) ?>:</strong> <span style="color: <?= $color ?>"><?= $resultStr ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
