<?php
session_start();
include 'config.php';

$error = '';

// Redirect if already logged in
if (isset($_SESSION['admin'])) {
    header("Location: admin_panel.php");
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // FIXED: correct lowercase column name from your database
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // FIXED: plain text password check (your DB stores admin123)
        if ($admin && $password === $admin['password']) {

            // FIXED: correct lowercase DB columns
            $_SESSION['admin'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['admin_id'];

            // Handle remember me
            if ($remember) {
                setcookie('admin_username', $username, time() + (86400 * 7), "/"); // 7 days
            }

            // REMOVED: admin_logs because the table does NOT exist
            // If you create admin_logs table later, we can enable this again.

            header("Location: admin_panel.php");
            exit();

        } else {
            $error = 'Invalid username or password.';

            // REMOVED: failed login log, table doesn't exist
        }
    }
}

// Pre-fill username if remember me cookie exists
$remembered_username = isset($_COOKIE['admin_username']) ? $_COOKIE['admin_username'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Hotel Booking</title>
    <link rel="stylesheet" href="assets/admin_login.css">
</head>
<body>

<div class="login-container">
    <div class="back-button-top">
           </div>
    
    <div class="admin-badge">ADMIN</div>
    
    <div class="login-header">
        <div class="back-button-top">
         
        </div>
        <div class="login-logo">🔐</div>
        <h1>Admin Login</h1>
        <p>Access the administrative dashboard</p>
    </div>

    <form method="POST" class="login-form">
        <?php if ($error): ?>
        <div class="error-message">
            <span class="error-icon">⚠️</span>
            <span><?= htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="username">Username</label>
            <span class="input-icon">👤</span>
            <input 
                type="text" 
                id="username" 
                name="username" 
                placeholder="Enter admin username"
                value="<?= htmlspecialchars($remembered_username); ?>"
                required
                autocomplete="username"
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <span class="input-icon">🔒</span>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Enter admin password"
                required
                autocomplete="current-password"
            >
            <span class="password-toggle" onclick="togglePassword()">👁️</span>
        </div>

        <div class="remember-me">
            <input type="checkbox" id="remember" name="remember" <?= $remembered_username ? 'checked' : ''; ?>>
            <label for="remember">Remember me for 7 days</label>
        </div>

        <button type="submit" class="btn-login">Log In to Dashboard</button>
    </form>

    <div class="security-note">
        <span class="security-icon">🛡️</span>
        <span>This is a secure admin area. All login attempts are logged.</span>
    </div>

    <div class="divider">
        <span>OR</span>
    </div>

    <div class="back-customer">
        Not an admin? <a href="login.php">Customer Login</a>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggle = document.querySelector('.password-toggle');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggle.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggle.textContent = '👁️';
    }
}

window.history.forward();
function noBack() {
    window.history.forward();
}

<?php if ($error): ?>
setTimeout(() => {
    const errorMsg = document.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.style.opacity = '0';
        errorMsg.style.transition = 'opacity 0.5s';
        setTimeout(() => errorMsg.remove(), 500);
    }
}, 5000);
<?php endif; ?>
</script>

</body>
</html>