<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch booking details with customer and room information
$stmt = $pdo->prepare("
    SELECT 
        b.Booking_ID, 
        b.Customer_ID, 
        b.Room_ID, 
        b.Book_In, 
        b.Book_Out, 
        b.Nights_Stayed, 
        b.Total_Price, 
        b.Status,
        c.Name as CustomerName, 
        c.Email,
        c.Contact_Number as Phone,
        r.Room_Type,
        r.Price_Per_Night
    FROM booking b
    LEFT JOIN customer c ON b.Customer_ID = c.Customer_ID
    LEFT JOIN room r ON b.Room_ID = r.Room_ID
    WHERE b.Booking_ID = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: manage_bookings.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking #<?= $booking['Booking_ID'] ?> - Solace Resort</title>
    <link rel="stylesheet" href="assets/admin.css">
    <link rel="stylesheet" href="assets/view_booking.css">
</head>
<body class="admin-bg">

<!-- Hamburger Menu -->
<input type="checkbox" id="menu-toggle" class="menu-toggle">
<label for="menu-toggle" class="menu-icon">☰</label>

<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <div class="page-wrapper">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1>📋 Booking Details</h1>
                <p class="subtitle">Booking ID: #<?= $booking['Booking_ID'] ?></p>
            </div>
            <div class="header-actions">
                <a href="manage_bookings.php" class="btn-back">← Back to Bookings</a>
                <a href="edit_booking.php?id=<?= $booking['Booking_ID'] ?>" class="btn-edit">✏️ Edit Booking</a>
            </div>
        </div>

        <!-- Booking Status Banner -->
        <div class="status-banner status-<?= strtolower($booking['Status']) ?>">
            <div class="status-icon">
                <?php if ($booking['Status'] === 'Booked'): ?>
                    ✓
                <?php elseif ($booking['Status'] === 'Cancelled'): ?>
                    ✗
                <?php else: ?>
                    ★
                <?php endif; ?>
            </div>
            <div class="status-info">
                <h3>Booking Status: <?= $booking['Status'] ?></h3>
                <p>This booking is currently marked as <?= strtolower($booking['Status']) ?></p>
            </div>
        </div>

        <div class="details-grid">
            
            <!-- Customer Information Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3>👤 Customer Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Customer ID:</span>
                        <span class="value">#<?= $booking['Customer_ID'] ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Name:</span>
                        <span class="value"><?= htmlspecialchars($booking['CustomerName']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email:</span>
                        <span class="value"><?= htmlspecialchars($booking['Email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Contact Number:</span>
                        <span class="value"><?= htmlspecialchars($booking['Phone'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>

            <!-- Room Information Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3>🏠 Room Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Room ID:</span>
                        <span class="value">Room #<?= $booking['Room_ID'] ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Room Type:</span>
                        <span class="value"><?= htmlspecialchars($booking['Room_Type']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Price Per Night:</span>
                        <span class="value">₱<?= number_format($booking['Price_Per_Night'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Booking Details Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3>📅 Booking Details</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Check-In Date:</span>
                        <span class="value highlight"><?= date('F d, Y', strtotime($booking['Book_In'])) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Check-Out Date:</span>
                        <span class="value highlight"><?= date('F d, Y', strtotime($booking['Book_Out'])) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Number of Nights:</span>
                        <span class="value"><strong><?= $booking['Nights_Stayed'] ?> nights</strong></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Booking Status:</span>
                        <span class="value">
                            <span class="status-badge status-<?= strtolower($booking['Status']) ?>">
                                <?= $booking['Status'] ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment Summary Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3>💰 Payment Summary</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Price Per Night:</span>
                        <span class="value">₱<?= number_format($booking['Price_Per_Night'], 2) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Number of Nights:</span>
                        <span class="value"><?= $booking['Nights_Stayed'] ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Subtotal:</span>
                        <span class="value">₱<?= number_format($booking['Price_Per_Night'] * $booking['Nights_Stayed'], 2) ?></span>
                    </div>
                    <div class="info-row total-row">
                        <span class="label">Total Amount:</span>
                        <span class="value total-amount">₱<?= number_format($booking['Total_Price'], 2) ?></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="action-section">
            <button onclick="printBooking()" class="btn-action btn-print">🖨️ Print Details</button>
            <a href="edit_booking.php?id=<?= $booking['Booking_ID'] ?>" class="btn-action btn-edit-main">✏️ Edit Booking</a>
        </div>

    </div>
</div>

<script>
function printBooking() {
    window.print();
}
</script>

</body>
</html>