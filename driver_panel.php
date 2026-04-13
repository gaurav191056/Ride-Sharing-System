 
<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'driver') {
    header("Location: login.html");
    exit();
}

$driver_id = $_SESSION['user_id'];

// Get pending ride requests (where driver_id is NULL and status is requested)
$pending_requests = $conn->query("
    SELECT * FROM rides 
   WHERE (driver_id IS NULL OR driver_id = 0) AND LOWER(status) = 'requested'
    ORDER BY created_at ASC
");

// Get driver's accepted/ongoing rides
$my_active_rides = $conn->query("
    SELECT * FROM rides 
    WHERE driver_id = '$driver_id' AND status IN ('accepted', 'ongoing')
    ORDER BY created_at DESC
");

// Get driver's completed rides for history
$completed_rides = $conn->query("
    SELECT * FROM rides 
    WHERE driver_id = '$driver_id' AND status = 'completed'
    ORDER BY created_at DESC LIMIT 5
");

// Get driver's vehicle info
$vehicle = $conn->query("SELECT * FROM vehicles WHERE driver_id='$driver_id'")->fetch_assoc();
$reviews = $conn->query("
    SELECT r.*, u.name as rider_name 
    FROM reviews r
    JOIN users u ON r.from_user_id = u.user_id
    WHERE r.to_user_id = '$driver_id'
    ORDER BY r.created_at DESC
    LIMIT 5
");

if(!$reviews){
    die("Query Error: " . $conn->error);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Panel - RideApp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .request-card {
            background: linear-gradient(135deg, #fff, #f8f9fa);
            border-left: 4px solid #06d6a0;
            transition: all 0.3s;
        }
        .request-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-accept {
            background: #06d6a0;
            color: white;
        }
        .btn-accept:hover {
            background: #05c090;
        }
        .section-title {
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .refresh-btn {
            background: #667eea;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            font-size: 14px;
        }
        .refresh-btn:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div id="loader">
        <div class="loader-content">
            <div class="spinner"></div>
            <p>Loading Driver Panel...</p>
        </div>
    </div>

    <nav class="navbar">
        <div class="logo">
            <i class="fa fa-car"></i> RideApp - Driver Mode
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fa fa-home"></i> Home</a>
            <a href="driver_panel.php"><i class="fa fa-tachometer"></i> Driver Panel</a>
            <a href="view_rides.php"><i class="fa fa-list"></i> My Rides</a>
            <a href="profile.php"><i class="fa fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Manual Refresh Button -->
        <div style="text-align: right; margin-bottom: 20px;">
            <button onclick="location.reload()" class="refresh-btn">
                <i class="fa fa-sync-alt"></i> Refresh Page
            </button>
        </div>

        <!-- Driver Status Card -->
        <div class="card" style="margin-bottom: 30px;">
            <h3><i class="fa fa-info-circle"></i> Your Status</h3>
            <div style="flex: 1; padding: 15px; background: #f8f9fa; border-radius: 12px;">
    <i class="fa fa-star"></i> Rating: 
    <strong>
        <?php 
       $res = $conn->query("SELECT rating FROM users WHERE user_id='$driver_id'");
$row = $res->fetch_assoc();
$rating = $row['rating'] ?? 0;

echo number_format($rating, 1);
        ?>
    </strong>
</div>
            <div style="display: flex; gap: 15px; margin-top: 15px; flex-wrap: wrap;">
                <div style="flex: 1; padding: 15px; background: #f8f9fa; border-radius: 12px;">
                    <i class="fa fa-car"></i> Vehicle: 
                    <strong><?php echo $vehicle ? $vehicle['model'] . ' (' . $vehicle['number'] . ')' : 'Not registered'; ?></strong>
                </div>
                <div style="flex: 1; padding: 15px; background: #f8f9fa; border-radius: 12px;">
                    <i class="fa fa-users"></i> Capacity: 
                    <strong><?php echo $vehicle ? $vehicle['capacity'] . ' seats' : 'N/A'; ?></strong>
                </div>
                <div style="flex: 1; padding: 15px; background: #f8f9fa; border-radius: 12px;">
                    <i class="fa fa-toggle-on"></i> Status: 
                    <strong style="color: #06d6a0;">Online & Available</strong>
                </div>
            </div>
        </div>
           <!-- Completed Rides History -->
        <?php if($completed_rides->num_rows > 0): ?>
        <div class="card">
            <h2><i class="fa fa-history"></i> Recently Completed</h2>
            <?php while($ride = $completed_rides->fetch_assoc()): ?>
            <div class="ride-card" style="opacity: 0.8;">
                <div class="ride-header">
                    <strong>Ride #<?php echo $ride['ride_id']; ?></strong>
                    <span class="ride-status status-completed">COMPLETED</span>
                </div>
                <div class="ride-info">
                    <div class="info-item">
                        <i class="fa fa-map-marker-alt"></i>
                        <span><?php echo $ride['pickup']; ?> → <?php echo $ride['drop_location']; ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fa fa-money-bill"></i>
                        <span>Earned: ₹<?php echo $ride['fare'] ; ?></span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
<!-- Driver Reviews -->
<?php if($reviews->num_rows > 0): ?>
<div class="card">
    <h2><i class="fa fa-star"></i> Recent Reviews</h2>

    <?php while($review = $reviews->fetch_assoc()): ?>
    <div class="ride-card" style="margin-bottom: 15px;">
        <div class="ride-header">
            <strong><?php echo $review['rider_name']; ?></strong>
            <span style="color: #f4b400;">
                <?php echo str_repeat("★", $review['rating']); ?>
                <?php echo str_repeat("☆", 5 - $review['rating']); ?>
            </span>
        </div>

        <div style="margin-top: 10px; color: #555;">
            <?php echo $review['comment']; ?>
        </div>

        <small style="color: #999;">
            <?php echo date('d M Y', strtotime($review['created_at'])); ?>
        </small>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>
        <!-- Pending Ride Requests -->
        <div class="card">
            <h2><i class="fa fa-bell"></i> New Ride Requests 
                <?php if($pending_requests->num_rows > 0): ?>
                    <span style="background: #ef476f; color: white; padding: 2px 10px; border-radius: 20px; font-size: 14px;">
                        <?php echo $pending_requests->num_rows; ?> new
                    </span>
                <?php endif; ?>
            </h2>
            
            <?php if($pending_requests->num_rows > 0): ?>
                <?php while($request = $pending_requests->fetch_assoc()): ?>
                <div class="ride-card request-card">
                    <div class="ride-header">
                        <strong><i class="fa fa-hashtag"></i> Request #<?php echo $request['ride_id']; ?></strong>
                        <span class="ride-status status-requested">PENDING</span>
                    </div>
                    <div class="ride-info">
                        <div class="info-item">
                            <i class="fa fa-map-marker-alt"></i>
                            <div>
                                <strong>Pickup Location</strong><br>
                                <?php echo $request['pickup']; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-flag-checkered"></i>
                            <div>
                                <strong>Drop Location</strong><br>
                                <?php echo $request['drop_location']; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-money-bill"></i>
                            <div>
                                <strong>Fare</strong><br>
                                ₹<?php echo number_format($request['fare'], 2); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-clock"></i>
                            <div>
                                <strong>Requested At</strong><br>
                                <?php echo date('h:i A', strtotime($request['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <button onclick="acceptRide(<?php echo $request['ride_id']; ?>)" class="btn btn-accept" style="width: 100%; margin-top: 10px;">
                        <i class="fa fa-check-circle"></i> Accept Ride
                    </button>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px 20px;">
                    <i class="fa fa-check-circle" style="font-size: 3rem; color: #06d6a0; margin-bottom: 15px;"></i>
                    <h3>No pending requests</h3>
                    <p>All caught up! New ride requests will appear here.</p>
                    <button onclick="location.reload()" class="btn btn-primary" style="margin-top: 10px;">
                        <i class="fa fa-sync-alt"></i> Check for new requests
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Active Rides -->
        <?php if($my_active_rides->num_rows > 0): ?>
        <div class="card">
            <h2><i class="fa fa-play-circle"></i> My Active Rides</h2>
            <?php while($ride = $my_active_rides->fetch_assoc()): ?>
            <div class="ride-card">
                <div class="ride-header">
                    <strong>Ride #<?php echo $ride['ride_id']; ?></strong>
                    <span class="ride-status status-<?php echo $ride['status']; ?>">
                        <?php echo strtoupper($ride['status']); ?>
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
                </div>
                <?php if($ride['status'] == 'accepted'): ?>
                <button onclick="startRide(<?php echo $ride['ride_id']; ?>)" class="btn btn-primary" style="width: 100%;">
                    <i class="fa fa-play"></i> Start Ride
                </button>
                <?php elseif($ride['status'] == 'ongoing'): ?>
                <button onclick="completeRide(<?php echo $ride['ride_id']; ?>)" class="btn btn-success" style="width: 100%;">
                    <i class="fa fa-flag-checkered"></i> Complete Ride
                </button>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
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

        function acceptRide(rideId) {
            if(confirm('Accept this ride request?')) {
                fetch('accept_ride.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'ride_id=' + rideId
                })
                .then(response => response.text())
                .then(data => {
                    if(data.trim() == 'Success') {
                        alert('Ride accepted successfully!');
                        location.reload();
                    } else {
                        alert('Error accepting ride: ' + data);
                    }
                })
                .catch(error => {
                    alert('Network error: ' + error);
                });
            }
        }

        function startRide(rideId) {
            if(confirm('Start the ride now?')) {
                updateRideStatus(rideId, 'ongoing');
            }
        }

        function completeRide(rideId) {
            if(confirm('Complete this ride?')) {
                updateRideStatus(rideId, 'completed');
            }
        }

        function updateRideStatus(rideId, status) {
            fetch('update_ride_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ride_id=' + rideId + '&status=' + status
            })
            .then(response => response.text())
            .then(data => {
                if(data.trim() == 'Success') {
                    alert('Ride status updated!');
                    location.reload();
                } else {
                    alert('Error updating status: ' + data);
                }
            });
        }
    </script>
</body>
</html>