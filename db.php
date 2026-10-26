<?php
// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'nic_system';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Connection successful
// You can now use $conn for queries
?>
