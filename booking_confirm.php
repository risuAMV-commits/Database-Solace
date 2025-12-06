<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id']) || !isset($_GET['room'])) {
    header("Location: customer_dashboard.php");
    exit();
}

$customerID = $_SESSION['customer_id'];
$roomID = $_GET['room'];
$checkIn = $_GET['checkin'];
$checkOut = $_GET['checkout'];

$stmt = $pdo->prepare("SELECT * FROM room WHERE Room_ID = ?");
$stmt->execute([$roomID]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    die("Room not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirm Booking</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="customer-bg">

<div class="content" style="background:rgba(255,255,255,0.9);padding:30px;border-radius:12px;max-width:600px;margin:60px auto;box-shadow:0 4px 15px rgba(0,0,0,0.3);">

<h2 style="text-align:center;">Confirm Your Booking</h2>
<p><strong>Room:</strong> <?= $room["Room_ID"]; ?> (<?= $room["Room_Type"]; ?>)</p>
<p><strong>Check-in:</strong> <?= $checkIn ?></p>
<p><strong>Check-out:</strong> <?= $checkOut ?></p>
<p><strong>Price per Night:</strong> ₱<?= number_format($room["Price_Per_Night"], 2) ?></p>

<div style="text-align:center;margin-top:20px;">
    <a href="booking_submit.php?room=<?= $roomID ?>&checkin=<?= $checkIn ?>&checkout=<?= $checkOut ?>" 
       class="admin-btn" 
       style="background:#22c55e;padding:10px 20px;border-radius:6px;">
        Yes, Book Now
    </a>

    <a href="customer_dashboard.php" 
       class="admin-btn" 
       style="background:#c0392b;margin-left:10px;padding:10px 20px;border-radius:6px;">
        Cancel
    </a>
</div>

</div>

</body>
</html>
