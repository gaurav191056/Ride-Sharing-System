<?php
session_start();
include "connect.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo "Unauthorized: Please login first";
    exit();
}

// Check if user is a driver
if($_SESSION['role'] != 'driver') {
    echo "Unauthorized: Only drivers can accept rides";
    exit();
}

// Check if ride_id is provided
if(!isset($_POST['ride_id'])) {
    echo "Error: No ride ID provided";
    exit();
}

$driver_id = $_SESSION['user_id'];
$ride_id = $_POST['ride_id'];

// First, check if the ride exists and is still available
$check_sql = "SELECT * FROM rides WHERE ride_id = '$ride_id' AND driver_id IS NULL AND status = 'requested'";
$check_result = $conn->query($check_sql);

if($check_result->num_rows == 0) {
    echo "Ride no longer available or already taken";
    exit();
}

// Accept the ride - update driver_id and status
$update_sql = "UPDATE rides SET driver_id = '$driver_id', status = 'accepted' WHERE ride_id = '$ride_id'";

if($conn->query($update_sql)) {
    echo "Success";
} else {
    echo "Database error: " . $conn->error;
}
?>