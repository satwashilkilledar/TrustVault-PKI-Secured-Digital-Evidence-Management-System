<?php
session_start();
header('Content-Type: application/json');
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$evidence_id = $_POST['evidence_id'] ?? null;
$status = $_POST['status'] ?? null;
$comment = $_POST['comment'] ?? null;

if (!$evidence_id || !$status) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing evidence ID or status']);
    exit;
}

$reviewer = $_SESSION['username'] ?? 'auditor';
$role = $_SESSION['role'] ?? 'auditor';

try {
    $stmt = $conn->prepare("UPDATE evidence SET status = ?, reviewed_by = ?, reviewed_at = NOW(), comment = ? WHERE id = ?");
    $stmt->bind_param("sssi", $status, $reviewer, $comment, $evidence_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Log the action
        $action = "Status set to '$status' for Evidence ID $evidence_id";
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_stmt = $conn->prepare("INSERT INTO audit_logs (action, performed_by, role, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("ssss", $action, $reviewer, $role, $ip);
        $log_stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Evidence status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Evidence not found or status unchanged']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}



