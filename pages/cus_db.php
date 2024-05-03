<?php
$servername = 'localhost';  // Host name
$dbname = 'we-aims';  // Database name
$charset = 'utf8mb4';  // Character set
$username = 'root';  // Database username
$password = '';  // Database password

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
