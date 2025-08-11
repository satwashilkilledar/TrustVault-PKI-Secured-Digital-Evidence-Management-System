<?php
include 'db_connect.php';
session_start();

if (isset($_FILES['evidenceFile'])) {
    $filename = basename($_FILES['evidenceFile']['name']);
    $target_dir = "uploads/";
    $target_file = $target_dir . $filename;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'mp4', 'txt'];
    if (!in_array($fileType, $allowed)) {
        die("Invalid file type.");
    }

    if (move_uploaded_file($_FILES['evidenceFile']['tmp_name'], $target_file)) {
        $category = $_POST['category'];
        $notes = $_POST['notes'];
        $hash_value = hash_file("sha256", $target_file);
        $uploaded_by = 'Investigator';

        $stmt = $conn->prepare("INSERT INTO evidence1 (filename, category, notes, hash_value, uploaded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $filename, $category, $notes, $hash_value, $uploaded_by);
        $stmt->execute();
        header("Location: dashboard_investigator.php");
        exit();
    } else {
        echo "Failed to upload file.";
    }
}
?>