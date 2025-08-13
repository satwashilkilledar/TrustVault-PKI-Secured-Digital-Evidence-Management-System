<?php
if ($_FILES['checkFile']['error'] === UPLOAD_ERR_OK) {
    $tempPath = $_FILES['checkFile']['tmp_name'];
    $filename = $_FILES['checkFile']['name'];
    $computedHash = hash_file('sha256', $tempPath);

    include 'db_connect.php';
    $stmt = $conn->prepare("SELECT * FROM evidence1 WHERE filename = ?");
    $stmt->bind_param("s", $filename);
    $stmt->execute();
    $result = $stmt->get_result();
    $msg = "Hash Not Found";
    if ($row = $result->fetch_assoc()) {
        $originalHash = $row['hash_value'];
        $msg = ($originalHash === $computedHash) ? "✅ Hash Verified" : "❌ Hash Mismatch!";
    }
    echo "<script>alert('$msg'); window.history.back();</script>";
}
?>
