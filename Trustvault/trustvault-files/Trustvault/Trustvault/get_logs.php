<?php
include 'db_connect.php';

$query = "SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT 10";
$result = $conn->query($query);

$logs = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

echo json_encode($logs);
?>
