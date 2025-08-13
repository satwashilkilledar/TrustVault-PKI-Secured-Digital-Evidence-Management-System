<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_id'], $_POST['new_role'])) {
    $userId = intval($_POST['user_id']);
    $newRole = $_POST['new_role'];

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $newRole, $userId);
        if ($stmt->execute()) {
            echo "<script>alert('User role updated successfully.'); window.location.href='dashboard_admin.php#roles';</script>";
        } else {
            echo "<script>alert('Error updating role.'); window.location.href='dashboard_admin.php#roles';</script>";
        }
    } else {
        echo "<script>alert('SQL Prepare Error.'); window.location.href='dashboard_admin.php#roles';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='dashboard_admin.php#roles';</script>";
}
?>
