<?php
session_start();
require_once 'vendor/autoload.php';
include 'db_connect.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

// Get current user
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Check if user already has a secret
$stmt = $conn->prepare("SELECT otp_secret FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $user = $result->fetch_assoc()) {
    if (empty($user['otp_secret'])) {
        // Generate new secret
        $g = new GoogleAuthenticator();
        $secret = $g->generateSecret();

        // Save to database
        $stmtUpdate = $conn->prepare("UPDATE users SET otp_secret = ? WHERE username = ?");
        $stmtUpdate->bind_param("ss", $secret, $username);
        $stmtUpdate->execute();
    } else {
        $secret = $user['otp_secret'];
    }

    // Generate QR code
    $qrCodeUrl = GoogleQrUrl::generate($username, $secret, 'DigitalEvidencePortal');
} else {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup 2FA | Digital Evidence Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background:
                linear-gradient(rgba(15, 25, 45, 0.85), rgba(15, 25, 45, 0.85)),
                url('https://images.unsplash.com/photo-1581093588401-70d905a1049b?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            color: #1e293b;
        }
        .qr-img {
            max-width: 250px;
            margin: 1.2rem auto;
            display: block;
            border: 4px solid #e2e8f0;
            padding: 10px;
            background: #fff;
            border-radius: 0.5rem;
        }
        a.btn-back {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="setup-box text-center">
            <h2 class="mb-3 text-primary fw-bold">Set Up Two-Factor Authentication</h2>
            <p class="lead">Scan the QR code below with <strong>Google Authenticator</strong> or a compatible app:</p>
            <img src="<?= htmlspecialchars($qrCodeUrl) ?>" alt="QR Code" class="qr-img">
            <p class="mt-3">Or manually enter this secret key:</p>
            <p><strong><?= htmlspecialchars($secret) ?></strong></p>
            <a href="dashboard_user.php" class="btn btn-secondary mt-4 btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
