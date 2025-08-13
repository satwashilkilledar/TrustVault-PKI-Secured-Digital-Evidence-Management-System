<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['evidence_ids']) && is_array($_POST['evidence_ids'])) {

    // Sanitize and convert IDs to integers
    $ids = array_map('intval', $_POST['evidence_ids']);

    if (count($ids) === 0) {
        $_SESSION['error'] = "No valid evidence IDs received.";
        header("Location: dashboard_admin.php");
        exit;
    }

    // Build dynamic placeholders (?, ?, ?...)
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    // Prepare SELECT to get filenames
    $stmt = $conn->prepare("SELECT id, filename FROM evidence1 WHERE id IN ($placeholders)");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: dashboard_admin.php");
        exit;
    }

    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $found_ids = [];
    while ($row = $result->fetch_assoc()) {
        $found_ids[] = $row['id'];
        $file = __DIR__ . "/uploads/" . $row['filename'];
        if (file_exists($file)) {
            unlink($file); // delete file
        }
    }
    $stmt->close();

    if (!empty($found_ids)) {
        $placeholders_del = implode(',', array_fill(0, count($found_ids), '?'));
        $stmt_del = $conn->prepare("DELETE FROM evidence1 WHERE id IN ($placeholders_del)");
        if (!$stmt_del) {
            $_SESSION['error'] = "Failed to prepare delete statement: " . $conn->error;
            header("Location: dashboard_admin.php");
            exit;
        }

        $types_del = str_repeat('i', count($found_ids));
        $stmt_del->bind_param($types_del, ...$found_ids);
        if ($stmt_del->execute()) {
            $_SESSION['msg'] = count($found_ids) . " evidence item(s) deleted.";
        } else {
            $_SESSION['error'] = "Deletion failed: " . $stmt_del->error;
        }
        $stmt_del->close();
    } else {
        $_SESSION['error'] = "No matching records found for deletion.";
    }

    header("Location: dashboard_admin.php");
    exit;
} else {
    $_SESSION['error'] = "Invalid request or no items selected.";
    header("Location: dashboard_admin.php");
    exit;
}
?>
