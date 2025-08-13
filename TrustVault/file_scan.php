<?php
$uploadSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        // You can process/save file here
        $uploadSuccess = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Secure File Upload</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes spin-emoji {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .emoji-spin {
      display: inline-block;
      animation: spin-emoji 2s linear infinite;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-6">

  <form action="file_scan.php" method="POST" enctype="multipart/form-data" class="bg-white shadow-lg rounded-xl p-6 space-y-4 w-full max-w-md">
    <h2 class="text-2xl font-bold text-center text-gray-800">📤 Upload a File</h2>
    <input type="file" name="file" required class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-500"/>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg w-full">
      Upload & Scan
    </button>
  </form>

  <?php if ($uploadSuccess): ?>
  <!-- Popup Overlay -->
  <div id="scanModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl px-8 py-6 max-w-md text-center animate-pulse">
      <div class="text-xl font-semibold text-gray-800">
        <span class="emoji-spin">🔍</span>
        Please wait, your file is being securely scanned for threats...
        <span class="emoji-spin">🛡️</span>
      </div>
    </div>
  </div>

  <script>
    // Auto-hide popup after 3 seconds
    setTimeout(() => {
      const modal = document.getElementById('scanModal');
      if (modal) modal.style.display = 'none';
    }, 3000);
  </script>
  <?php endif; ?>

</body>
</html>
