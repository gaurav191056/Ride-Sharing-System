<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get user stats
$total_rides = $conn->query("SELECT COUNT(*) as count FROM rides WHERE rider_id='$user_id' OR driver_id='$user_id'")->fetch_assoc()['count'];
$completed_rides = $conn->query("SELECT COUNT(*) as count FROM rides WHERE (rider_id='$user_id' OR driver_id='$user_id') AND status='completed'")->fetch_assoc()['count'];
$wallet = $conn->query("SELECT balance FROM wallet WHERE user_id='$user_id'")->fetch_assoc()['balance'];

// Get pending ride requests for driver
$pending_requests = 0;
if($role == 'driver') {
    $pending = $conn->query("SELECT COUNT(*) as count FROM rides WHERE driver_id IS NULL AND status='requested'");
    $pending_requests = $pending->fetch_assoc()['count'];
}

// Get recent rides
$recent_rides = $conn->query("SELECT * FROM rides WHERE rider_id='$user_id' OR driver_id='$user_id' ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RideApp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .notification-badge {
            background-color: #ef476f;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            margin-left: 5px;
        }
        .refresh-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .refresh-btn:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div id="loader">
        <div class="loader-content">
            <div class="spinner"></div>
            <p>Loading Dashboard...</p>
        </div>
    </div>

    <nav class="navbar">
        <div class="logo">
            <i class="fa fa-car"></i> RideApp
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fa fa-home"></i> Home</a>
            
            <?php if($role == 'rider'): ?>
                <a href="book_ride.html"><i class="fa fa-taxi"></i> Book Ride</a>
            <?php endif; ?>
            
            <?php if($role == 'driver'): ?>
                <a href="driver_panel.php">
                    <i class="fa fa-tachometer"></i> Driver Panel
                    <?php if($pending_requests > 0): ?>
                        <span class="notification-badge"><?php echo $pending_requests; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            
            <a href="view_rides.php"><i class="fa fa-list"></i> My Rides</a>
            <a href="profile.php"><i class="fa fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div style="margin-bottom: 30px;">
            <h1 style="color: white;">Welcome back, <?php echo $_SESSION['name']; ?>!
                <button onclick="location.reload()" class="refresh-btn" title="Refresh manually">
                    <i class="fa fa-sync-alt"></i> Refresh
                </button>
            </h1>
            <p style="color: rgba(255,255,255,0.9);">
                <?php if($role == 'rider'): ?>
                    Ready for your next ride?
                <?php else: ?>
                    Ready to serve customers?
                <?php endif; ?>
            </p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fa fa-chart-line"></i>
                <h3><?php echo $total_rides; ?></h3>
                <p>Total Rides</p>
            </div>
            <div class="stat-card">
                <i class="fa fa-check-circle"></i>
                <h3><?php echo $completed_rides; ?></h3>
                <p>Completed Rides</p>
            </div>
            <div class="stat-card">
                <i class="fa fa-wallet"></i>
                <h3>₹<?php echo number_format($wallet, 2); ?></h3>
                <p>Wallet Balance</p>
            </div>
        </div>

        <?php if($role == 'driver' && $pending_requests > 0): ?>
        <div class="card" style="margin-bottom: 30px; background: linear-gradient(135deg, #ef476f, #f78c6e); color: white; text-align: center;">
            <i class="fa fa-bell" style="font-size: 2rem; margin-bottom: 10px;"></i>
            <h3>You have <?php echo $pending_requests; ?> new ride request(s)!</h3>
            <a href="driver_panel.php" class="btn" style="background: white; color: #ef476f; margin-top: 10px;">
                View Requests Now
            </a>
        </div>
        <?php endif; ?>

        <?php if($role == 'rider'): ?>
        <div class="card" style="margin-bottom: 30px; text-align: center;">
            <h2><i class="fa fa-taxi"></i> Quick Booking</h2>
            <p>Book a ride in seconds</p>
            <a href="book_ride.html" class="btn btn-primary" style="margin-top: 15px;">
                <i class="fa fa-plus"></i> Book Now
            </a>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fa fa-history"></i> Recent Rides</h2>
            <?php if($recent_rides->num_rows > 0): ?>
                <?php while($ride = $recent_rides->fetch_assoc()): ?>
                <div class="ride-card">
                    <div class="ride-header">
                        <strong>Ride #<?php echo $ride['ride_id']; ?></strong>
                        <span class="ride-status status-<?php echo $ride['status']; ?>">
                            <?php echo ucfirst($ride['status']); ?>
                        </span>
                    </div>
                    <div class="ride-info">
                        <div class="info-item">
                            <i class="fa fa-map-marker-alt"></i>
                            <span>From: <?php echo $ride['pickup']; ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-flag-checkered"></i>
                            <span>To: <?php echo $ride['drop_location']; ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-money-bill"></i>
                            <span>Fare: ₹<?php echo $ride['fare']; ?></span>
                        </div>
                        <?php if($ride['status'] == 'requested' && $role == 'rider'): ?>
                        <div class="info-item">
                            <i class="fa fa-clock"></i>
                            <span style="color: #ffd166;">Waiting for driver to accept...</span>
                        </div>
                        <?php elseif($ride['status'] == 'accepted' && $role == 'rider'): ?>
                        <div class="info-item">
                            <i class="fa fa-check-circle"></i>
                            <span style="color: #06d6a0;">Driver has accepted your ride!</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: var(--gray);">No rides yet. 
                <?php if($role == 'rider'): ?>
                    Book your first ride!
                <?php else: ?>
                    Wait for ride requests.
                <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if($role == 'rider'): ?>
    <a href="book_ride.html" class="fab">
        <i class="fa fa-plus"></i>
    </a>
    <?php endif; ?>

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