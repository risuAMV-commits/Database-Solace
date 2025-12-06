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

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $agree = isset($_POST['agree']);

    // Validation
    if (empty($name) || empty($email) || empty($contact) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!$agree) {
        $error = 'You must agree to the terms and conditions.';
    } else {
        // Check if email already exists
        $checkStmt = $pdo->prepare("SELECT Customer_ID FROM customer WHERE Email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->fetch()) {
            $error = 'Email already registered. Please login instead.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new customer
            $stmt = $pdo->prepare("
                INSERT INTO customer (Name, Email, Contact_Number, Password, VIP_Status) 
                VALUES (?, ?, ?, ?, 'Regular')
            ");
            
            if ($stmt->execute([$name, $email, $contact, $hashed_password])) {
                // Registration successful
                header("Location: login.php?registered=success");
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hotel Booking</title>
    <link rel="stylesheet" href="assets/register.css">
</head>
<body>

<div class="register-container">
    <div class="register-header">
        <div class="register-logo">🏨</div>
        <h1>Create Account</h1>
        <p>Join us and start booking your perfect stay</p>
    </div>

    <form method="POST" class="register-form" id="registerForm">
        <?php if ($error): ?>
        <div class="error-message">
            <span class="error-icon">⚠️</span>
            <span><?= htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Enter your full name"
                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input 
                    type="tel" 
                    id="contact" 
                    name="contact" 
                    placeholder="e.g. 09123456789"
                    value="<?= isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="Enter your email"
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                required
            >
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Minimum 6 characters"
                    required
                >
                <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Re-enter password"
                    required
                >
                <span class="password-toggle" onclick="togglePassword('confirm_password')">👁️</span>
            </div>
        </div>

        <div class="password-strength">
            <div class="strength-bar" id="strengthBar"></div>
            <span class="strength-text" id="strengthText"></span>
        </div>

        <div class="terms-checkbox">
            <input type="checkbox" id="agree" name="agree" required>
            <label for="agree">
                I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a> 
                and <a href="privacy.php" target="_blank">Privacy Policy</a>
            </label>
        </div>

        <button type="submit" class="btn-register">Create Account</button>
    </form>

    <div class="divider">
        <span>OR</span>
    </div>

    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>

    <div class="back-home">
        <a href="index.php">← Back to Home</a>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggle = passwordInput.nextElementSibling;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggle.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggle.textContent = '👁️';
    }
}

// Password strength checker
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    const colors = ['#e74c3c', '#e67e22', '#f39c12', '#2ecc71', '#27ae60'];
    const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const widths = ['20%', '40%', '60%', '80%', '100%'];
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthText.textContent = '';
    } else {
        strengthBar.style.width = widths[strength];
        strengthBar.style.backgroundColor = colors[strength];
        strengthText.textContent = texts[strength];
        strengthText.style.color = colors[strength];
    }
});

// Confirm password validation
const confirmPassword = document.getElementById('confirm_password');
confirmPassword.addEventListener('input', function() {
    if (this.value !== passwordInput.value && this.value.length > 0) {
        this.style.borderColor = '#e74c3c';
    } else {
        this.style.borderColor = '#e0e0e0';
    }
});

// Auto-hide error message after 5 seconds
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