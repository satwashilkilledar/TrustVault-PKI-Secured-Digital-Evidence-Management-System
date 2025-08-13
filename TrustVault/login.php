<?php
session_start();
include 'db_connect.php';                // Make sure this defines $conn (mysqli)
require_once 'vendor/autoload.php';      // For Sonata\GoogleAuthenticator

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

// You can use this object if you wish to verify TOTP in the future
$g = new GoogleAuthenticator();
$otpSecret = $g->generateSecret();

$error = "";

// Handle login POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Use prepared statement to fetch user
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // --- Use password_verify if passwords are hashed ---
        // $validPass = password_verify($password, $user['password']); // Use this if hashed
        $validPass = ($user['password'] === $password); // Use this if plain-text (not recommended)

        if ($validPass) {
            // 2FA enabled? ('otp_secret' is not empty or null)
            if (!empty($user['otp_secret'])) {
                // Stage 1: Password OK, now prompt for OTP code
                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['temp_username'] = $user['username'];
                header("Location: verify_2fa.php");
                exit();
            } else {
                // 2FA not set, complete login
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['just_logged_in'] = true;

                // Redirect based on role
                switch ($user['role']) {
                    case 'Admin':         header("Location: dashboard_admin.php"); break;
                    case 'Auditor':       header("Location: dashboard_auditor.php"); break;
                    case 'Investigator':  header("Location: dashboard_investigator.php"); break;
                    default:              header("Location: dashboard_user.php"); break;
                }
                exit();
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | Digital Evidence Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(to bottom right, #1e3a8a, #111827);
      background-size: cover;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
    }
    .glass {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
  </style>
</head>
<body class="flex items-center justify-center px-6 py-12">

  <div class="max-w-md w-full space-y-8">
    <div class="text-center">
      <h1 class="text-white text-3xl font-extrabold">
        🛡️ Digital Evidence Portal
      </h1>
      <p class="text-indigo-200 mt-2 text-sm">Secure login for authorized personnel</p>
    </div>

    <div class="glass p-8 shadow-xl">
      <?php if (!empty($error)): ?>
        <div class="mb-4 bg-red-100 text-red-700 px-4 py-2 rounded-md text-sm font-semibold text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-6" novalidate>
        <div>
          <label for="username" class="block text-sm font-medium text-white mb-1">Username</label>
          <input id="username" name="username" type="text" required autofocus autocomplete="username"
            class="w-full px-4 py-2 rounded-md bg-white/80 text-black border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            placeholder="Enter username" />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-white mb-1">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password"
            class="w-full px-4 py-2 rounded-md bg-white/80 text-black border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            placeholder="Enter password" />
        </div>

        <div class="flex items-center justify-between text-sm text-gray-200">
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="remember" class="text-indigo-600 focus:ring-indigo-500 rounded" />
            <span>Remember me</span>
          </label>
          <a href="#" class="text-indigo-300 hover:underline">Forgot password?</a>
        </div>

        <button type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Sign In
        </button>

        <p class="text-center text-sm text-gray-300">
          Don't have an account?
          <a href="register.php" class="text-indigo-300 font-semibold hover:underline">Register</a>
        </p>

      </form>
    </div>
  </div>
</body>
</html>
