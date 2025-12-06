<?php
session_start();

// Check if confirmation is provided
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Hotel Booking</title>
    <link rel="stylesheet" href="assets/logout.css">
</head>
<body>

<div class="logout-container">
    <div class="logout-icon">👋</div>
    <h1>Logout Confirmation</h1>
    <p>Are you sure you want to logout? You will need to login again to access your account.</p>
    
    <div class="button-group">
        <a href="logout.php?confirm=yes" class="btn btn-logout">Yes, Logout</a>
        <a href="javascript:history.back()" class="btn btn-cancel">Cancel</a>
    </div>
</div>

</body>
</html>