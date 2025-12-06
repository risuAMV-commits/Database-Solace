<?php
include 'config.php';

/* Fetch booking stats */
$active = $pdo->query("SELECT COUNT(*) FROM booking WHERE Status='Booked'")->fetchColumn();
$cancelled = $pdo->query("SELECT COUNT(*) FROM booking WHERE Status='Cancelled'")->fetchColumn();

header("Content-Type: image/png");

$width = 600;
$height = 300;

$img = imagecreatetruecolor($width, $height);

/* Colors */
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
$blue  = imagecolorallocate($img, 0, 102, 204);
$red   = imagecolorallocate($img, 200, 40, 40);

/* Background */
imagefilledrectangle($img, 0, 0, $width, $height, $white);

/* Bar values */
$activeHeight = $active * 5;
$cancelHeight = $cancelled * 5;

/* Draw bars */
imagefilledrectangle($img, 100, 250 - $activeHeight, 220, 250, $blue);
imagefilledrectangle($img, 350, 250 - $cancelHeight, 470, 250, $red);

/* Labels */
imagestring($img, 5, 120, 260, "Active ($active)", $black);
imagestring($img, 5, 360, 260, "Cancelled ($cancelled)", $black);

imagestring($img, 5, 200, 20, "Bookings Graph", $black);

imagepng($img);
imagedestroy($img);
?>
