<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customerID = $_SESSION['customer_id'];

/* Validate POST */
if (!isset($_POST['room_id'], $_POST['book_in'], $_POST['book_out'])) {
    die("Missing booking data.");
}

$roomID = $_POST['room_id'];
$bookIn = $_POST['book_in'];
$bookOut = $_POST['book_out'];

/* Days validation */
$days = (strtotime($bookOut) - strtotime($bookIn)) / 86400;

if ($days < 1) {
    die("Check-out must be at least 1 day after check-in.");
}

/* Fetch room */
$stmt = $pdo->prepare("SELECT * FROM room WHERE Room_ID = ?");
$stmt->execute([$roomID]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    die("Room not found.");
}

/* Check VIP */
$custStmt = $pdo->prepare("SELECT VIP_Status FROM customer WHERE Customer_ID = ?");
$custStmt->execute([$customerID]);
$cust = $custStmt->fetch(PDO::FETCH_ASSOC);

$isVIP = ($cust['VIP_Status'] === 'VIP');

$pricePerNight = $room['Price_Per_Night'];
if ($isVIP) {
    $pricePerNight *= 0.90;
}

$totalPrice = $pricePerNight * $days;

/* Insert booking */
$insert = $pdo->prepare("
    INSERT INTO booking (Customer_ID, Room_ID, Book_In, Book_Out, Nights_Stayed, Total_Price, Status)
    VALUES (?, ?, ?, ?, ?, ?, 'Active')
");
$insert->execute([$customerID, $roomID, $bookIn, $bookOut, $days, $totalPrice]);

/* Update room */
$pdo->prepare("UPDATE room SET Availability='Unavailable' WHERE Room_ID=?")->execute([$roomID]);

header("Location: customer_dashboard.php?booked=1");
exit();
