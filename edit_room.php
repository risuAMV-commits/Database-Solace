<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Get room ID from URL
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_type = $_POST['room_type'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    // Handle image upload
    $image = $_POST['existing_image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = 'assets/rooms/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Delete old image if exists
            if ($image && file_exists($upload_dir . $image)) {
                unlink($upload_dir . $image);
            }
            $image = time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        }
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE room SET Room_Type = ?, Price_Per_Night = ?, Availability = ?, Description = ?, Category = ?, Image = ? WHERE Room_ID = ?");
    
    if ($stmt->execute([$room_type, $price, $availability, $description, $category, $image, $room_id])) {
        header("Location: edit_room.php?id=$room_id&success=1");
        exit();
    } else {
        $error_message = "Error updating room.";
    }
}

// Fetch room details
$stmt = $pdo->prepare("SELECT * FROM room WHERE Room_ID = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    header("Location: manage_rooms.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room #<?= $room['Room_ID'] ?> - Solace Resort</title>
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
                <h1>✏️ Edit Room</h1>
                <p class="subtitle">Room ID: #<?= $room['Room_ID'] ?></p>
            </div>
            <div class="header-actions">
                <a href="manage_rooms.php" class="btn-back">← Back to Rooms</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            ✓ Room updated successfully!
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            ✗ <?= $error_message ?>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="edit-form" id="editRoomForm">
            <input type="hidden" name="existing_image" value="<?= $room['Image'] ?>">
            
            <div class="form-grid">
                
                <!-- Room Details Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>🛏️ Room Details</h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label for="room_type">Room Type *</label>
                            <input type="text" name="room_type" id="room_type" value="<?= htmlspecialchars($room['Room_Type']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Room Category *</label>
                            <select name="category" id="category" required>
                                <option value="Regular" <?= ($room['Category'] ?? 'Regular') === 'Regular' ? 'selected' : '' ?>>Regular</option>
                                <option value="VIP" <?= ($room['Category'] ?? 'Regular') === 'VIP' ? 'selected' : '' ?>>VIP</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea name="description" id="description" rows="4" required><?= htmlspecialchars($room['Description']) ?></textarea>
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
                            <input type="number" name="price" id="price" step="0.01" value="<?= $room['Price_Per_Night'] ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="availability">Availability Status *</label>
                            <select name="availability" id="availability" required>
                                <option value="Available" <?= $room['Availability'] === 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Unavailable" <?= $room['Availability'] === 'Unavailable' ? 'selected' : '' ?>>Unavailable</option>
                            </select>
                        </div>

                        <div class="info-box">
                            <strong>Current Status:</strong> <?= $room['Availability'] ?><br>
                            <strong>Category:</strong> <?= $room['Category'] ?? 'Regular' ?>
                        </div>
                    </div>
                </div>

                <!-- Room Image Card -->
                <div class="form-card full-width">
                    <div class="form-card-header">
                        <h3>🖼️ Room Image</h3>
                    </div>
                    <div class="form-card-body">
                        <?php if ($room['Image'] && file_exists("assets/rooms/" . $room['Image'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <label>Current Image:</label>
                            <div style="margin-top: 0.5rem;">
                                <img src="assets/rooms/<?= $room['Image'] ?>" style="max-width: 300px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="image">Upload New Image (Optional)</label>
                            <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)">
                            <small style="color: #6b7280; display: block; margin-top: 0.5rem;">Leave empty to keep current image. Supported formats: JPG, JPEG, PNG, GIF</small>
                        </div>

                        <div id="imagePreview" style="display: none; margin-top: 1rem;">
                            <label>New Image Preview:</label>
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
                    💾 Save Changes
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
document.getElementById('editRoomForm').addEventListener('submit', function(e) {
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