<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$rider_id = $_SESSION['user_id'];
$pickup = mysqli_real_escape_string($conn, $_POST['pickup']);
$drop = mysqli_real_escape_string($conn, $_POST['drop']);
$vehicle_type = mysqli_real_escape_string($conn, $_POST['vehicle_type']);
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

// Calculate fare based on vehicle type
$fare_rates = ['car' => 100, 'suv' => 150, 'auto' => 80, 'bike' => 50];
$fare = $fare_rates[$vehicle_type] ?? 100;

// IMPORTANT: Set driver_id as NULL initially (waiting for driver acceptance)
$status = "requested";
    
$sql = "INSERT INTO rides (rider_id, driver_id, pickup, drop_location, status, fare, payment_method) 
        VALUES ('$rider_id', NULL, '$pickup', '$drop', '$status', '$fare', '$payment_method')";

if($conn->query($sql)) {
    $ride_id = $conn->insert_id;
    
    // Create payment record
    $conn->query("INSERT INTO payments (ride_id, amount, method, status) 
                  VALUES ('$ride_id', '$fare', '$payment_method', 'pending')");
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='style.css'>
        <style>
            .success-container {
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                background: linear-gradient(135deg, #667eea, #764ba2);
            }
            .success-card {
                background: white;
                padding: 40px;
                border-radius: 20px;
                text-align: center;
                animation: fadeInUp 0.6s ease;
                max-width: 400px;
            }
            .success-card i {
                font-size: 4rem;
                color: #06d6a0;
                margin-bottom: 20px;
            }
            .ride-details {
                text-align: left;
                margin: 20px 0;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 12px;
            }
        </style>
    </head>
    <body>
        <div class='success-container'>
            <div class='success-card'>
                <i class='fa fa-check-circle'></i>
                <h2>Ride Request Sent!</h2>
                <div class='ride-details'>
                    <p><strong>Ride ID:</strong> #$ride_id</p>
                    <p><strong>Pickup:</strong> $pickup</p>
                    <p><strong>Drop:</strong> $drop</p>
                    <p><strong>Fare:</strong> ₹$fare</p>
                    <p><strong>Status:</strong> Waiting for driver to accept</p>
                </div>
                <p style='color: #666; margin: 10px 0;'>A nearby driver will accept your request shortly.</p>
                <a href='dashboard.php' class='btn btn-primary'>Go to Dashboard</a>
                <a href='view_rides.php' class='btn' style='margin-top: 10px; display: inline-block;'>Track Ride Status</a>
            </div>
        </div>
    </body>
    </html>";
} else {
    echo "Error: " . $conn->error;
}
?>