<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "evidence_system");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$success = "";
$error = "";

// --- 2FA SECTION ---

// Fetch user's current 2FA status
$stmt2fa = $mysqli->prepare("SELECT is_2fa_enabled FROM users WHERE username = ?");
if (!$stmt2fa) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt2fa->bind_param("s", $username);
$stmt2fa->execute();
$stmt2fa->bind_result($is_2fa_enabled);
$stmt2fa->fetch();
$stmt2fa->close();
$is_2fa_enabled = (int)$is_2fa_enabled;

// Handle 2FA Enable/Disable actions
if (isset($_POST["enable_2fa"])) {
    $stmt = $mysqli->prepare("UPDATE users SET is_2fa_enabled=1 WHERE username=?");
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        $success = "2FA enabled successfully!";
        // Log the action
        $action = "Enabled 2FA";
        $logStmt = $mysqli->prepare("INSERT INTO audit_log (username, action, log_time, ip_address) VALUES (?, ?, NOW(), ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $logStmt->bind_param("sss", $username, $action, $ip);
        $logStmt->execute(); $logStmt->close();
        $is_2fa_enabled = 1;
    } else {
        $error = "Failed to enable 2FA.";
    }
    $stmt->close();
} elseif (isset($_POST["disable_2fa"])) {
    $stmt = $mysqli->prepare("UPDATE users SET is_2fa_enabled=0 WHERE username=?");
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        $success = "2FA disabled successfully.";
        // Log the action
        $action = "Disabled 2FA";
        $logStmt = $mysqli->prepare("INSERT INTO audit_log (username, action, log_time, ip_address) VALUES (?, ?, NOW(), ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $logStmt->bind_param("sss", $username, $action, $ip);
        $logStmt->execute(); $logStmt->close();
        $is_2fa_enabled = 0;
    } else {
        $error = "Failed to disable 2FA.";
    }
    $stmt->close();
}

// --- EVIDENCE SECTION ---

$status_filter = $_GET['status'] ?? '';

// Handle evidence file upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['evidence_file']) && !isset($_POST['enable_2fa']) && !isset($_POST['disable_2fa'])) {
    $filename_orig = $_FILES['evidence_file']['name'];
    $tmp_name = $_FILES['evidence_file']['tmp_name'];
    $category = $_POST['category'] ?? '';
    $comments = $_POST['comments'] ?? '';
    $status = "Pending";
    $upload_time = date("Y-m-d H:i:s");

    $target_dir = "uploads/";
    $filepath = $target_dir . basename($filename_orig);

    // Avoid overwrite
    $i = 1;
    $orig_filename = pathinfo($filename_orig, PATHINFO_FILENAME);
    $ext = pathinfo($filename_orig, PATHINFO_EXTENSION);
    $filename = $filename_orig;
    while (file_exists($filepath)) {
        $filename = $orig_filename . "_$i." . $ext;
        $filepath = $target_dir . $filename;
        $i++;
    }

    $allowed_extensions = ['jpg','jpeg','png','gif','pdf','mp4','avi','mov','doc','docx','txt','xlsx','csv'];
    if (!in_array(strtolower($ext), $allowed_extensions)) {
        $error = "Unsupported file type.";
    } elseif (!move_uploaded_file($tmp_name, $filepath)) {
        $error = "Failed to upload the file.";
    } else {
        // --- Begin Defender Scan Section ---
        $defender_base = "C:\\ProgramData\\Microsoft\\Windows Defender\\Platform";
        $platform_dirs = glob($defender_base . "\\*", GLOB_ONLYDIR);
        rsort($platform_dirs); // Latest first

        if (!$platform_dirs || !file_exists($platform_dirs[0] . "\\MpCmdRun.exe")) {
            $error = "Defender not found. Scan skipped for safety.";
            unlink($filepath);
        } else {
            $mpcmdrun = $platform_dirs[0] . "\\MpCmdRun.exe";
            $escaped_file = escapeshellarg(realpath($filepath));
            $scan_cmd = "\"$mpcmdrun\" -Scan -ScanType 3 -File $escaped_file";

            $scan_output = shell_exec($scan_cmd);
            file_put_contents("defender_log.txt", $scan_output);

            if (stripos($scan_output, "no threats") === false) {
                unlink($filepath);
                $error = "⛔ Upload blocked: file may be malicious. Defender detected potential threats.";
            } else {
                // File passed the scan
                $hash_value = hash_file('sha256', $filepath);
                $stmt = $mysqli->prepare("INSERT INTO evidence 
                    (filename, category, comments, status, upload_time, username, hash_value)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sssssss", $filename, $category, $comments, $status, $upload_time, $username, $hash_value);
                    if ($stmt->execute()) {
                        $success = "✅ Evidence uploaded and scanned successfully!";
                    } else {
                        $error = "Database insert failed: " . $stmt->error;
                        unlink($filepath);
                    }
                    $stmt->close();
                } else {
                    $error = "Database prepare failed: " . $mysqli->error;
                    unlink($filepath);
                }
            }
        }
        // --- End Defender Scan Section ---
    }
}

// Prepare SQL to fetch user evidence filtered by username and optional status filter
if ($status_filter && in_array($status_filter, ['Pending', 'Approved', 'Rejected'], true)) {
    $sql = "SELECT *, IFNULL(approval_status, status) AS current_status FROM evidence WHERE username = ? AND status = ? ORDER BY upload_time DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $username, $status_filter);
} else {
    $sql = "SELECT *, IFNULL(approval_status, status) AS current_status FROM evidence WHERE username = ? ORDER BY upload_time DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
}
$stmt->execute();
$result = $stmt->get_result();

// Helper for Bootstrap badge by status
function statusColorClass($status) {
    switch (strtolower(trim($status))) {
        case 'approved':
            return 'bg-success';
        case 'rejected':
            return 'bg-danger';
        case 'pending':
        default:
            return 'bg-secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Dashboard | Evidence Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background:
                linear-gradient(rgba(15, 25, 45, 0.85), rgba(15, 25, 45, 0.85)),
                url('https://images.unsplash.com/photo-1581093588401-70d905a1049b?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
            Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
        }
        .top-bar {
            background: linear-gradient(90deg, #173462 0%, #1d59a6 100%);
            color: #fff;
            padding: 14px 28px;
            box-shadow: 0 2px 8px 0 rgba(30,60,120,0.06);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logout-btn {
            background-color: #e53935;
            border: none;
            transition: background .15s;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 1rem;
            font-weight: 500;
        }
        .logout-btn:hover {
            background: #b71c1c;
            color: white;
        }
        .dashboard-card {
            box-shadow: 0 2px 16px rgba(21,49,136,0.09);
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: #1e293b;
            border-radius: 0.5rem;
        }
        .avatar {
            background: #1e40af;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 100%;
            width: 40px; height: 40px;
            font-size: 22px;
            margin-right: 13px;
            font-weight: bold;
            box-shadow: 0 2px 5px 0 rgba(0,0,0,0.05);
        }
        .table thead th {
            background: #153e94;
            color: #fff;
            border-bottom: 2px solid #275ebf;
        }
        .badge {
            letter-spacing: 0.04em;
            font-size: 0.96em;
        }
        .comment-tooltip {
            cursor: help;
            text-decoration: underline dotted;
        }
        /* 2FA badge */
        .badge-2fa {
            font-size: 1em;
            font-weight: 500;
            vertical-align: middle;
            padding: 0.45em 0.8em;
            margin-left: 0.7em;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <header class="top-bar d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center">
            <div class="avatar" title="Logged in as"><?php echo strtoupper(htmlspecialchars(substr($username, 0, 1))); ?></div>
            <h4 class="mb-0 fs-4">Welcome, <b><?php echo htmlspecialchars($username); ?></b> <span style="font-size:1.1em">👋</span></h4>
        </div>
        <a href="logout.php" class="logout-btn shadow-sm">Logout</a>
    </header>

    <div class="container my-4" style="max-width: 950px;">

        <!-- Show upload/2FA success/error -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- 2FA block -->
        <div class="card dashboard-card shadow-sm p-3 mb-4">
            <h5 class="mb-3 text-primary fw-semibold">Two-Factor Authentication (2FA)</h5>
            <div class="mt-2">
                <a href="setup_2fa.php"
                    class="d-block text-center bg-primary text-white fw-semibold py-2 px-4 rounded shadow-sm text-decoration-none">
                    Enable Two-Factor Authentication
                </a>
            </div>
        </div>

        <!-- Scan File Button -->
        <div class="card mt-4 mb-4 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Scan a File for Viruses</h6>
                    <p class="mb-0 text-muted small">Note: Make sure to scan the file before uploading it.</p>
                </div>
                <a href="virus.php" class="btn btn-warning fw-semibold shadow-sm px-4 py-2">
                    🔎 Scan File
                </a>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="card dashboard-card shadow-sm p-3 mb-4">
            <h5 class="mb-3 text-primary fw-semibold">Upload New Evidence</h5>
            <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="evidence_file">Evidence File <span class="text-danger">*</span></label>
                    <input type="file" name="evidence_file" id="evidence_file" class="form-control" required>
                    <div class="form-text">Accepted: image, PDF, video, doc, etc.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold" for="category">Category <span class="text-danger">*</span></label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="Image">Image</option>
                        <option value="PDF">PDF</option>
                        <option value="Video">Video</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="comments">Comments</label>
                    <textarea name="comments" id="comments" class="form-control" rows="2" maxlength="1024" placeholder="Any details about this evidence..."></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow">Upload Evidence</button>
                </div>
            </form>
        </div>

        <!-- Status Filter -->
        <div class="mb-3">
            <form method="get" class="d-flex gap-2 align-items-center">
                <label class="me-2 fw-semibold" for="status_filter">Filter by status:</label>
                <select name="status" id="status_filter" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="" <?php if ($status_filter === '') echo 'selected'; ?>>All</option>
                    <option value="Pending" <?php if ($status_filter === 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Approved" <?php if ($status_filter === 'Approved') echo 'selected'; ?>>Approved</option>
                    <option value="Rejected" <?php if ($status_filter === 'Rejected') echo 'selected'; ?>>Rejected</option>
                </select>
            </form>
        </div>

        <!-- Your Evidence -->
        <section class="mb-5">
            <h3 class="mb-3">Your Evidence</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Filename</th>
                            <th>Category</th>
                            <th>Comments</th>
                            <th>Upload Time</th>
                            <th>Hash</th>
                            <th>Download</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['filename']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['comments'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($row['upload_time']))); ?></td>
                                    <td><code><?php echo substr(htmlspecialchars($row['hash_value']), 0, 16); ?>...</code></td>
                                    <td>
                                        <a href="uploads/<?php echo urlencode($row['filename']); ?>" download class="btn btn-sm btn-primary">
                                            Download
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo statusColorClass($row['current_status']); ?>">
                                            <?php echo htmlspecialchars($row['current_status'] ?: 'Pending'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">You have not uploaded any evidence yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Bootstrap JS bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('.comment-tooltip'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
    <?php if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in']): ?>
  <!-- Login Success Modal -->
  <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-dark">
        <div class="modal-header">
          <h5 class="modal-title" id="welcomeModalLabel">Welcome Back, <?php echo htmlspecialchars($username); ?>!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ✅ You’ve successfully logged in to your dashboard.<br>
          🔐 Please enable 2FA for enhanced security.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Let's Go!</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.addEventListener('load', function () {
      var welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
      welcomeModal.show();
    });
  </script>
<?php unset($_SESSION['just_logged_in']); endif; ?>
</body>
</html>
<?php
$stmt->close();
$mysqli->close();
?>
