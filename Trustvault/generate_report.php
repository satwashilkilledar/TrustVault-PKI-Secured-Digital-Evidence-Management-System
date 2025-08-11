<?php
// generate_report.php

include 'db_connect.php';

// Send proper headers to trigger file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=audit_report_' . date('Ymd_His') . '.csv');

// Output file pointer connected to output stream
$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, [
    'Timestamp',
    'User',
    'Role',
    'Action',
    'Target',
    'Hash at Action',
    'IP',
    'Status/Changes',
    'Notes'
]);

// Fetch audit logs from database (adjust table/column names as needed)
$query = "
    SELECT 
        log_time AS Timestamp,
        username AS User,
        user_role AS Role,
        action AS Action,
        target_object AS Target,   -- adjust if your column is called 'filename', 'evidence_id', etc.
        hash_value AS `Hash at Action`,
        ip_address AS IP,
        status_changes AS `Status/Changes`,  -- e.g. 'Pending -> Approved'. If not available, use approval_status or ''
        notes AS Notes
    FROM audit_logs
    ORDER BY log_time DESC
";

$result = $conn->query($query);

// Output each row as CSV
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['Timestamp'],
            $row['User'],
            $row['Role'],
            $row['Action'],
            $row['Target'],
            $row['Hash at Action'],
            $row['IP'],
            $row['Status/Changes'],
            $row['Notes'],
        ]);
    }
}

fclose($output);
exit;
?>
