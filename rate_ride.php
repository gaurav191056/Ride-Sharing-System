<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$ride_id = $_POST['ride_id'];
$rating = $_POST['rating'];
$comment = mysqli_real_escape_string($conn, $_POST['comment']);
$user_id = $_SESSION['user_id'];

// Get ride details
$ride_query = $conn->query("SELECT * FROM rides WHERE ride_id='$ride_id'");

if(!$ride_query || $ride_query->num_rows == 0){
    die("Ride not found");
}

$ride = $ride_query->fetch_assoc();

$to_user_id = ($ride['rider_id'] == $user_id) ? $ride['driver_id'] : $ride['rider_id'];

// Insert review
$sql = "INSERT INTO reviews (ride_id, from_user_id, to_user_id, rating, comment) 
        VALUES ('$ride_id', '$user_id', '$to_user_id', '$rating', '$comment')";

if(!$conn->query($sql)){
    die("Insert Error: " . $conn->error);
}

// Update ride rating given status
$conn->query("UPDATE rides SET rating_given = 1 WHERE ride_id='$ride_id'");

// Update user average rating
$avg_res = $conn->query("SELECT AVG(rating) as avg FROM reviews WHERE to_user_id='$to_user_id'");
$row = $avg_res->fetch_assoc();
$avg_rating = $row['avg'] ?? 0;

$conn->query("UPDATE users SET rating = '$avg_rating' WHERE user_id='$to_user_id'");

echo "Success";
?>