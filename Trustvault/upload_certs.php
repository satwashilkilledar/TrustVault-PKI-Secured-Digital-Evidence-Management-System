<?php
session_start();
include 'db_connect.php';

$certName = $_POST['cert_name'];
$uploadedBy = $_SESSION['username'] ?? 'admin';

$certFile = $_FILES['cert_file'];
$keyFile  = $_FILES['key_file'];

$certPath = 'certs/' . basename($certFile['name']);
$keyPath  = 'certs/' . basename($keyFile['name']);

// Upload both files
if (move_uploaded_file($certFile['tmp_name'], $certPath) && move_uploaded_file($keyFile['tmp_name'], $keyPath)) {
    $stmt = $conn->prepare("INSERT INTO pki_certificates (cert_name, cert_path, key_path, uploaded_by) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $certName, $certPath, $keyPath, $uploadedBy);
        $stmt->execute();
    }
    header("Location: dashboard_admin.php#certs");
    exit;
} else {
    echo "Upload failed!";
}
?>
