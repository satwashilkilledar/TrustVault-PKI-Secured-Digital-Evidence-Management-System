<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "evidence_system");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$success = "";

// Handle evidence upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['evidence_file'])) {
    $filename = $_FILES['evidence_file']['name'];
    $tmp_name = $_FILES['evidence_file']['tmp_name'];
    $category = $_POST['category'];
    $comments = $_POST['comments'];
    $status = "Pending";
    $upload_time = date("Y-m-d H:i:s");

    $target_dir = "uploads/";
    $filepath = $target_dir . basename($filename);

    // Avoid file overwrite
    $i = 1;
    $orig_filename = pathinfo($filename, PATHINFO_FILENAME);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    while (file_exists($filepath)) {
        $filename = $orig_filename . "_$i." . $ext;
        $filepath = $target_dir . $filename;
        $i++;
    }
    move_uploaded_file($tmp_name, $filepath);

    $stmt = $mysqli->prepare("INSERT INTO evidence (filename, category, comments, status, upload_time, username) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssss", $filename, $category, $comments, $status, $upload_time, $username);
        $stmt->execute();
        $stmt->close();
        $success = "✅ Evidence uploaded successfully!";
    } else {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
}

// Fetch user evidence
$sql = "SELECT * FROM evidence WHERE username = ? ORDER BY upload_time DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard | Evidence Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html, body { height: 100%; margin: 0; }

        body {
            /* Background image with dark overlay for readability */
            background: 
                linear-gradient(rgba(15, 25, 45, 0.75), rgba(15, 25, 45, 0.75)),
                url('https://images.unsplash.com/photo-1581093588401-70d905a1049b?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
              Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            color: #f8fafc;
        }

        .top-bar {
            background: linear-gradient(90deg, #173462 0%, #1d59a6 100%);
            color: #fff;
            padding: 14px 28px;
            box-shadow: 0 2px 8px 0 rgba(30,60,120,0.06);
            position: sticky;
            top: 0; /* fixed top bar */
            z-index: 100;
        }

        .logout-btn {
            background-color: #e53935;
            border: none;
            transition: background .15s;
        }
        .logout-btn:hover { 
            background: #b71c1c; 
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

        .upload-note {
            font-size: 0.96em;
            color: #64748b;
        }

        @media(max-width: 600px) {
            .top-bar { flex-direction: column; gap: 12px; padding: 12px 8px !important;}
            .avatar { margin-bottom: 8px;}
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <header class="top-bar d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center">
            <div class="avatar" title="Logged in as">
                <?php echo strtoupper(substr(htmlspecialchars($username), 0, 1)); ?>
            </div>
            <h4 class="mb-0 fs-4">Welcome, <b><?php echo htmlspecialchars($username); ?></b> <span aria-label="waving hand" style="font-size:1.1em">👋</span></h4>
        </div>
        <a href="logout.php" class="btn btn-sm logout-btn text-white px-3 py-2 fs-6 shadow-sm">Logout</a>
    </header>

    <div class="container my-4" style="max-width: 950px;">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card dashboard-card shadow-sm p-3 p-sm-4 mb-4">
            <h5 class="mb-3 text-primary" style="font-weight: 600; letter-spacing: .01em;">Upload New Evidence</h5>
            <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Evidence File <span class="text-danger">*</span></label>
                    <input type="file" name="evidence_file" class="form-control" required>
                    <span class="upload-note">Accepted: image, PDF, video, doc, etc.</span>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="Image">Image</option>
                        <option value="PDF">PDF</option>
                        <option value="Video">Video</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Comments</label>
                    <textarea name="comments" class="form-control" rows="2" maxlength="1024" placeholder="Any details about this evidence..."></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow">Upload Evidence</button>
                </div>
            </form>
        </div>

        <div class="card dashboard-card shadow-sm p-2 p-sm-4">
            <h5 class="mb-3 mt-1 text-primary" style="font-weight: 600;">Your Uploaded Evidence</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 rounded">
                    <thead>
                        <tr>
                            <th scope="col" style="width:48px;">ID</th>
                            <th scope="col">Filename</th>
                            <th scope="col">Category</th>
                            <th scope="col">Comments</th>
                            <th scope="col">Status</th>
                            <th scope="col">Upload Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows === 0): ?>
                            <tr>
                                <td colspan="6" class="text-muted text-center py-4">No evidence uploaded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo (int) $row['id']; ?></td>
                                    <td>
                                        <a href="uploads/<?php echo urlencode($row['filename']); ?>" class="text-decoration-underline text-primary" target="_blank" title="Download/View">
                                            <?php echo htmlspecialchars($row['filename']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td>
                                        <?php
                                            $commentTxt = htmlspecialchars($row['comments']);
                                            if (strlen($commentTxt) > 40) {
                                                echo '<span data-bs-toggle="tooltip" data-bs-title="'.$commentTxt.'">'.substr($commentTxt, 0, 40).'…</span>';
                                            } else {
                                                echo $commentTxt ? $commentTxt : '<span class="text-muted">—</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php
                                                switch ($row['status']) {
                                                    case "Pending": echo "bg-secondary"; break;
                                                    case "Approved": echo "bg-success"; break;
                                                    case "Rejected": echo "bg-danger"; break;
                                                    default: echo "bg-secondary";
                                                }
                                            ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("d M Y, H:i", strtotime($row['upload_time'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS + Tooltips -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable all tooltips on the page
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>
