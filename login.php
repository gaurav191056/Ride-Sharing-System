<?php
session_start();
include "connect.php";

// Get data
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

// SQL query
$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

// Error check
if(!$result){
    die("SQL Error: " . $conn->error);
}

// Check user
if($row = $result->fetch_assoc()) {

    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['name'] = $row['name'];
    $_SESSION['role'] = $row['role'];
    $_SESSION['email'] = $row['email'];

    header("Location: dashboard.php");
    exit();

} else {
    echo "<h2 style='color:red;text-align:center;'>Invalid Email or Password</h2>";
    echo "<a href='login.html' style='display:block;text-align:center;'>Try Again</a>";
}
?>