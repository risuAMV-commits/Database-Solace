<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $room_id = $_POST['room_id'];
    $book_in = $_POST['book_in'];
    $book_out = $_POST['book_out'];
    $status = $_POST['status'];
    
    // Calculate nights stayed
    $date1 = new DateTime($book_in);
    $date2 = new DateTime($book_out);
    $nights = $date2->diff($date1)->days;
    
    // Get room price
    $room_stmt = $pdo->prepare("SELECT Price_Per_Night FROM room WHERE Room_ID = ?");
    $room_stmt->execute([$room_id]);
    $room = $room_stmt->fetch();
    $total_price = $room['Price_Per_Night'] * $nights;
    
    // Update booking
    $update_stmt = $pdo->prepare("
        UPDATE booking 
        SET Customer_ID = ?, 
            Room_ID = ?, 
            Book_In = ?, 
            Book_Out = ?, 
            Nights_Stayed = ?, 
            Total_Price = ?,
            Status = ?
        WHERE Booking_ID = ?
    ");
    
    if ($update_stmt->execute([$customer_id, $room_id, $book_in, $book_out, $nights, $total_price, $status, $booking_id])) {
        $success_message = "Booking updated successfully!";
        // Refresh booking data
        header("Location: edit_booking.php?id=$booking_id&success=1");
        exit();
    } else {
        $error_message = "Error updating booking.";
    }
}

// Fetch booking details
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

// Fetch all customers for dropdown
$customers = $pdo->query("SELECT Customer_ID, Name, Email FROM customer ORDER BY Name")->fetchAll();

// Fetch all available rooms for dropdown
$rooms = $pdo->query("SELECT Room_ID, Room_Type, Price_Per_Night FROM room ORDER BY Room_ID")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking #<?= $booking['Booking_ID'] ?> - Solace Resort</title>
    <link rel="stylesheet" href="assets/admin.css">
    <link rel="stylesheet" href="assets/edit_booking.css">
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
                <h1>✏️ Edit Booking</h1>
                <p class="subtitle">Booking ID: #<?= $booking['Booking_ID'] ?></p>
            </div>
            <div class="header-actions">
                <a href="view_booking.php?id=<?= $booking['Booking_ID'] ?>" class="btn-back">← Back to View</a>
                <a href="manage_bookings.php" class="btn-back">📋 All Bookings</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            ✓ Booking updated successfully!
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            ✗ <?= $error_message ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="edit-form" id="editBookingForm">
            
            <div class="form-grid">
                
                <!-- Customer Selection Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>👤 Customer Information</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="customer_id">Select Customer *</label>
                            <select name="customer_id" id="customer_id" required>
                                <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['Customer_ID'] ?>" 
                                        <?= $customer['Customer_ID'] == $booking['Customer_ID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customer['Name']) ?> (<?= htmlspecialchars($customer['Email']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="info-box">
                            <strong>Current Customer:</strong><br>
                            <?= htmlspecialchars($booking['CustomerName']) ?><br>
                            <small><?= htmlspecialchars($booking['Email']) ?></small>
                        </div>
                    </div>
                </div>

                <!-- Room Selection Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>🏠 Room Information</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="room_id">Select Room *</label>
                            <select name="room_id" id="room_id" required onchange="updatePrice()">
                                <?php foreach ($rooms as $room): ?>
                                <option value="<?= $room['Room_ID'] ?>" 
                                        data-price="<?= $room['Price_Per_Night'] ?>"
                                        <?= $room['Room_ID'] == $booking['Room_ID'] ? 'selected' : '' ?>>
                                    Room #<?= $room['Room_ID'] ?> - <?= $room['Room_Type'] ?> 
                                    (₱<?= number_format($room['Price_Per_Night'], 2) ?>/night)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="info-box">
                            <strong>Current Room:</strong><br>
                            Room #<?= $booking['Room_ID'] ?> - <?= $booking['Room_Type'] ?><br>
                            <small>₱<?= number_format($booking['Price_Per_Night'], 2) ?> per night</small>
                        </div>
                    </div>
                </div>

                <!-- Booking Dates Card -->
                <div class="form-card full-width">
                    <div class="form-card-header">
                        <h3>📅 Booking Dates</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="date-grid">
                            <div class="form-group">
                                <label for="book_in">Check-In Date *</label>
                                <input type="date" 
                                       name="book_in" 
                                       id="book_in" 
                                       value="<?= $booking['Book_In'] ?>" 
                                       required
                                       onchange="calculateNights()">
                            </div>
                            
                            <div class="form-group">
                                <label for="book_out">Check-Out Date *</label>
                                <input type="date" 
                                       name="book_out" 
                                       id="book_out" 
                                       value="<?= $booking['Book_Out'] ?>" 
                                       required
                                       onchange="calculateNights()">
                            </div>
                        </div>
                        
                        <div class="calculation-display">
                            <div class="calc-item">
                                <span class="calc-label">Number of Nights:</span>
                                <span class="calc-value" id="nightsDisplay"><?= $booking['Nights_Stayed'] ?></span>
                            </div>
                            <div class="calc-item">
                                <span class="calc-label">Price Per Night:</span>
                                <span class="calc-value" id="priceDisplay">₱<?= number_format($booking['Price_Per_Night'], 2) ?></span>
                            </div>
                            <div class="calc-item total">
                                <span class="calc-label">Total Amount:</span>
                                <span class="calc-value" id="totalDisplay">₱<?= number_format($booking['Total_Price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="form-card full-width">
                    <div class="form-card-header">
                        <h3>📊 Booking Status</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="status">Booking Status *</label>
                            <select name="status" id="status" required>
                                <option value="Booked" <?= $booking['Status'] === 'Booked' ? 'selected' : '' ?>>Booked</option>
                                <option value="Cancelled" <?= $booking['Status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="Completed" <?= $booking['Status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        
                        <div class="status-warning">
                            <strong>⚠️ Note:</strong> Changing the status will affect room availability and booking records.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="button" onclick="window.location.href='view_booking.php?id=<?= $booking['Booking_ID'] ?>'" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    💾 Save Changes
                </button>
            </div>

        </form>

    </div>
</div>

<script>
function calculateNights() {
    const checkIn = new Date(document.getElementById('book_in').value);
    const checkOut = new Date(document.getElementById('book_out').value);
    
    if (checkIn && checkOut && checkOut > checkIn) {
        const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        document.getElementById('nightsDisplay').textContent = nights;
        updateTotal(nights);
    }
}

function updatePrice() {
    const roomSelect = document.getElementById('room_id');
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price'));
    
    document.getElementById('priceDisplay').textContent = '₱' + price.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    
    const nights = parseInt(document.getElementById('nightsDisplay').textContent);
    updateTotal(nights);
}

function updateTotal(nights) {
    const roomSelect = document.getElementById('room_id');
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price'));
    const total = price * nights;
    
    document.getElementById('totalDisplay').textContent = '₱' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Form validation
document.getElementById('editBookingForm').addEventListener('submit', function(e) {
    const checkIn = new Date(document.getElementById('book_in').value);
    const checkOut = new Date(document.getElementById('book_out').value);
    
    if (checkOut <= checkIn) {
        e.preventDefault();
        alert('Check-out date must be after check-in date!');
        return false;
    }
});
</script>

</body>
</html>