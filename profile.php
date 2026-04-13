<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM users WHERE user_id='$user_id'");
$user = $user_query->fetch_assoc();

$wallet = $conn->query("SELECT balance FROM wallet WHERE user_id='$user_id'")->fetch_assoc();

// Get user's reviews
$reviews = $conn->query("SELECT r.*, u.name as from_user 
                         FROM reviews r 
                         JOIN users u ON r.from_user_id = u.user_id 
                         WHERE r.to_user_id='$user_id' 
                         ORDER BY r.created_at DESC 
                         LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - RideApp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div id="loader">
        <div class="loader-content">
            <div class="spinner"></div>
            <p>Loading...</p>
        </div>
    </div>

    <nav class="navbar">
        <div class="logo">
            <i class="fa fa-car"></i> RideApp
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fa fa-home"></i> Home</a>
            <a href="book_ride.html"><i class="fa fa-taxi"></i> Book Ride</a>
            <a href="view_rides.php"><i class="fa fa-list"></i> My Rides</a>
            <a href="profile.php"><i class="fa fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa fa-user" style="font-size: 3rem; color: white;"></i>
                </div>
                <h2><?php echo $user['name']; ?></h2>
                <div class="rating">
                    <?php 
                    $rating = round($user['rating'], 1);
                    for($i = 1; $i <= 5; $i++): ?>
                        <i class="fa fa-star <?php echo $i <= $rating ? 'active' : ''; ?>" style="color: <?php echo $i <= $rating ? '#ffd700' : '#ddd'; ?>"></i>
                    <?php endfor; ?>
                    <span>(<?php echo $rating; ?>)</span>
                </div>
            </div>

            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <i class="fa fa-envelope"></i>
                    <h3><?php echo $user['email']; ?></h3>
                    <p>Email</p>
                </div>
                <div class="stat-card">
                    <i class="fa fa-phone"></i>
                    <h3><?php echo $user['phone']; ?></h3>
                    <p>Phone</p>
                </div>
                <div class="stat-card">
                    <i class="fa fa-wallet"></i>
                    <h3>₹<?php echo number_format($wallet['balance'], 2); ?></h3>
                    <p>Wallet Balance</p>
                </div>
                <div class="stat-card">
                    <i class="fa fa-calendar"></i>
                    <h3><?php echo date('d M Y', strtotime($user['joined_date'])); ?></h3>
                    <p>Member Since</p>
                </div>
            </div>

            <?php if($reviews && $reviews->num_rows > 0): ?>
            <div>
                <h3><i class="fa fa-comments"></i> Recent Reviews</h3>
                <?php while($review = $reviews->fetch_assoc()): ?>
                <div class="ride-card">
                    <div class="ride-header">
                        <strong><?php echo $review['from_user']; ?></strong>
                        <div class="rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fa fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>" style="color: <?php echo $i <= $review['rating'] ? '#ffd700' : '#ddd'; ?>; font-size: 0.8rem;"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p><?php echo $review['comment']; ?></p>
                    <small style="color: var(--gray);"><?php echo date('d M Y', strtotime($review['created_at'])); ?></small>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                document.getElementById("loader").style.opacity = "0";
                setTimeout(function() {
                    document.getElementById("loader").style.display = "none";
                }, 500);
            }, 500);
        }
    </script>
</body>
</html>