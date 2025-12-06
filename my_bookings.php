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

/* Fetch bookings */
$bookingsStmt = $pdo->prepare("
    SELECT b.*, r.Room_Type, r.Price_Per_Night, r.Image
    FROM booking b
    JOIN room r ON b.Room_ID = r.Room_ID
    WHERE b.Customer_ID = ?
    ORDER BY b.Booking_ID DESC
");
$bookingsStmt->execute([$customerID]);
$bookings = $bookingsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Hotel Booking</title>
    <link rel="stylesheet" href="assets/bookings.css">
</head>

<body>

<!-- Hamburger Menu -->
<input type="checkbox" id="menu-toggle" class="menu-toggle">
<label for="menu-toggle" class="menu-icon">☰</label>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Customer Menu</h2>
    </div>
    <nav class="sidebar-nav">
        <a href="customer_dashboard.php">
            <span class="nav-icon">📋</span>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="customer_rooms.php">
            <span class="nav-icon">🛏️</span>
            <span class="nav-text">Book a Room</span>
        </a>
        <a href="my_bookings.php" class="active">
            <span class="nav-icon">📅</span>
            <span class="nav-text">My Bookings</span>
        </a>
        <a href="profile.php">
            <span class="nav-icon">👤</span>
            <span class="nav-text">Profile</span>
        </a>
        <a href="logout.php" class="logout-link">
            <span class="nav-icon">🚪</span>
            <span class="nav-text">Logout</span>
        </a>
    </nav>
</div>

<!-- Main Container -->
<div class="main-container">
    <!-- Header Card -->
    <div class="header-card">
        <div class="header-content">
            <div class="header-left">
                <span class="header-emoji">📅</span>
                <div class="header-text">
                    <h1>My Bookings</h1>
                    <p>View and manage your room reservations</p>
                </div>
            </div>
            <div class="vip-section">
                <?php if ($isVIP): ?>
                    <div class="vip-badge vip-active">
                        <span>👑</span>
                        <span>VIP Member</span>
                    </div>
                    <p class="vip-benefits">10% Discount • Priority Booking • Free Upgrade</p>
                <?php else: ?>
                    <div class="vip-badge regular">
                        <span>⭐</span>
                        <span>Standard Member</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bookings Section -->
    <div class="bookings-section">
        <div class="section-header">
            <span style="font-size: 1.8rem;">📅</span>
            <h2>Your Bookings</h2>
        </div>

        <?php if (count($bookings) > 0): ?>
        <div class="bookings-grid">
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-image-container">
                        <?php if (!empty($booking['Image'])): ?>
                            <img src="assets/rooms/<?= htmlspecialchars($booking['Image']); ?>" alt="Room <?= $booking['Room_ID']; ?>" class="booking-image">
                        <?php else: ?>
                            <div class="booking-no-image">
                                <span>🛏️</span>
                            </div>
                        <?php endif; ?>
                        
                        <span class="booking-status-badge status-<?= strtolower($booking['Status']); ?>">
                            <?= htmlspecialchars($booking['Status']); ?>
                        </span>
                    </div>

                    <div class="booking-details">
                        <div class="booking-header">
                            <h3 class="booking-title">Room <?= $booking['Room_ID']; ?></h3>
                            <span class="booking-id">#<?= $booking['Booking_ID']; ?></span>
                        </div>
                        
                        <p class="room-type"><?= htmlspecialchars($booking['Room_Type']); ?></p>
                        
                        <div class="booking-dates">
                            <div class="date-item">
                                <span class="date-label">Check-In</span>
                                <span class="date-value"><?= date('M d, Y', strtotime($booking['Book_In'])); ?></span>
                            </div>
                            <div class="date-divider">→</div>
                            <div class="date-item">
                                <span class="date-label">Check-Out</span>
                                <span class="date-value"><?= date('M d, Y', strtotime($booking['Book_Out'])); ?></span>
                            </div>
                        </div>

                        <div class="booking-info">
                            <div class="info-item">
                                <span class="info-icon">🌙</span>
                                <span><?= $booking['Nights_Stayed']; ?> Night<?= $booking['Nights_Stayed'] > 1 ? 's' : ''; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">💰</span>
                                <span class="price-highlight">₱<?= number_format($booking['Total_Price'], 2); ?></span>
                            </div>
                        </div>

                        <?php if ($booking['Status'] !== 'Cancelled' && $booking['Status'] !== 'Completed'): ?>
                        <div class="booking-actions">
                            <a href="cancel_booking.php?id=<?= $booking['Booking_ID']; ?>" 
                               class="btn-cancel" 
                               onclick="return confirm('Are you sure you want to cancel this booking?');">
                                Cancel Booking
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📅</div>
            <h3>No Bookings Yet</h3>
            <p>You haven't made any bookings yet. Start by browsing our available rooms!</p>
            <a href="customer_rooms.php" class="btn-browse">Browse Rooms</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Close menu when clicking on the overlay (main-container)
document.querySelector('.main-container').addEventListener('click', function(e) {
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle.checked && e.target === this) {
        menuToggle.checked = false;
    }
});
</script>

</body>
</html>




