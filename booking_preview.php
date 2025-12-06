<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['room'])) {
    die("No room selected.");
}

$roomID = $_GET['room'];
$customerID = $_SESSION['customer_id'];

/* Fetch Room */
$stmt = $pdo->prepare("SELECT * FROM room WHERE Room_ID = ?");
$stmt->execute([$roomID]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) { die("Room not found."); }

/* VIP */
$cust = $pdo->prepare("SELECT Name, VIP_Status FROM customer WHERE Customer_ID = ?");
$cust->execute([$customerID]);
$custData = $cust->fetch(PDO::FETCH_ASSOC);

$isVIP = ($custData['VIP_Status'] === "VIP");

$originalPrice = $room['Price_Per_Night'];
$vipDiscount = $isVIP ? $originalPrice * 0.10 : 0;
$finalPrice = $originalPrice - $vipDiscount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Preview - Hotel Booking</title>
    <link rel="stylesheet" href="assets/booking_preview.css">
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
            <span>Dashboard</span>
        </a>
        <a href="customer_rooms.php" class="active">
            <span class="nav-icon">🛏️</span>
            <span>Book a Room</span>
        </a>
        <a href="my_bookings.php">
            <span class="nav-icon">📅</span>
            <span>My Bookings</span>
        </a>
        <a href="profile.php">
            <span class="nav-icon">👤</span>
            <span>Profile</span>
        </a>
        <a href="logout.php" class="logout-link">
            <span class="nav-icon">🚪</span>
            <span>Logout</span>
        </a>
    </nav>
</div>

<!-- Main Container -->
<div class="main-container">
    <!-- Header Card -->
    <div class="header-card">
        <div class="header-content">
            <div class="header-left">
                <span class="header-emoji">📝</span>
                <div class="header-text">
                    <h1>Booking Preview</h1>
                    <p>Review your booking details before confirmation</p>
                </div>
            </div>
            <div class="vip-section">
                <?php if ($isVIP): ?>
                    <div class="vip-badge vip-active">
                        <span>👑</span>
                        <span>VIP Member</span>
                    </div>
                <?php else: ?>
                    <div class="vip-badge regular">
                        <span>⭐</span>
                        <span>Standard Member</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Booking Preview Section -->
    <div class="preview-section">
        <div class="preview-grid">
            
            <!-- Room Details Card -->
            <div class="room-details-card">
                <div class="room-image-container">
                    <?php if (!empty($room['Image'])): ?>
                        <img src="assets/rooms/<?= htmlspecialchars($room['Image']); ?>" alt="Room <?= $room['Room_ID']; ?>" class="room-image">
                    <?php else: ?>
                        <div class="room-no-image">
                            <span>🛏️</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="room-info">
                    <h2 class="room-title">Room <?= $room['Room_ID']; ?></h2>
                    <p class="room-type"><?= htmlspecialchars($room['Room_Type']); ?></p>
                    
                    <div class="price-breakdown">
                        <div class="price-item">
                            <span class="price-label">Original Price</span>
                            <span class="price-value">₱<?= number_format($originalPrice, 2); ?></span>
                        </div>

                        <?php if ($isVIP): ?>
                        <div class="price-item discount">
                            <span class="price-label">VIP Discount (10%)</span>
                            <span class="price-value">-₱<?= number_format($vipDiscount, 2); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="price-item total">
                            <span class="price-label">Final Price per Night</span>
                            <span class="price-value">₱<?= number_format($finalPrice, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form Card -->
            <div class="booking-form-card">
                <h3 class="form-title">Select Your Dates</h3>

                <form id="bookingForm" method="POST" action="booking_submit.php">
                    <input type="hidden" name="room_id" value="<?= $roomID; ?>">

                    <div class="form-group">
                        <label>Check-in Date</label>
                        <input type="date" name="book_in" id="checkInDate" required min="<?= date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Check-out Date</label>
                        <input type="date" name="book_out" id="checkOutDate" required min="<?= date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>

                    <div class="nights-display" id="nightsDisplay" style="display: none;">
                        <span class="nights-icon">🌙</span>
                        <span class="nights-text">Total Nights: <strong id="totalNights">0</strong></span>
                    </div>

                    <div class="total-cost" id="totalCost" style="display: none;">
                        <span class="cost-label">Total Cost</span>
                        <span class="cost-value" id="costValue">₱0.00</span>
                    </div>

                    <button type="button" onclick="confirmBooking()" class="confirm-btn">
                        ✓ Confirm Booking
                    </button>
                </form>

                <?php if ($isVIP): ?>
                <div class="vip-benefits-box">
                    <div class="benefits-header">
                        <span class="benefits-icon">👑</span>
                        <h4>Your VIP Benefits</h4>
                    </div>
                    <ul class="benefits-list">
                        <li><span class="check">✓</span> 10% OFF on all bookings</li>
                        <li><span class="check">✓</span> Late checkout available</li>
                        <li><span class="check">✓</span> Free welcome drink</li>
                        <li><span class="check">✓</span> Priority room assignment</li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
// Close menu when clicking on overlay
document.querySelector('.main-container').addEventListener('click', function(e) {
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle.checked && e.target === this) {
        menuToggle.checked = false;
    }
});

// Calculate nights and total cost
const checkInInput = document.getElementById('checkInDate');
const checkOutInput = document.getElementById('checkOutDate');
const nightsDisplay = document.getElementById('nightsDisplay');
const totalNightsSpan = document.getElementById('totalNights');
const totalCostDiv = document.getElementById('totalCost');
const costValueSpan = document.getElementById('costValue');

const pricePerNight = <?= $finalPrice; ?>;

function calculateNightsAndCost() {
    const checkIn = new Date(checkInInput.value);
    const checkOut = new Date(checkOutInput.value);
    
    if (checkInInput.value && checkOutInput.value && checkOut > checkIn) {
        const timeDiff = checkOut - checkIn;
        const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
        
        totalNightsSpan.textContent = nights;
        nightsDisplay.style.display = 'flex';
        
        const totalCost = nights * pricePerNight;
        costValueSpan.textContent = '₱' + totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        totalCostDiv.style.display = 'flex';
    } else {
        nightsDisplay.style.display = 'none';
        totalCostDiv.style.display = 'none';
    }
}

checkInInput.addEventListener('change', calculateNightsAndCost);
checkOutInput.addEventListener('change', calculateNightsAndCost);

// Update min date for checkout when checkin changes
checkInInput.addEventListener('change', function() {
    const checkInDate = new Date(this.value);
    checkInDate.setDate(checkInDate.getDate() + 1);
    const minCheckOut = checkInDate.toISOString().split('T')[0];
    checkOutInput.min = minCheckOut;
    
    // Reset checkout if it's before new min date
    if (checkOutInput.value && checkOutInput.value < minCheckOut) {
        checkOutInput.value = '';
    }
});

function confirmBooking() {
    const form = document.getElementById("bookingForm");

    let checkIn = form.book_in.value;
    let checkOut = form.book_out.value;

    if (!checkIn || !checkOut) {
        alert("Please select your Check-in and Check-out dates.");
        return;
    }

    // Validate dates
    let checkInDate = new Date(checkIn);
    let checkOutDate = new Date(checkOut);
    
    if (checkOutDate <= checkInDate) {
        alert("Check-out date must be after check-in date.");
        return;
    }

    const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
    const totalCost = nights * pricePerNight;
    
    const message = `Confirm your booking?\n\n` +
                   `Room: <?= $room['Room_Type']; ?>\n` +
                   `Check-in: ${checkIn}\n` +
                   `Check-out: ${checkOut}\n` +
                   `Total Nights: ${nights}\n` +
                   `Total Cost: ₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;

    if (confirm(message)) {
        form.submit();
    }
}
</script>

</body>
</html>