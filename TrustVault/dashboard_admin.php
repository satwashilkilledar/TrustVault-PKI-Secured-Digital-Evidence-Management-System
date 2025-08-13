<?php
session_start();
include 'db_connect.php';

// Log admin login
$action = "Login";
$user = $_SESSION['username'] ?? 'admin';
$role = $_SESSION['role'] ?? 'Administrator';
$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO audit_logs (action, performed_by, role, ip_address) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("ssss", $action, $user, $role, $ip);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      html, body { height: 100%; }
      body { min-height: 100vh; }
      /* Custom scrollbar for sidebar and main content */
      ::-webkit-scrollbar { width: 8px; background: #e2e8f0;}
      ::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 6px;}
    </style>
</head>
<body class="bg-gray-100 h-full">

<!-- Topbar -->
<nav class="fixed w-full bg-white shadow flex items-center z-20 px-6 py-3">
  <div class="flex-1 flex items-center space-x-4">
    <img src="https://img.icons8.com/color/48/000000/dashboard-layout.png" alt="" class="h-8 w-8">
    <h1 class="text-2xl font-bold text-blue-900 tracking-tight">Admin Dashboard</h1>
  </div>
  <div>
    <span class="font-medium text-gray-600 mr-4"><?php echo htmlspecialchars($user); ?> (<?php echo htmlspecialchars($role); ?>)</span>
    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-semibold shadow transition">🚪 Logout</a>
  </div>
</nav>

<div class="px-6 pt-4">
<?php if (isset($_SESSION['msg'])): ?>
  <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
    <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
  <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
  </div>
<?php endif; ?>
</div>

<div class="flex pt-20 min-h-full">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r shadow-lg h-[calc(100vh-4rem)] fixed top-16 left-0 z-10 flex flex-col p-4 space-y-1 overflow-y-auto transition">
        <div>
            <h2 class="text-lg font-extrabold text-blue-700 mb-2 tracking-wide">Admin Panel</h2>
            <nav class="flex flex-col space-y-2 mt-2 text-base font-medium">
                <a href="#upload" class="hover:text-blue-600 transition">📤 Submit Evidence</a>
                <a href="#evidence" class="hover:text-blue-600 transition">📁 View Evidence</a>
                <a href="#verify" class="hover:text-blue-600 transition">🛡️ Verify File Hash</a>
                <a href="#custody" class="hover:text-blue-600 transition">🔗 Chain of Custody</a>
                <a href="#logs" class="hover:text-blue-600 transition">📋 System Logs</a>
                <a href="#report" class="hover:text-blue-600 transition">📄 Audit Reports</a>
                <a href="#users" class="hover:text-blue-600 transition">👥 Manage Users</a>
                <a href="#roles" class="hover:text-blue-600 transition">🔐 Assign Roles</a>
                <a href="#certs" class="hover:text-blue-600 transition">🔏 PKI Management</a>
                <a href="#delete" class="hover:text-blue-600 transition">🗑️ Delete Evidence</a>
                <a href="http://localhost:3000" class="hover:text-blue-600 transition" target="_blank">📊 Monitoring (Grafana)</a>
                <a href="#ci" class="hover:text-blue-600 transition">🚀 Trigger CI/CD</a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 overflow-y-auto p-8 space-y-12">
        <!-- Upload Evidence -->
        <section id="upload" class="mb-6">
            <h2 class="text-2xl font-bold mb-4 text-blue-800">Submit/Upload Evidence</h2>
            <form action="upload_evidence.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow flex flex-col gap-4 max-w-lg">
                <input type="file" name="evidenceFile" required class="border p-2 rounded">
                <select name="category" class="border p-2 rounded">
                    <option value="Image">Image</option>
                    <option value="PDF">PDF</option>
                    <option value="Video">Video</option>
                </select>
                <textarea name="notes" class="border p-2 rounded" placeholder="Notes..."></textarea>
                <button class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2 rounded transition font-semibold">Upload</button>
            </form>
        </section>

        <!-- View Evidence -->
        <section id="evidence" class="mb-6">
            <h2 class="text-2xl font-bold mb-4 text-blue-800">View Submitted Evidence</h2>
            <div class="overflow-x-auto rounded shadow">
              <table class="w-full bg-white rounded text-sm">
                <thead class="bg-blue-50">
                  <tr>
                    <th class="p-2 text-left">Filename</th>
                    <th>Category</th>
                    <th>Uploaded By</th>
                    <th>Upload Time</th>
                    <th>Hash</th>
                    <th>Download</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                $res = $conn->query("SELECT * FROM evidence1 ORDER BY upload_time DESC");
                while ($r = $res->fetch_assoc()) {
                  echo "<tr class='border-b hover:bg-blue-50 transition'>
                    <td class='p-2 font-medium'>{$r['filename']}</td>
                    <td>{$r['category']}</td>
                    <td>{$r['uploaded_by']}</td>
                    <td>{$r['upload_time']}</td>
                    <td><code>" . substr($r['hash_value'], 0, 12) . "...</code></td>
                    <td><a href='uploads/{$r['filename']}' class='text-blue-700 underline'>Download</a></td>
                    <td>
                      <form action='delete_evidence.php' method='POST' onsubmit=\"return confirm('Delete this evidence?');\">
                        <input type='hidden' name='evidence_id' value='{$r['id']}' />
                        <button class='text-red-600 hover:underline font-bold'>Delete</button>
                      </form>
                    </td>
                  </tr>";
                }
                ?>
                </tbody>
              </table>
            </div>
        </section>

        <!-- Verify Hash -->
        <section id="verify" class="mb-6">
            <h2 class="text-2xl font-bold mb-4 text-blue-800">Verify File Hash</h2>
            <form action="verify_hash.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow max-w-sm flex flex-col gap-3">
                <input type="file" name="checkFile" required class="border p-2 rounded">
                <button class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2 rounded transition font-semibold">Verify</button>
            </form>
        </section>

        <!-- Chain of Custody -->
        <section id="custody" class="mb-6">
            <h2 class="text-2xl font-bold mb-4 text-blue-800">Chain of Custody</h2>
            <div class="bg-white p-6 rounded-lg shadow">
              <?php include 'custody_chain.php'; ?>
            </div>
        </section>

        <!-- System Logs -->
        <!-- System Logs -->
<section id="logs" class="mb-6">
    <h2 class="text-2xl font-bold mb-4 text-blue-800">System Logs</h2>
    <div class="overflow-x-auto rounded shadow">
      <table class="w-full bg-white rounded text-sm">
        <thead class="bg-blue-50">
          <tr>
            <th class="p-2">Action</th><th>User</th><th>Role</th><th>Date</th><th>IP</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $logs = $conn->query("SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT 15");
        while ($log = $logs->fetch_assoc()) {
          echo "<tr class='border-b hover:bg-blue-50 transition'>
            <td class='p-2'>{$log['action']}</td>
            <td>{$log['performed_by']}</td>
            <td>{$log['role']}</td>
            <td>{$log['timestamp']}</td>
            <td>{$log['ip_address']}</td>
          </tr>";
        }
        ?>
        </tbody>
      </table>
    </div>
</section>
        <!-- Audit Reports -->
        <section id="report" class="mb-6">
            <h2 class="text-2xl font-bold mb-4 text-blue-800">Generate Audit Reports</h2>
            <form action="generate_report.php" method="POST" class="bg-white p-6 rounded-lg shadow flex flex-col gap-3 max-w-xl">
                <div class="flex flex-wrap gap-3">
                  <input type="date" name="from_date" required class="border p-2 rounded">
                  <input type="date" name="to_date" required class="border p-2 rounded">
                  <select name="role" class="border p-2 rounded">
                    <option value="All">All</option>
                    <option value="Administrator">Administrator</option>
                    <option value="Investigator">Investigator</option>
                    <option value="Auditor">Auditor</option>
                  </select>
                  <button class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2 rounded transition font-semibold">Generate CSV</button>
                </div>
            </form>
        </section>

        <!-- Manage Users -->
        <section id="users" class="mb-6">
          <h2 class="text-2xl font-bold mb-4 text-blue-800">Manage Users</h2>
          <?php
          // Handle Add/Update/Delete User
          if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST['add_user'])) {
              $username = $_POST['username'];
              $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
              $role = $_POST['role'];
              $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
              $stmt->bind_param("sss", $username, $password, $role);
              $stmt->execute();
            }
            if (isset($_POST['update_user'])) {
              $user_id = $_POST['user_id'];
              $new_role = $_POST['new_role'];
              $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
              $stmt->bind_param("si", $new_role, $user_id);
              $stmt->execute();
            }
            if (isset($_POST['delete_user'])) {
              $user_id = $_POST['user_id'];
              $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
              $stmt->bind_param("i", $user_id);
              $stmt->execute();
            }
            // Refresh to show updated table
            echo "<meta http-equiv='refresh' content='0'>";
          }
          ?>
          <form action="" method="POST" class="bg-white p-5 rounded shadow flex flex-col md:flex-row md:items-center gap-3 mb-6 max-w-2xl">
            <input type="text" name="username" placeholder="Username" required class="border p-2 rounded flex-1" />
            <input type="password" name="password" placeholder="Password" required class="border p-2 rounded flex-1" />
            <select name="role" class="border p-2 rounded">
              <option>Administrator</option>
              <option>Investigator</option>
              <option>Auditor</option>
              <option>User</option>
            </select>
            <button type="submit" name="add_user" class="bg-green-600 text-white px-4 py-2 rounded font-semibold">Add User</button>
          </form>
          <div class="overflow-x-auto rounded shadow">
            <table class="w-full bg-white text-sm rounded">
              <thead class="bg-blue-50">
                <tr><th class="p-2 text-left">Username</th><th>Role</th><th>Created</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php
                $res = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
                if (!$res) {
                  echo "<p class='text-red-600'>SQL Error: " . $conn->error . "</p>";
                } else {
                  while ($u = $res->fetch_assoc()) {
                    echo "<tr class='border-b hover:bg-blue-50 transition'>
                      <td class='p-2 font-medium'>{$u['username']}</td>
                      <td>{$u['role']}</td>
                      <td>{$u['created_at']}</td>
                      <td class='flex space-x-2'>
                        <form action='' method='POST' class='inline'>
                          <input type='hidden' name='user_id' value='{$u['id']}' />
                          <select name='new_role' class='text-sm border p-1 rounded'>
                            <option " . ($u['role'] == 'Administrator' ? 'selected' : '') . ">Administrator</option>
                            <option " . ($u['role'] == 'Investigator' ? 'selected' : '') . ">Investigator</option>
                            <option " . ($u['role'] == 'Auditor' ? 'selected' : '') . ">Auditor</option>
                            <option " . ($u['role'] == 'User' ? 'selected' : '') . ">User</option>
                          </select>
                          <button name='update_user' class='bg-yellow-500 text-white px-2 py-1 rounded ml-1 font-bold'>Update</button>
                        </form>
                        <form action='' method='POST' class='inline'>
                          <input type='hidden' name='user_id' value='{$u['id']}' />
                          <button name='delete_user' class='bg-red-600 text-white px-2 py-1 rounded font-bold'>Delete</button>
                        </form>
                      </td>
                    </tr>";
                  }
                }
                ?>
              </tbody>
            </table>
        </div>
      </section>
      
      <!-- Assign Roles -->
      <section id="roles" class="mb-6">
        <h2 class="text-2xl font-bold mb-4 text-blue-800">Assign Roles and Permissions</h2>
        <?php
        // Fetch users
        $result = $conn->query("SELECT id, username, role FROM users");
        if ($result && $result->num_rows > 0):
        ?>
        <div class="overflow-x-auto rounded shadow">
        <table class="w-full bg-white text-sm rounded">
          <thead class="bg-blue-50">
            <tr>
              <th class="p-2 text-left">Username</th>
              <th>Current Role</th>
              <th>Change Role</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($user = $result->fetch_assoc()): ?>
              <tr class="border-b hover:bg-blue-50 transition">
                <td class="p-2"><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                  <form action="" method="POST" class="flex gap-2 items-center">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="new_role" class="border p-1 rounded">
                      <option <?= $user['role'] == 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                      <option <?= $user['role'] == 'Investigator' ? 'selected' : '' ?>>Investigator</option>
                      <option <?= $user['role'] == 'Auditor' ? 'selected' : '' ?>>Auditor</option>
                      <option <?= $user['role'] == 'User' ? 'selected' : '' ?>>User</option>
                    </select>
                </td>
                <td>
                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded font-semibold">Update</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        </div>
        <?php else: ?>
          <p class="text-gray-500 mt-2">No users found.</p>
        <?php endif; ?>
      </section>

      <!-- PKI Management -->
      <section id="certs" class="mb-6">
        <h2 class="text-2xl font-bold mb-4 text-blue-800">Configure PKI / Manage Certificates</h2>
        <?php
        $certDir = __DIR__ . '/certs/';
        $msg = "";

        // Ensure certs directory exists
        if (!file_exists($certDir)) {
            mkdir($certDir, 0777, true);
        }

        // Handle upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $allowedCertTypes = ['crt', 'pem'];
            $allowedKeyTypes = ['key'];

            // Certificate file
            if (isset($_FILES['cert_file']) && $_FILES['cert_file']['error'] === UPLOAD_ERR_OK) {
                $certTmp = $_FILES['cert_file']['tmp_name'];
                $certName = basename($_FILES['cert_file']['name']);
                $certExt = strtolower(pathinfo($certName, PATHINFO_EXTENSION));

                if (in_array($certExt, $allowedCertTypes)) {
                    move_uploaded_file($certTmp, $certDir . $certName);
                    $msg .= "<p class='text-green-600'>Certificate uploaded: $certName</p>";
                } else {
                    $msg .= "<p class='text-red-600'>Invalid certificate format: .$certExt</p>";
                }
            }

            // Key file
            if (isset($_FILES['key_file']) && $_FILES['key_file']['error'] === UPLOAD_ERR_OK) {
                $keyTmp = $_FILES['key_file']['tmp_name'];
                $keyName = basename($_FILES['key_file']['name']);
                $keyExt = strtolower(pathinfo($keyName, PATHINFO_EXTENSION));

                if (in_array($keyExt, $allowedKeyTypes)) {
                    move_uploaded_file($keyTmp, $certDir . $keyName);
                    $msg .= "<p class='text-green-600'>Private key uploaded: $keyName</p>";
                } else {
                    $msg .= "<p class='text-red-600'>Invalid key format: .$keyExt</p>";
                }
            }
        }
        ?>
        <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow flex flex-col gap-4 max-w-lg">
          <div>
            <label class="block font-semibold">Upload Certificate (.crt or .pem)</label>
            <input type="file" name="cert_file" accept=".crt,.pem" required class="border p-2 rounded w-full" />
          </div>
          <div>
            <label class="block font-semibold">Upload Private Key (.key)</label>
            <input type="file" name="key_file" accept=".key" required class="border p-2 rounded w-full" />
          </div>
          <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800 font-semibold">Upload</button>
        </form>
        <?= $msg ?>
        <div class="mt-6 bg-gray-50 p-4 rounded">
          <h3 class="text-lg font-semibold mb-2">Current Certificates & Keys:</h3>
          <ul class="text-sm list-disc list-inside text-blue-900">
            <?php
            $files = scandir($certDir);
            $shown = false;
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo "<li>$file</li>";
                    $shown = true;
                }
            }
            if (!$shown) {
                echo "<li>No certificates or keys uploaded yet.</li>";
            }
            ?>
          </ul>
        </div>
      </section>
    
    <section id="evidence" class="mb-6">
    <h2 class="text-2xl font-bold mb-4 text-blue-800">View Submitted Evidence</h2>

    <form action="delete_evidence.php" method="POST" onsubmit="return confirm('Delete selected evidence items?');" class="bg-white p-6 rounded-lg shadow max-w-full">
        <div class="overflow-x-auto rounded shadow">
            <table class="w-full bg-white rounded text-sm">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="p-2 text-left"><input type="checkbox" id="select_all" title="Select All"></th>
                        <th class="p-2 text-left">Filename</th>
                        <th>Category</th>
                        <th>Uploaded By</th>
                        <th>Upload Time</th>
                        <th>Hash</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = $conn->query("SELECT * FROM evidence1 ORDER BY upload_time DESC");
                if (!$res) {
                    echo "<tr><td colspan='7' class='text-red-600'>SQL Error: " . htmlspecialchars($conn->error) . "</td></tr>";
                } elseif ($res->num_rows == 0) {
                    echo "<tr><td colspan='7' class='text-gray-600 text-center'>No evidence records found.</td></tr>";
                } else {
                    while ($r = $res->fetch_assoc()) {
                        $id = htmlspecialchars($r['id']);
                        $filename = htmlspecialchars($r['filename']);
                        $category = htmlspecialchars($r['category']);
                        $uploaded_by = htmlspecialchars($r['uploaded_by']);
                        $upload_time = htmlspecialchars($r['upload_time']);
                        $hash_short = htmlspecialchars(substr($r['hash_value'], 0, 12)) . "...";

                        echo "<tr class='border-b hover:bg-blue-50 transition'>
                            <td class='p-2 text-center'>
                              <input type='checkbox' name='evidence_ids[]' value='$id' class='select_item'>
                            </td>
                            <td class='p-2 font-medium'>$filename</td>
                            <td>$category</td>
                            <td>$uploaded_by</td>
                            <td>$upload_time</td>
                            <td><code>$hash_short</code></td>
                            <td><a href='uploads/$filename' class='text-blue-700 underline' target='_blank' rel='noopener'>Download</a></td>
                        </tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        </div>

        <button type="submit" name="delete_selected" class="mt-4 bg-red-600 text-white px-5 py-2 rounded font-semibold hover:bg-red-700 transition">Delete Selected</button>
    </form>

    <script>
      // Select/Deselect all checkboxes
      document.getElementById('select_all').addEventListener('change', function() {
          const checked = this.checked;
          document.querySelectorAll('.select_item').forEach(cb => cb.checked = checked);
      });
    </script>
</section>

      <!-- CI/CD -->
      <section id="ci" class="mb-6">
        <h2 class="text-2xl font-bold mb-4 text-blue-800">Trigger CI/CD Deployments</h2>
        <?php if (isset($_SESSION['deploy_msg'])): ?>
          <p class="mb-2 text-sm text-blue-600"><?= $_SESSION['deploy_msg']; unset($_SESSION['deploy_msg']); ?></p>
        <?php endif; ?>
        <form action="trigger_deploy.php" method="POST">
          <button class="bg-green-600 text-white px-5 py-2 rounded font-semibold hover:bg-green-700 transition">🚀 Trigger Now</button>
        </form>
      </section>
    </main>
</div>
</body>
</html>
