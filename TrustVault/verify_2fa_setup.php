<?php
session_start();
require_once 'vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$error = null;
$success = null;
$g = new GoogleAuthenticator();

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT otp_secret FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = trim($_POST['otp']);

    if ($g->checkCode($user['otp_secret'], $otp)) {
        // 2FA verified for setup, optionally set a DB flag if needed
        $success = "2FA successfully verified and enabled!";
    } else {
        $error = "Invalid authentication code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify 2FA Setup</title>
    <meta charset="UTF-8" />
</head>
<body>
    <h1>Verify Two-Factor Authentication Setup</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
        <p><a href="dashboard_user.php">Go to Dashboard</a></p>
        <?php exit(); ?>
    <?php endif; ?>

    <form method="POST">
        <label for="otp">Enter the 6-digit authentication code from your app:</label><br/>
        <input id="otp" name="otp" pattern="\d{6}" maxlength="6" required autofocus /><br/>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
