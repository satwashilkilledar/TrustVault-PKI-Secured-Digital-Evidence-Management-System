<?php 
include 'db_connect.php';

$query = "SELECT custody_log.*, evidence.filename 
          FROM custody_log 
          JOIN evidence ON custody_log.evidence_id = evidence.id 
          ORDER BY custody_log.timestamp DESC";

$result = $conn->query($query);

if (!$result) {
    echo "<p class='text-red-600'>Error: " . $conn->error . "</p>";
    return;
}

echo "<ul class='text-sm space-y-2'>";
while ($row = $result->fetch_assoc()) {
    echo "<li>
        <strong>".htmlspecialchars($row['action'])."</strong> on 
        <em>".htmlspecialchars($row['filename'])."</em> by 
        <span class='font-medium'>".htmlspecialchars($row['actor'])."</span> at 
        ".htmlspecialchars($row['timestamp'])."<br>
        <span class='text-xs text-gray-500'>Hash: ".htmlspecialchars($row['hash_value'])."</span>
    </li>";
}
echo "</ul>";
?>
