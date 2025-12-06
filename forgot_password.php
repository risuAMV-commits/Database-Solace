<?php
session_start();
include 'config.php';

$message = '';
$messageType = '';
$step = 1; // Step 1: Email verification, Step 2: New password

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // STEP 1: Verify Email
    if (isset($_POST['verify_email'])) {
        $email = trim($_POST['email']);
        
        if (empty($email)) {
            $message = "Please enter your email address.";
            $messageType = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $messageType = "error";
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT Customer_ID, Name FROM customer WHERE Email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $step = 2;
                $_SESSION['reset_email'] = $email;
                $message = "Email verified! Please enter your new password.";
                $messageType = "success";
            } else {
                $message = "No account found with that email address.";
                $messageType = "error";
            }
        }
    }
    
    // STEP 2: Update Password
    if (isset($_POST['reset_password'])) {
        $newPassword = trim($_POST['new_password']);
        $confirmPassword = trim($_POST['confirm_password']);
        $email = $_SESSION['reset_email'] ?? '';
        
        if (empty($newPassword) || empty($confirmPassword)) {
            $message = "Please fill in all password fields.";
            $messageType = "error";
            $step = 2;
        } elseif ($newPassword !== $confirmPassword) {
            $message = "Passwords do not match!";
            $messageType = "error";
            $step = 2;
        } elseif (strlen($newPassword) < 6) {
            $message = "Password must be at least 6 characters long.";
            $messageType = "error";
            $step = 2;
        } else {
            // Update password in database (plain text)
            $updateStmt = $pdo->prepare("UPDATE customer SET Password = ? WHERE Email = ?");
            $updateStmt->execute([$newPassword, $email]);
            
            $message = "Password reset successful! You can now login with your new password.";
            $messageType = "success";
            $step = 1;
            unset($_SESSION['reset_email']);
        }
    }
}

// Check if we're in step 2
if (isset($_SESSION['reset_email']) && $step == 1) {
    $step = 2;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Solace Resort</title>
    <link rel="stylesheet" href="assets/forgot_password.css">
</head>

<body>

<!-- Back Button -->
<a href="login.php" class="back-btn">
    <span>←</span> Back to Login
</a>

<div class="forgot-password-container">
    <div class="forgot-password-card">
        
        <!-- Icon/Logo -->
        <div class="icon-wrapper">
            <div class="lock-icon">🔒</div>
        </div>

        <!-- Header -->
        <div class="header-section">
            <h1><?= $step == 1 ? 'Reset Password' : 'Create New Password' ?></h1>
            <p class="subtitle">
                <?= $step == 1 
                    ? 'Enter your email address to reset your password.' 
                    : 'Enter your new password below.' 
                ?>
            </p>
        </div>

        <!-- Message Display -->
        <?php if ($message): ?>
            <div class="message-box <?= $messageType ?>">
                <span class="message-icon">
                    <?= $messageType === 'success' ? '✓' : '⚠' ?>
                </span>
                <div class="message-text">
                    <?= $message ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <!-- STEP 1: Email Verification Form -->
            <form method="POST" class="forgot-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email address"
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <button type="submit" name="verify_email" class="submit-btn">
                    <span class="btn-icon">🔍</span>
                    <span>Verify Email</span>
                </button>
            </form>

        <?php else: ?>
            <!-- STEP 2: New Password Form -->
            <form method="POST" class="forgot-form">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔑</span>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            placeholder="Enter new password"
                            required
                            minlength="6"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔐</span>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm new password"
                            required
                            minlength="6"
                        >
                    </div>
                </div>

                <div class="password-requirements">
                    <p>Password requirements:</p>
                    <ul>
                        <li>At least 6 characters long</li>
                        <li>Both passwords must match</li>
                    </ul>
                </div>

                <button type="submit" name="reset_password" class="submit-btn">
                    <span class="btn-icon">✓</span>
                    <span>Reset Password</span>
                </button>

                <button type="button" onclick="cancelReset()" class="cancel-btn">
                    Cancel
                </button>
            </form>
        <?php endif; ?>

        <!-- Additional Links -->
        <div class="footer-links">
            <p>Remember your password? <a href="login.php">Login here</a></p>
            <p>Don't have an account? <a href="register.php">Sign up</a></p>
        </div>

        <!-- Security Note -->
        <div class="security-note">
            <span class="security-icon">🛡️</span>
            <p>For security reasons, make sure to use a strong and unique password.</p>
        </div>

    </div>
</div>

<script>
// Cancel reset and go back to step 1
function cancelReset() {
    window.location.href = 'forgot_password.php';
}

// Password match validation
const form = document.querySelector('.forgot-form');
if (form) {
    form.addEventListener('submit', function(e) {
        const newPass = document.getElementById('new_password');
        const confirmPass = document.getElementById('confirm_password');
        
        if (newPass && confirmPass) {
            if (newPass.value !== confirmPass.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (newPass.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        }
    });
}

// Auto-hide success message after 5 seconds
<?php if ($messageType === 'success' && $step == 1): ?>
setTimeout(function() {
    const messageBox = document.querySelector('.message-box.success');
    if (messageBox) {
        messageBox.style.opacity = '0';
        setTimeout(() => {
            messageBox.remove();
            window.location.href = 'login.php';
        }, 300);
    }
}, 5000);
<?php endif; ?>
</script>

</body>
</html>