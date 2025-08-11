<?php
session_start();
include 'db_connect.php';
require_once 'vendor/autoload.php';
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

$g = new GoogleAuthenticator();
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = trim($_POST['otp']);
    $userId = $_SESSION['temp_user_id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && !empty($user['otp_secret'])) {
        if ($g->checkCode($user['otp_secret'], $otp)) {
            // OTP valid, complete login
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            unset($_SESSION['temp_user_id'], $_SESSION['temp_username']);

            switch ($user['role']) {
                case 'Admin': header("Location: dashboard_admin.php"); break;
                case 'Auditor': header("Location: dashboard_auditor.php"); break;
                case 'Investigator': header("Location: dashboard_investigator.php"); break;
                default: header("Location: dashboard_user.php"); break;
            }
            exit();
        } else {
            $error = "Invalid authentication code. Please try again.";
        }
    } else {
        $error = "Invalid session or user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Two-Factor Authentication</title>
    <meta charset="UTF-8" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center px-6 py-12 bg-gray-900 text-white min-h-screen">
    <div class="max-w-md w-full bg-gray-800 p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Two-Factor Authentication</h2>
        
        <?php if ($error): ?>
            <div class="mb-4 bg-red-600 px-4 py-2 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4" novalidate>
            <label for="otp" class="block text-sm font-medium">Authentication Code</label>
            <input id="otp" name="otp" type="text" required autofocus maxlength="6"
                class="w-full px-4 py-2 rounded bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter 6-digit code" />
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 py-2 rounded font-semibold shadow-md">
                Verify
            </button>
        </form>
    </div>
</body>
</html>
