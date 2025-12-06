<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customerID = $_SESSION['customer_id'];

/* Fetch customer */
$stmt = $pdo->prepare("SELECT Name, VIP_Status FROM customer WHERE Customer_ID = ?");
$stmt->execute([$customerID]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

$isVIP = ($customer['VIP_Status'] === "VIP");

/* Fetch Available rooms based on VIP status */
if ($isVIP) {
    // VIP customers see all available rooms
    $roomsQuery = $pdo->prepare("
        SELECT * FROM room 
        WHERE Availability = 'Available'
        ORDER BY 
            CASE WHEN Category = 'VIP' THEN 0 ELSE 1 END,
            Room_ID DESC
    ");
} else {
    // Regular customers see only non-VIP available rooms
    $roomsQuery = $pdo->prepare("
        SELECT * FROM room 
        WHERE Availability = 'Available' 
        AND (Category <> 'VIP' OR Category IS NULL)
        ORDER BY Room_ID DESC
    ");
}
$roomsQuery->execute();
$availableRooms = $roomsQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Solace Resort</title>
    <link rel="stylesheet" href="assets/customer_dashboard.css">
</head>

<body class="customer-bg">

<!-- Hamburger Menu -->
<input type="checkbox" id="menu-toggle" class="menu-toggle">
<label for="menu-toggle" class="menu-icon">☰</label>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Customer Menu</h2>
    </div>
    <a href="customer_dashboard.php" class="active">
        <span class="icon">📋</span>
        <span>Dashboard</span>
    </a>
    <a href="customer_rooms.php">
        <span class="icon">🛏️</span>
        <span>Book a Room</span>
    </a>
    <a href="profile.php">
        <span class="icon">👤</span>
        <span>Profile</span>
    </a>
    <a href="logout.php">
        <span class="icon">🚪</span>
        <span>Logout</span>
    </a>
</div>

<!-- Content -->
<div class="content">
    <div class="page-wrapper">
        
        <!-- Welcome Header -->
        <div class="welcome-header">
            <div>
                <h1>👋 Welcome back, <?= htmlspecialchars($customer['Name']); ?>!</h1>
                <p class="welcome-subtitle">Manage your bookings and explore available rooms</p>
            </div>
            <div class="vip-badge-container">
                <?php if ($isVIP): ?>
                    <div class="vip-badge vip-active">
                        <span class="badge-icon">👑</span>
                        <span>VIP Member</span>
                    </div>
                    <p class="vip-benefits">10% Discount • Priority Booking • Free Upgrade</p>
                <?php else: ?>
                    <div class="vip-badge regular">
                        <span class="badge-icon">⭐</span>
                        <span>Standard Member</span>
                    </div>
                    <p class="vip-benefits">Upgrade to VIP for exclusive benefits</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Available Rooms Section -->
        <section class="rooms-section">
            <div class="section-header">
                <h2>🏨 Available Rooms</h2>
                <a href="customer_rooms.php" class="view-all">View All →</a>
            </div>

            <?php if (count($availableRooms) > 0): ?>
            <div class="rooms-grid">
                <?php foreach ($availableRooms as $room): ?>
                    <div class="room-card">
                        <div class="room-image-container">
                            <?php if (!empty($room['Image'])): ?>
                                <img src="assets/rooms/<?= htmlspecialchars($room['Image']); ?>" alt="Room <?= $room['Room_ID']; ?>" class="room-image">
                            <?php else: ?>
                                <div class="room-no-image">
                                    <span>🛏️</span>
                                    <p>No Image Available</p>
                                </div>
                            <?php endif; ?>
                            
                            <span class="room-status-badge <?= $room['Availability'] === 'Available' ? 'available' : 'occupied'; ?>">
                                <?= htmlspecialchars($room['Availability']); ?>
                            </span>
                            
                            <?php if ($room['Category'] === 'VIP'): ?>
                                <span class="vip-only-badge">👑 VIP</span>
                            <?php endif; ?>
                        </div>

                        <div class="room-details">
                            <div class="room-title">Room <?= $room['Room_ID']; ?></div>
                            <div class="room-type"><?= htmlspecialchars($room['Room_Type']); ?></div>
                            
                            <?php if (!empty($room['Description'])): ?>
                                <p class="room-description"><?= htmlspecialchars($room['Description']); ?></p>
                            <?php endif; ?>

                            <div class="room-footer">
                                <div class="room-price">
                                    <span class="price-label">Per Night</span>
                                    <span class="price-value">₱<?= number_format($room['Price_Per_Night'], 2); ?></span>
                                </div>

                                <a href="booking_preview.php?room=<?= $room['Room_ID']; ?>" class="btn-book">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="table-container">
                <div class="no-bookings">
                    <div class="empty-state">
                        <div class="empty-icon">🛏️</div>
                        <p>No rooms available at the moment.</p>
                        <p style="font-size: 14px; color: #9ca3af;">Please check back later!</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>

    </div>
</div>

<script>
// Hamburger menu toggle
const menuToggle = document.getElementById('menu-toggle');
const menuIcon = document.querySelector('.menu-icon');
const sidebar = document.querySelector('.sidebar');

// Toggle menu when clicking the icon
menuIcon.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    menuToggle.checked = !menuToggle.checked;
    console.log('Menu clicked, checked:', menuToggle.checked);
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    if (menuToggle.checked && !sidebar.contains(event.target) && !menuIcon.contains(event.target)) {
        menuToggle.checked = false;
    }
});
</script>

</body>
</html>