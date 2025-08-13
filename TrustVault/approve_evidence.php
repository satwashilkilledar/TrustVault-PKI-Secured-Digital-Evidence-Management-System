<?php
include 'db_connect.php';

header('Content-Type: application/json');
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed. Use POST.']);
    exit;
}

// Fetch and validate POST inputs
$input_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$input_source = filter_input(INPUT_POST, 'source', FILTER_SANITIZE_STRING);
$input_action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

if ($input_id === false || $input_id === null || empty(trim($input_source)) || empty(trim($input_action))) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing or invalid id, source or action parameter.']);
    exit;
}

$allowed_sources = ['user', 'investigator'];
$allowed_actions = ['approve', 'reject'];

$source = strtolower(trim($input_source));
$action = strtolower(trim($input_action));

if (!in_array($source, $allowed_sources, true) || !in_array($action, $allowed_actions, true)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid source or action parameter.']);
    exit;
}

$table_map = [
    'user' => 'evidence',
    'investigator' => 'evidence1'
];

$table = $table_map[$source];

// Check if record exists
$stmt_check = $conn->prepare("SELECT approval_status FROM {$table} WHERE id = ?");
if (!$stmt_check) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to prepare statement: ' . $conn->error]);
    exit;
}

$stmt_check->bind_param('i', $input_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['message' => 'Evidence record not found.']);
    $stmt_check->close();
    $conn->close();
    exit;
}

$current_approval = $result_check->fetch_assoc()['approval_status'];
$stmt_check->close();

if ($current_approval === 'Approved' && $action === 'approve') {
    echo json_encode(['message' => 'Already approved.']);
    $conn->close();
    exit;
}

if ($current_approval === 'Rejected' && $action === 'reject') {
    echo json_encode(['message' => 'Already rejected.']);
    $conn->close();
    exit;
}

// Update approval_status
$new_status = ucfirst($action === 'approve' ? 'Approved' : 'Rejected');

$stmt_update = $conn->prepare("UPDATE {$table} SET approval_status = ? WHERE id = ?");
if (!$stmt_update) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to prepare update statement: ' . $conn->error]);
    exit;
}

$stmt_update->bind_param('si', $new_status, $input_id);

if ($stmt_update->execute()) {
    if ($stmt_update->affected_rows > 0) {
        echo json_encode(['message' => "Evidence {$new_status} successfully."]);
    } else {
        http_response_code(304);
        echo json_encode(['message' => 'No changes made.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Database update failed: ' . $stmt_update->error]);
}

$stmt_update->close();
$conn->close();
