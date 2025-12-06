<?php
session_start();
include 'config.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isset($_SESSION['customer_id'])) {
    header("Location: customer_dashboard.php");
    exit();
}

// Handle registration success message
if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $success = 'Registration successful! Please login with your credentials.';
}

// Handle password reset success message
if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $success = 'Password reset successful! Please login with your new password.';
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM customer WHERE Email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check plain text password (no hashing)
        if ($customer && $customer['Password'] === $password) {
            // Set session variables
            $_SESSION['customer_id'] = $customer['Customer_ID'];
            $_SESSION['customer'] = $customer['Name'];
            $_SESSION['customer_email'] = $customer['Email'];
            $_SESSION['vip_status'] = $customer['VIP_Status'];

            // Handle remember me
            if ($remember) {
                setcookie('customer_email', $email, time() + (86400 * 30), "/"); // 30 days
            }

            header("Location: customer_dashboard.php");
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Pre-fill email if remember me cookie exists
$remembered_email = isset($_COOKIE['customer_email']) ? $_COOKIE['customer_email'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Solace Resort</title>
    <link rel="stylesheet" href="assets/customer_login.css">
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <div class="login-logo">🏨</div>
        <h1>Customer Login</h1>
        <p>Welcome back! Please login to your account.</p>
    </div>

    <form method="POST" class="login-form">
        <?php if ($error): ?>
        <div class="error-message">
            <span class="error-icon">⚠️</span>
            <span><?= htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="success-message">
            <span class="success-icon">✔</span>
            <span><?= htmlspecialchars($success); ?></span>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="Enter your email"
                value="<?= htmlspecialchars($remembered_email); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                >
                <span class="password-toggle" onclick="togglePassword()">👁️</span>
            </div>
        </div>

        <div class="remember-forgot">
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" <?= $remembered_email ? 'checked' : ''; ?>>
                <label for="remember">Remember me</label>
            </div>
            <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-login">Log In</button>
    </form>

    <div class="divider">
        <span>OR</span>
    </div>

    <div class="register-link">
        No account? <a href="register.php">Register here</a>
    </div>

    <div class="back-home">
        <a href="index.php">← Back to Home</a>
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

// Auto-hide success message after 5 seconds
<?php if ($success): ?>
setTimeout(() => {
    const successMsg = document.querySelector('.success-message');
    if (successMsg) {
        successMsg.style.opacity = '0';
        successMsg.style.transition = 'opacity 0.5s';
        setTimeout(() => successMsg.remove(), 500);
    }
}, 5000);
<?php endif; ?>
</script>

</body>
</html>