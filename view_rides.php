<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get all rides for the user
$rides_query = "SELECT r.*, 
                CASE 
                    WHEN r.rider_id = '$user_id' THEN 'As Rider'
                    ELSE 'As Driver'
                END as role_type,
                u.name as other_party_name
                FROM rides r 
                LEFT JOIN users u ON (CASE 
                    WHEN r.rider_id = '$user_id' THEN u.user_id = r.driver_id
                    ELSE u.user_id = r.rider_id
                END)
                WHERE r.rider_id = '$user_id' OR r.driver_id = '$user_id' 
                ORDER BY r.created_at DESC";
$rides_result = $conn->query($rides_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rides - RideApp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-timeline {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        .timeline-step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        .timeline-step .dot {
            width: 30px;
            height: 30px;
            background: #ddd;
            border-radius: 50%;
            margin: 0 auto 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .timeline-step.active .dot {
            background: #06d6a0;
            color: white;
        }
        .timeline-step.completed .dot {
            background: #06d6a0;
            color: white;
        }
        .timeline-step .label {
            font-size: 12px;
            color: #666;
        }
        .timeline-step.active .label {
            color: #06d6a0;
            font-weight: bold;
        }
        .refresh-btn {
            background: #667eea;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
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
            <p>Loading...</p>
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
                <a href="driver_panel.php"><i class="fa fa-tachometer"></i> Driver Panel</a>
            <?php endif; ?>
            
            <a href="view_rides.php"><i class="fa fa-list"></i> My Rides</a>
            <a href="profile.php"><i class="fa fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2><i class="fa fa-history"></i> My Ride History</h2>
                <div>
                    <button onclick="location.reload()" class="refresh-btn">
                        <i class="fa fa-sync-alt"></i> Refresh
                    </button>
                    <?php if($role == 'rider'): ?>
                    <a href="book_ride.html" class="btn btn-primary" style="margin-left: 10px;">
                        <i class="fa fa-plus"></i> Book New Ride
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($rides_result->num_rows > 0): ?>
                <?php while($ride = $rides_result->fetch_assoc()): ?>
                <div class="ride-card">
                    <div class="ride-header">
                        <div>
                            <strong>Ride #<?php echo $ride['ride_id']; ?></strong>
                            <span style="margin-left: 10px; font-size: 0.85rem; color: var(--gray);">
                                <i class="fa fa-tag"></i> <?php echo $ride['role_type']; ?>
                            </span>
                        </div>
                        <span class="ride-status status-<?php echo $ride['status']; ?>">
                            <?php 
                            $status_text = ucfirst($ride['status']);
                            if($ride['status'] == 'requested' && $role == 'rider') {
                                $status_text = "Waiting for driver...";
                            } elseif($ride['status'] == 'accepted' && $role == 'rider') {
                                $status_text = "Driver Accepted!";
                            }
                            echo $status_text;
                            ?>
                        </span>
                    </div>
                    
                    <div class="ride-info">
                        <div class="info-item">
                            <i class="fa fa-map-marker-alt"></i>
                            <div>
                                <strong>Pickup</strong><br>
                                <?php echo $ride['pickup']; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-arrow-down"></i>
                            <div>
                                <strong>Drop</strong><br>
                                <?php echo $ride['drop_location']; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-calendar"></i>
                            <div>
                                <strong>Date & Time</strong><br>
                                <?php echo date('d M Y, h:i A', strtotime($ride['created_at'])); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-money-bill"></i>
                            <div>
                                <strong>Fare</strong><br>
                                ₹<?php echo number_format($ride['fare'], 2); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline for Riders -->
                    <?php if($role == 'rider' && $ride['status'] != 'completed' && $ride['status'] != 'cancelled'): ?>
                    <div class="status-timeline">
                        <div class="timeline-step <?php echo in_array($ride['status'], ['requested', 'accepted', 'ongoing', 'completed']) ? 'completed' : ''; ?>">
                            <div class="dot"><i class="fa fa-clock"></i></div>
                            <div class="label">Requested</div>
                        </div>
                        <div class="timeline-step <?php echo in_array($ride['status'], ['accepted', 'ongoing', 'completed']) ? 'active' : ''; ?>">
                            <div class="dot"><i class="fa fa-check"></i></div>
                            <div class="label">Accepted</div>
                        </div>
                        <div class="timeline-step <?php echo in_array($ride['status'], ['ongoing', 'completed']) ? 'active' : ''; ?>">
                            <div class="dot"><i class="fa fa-play"></i></div>
                            <div class="label">Started</div>
                        </div>
                        <div class="timeline-step <?php echo $ride['status'] == 'completed' ? 'active' : ''; ?>">
                            <div class="dot"><i class="fa fa-flag-checkered"></i></div>
                            <div class="label">Completed</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($ride['status'] == 'accepted' && $role == 'rider'): ?>
                    <div style="margin-top: 15px; padding: 10px; background: #e8f5e9; border-radius: 8px; text-align: center;">
                        <i class="fa fa-check-circle" style="color: #06d6a0;"></i>
                        A driver has accepted your ride and is on the way!
                    </div>
                    <?php endif; ?>

                    <?php if($ride['status'] == 'completed' && $ride['rating_given'] == 0 && $role == 'rider'): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
                        <button onclick="showRatingModal(<?php echo $ride['ride_id']; ?>)" class="btn btn-primary" style="width: auto;">
                            <i class="fa fa-star"></i> Rate this ride
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <i class="fa fa-car" style="font-size: 4rem; color: var(--gray); margin-bottom: 20px;"></i>
                    <h3>No Rides Found</h3>
                    <p>You haven't taken any rides yet.</p>
                    <?php if($role == 'rider'): ?>
                    <a href="book_ride.html" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fa fa-taxi"></i> Book Your First Ride
                    </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rating Modal -->
    <div id="ratingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div class="card" style="max-width: 400px; margin: 20px;">
            <h3>Rate Your Ride</h3>
            <div id="ratingStars" style="margin: 20px 0; text-align: center;">
                <i class="fa fa-star star" data-rating="1"></i>
                <i class="fa fa-star star" data-rating="2"></i>
                <i class="fa fa-star star" data-rating="3"></i>
                <i class="fa fa-star star" data-rating="4"></i>
                <i class="fa fa-star star" data-rating="5"></i>
            </div>
            <textarea id="reviewComment" placeholder="Share your experience..." rows="3" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;"></textarea>
            <div style="margin-top: 20px;">
                <button onclick="submitRating()" class="btn btn-primary">Submit Rating</button>
                <button onclick="closeRatingModal()" class="btn">Cancel</button>
            </div>
            <input type="hidden" id="currentRideId">
        </div>
    </div>

    <script>
        let selectedRating = 0;
        let currentRideId = null;

        window.onload = function() {
            setTimeout(function() {
                document.getElementById("loader").style.opacity = "0";
                setTimeout(function() {
                    document.getElementById("loader").style.display = "none";
                }, 500);
            }, 500);
        }

        function showRatingModal(rideId) {
            currentRideId = rideId;
            document.getElementById('ratingModal').style.display = 'flex';
            
            // Setup star rating
            const stars = document.querySelectorAll('#ratingStars .star');
            stars.forEach(star => {
                star.onclick = function() {
                    selectedRating = this.dataset.rating;
                    stars.forEach(s => s.classList.remove('active'));
                    for(let i = 0; i < selectedRating; i++) {
                        stars[i].classList.add('active');
                    }
                };
            });
        }

        function closeRatingModal() {
            document.getElementById('ratingModal').style.display = 'none';
            selectedRating = 0;
            document.getElementById('reviewComment').value = '';
        }

        function submitRating() {
            if(selectedRating === 0) {
                alert('Please select a rating!');
                return;
            }
            
            const comment = document.getElementById('reviewComment').value;
            
            fetch('rate_ride.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ride_id=${currentRideId}&rating=${selectedRating}&comment=${encodeURIComponent(comment)}`
            })
            .then(response => response.text())
            .then(data => {
                alert('Thank you for your rating!');
                location.reload();
            });
        }
    </script>
</body>
</html>