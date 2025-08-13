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
$user_role = $_SESSION['role'] ?? 'user'; // Or wherever you store user role

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['evidenceFile'])) {
    $filename_orig = $_FILES['evidenceFile']['name'];
    $tmp_name = $_FILES['evidenceFile']['tmp_name'];
    $category = $_POST['category'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $status = "Pending";
    $upload_time = date("Y-m-d H:i:s");

    $target_dir = "uploads/";
    $filepath = $target_dir . basename($filename_orig);
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
        die("Unsupported file type.");
    }

    if (!move_uploaded_file($tmp_name, $filepath)) {
        die("Failed to upload file.");
    }

    $hash_value = hash_file('sha256', $filepath);

    // Always insert into evidence1 table, no matter user role
    $stmt = $mysqli->prepare("INSERT INTO evidence1 (filename, category, notes, status, upload_time, uploaded_by, hash_value) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $filename, $category, $notes, $status, $upload_time, $username, $hash_value);

    if ($stmt->execute()) {
        // Redirect to dashboard after successful upload
        header("Location: dashboard_investigator.php");  // Change 'dashboard.php' to your exact dashboard URL
        exit();

    if ($stmt->execute()) {
        echo "Upload successful!";
    } else {
        echo "Upload failed: " . $stmt->error;
        unlink($filepath);
    }
    $stmt->close();
}
}
?>