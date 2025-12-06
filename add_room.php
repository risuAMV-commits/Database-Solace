<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_type = $_POST['room_type'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = 'assets/rooms/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $image = time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        }
    }
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO room (Room_Type, Price_Per_Night, Availability, Description, Category, Image) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$room_type, $price, $availability, $description, $category, $image])) {
        header("Location: manage_rooms.php?added=1");
        exit();
    } else {
        $error_message = "Error adding room.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room - Solace Resort</title>
    <link rel="stylesheet" href="assets/admin.css">
    <link rel="stylesheet" href="assets/add_room.css">
</head>
<body class="admin-bg">

<!-- Hamburger Menu -->
<input type="checkbox" id="menu-toggle" class="menu-toggle">
<label for="menu-toggle" class="menu-icon">☰</label>

<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <div class="page-wrapper">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1>➕ Add New Room</h1>
                <p class="subtitle">Create a new room listing</p>
            </div>
            <div class="header-actions">
                <a href="manage_rooms.php" class="btn-back">← Back to Rooms</a>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            ✗ <?= $error_message ?>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="edit-form" id="addRoomForm">
            
            <div class="form-grid">
                
                <!-- Room Details Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>🛏️ Room Details</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="room_type">Room Type *</label>
                            <input type="text" name="room_type" id="room_type" required placeholder="e.g., Deluxe Suite">
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Room Category *</label>
                            <select name="category" id="category" required>
                                <option value="Regular">Regular</option>
                                <option value="VIP">VIP</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea name="description" id="description" rows="4" required placeholder="Describe the room features..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Availability Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>💰 Pricing & Availability</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="price">Price Per Night (₱) *</label>
                            <input type="number" name="price" id="price" step="0.01" required placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="availability">Availability Status *</label>
                            <select name="availability" id="availability" required>
                                <option value="Available">Available</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>

                        <div class="info-box">
                            <strong>💡 Tip:</strong> Set the room as "Available" if it's ready for booking.
                        </div>
                    </div>
                </div>

                <!-- Room Image Card -->
                <div class="form-card full-width">
                    <div class="form-card-header">
                        <h3>🖼️ Room Image</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="image">Upload Room Image</label>
                            <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)">
                            <small style="color: #6b7280; display: block; margin-top: 0.5rem;">Supported formats: JPG, JPEG, PNG, GIF</small>
                        </div>

                        <div id="imagePreview" style="display: none; margin-top: 1rem;">
                            <label>Preview:</label>
                            <div style="margin-top: 0.5rem;">
                                <img id="preview" style="max-width: 300px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="button" onclick="window.location.href='manage_rooms.php'" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    ✓ Add Room
                </button>
            </div>

        </form>

    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

// Form validation
document.getElementById('addRoomForm').addEventListener('submit', function(e) {
    const price = parseFloat(document.getElementById('price').value);
    
    if (price <= 0) {
        e.preventDefault();
        alert('Price must be greater than 0!');
        return false;
    }
});
</script>

</body>
</html>