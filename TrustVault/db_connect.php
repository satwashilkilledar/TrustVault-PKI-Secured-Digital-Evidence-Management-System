<?php
$conn = new mysqli("localhost", "root", "", "evidence_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
