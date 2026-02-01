<?php
$host = 'localhost';
$db   = 'webmanag_popsdb';
$user = 'webmanag_popsdb';
$pass = 'webmanag_popsdb';
$charset = 'utf8mb4';

// Create connection using MySQLi
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
