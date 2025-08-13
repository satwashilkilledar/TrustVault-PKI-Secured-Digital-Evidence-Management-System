<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $evidence_id = $_POST['evidence_id'];
    $new_notes = $_POST['new_notes'];

    // Validate
    if (!empty($evidence_id) && !empty($new_notes)) {
        // Prepare query
        $stmt = $conn->prepare("UPDATE evidence SET notes = CONCAT(notes, '\nInvestigator: ', ?) WHERE id = ?");
        
        // Check if prepare() succeeded
        if (!$stmt) {
            die("SQL Prepare Failed: " . $conn->error);
        }

        $stmt->bind_param("si", $new_notes, $evidence_id);
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard_investigator.php");
        exit;
    } else {
        echo "Missing fields.";
    }
} else {
    echo "Invalid request.";
}
?>
