<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'ride_system_enhanced';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');
?>