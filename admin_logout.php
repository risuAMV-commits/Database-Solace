<?php
session_start();

// Check if confirmation is provided
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $_SESSION = [];
    session_destroy();
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logout - Hotel Booking</title>
    <link rel="stylesheet" href="assets/admin_logout.css">
</head>
<body>

<div class="logout-container">
    <div class="logout-icon">🔐</div>
    <h1>Admin Logout</h1>
    <p>Are you sure you want to logout from the admin panel? You will need to login again to access the dashboard.</p>
    
    <div class="button-group">
        <a href="admin_logout.php?confirm=yes" class="btn btn-logout">Yes, Logout</a>
        <a href="admin_dashboard.php" class="btn btn-cancel">Cancel</a>
    </div>
</div>

</body>
</html>