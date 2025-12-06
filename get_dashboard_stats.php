<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get real-time statistics
$total_customers = $pdo->query("SELECT COUNT(*) FROM customer")->fetchColumn();
$total_rooms = $pdo->query("SELECT COUNT(*) FROM room")->fetchColumn();

// Occupied rooms = rooms with active bookings (Status = 'Booked')
$occupied_rooms = $pdo->query("
    SELECT COUNT(DISTINCT Room_ID) 
    FROM booking 
    WHERE Status = 'Booked'
")->fetchColumn();

// Available rooms = Total rooms - Occupied rooms
$available_rooms = $total_rooms - $occupied_rooms;

$total_bookings = $pdo->query("SELECT COUNT(*) FROM booking WHERE Status = 'Booked'")->fetchColumn();
$cancelled_bookings = $pdo->query("SELECT COUNT(*) FROM booking WHERE Status = 'Cancelled'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'total_customers' => (int)$total_customers,
    'total_rooms' => (int)$total_rooms,
    'available_rooms' => (int)$available_rooms,
    'occupied_rooms' => (int)$occupied_rooms,
    'total_bookings' => (int)$total_bookings,
    'cancelled_bookings' => (int)$cancelled_bookings,
    'total_admins' => (int)$total_admins
]);
?>