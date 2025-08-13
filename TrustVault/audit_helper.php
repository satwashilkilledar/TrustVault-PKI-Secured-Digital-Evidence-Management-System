<?php
// db_connect.php is already included
function log_action($conn, $username, $role, $action, $ip_address = null) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (username, role, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $role, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}
?>
