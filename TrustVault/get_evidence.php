<?php
include 'db_connect.php';
header('Content-Type: application/json');

// Fetch evidence from users
$userQuery = "SELECT id, filename, category, uploaded_by, 'user' AS source, upload_time, hash_value, approval_status 
              FROM evidence";

// Fetch evidence from investigators
$investigatorQuery = "SELECT id, filename, category, uploaded_by, 'investigator' AS source, upload_time, hash_value, approval_status 
                      FROM evidence1";

// Combine both using UNION
$combinedQuery = "($userQuery) UNION ALL ($investigatorQuery) ORDER BY upload_time DESC";

$result = $conn->query($combinedQuery);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['download_url'] = 'uploads/' . urlencode($row['filename']);
        $data[] = $row;
    }
}

echo json_encode($data);
?>

