<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST['id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = "User";

    // Check for duplicate usernames
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $error = "Username already exists.";
        $check_stmt->close();
    } else {
        $check_stmt->close();

        // IMPORTANT: Insecure, consider using password_hash() instead
        $insert_stmt = $conn->prepare("INSERT INTO users (id, username, password, role) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("isss", $id, $username, $password, $role);

        if ($insert_stmt->execute()) {
            $insert_stmt->close();
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
            $insert_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register | Digital Evidence Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(to bottom right, #1e3a8a, #111827);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Inter', sans-serif;
    }

    .glass {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
  </style>
</head>
<body class="text-white">

  <div class="max-w-md w-full glass p-8 shadow-xl space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-white">Create Your Account</h1>
      <p class="text-indigo-200 mt-1 text-sm">Digital Evidence Management System</p>
    </div>

    <?php if (isset($error)) : ?>
      <div class="bg-red-100 text-red-800 px-4 py-2 rounded text-sm font-semibold text-center">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off" class="space-y-5">
      <div>
        <label for="id" class="block mb-1 text-sm font-medium text-indigo-100">ID</label>
        <input type="text" name="id" id="id" required
               class="w-full rounded-md px-4 py-2 text-black bg-white/80 border border-gray-300 focus:ring-2 focus:ring-indigo-500"
               placeholder="Enter your ID" />
      </div>

      <div>
        <label for="username" class="block mb-1 text-sm font-medium text-indigo-100">Username</label>
        <input type="text" name="username" id="username" required
               class="w-full rounded-md px-4 py-2 text-black bg-white/80 border border-gray-300 focus:ring-2 focus:ring-indigo-500"
               placeholder="Choose a username" />
      </div>

      <div>
        <label for="password" class="block mb-1 text-sm font-medium text-indigo-100">Password</label>
        <input type="password" name="password" id="password" required
               class="w-full rounded-md px-4 py-2 text-black bg-white/80 border border-gray-300 focus:ring-2 focus:ring-indigo-500"
               placeholder="Create a password" />
      </div>

      <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-md focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400">
        Register
      </button>

      <p class="text-center text-sm text-indigo-200 mt-2">
        Already have an account?
        <a href="login.php" class="text-indigo-300 font-semibold hover:underline">Login</a>
      </p>
    </form>
  </div>

</body>
</html>
