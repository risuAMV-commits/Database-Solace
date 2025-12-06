<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: manage_rooms.php");
    exit();
}

// 1. Get image
$stmt = $pdo->prepare("SELECT Image FROM room WHERE Room_ID = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Delete DB row
$del = $pdo->prepare("DELETE FROM room WHERE Room_ID = ?");
$del->execute([$id]);

// 3. Delete image file
if ($row && !empty($row['Image'])) {
    $file = __DIR__ . "/assets/rooms/" . $row['Image'];
    if (file_exists($file)) unlink($file);
}

// 4. Reset AUTO_INCREMENT properly (MAX + 1)
$max = $pdo->query("SELECT MAX(Room_ID) AS max_id FROM room")->fetch(PDO::FETCH_ASSOC)['max_id'];
$next = $max ? $max + 1 : 1;
$pdo->query("ALTER TABLE room AUTO_INCREMENT = $next");

// Redirect back
header("Location: manage_rooms.php");
exit();
?>
