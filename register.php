<?php
include "connect.php";

$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$password = $_POST['password'];
$role = mysqli_real_escape_string($conn, $_POST['role']);

// Check if email exists
$check = $conn->query("SELECT * FROM users WHERE email='$email'");
if($check->num_rows > 0) {
    die("<script>alert('Email already registered!'); window.location.href='register.html';</script>");
}

$sql = "INSERT INTO users (name, email, phone, password, role) VALUES ('$name','$email','$phone','$password','$role')";

if($conn->query($sql)) {
    $user_id = $conn->insert_id;
    
    // Create wallet for user
    $conn->query("INSERT INTO wallet (user_id, balance) VALUES ('$user_id', 0)");
    
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
        </style>
    </head>
    <body>
        <div class='success-container'>
            <div class='success-card'>
                <i class='fa fa-check-circle'></i>
                <h2>Registration Successful!</h2>
                <p>Welcome to RideApp, $name!</p>
                <a href='login.html' class='btn btn-primary' style='margin-top: 20px; display: inline-block;'>
                    <i class='fa fa-sign-in'></i> Login Now
                </a>
            </div>
        </div>
    </body>
    </html>";
} else {
    echo "Error: " . $conn->error;
}
?>