<?php
include 'config.php';

$id = $_GET['id'];

// fetch room id
$get = $pdo->prepare("SELECT Room_ID FROM booking WHERE Booking_ID=?");
$get->execute([$id]);
$r = $get->fetch();

if ($r) {
    // return room to available
    $updateRoom = $pdo->prepare("UPDATE room SET Availability='Available' WHERE Room_ID=?");
    $updateRoom->execute([$r['Room_ID']]);
}

// cancel booking
$cancel = $pdo->prepare("UPDATE booking SET Status='Cancelled' WHERE Booking_ID=?");
$cancel->execute([$id]);

header("Location: customer_dashboard.php?cancelled=1");
exit();
?>
