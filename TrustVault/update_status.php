<?php
include 'db_connect.php';
session_start();

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed. Use POST.']);
    exit;
}

// Fetch and sanitize input
$evidence_id = filter_input(INPUT_POST, 'evidence_id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
$comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);

// Validate required inputs
if (!$evidence_id || !$status) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing or invalid Evidence ID or Status.']);
    exit;
}

// Optional: Strictly allow only certain statuses
$allowed_status = ['Approved', 'Rejected'];
if (!in_array($status, $allowed_status)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid status value.']);
    exit;
}

// Prepare and execute SQL update
$stmt = $conn->prepare("UPDATE evidence SET status = ?, comment = ? WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ssi', $status, $comment, $evidence_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['message' => 'Evidence status updated successfully.']);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Evidence not found or no changes made.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Database update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
