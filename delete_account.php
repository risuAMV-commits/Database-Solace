<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customerID = $_SESSION['customer_id'];

// OPTIONAL: Soft delete bookings 
$pdo->prepare("UPDATE booking SET Status='Cancelled' WHERE Customer_ID=?")
    ->execute([$customerID]);

// Delete customer account
$delete = $pdo->prepare("DELETE FROM customer WHERE Customer_ID=?");
$delete->execute([$customerID]);

// Logout and destroy session
session_unset();
session_destroy();

header("Location: login.php?deleted=1");
exit();
?>
