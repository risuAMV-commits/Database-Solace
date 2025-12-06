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

// fetch image name
$stmt = $pdo->prepare("SELECT Image FROM room WHERE Room_ID = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $img = $row['Image'];
    // delete DB row
    $del = $pdo->prepare("DELETE FROM room WHERE Room_ID = ?");
    if ($del->execute([$id])) {
        if (!empty($img) && file_exists(__DIR__ . '/assets/rooms/' . $img)) {
            @unlink(__DIR__ . '/assets/rooms/' . $img);
        }
    }
}

header("Location: manage_rooms.php");
exit();
