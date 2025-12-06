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

/* Fetch Regular rooms */
$regularRoomsQ = $pdo->prepare("
    SELECT * FROM room 
    WHERE Category <> 'VIP' OR Category IS NULL
    ORDER BY Room_ID DESC
");
$regularRoomsQ->execute();
$regularRooms = $regularRoomsQ->fetchAll(PDO::FETCH_ASSOC);

/* Fetch VIP rooms (only if VIP customer) */
$vipRooms = [];
if ($isVIP) {
    $vipQuery = $pdo->prepare("
        SELECT * FROM room 
        WHERE Category = 'VIP'
        ORDER BY Room_ID DESC
    ");
    $vipQuery->execute();
    $vipRooms = $vipQuery->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms - Solace Resort</title>
    <link rel="stylesheet" href="assets/customer_rooms.css">
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
    <a href="customer_dashboard.php">
        <span class="icon">📋</span>
        <span>Dashboard</span>
    </a>
    <a href="customer_rooms.php" class="active">
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
    <div class="dashboard-container">
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>🏨 Available Rooms</h1>
                <p class="subtitle">Browse and book your perfect room</p>
            </div>
            <div class="vip-badge-container">
                <?php if ($isVIP): ?>
                    <div class="vip-badge vip-active">
                        <span class="badge-icon">👑</span>
                        <span>VIP Member</span>
                    </div>
                    <p class="vip-benefits">Access to exclusive VIP rooms</p>
                <?php else: ?>
                    <div class="vip-badge regular">
                        <span class="badge-icon">⭐</span>
                        <span>Standard Member</span>
                    </div>
                    <p class="vip-benefits">Upgrade to VIP for exclusive access</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- VIP Upgrade Notice (for non-VIP) -->
        <?php if (!$isVIP): ?>
        <div class="upgrade-notice">
            <span class="icon">🔒</span>
            <div>
                <h3>VIP Rooms are Hidden</h3>
                <p>Upgrade to VIP status to unlock exclusive premium rooms and special benefits.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- VIP Rooms Section (Visible ONLY to VIP customers) -->
        <?php if ($isVIP && count($vipRooms) > 0): ?>
        <section class="rooms-section">
            <div class="section-header vip">
                <h2><span>✨</span> VIP Exclusive Rooms</h2>
            </div>

            <div class="rooms-grid">
                <?php foreach ($vipRooms as $r): ?>
                    <div class="room-card">
                        <div class="room-image-container">
                            <?php if (!empty($r['Image'])): ?>
                                <img src="assets/rooms/<?= htmlspecialchars($r['Image']); ?>" alt="Room <?= $r['Room_ID']; ?>" class="room-image">
                            <?php else: ?>
                                <div class="room-no-image">
                                    <span>🛏️</span>
                                    <p>No Image Available</p>
                                </div>
                            <?php endif; ?>
                            
                            <span class="room-status-badge <?= $r['Availability'] === 'Available' ? 'available' : 'occupied'; ?>">
                                <?= htmlspecialchars($r['Availability']); ?>
                            </span>
                            
                            <span class="vip-only-badge">👑 VIP Only</span>
                        </div>

                        <div class="room-details">
                            <div class="room-title">Room <?= $r['Room_ID']; ?></div>
                            <div class="room-type"><?= htmlspecialchars($r['Room_Type']); ?></div>
                            
                            <?php if (!empty($r['Description'])): ?>
                                <p class="room-description"><?= htmlspecialchars($r['Description']); ?></p>
                            <?php endif; ?>

                            <div class="room-footer">
                                <div class="room-price">
                                    <span class="price-label">Per Night</span>
                                    <span class="price-value">₱<?= number_format($r['Price_Per_Night'], 2); ?></span>
                                </div>

                                <?php if ($r['Availability'] === "Available"): ?>
                                    <a href="booking_preview.php?room=<?= $r['Room_ID']; ?>" class="btn-book">Book Now</a>
                                <?php else: ?>
                                    <button class="btn-book disabled" disabled>Unavailable</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Regular Rooms Section (Visible to ALL customers) -->
        <section class="rooms-section">
            <div class="section-header">
                <h2>🏠 Regular Rooms</h2>
            </div>

            <?php if (count($regularRooms) > 0): ?>
            <div class="rooms-grid">
                <?php foreach ($regularRooms as $r): ?>
                    <div class="room-card">
                        <div class="room-image-container">
                            <?php if (!empty($r['Image'])): ?>
                                <img src="assets/rooms/<?= htmlspecialchars($r['Image']); ?>" alt="Room <?= $r['Room_ID']; ?>" class="room-image">
                            <?php else: ?>
                                <div class="room-no-image">
                                    <span>🛏️</span>
                                    <p>No Image Available</p>
                                </div>
                            <?php endif; ?>
                            
                            <span class="room-status-badge <?= $r['Availability'] === 'Available' ? 'available' : 'occupied'; ?>">
                                <?= htmlspecialchars($r['Availability']); ?>
                            </span>
                        </div>

                        <div class="room-details">
                            <div class="room-title">Room <?= $r['Room_ID']; ?></div>
                            <div class="room-type"><?= htmlspecialchars($r['Room_Type']); ?></div>
                            
                            <?php if (!empty($r['Description'])): ?>
                                <p class="room-description"><?= htmlspecialchars($r['Description']); ?></p>
                            <?php endif; ?>

                            <div class="room-footer">
                                <div class="room-price">
                                    <span class="price-label">Per Night</span>
                                    <span class="price-value">₱<?= number_format($r['Price_Per_Night'], 2); ?></span>
                                </div>

                                <?php if ($r['Availability'] === "Available"): ?>
                                    <a href="booking_preview.php?room=<?= $r['Room_ID']; ?>" class="btn-book">Book Now</a>
                                <?php else: ?>
                                    <button class="btn-book disabled" disabled>Unavailable</button>
                                <?php endif; ?>
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
                        <p>No regular rooms available at the moment.</p>
                        <p style="font-size: 14px; color: #9ca3af;">Please check back later or contact support.</p>
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