<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$ride_id = $_POST['ride_id'];
$status = $_POST['status'];
$driver_id = $_SESSION['user_id'];

// Verify driver owns this ride
$verify = $conn->query("SELECT * FROM rides WHERE ride_id='$ride_id' AND driver_id='$driver_id'");

if($verify->num_rows == 0) {
    echo "Unauthorized";
    exit();
}

$sql = "UPDATE rides SET status='$status' WHERE ride_id='$ride_id'";

if($conn->query($sql)) {
    if($status == 'completed') {
        $conn->query("UPDATE rides SET completed_at = NOW() WHERE ride_id='$ride_id'");
        
        // Get ride details for payment
        $ride = $conn->query("SELECT * FROM rides WHERE ride_id='$ride_id'")->fetch_assoc();
        
        // Update payment status
        $conn->query("UPDATE payments SET status='completed' WHERE ride_id='$ride_id'");
        
        // Update driver earnings (80% of fare)
        $driver_earning = $ride['fare'] * 0.8;
        $conn->query("UPDATE wallet SET balance = balance + $driver_earning WHERE user_id='$driver_id'");
        
        // Update total rides count
        $conn->query("UPDATE users SET total_rides = total_rides + 1 WHERE user_id='$driver_id'");
        $conn->query("UPDATE users SET total_rides = total_rides + 1 WHERE user_id='{$ride['rider_id']}'");
    }
    echo "Success";
} else {
    echo "Error: " . $conn->error;
}
?>