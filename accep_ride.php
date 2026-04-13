<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'driver') {
    echo "Unauthorized";
    exit();
}

$driver_id = $_SESSION['user_id'];
$ride_id = $_POST['ride_id'];

// Check if ride is still available (not taken by another driver)
$check = $conn->query("SELECT * FROM rides WHERE ride_id='$ride_id' AND driver_id IS NULL AND status='requested'");

if($check->num_rows > 0) {
    // Accept the ride
    $sql = "UPDATE rides SET driver_id='$driver_id', status='accepted' WHERE ride_id='$ride_id'";
    
    if($conn->query($sql)) {
        echo "Success";
    } else {
        echo "Database error: " . $conn->error;
    }
} else {
    echo "Ride no longer available";
}
?>