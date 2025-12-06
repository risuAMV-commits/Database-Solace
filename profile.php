<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customerID = $_SESSION['customer_id'];

/* -----------------------------------
   FETCH CUSTOMER DATA
----------------------------------- */
$stmt = $pdo->prepare("
    SELECT Name, Email, Contact_Number, VIP_Status
    FROM customer
    WHERE Customer_ID = ?
");
$stmt->execute([$customerID]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("Customer not found.");
}

$isVIP = ($customer['VIP_Status'] === "VIP");

/* -----------------------------------
   UPDATE PROFILE
----------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfile'])) {
    $newName   = trim($_POST['name']);
    $newEmail  = trim($_POST['email']);
    $newContact = trim($_POST['contact']);

    $update = $pdo->prepare("
        UPDATE customer SET Name=?, Email=?, Contact_Number=?
        WHERE Customer_ID=?
    ");
    $update->execute([$newName, $newEmail, $newContact, $customerID]);

    // Update session
    $_SESSION['customer'] = $newName;
    $_SESSION['customer_email'] = $newEmail;
    $_SESSION['customer_contact'] = $newContact;

    header("Location: profile.php?updated=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hotel Booking</title>
    <link rel="stylesheet" href="assets/profile.css">
</head>

<body>

<?php if (isset($_GET['updated'])): ?>
<div class="popup" id="popup">✓ Profile Updated Successfully</div>
<script>
    setTimeout(() => {
        document.getElementById('popup').style.display = 'none';
    }, 3000);
</script>
<?php endif; ?>

<!-- Hamburger Menu -->
<input type="checkbox" id="menu-toggle" class="menu-toggle">
<label for="menu-toggle" class="menu-icon">☰</label>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Customer Menu</h2>
    </div>
    <nav class="sidebar-nav">
        <a href="customer_dashboard.php">
            <span class="nav-icon">📋</span>
            <span>Dashboard</span>
        </a>
        <a href="customer_rooms.php">
            <span class="nav-icon">🛏️</span>
            <span>Book a Room</span>
        </a>
        <a href="my_bookings.php">
            <span class="nav-icon">📅</span>
            <span>My Bookings</span>
        </a>
        <a href="profile.php" class="active">
            <span class="nav-icon">👤</span>
            <span>Profile</span>
        </a>
        <a href="logout.php" class="logout-link">
            <span class="nav-icon">🚪</span>
            <span>Logout</span>
        </a>
    </nav>
</div>

<!-- Main Container -->
<div class="main-container">
    <!-- Header Card -->
    <div class="header-card">
        <div class="header-content">
            <div class="header-left">
                <span class="header-emoji">👤</span>
                <div class="header-text">
                    <h1>My Profile</h1>
                    <p>Manage your account information</p>
                </div>
            </div>
            <div class="vip-section">
                <?php if ($isVIP): ?>
                    <div class="vip-badge vip-active">
                        <span>👑</span>
                        <span>VIP Member</span>
                    </div>
                <?php else: ?>
                    <div class="vip-badge regular">
                        <span>⭐</span>
                        <span>Standard Member</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="profile-section">
        <div class="profile-grid">
            
            <!-- Profile Form Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h2>Account Information</h2>
                    <button id="editBtn" class="edit-btn">✏️ Edit Profile</button>
                </div>

                <form method="POST" id="profileForm">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" id="nameField"
                               value="<?= htmlspecialchars($customer['Name']) ?>" disabled required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="emailField"
                               value="<?= htmlspecialchars($customer['Email']) ?>" disabled required>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact" id="contactField"
                               value="<?= htmlspecialchars($customer['Contact_Number']) ?>" disabled required>
                    </div>

                    <div class="form-group">
                        <label>Account Status</label>
                        <input type="text" value="<?= htmlspecialchars($customer['VIP_Status']) ?>" disabled>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="updateProfile" id="saveBtn" class="save-btn" disabled>
                            💾 Save Changes
                        </button>
                        <button type="button" class="delete-btn" onclick="confirmDelete()">
                            🗑️ Delete Account
                        </button>
                    </div>
                </form>
            </div>

            <!-- Benefits Card -->
            <?php if ($isVIP): ?>
            <div class="benefits-card vip-benefits">
                <div class="benefits-header">
                    <span class="benefits-icon">👑</span>
                    <h3>VIP Benefits</h3>
                </div>
                <ul class="benefits-list">
                    <li>
                        <span class="check-icon">✓</span>
                        <span>10% Discount on All Bookings</span>
                    </li>
                    <li>
                        <span class="check-icon">✓</span>
                        <span>Priority Room Upgrade</span>
                    </li>
                    <li>
                        <span class="check-icon">✓</span>
                        <span>Exclusive VIP Rooms</span>
                    </li>
                    <li>
                        <span class="check-icon">✓</span>
                        <span>Late Checkout Available</span>
                    </li>
                    <li>
                        <span class="check-icon">✓</span>
                        <span>Free Welcome Drink</span>
                    </li>
                </ul>
            </div>
            <?php else: ?>
            <div class="benefits-card standard-benefits">
                <div class="benefits-header">
                    <span class="benefits-icon">⭐</span>
                    <h3>Standard Member</h3>
                </div>
                <ul class="benefits-list">
                    <li>
                        <span class="check-icon">✓</span>
                        <span>Access to Standard Rooms</span>
                    </li>
                    <li>
                        <span class="check-icon">✓</span>
                        <span>Free WiFi</span>
                    </li>
                    <li>
                        <span class="check-icon">✓</span>
                        <span>24/7 Customer Support</span>
                    </li>
                </ul>
                <div class="upgrade-notice">
                    <p>💡 Stay more to earn VIP status and enjoy exclusive benefits!</p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
document.querySelector('.main-container').addEventListener('click', function(e) {
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle.checked && e.target === this) {
        menuToggle.checked = false;
    }
});

document.getElementById("editBtn").onclick = function () {
    const nameField = document.getElementById("nameField");
    const emailField = document.getElementById("emailField");
    const contactField = document.getElementById("contactField");
    const saveBtn = document.getElementById("saveBtn");
    
    if (nameField.disabled) {
        nameField.disabled = false;
        emailField.disabled = false;
        contactField.disabled = false;
        saveBtn.disabled = false;
        
        this.textContent = "✓ Editing Mode";
        this.style.background = "linear-gradient(135deg, #f59e0b 0%, #d97706 100%)";
    } else {
        nameField.disabled = true;
        emailField.disabled = true;
        contactField.disabled = true;
        saveBtn.disabled = true;
        
        this.textContent = "✏️ Edit Profile";
        this.style.background = "linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)";
    }
};

function confirmDelete() {
    if (confirm("⚠️ Are you sure you want to permanently delete your account? This action cannot be undone.")) {
        window.location.href = "delete_account.php";
    }
}
</script>

</body>
</html>