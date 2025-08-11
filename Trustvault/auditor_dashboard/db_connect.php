<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'evidence_db';

$conn = new mysqli('localhost', 'root', '', 'evidence_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
