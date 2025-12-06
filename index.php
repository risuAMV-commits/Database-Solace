<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resort Booking System</title>
    <link rel="stylesheet" href="assets/index.css">
</head>

<body>

<?php if (isset($_SESSION['customer'])): ?>
    <!-- CUSTOMER HAMBURGER -->
    <input type="checkbox" id="menu-toggle" class="menu-toggle">
    <label for="menu-toggle" class="menu-icon">☰</label>

    <div class="sidebar">
        <a href="index.php">🏠 Home</a>
        <a href="customer_dashboard.php">📋 Dashboard</a>
        <a href="customer_rooms.php">🛏 Book a Room</a>
        <a href="profile.php">👤 My Profile</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="content customer-bg">
        <div class="customer-content-wrapper">
            <h1>Welcome to the Resort, <?= $_SESSION['customer']; ?>!</h1>
            <p>You are signed in as a customer.</p>
            <p><a href="customer_rooms.php" class="admin-btn">Browse Available Rooms</a></p>
        </div>
    </div>

<?php elseif (isset($_SESSION['admin'])): ?>
    <!-- ADMIN HAMBURGER -->
    <input type="checkbox" id="menu-toggle" class="menu-toggle">
    <label for="menu-toggle" class="menu-icon">☰</label>

    <div class="sidebar">
        <a href="index.php">🏠 Home</a>
        <a href="admin_panel.php">📊 Dashboard</a>
        <a href="manage_customers.php">👥 Manage Customers</a>
        <a href="manage_rooms.php">🏨 Manage Rooms</a>
        <a href="manage_bookings.php">📅 Manage Bookings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="content admin-bg">
        <div class="dashboard-wrapper">
            <h1>Welcome Admin!</h1>
            <p>Use the menu to manage the system.</p>
            <p><a href="admin_panel.php" class="admin-btn">Go to Dashboard</a></p>
        </div>
    </div>

<?php else: ?>
    <!-- PUBLIC HOMEPAGE - MODERN DESIGN -->

    <!-- Fixed Navigation Bar -->
    <div class="top-nav">
        <div class="logo">Solace Resort</div>

        <div class="nav-links">
            <a href="#rooms">Rooms</a>
            <a href="#about">About</a>
            <a href="login.php">Login</a>
            <a href="register.php" class="cta-btn">Get Started</a>
            <a href="admin_login.php" class="admin-btn">Admin</a>
        </div>
    </div>

    <!-- Modern Hero Section -->
    <div class="hero-landing-modern">
        <div class="hero-content-modern">
            <div class="welcome-header">
                <h1>Welcome to Solace Resort!</h1>
                <p>Discover comfort, peace, and premium resort living</p>
            </div>
        </div>
    </div>

    <!-- Available Rooms Section -->
    <section id="rooms" class="rooms-showcase-section">
        <div class="rooms-showcase-container">
            <div class="section-header">
                <h2>Available Rooms</h2>
                <a href="customer_rooms.php" class="view-all-link">View All →</a>
            </div>

            <div class="room-grid-modern">
                <?php
                // Check if database connection exists
                if (isset($pdo)):
                    try {
                        // Fetch available rooms from database using PDO
                        $sql = "SELECT * FROM room WHERE Availability = 'Available' ORDER BY Room_Type DESC, Room_ID ASC LIMIT 4";
                        $stmt = $pdo->query($sql);
                        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($rooms) > 0):
                            foreach ($rooms as $room):
                ?>
                            <div class="room-card-modern">
                                <?php 
                                // Check if it's a VIP room (you can adjust this logic based on your Room_Type)
                                $isVIP = (strtolower($room['Room_Type']) === 'vip' || $room['Category'] === 'VIP');
                                if ($isVIP): 
                                ?>
                                    <span class="badge-vip">👑 VIP</span>
                                <?php endif; ?>
                                
                                <span class="badge-available">Available</span>
                                
                                <?php if (!empty($room['Image'])): ?>
                                    <img src="assets/rooms/<?= htmlspecialchars($room['Image']); ?>" alt="Room <?= htmlspecialchars($room['Room_ID']); ?>" class="room-img-modern">
                                <?php else: ?>
                                    <div class="no-img-modern">Room <?= htmlspecialchars($room['Room_ID']); ?></div>
                                <?php endif; ?>
                                
                                <div class="room-details-modern">
                                    <h3>Room <?= htmlspecialchars($room['Room_ID']); ?></h3>
                                    <p class="room-type"><?= htmlspecialchars($room['Room_Type']); ?> - <?= htmlspecialchars($room['Category']); ?></p>
                                    <p class="room-desc"><?= htmlspecialchars($room['Description']); ?></p>
                                    
                                    <div class="room-footer">
                                        <div class="price-section">
                                            <span class="price-label">PER NIGHT</span>
                                            <span class="price-amount">₱<?= number_format($room['Price_Per_Night'], 2); ?></span>
                                        </div>
                                        <a href="register.php" class="book-btn-modern">Book Now</a>
                                    </div>
                                </div>
                            </div>
                <?php 
                            endforeach;
                        else:
                ?>
                            <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                                <p style="color: #1f2937; font-size: 18px;">No rooms available at the moment. Please check back later!</p>
                            </div>
                <?php 
                        endif;
                    } catch (PDOException $e) {
                ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                            <p style="color: #dc2626; font-size: 18px; font-weight: 600;">Unable to load rooms.</p>
                            <p style="color: #6b7280; font-size: 14px; margin-top: 10px;">Database error: <?= htmlspecialchars($e->getMessage()); ?></p>
                        </div>
                <?php
                    }
                else:
                    // Database connection not available
                ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                        <p style="color: #dc2626; font-size: 18px; font-weight: 600;">Database connection not available.</p>
                        <p style="color: #6b7280; font-size: 14px; margin-top: 10px;">Please check config.php configuration.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section-modern">
        <div class="about-wrapper-modern">
            <h2 class="about-title">About Solace Resort</h2>

            <p class="about-description">
                Welcome to our resort — a peaceful escape offering modern rooms, premium amenities,
                and a relaxing environment designed for comfort and memorable vacations. Experience
                luxury living with world-class service and breathtaking views.
            </p>

            <div class="about-features">
                <div class="feature-item">
                    <span class="feature-icon">🏊</span>
                    <h4>Premium Pool</h4>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🍽️</span>
                    <h4>Fine Dining</h4>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">💆</span>
                    <h4>Spa & Wellness</h4>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🌴</span>
                    <h4>Beach Access</h4>
                </div>
            </div>

            <div class="about-cta">
                <a href="register.php" class="about-btn-modern">Start Your Journey</a>
            </div>
        </div>
    </section>

<?php endif; ?>

</body>
</html>