<?php
session_start();
include 'config.php';

// User must be logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$roomID = $_GET['room'] ?? 0;

// Fetch room info
$stmt = $pdo->prepare("SELECT * FROM room WHERE Room_ID = ?");
$stmt->execute([$roomID]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    echo "Room not found.";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Room</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="admin-bg">

<div class="content">
    <h1>Booking Room #<?php echo $room['Room_ID']; ?></h1>

    <div class="room-card">
        <img src="assets/rooms/<?php echo $room['Image']; ?>" class="room-img">
        <h3><?php echo $room['Room_Type']; ?></h3>
        <p>Price Per Night: ₱<?php echo number_format($room['Price_Per_Night'], 2); ?></p>
        <p>Status: <?php echo $room['Availability']; ?></p>
    </div>

    <?php if ($room['Availability'] !== "Available"): ?>
        <p style="color:red;">This room is currently unavailable.</p>
    <?php else: ?>

        <form action="booking_submit.php" method="POST">

            <input type="hidden" name="room_id" value="<?php echo $room['Room_ID']; ?>">

            <label>Book In:</label>
            <input type="date" name="checkin" required>

            <label>Book Out:</label>
            <input type="date" name="checkout" required>

            <button type="submit" class="admin-btn">Confirm Booking</button>

        </form>

    <?php endif; ?>

</div>

</body>
</html>
