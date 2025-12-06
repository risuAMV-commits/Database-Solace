<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// FETCH ALL ROOMS
$stmt = $pdo->query("SELECT * FROM room ORDER BY Room_ID DESC");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalRooms = count($rooms);
$availableRooms = count(array_filter($rooms, function($r) { return strtolower($r['Availability']) === 'available'; }));
$occupiedRooms = count(array_filter($rooms, function($r) { return strtolower($r['Availability']) === 'unavailable'; }));
$vipRooms = count(array_filter($rooms, function($r) { return ($r['Category'] ?? 'Regular') === 'VIP'; }));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Solace Resort</title>
    <link rel="stylesheet" href="assets/admin.css">
    <link rel="stylesheet" href="assets/manage_rooms.css">
</head>

<body class="admin-bg">

<!-- Hamburger Menu -->
<input type="checkbox" id="menu-toggle" class="menu-toggle">
<label for="menu-toggle" class="menu-icon">☰</label>

<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <div class="main-container">
        
        <div class="page-card">
            <!-- Page Header -->
            <div class="page-title-section">
                <div class="title-group">
                    <h1>
                        <span class="title-icon">🛏️</span>
                        Manage Rooms
                    </h1>
                    <p>View and manage all resort rooms</p>
                </div>
                <a href="add_room.php" class="btn-add-room">
                    ➕ Add New Room
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-icon">🏨</div>
                    <div class="stat-content">
                        <h3><?= $totalRooms ?></h3>
                        <p>Total Rooms</p>
                    </div>
                </div>

                <div class="stat-card available">
                    <div class="stat-icon">✓</div>
                    <div class="stat-content">
                        <h3><?= $availableRooms ?></h3>
                        <p>Available</p>
                    </div>
                </div>

                <div class="stat-card occupied">
                    <div class="stat-icon">🚪</div>
                    <div class="stat-content">
                        <h3><?= $occupiedRooms ?></h3>
                        <p>Occupied</p>
                    </div>
                </div>

                <div class="stat-card vip">
                    <div class="stat-icon">👑</div>
                    <div class="stat-content">
                        <h3><?= $vipRooms ?></h3>
                        <p>VIP Rooms</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regular Rooms Section -->
        <div class="data-section">
            <div class="section-header">
                <h2 class="section-title">
                    🛏️ Regular Rooms
                </h2>
            </div>

            <div class="modern-table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>IMAGE</th>
                            <th>ROOM NO.</th>
                            <th>TYPE</th>
                            <th>PRICE</th>
                            <th>STATUS</th>
                            <th>DESCRIPTION</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $hasRegular = false;
                    foreach ($rooms as $r):
                        if (($r['Category'] ?? 'Regular') !== 'Regular') continue;
                        $hasRegular = true;

                        $image = $r['Image'] ?? '';
                        $roomId = $r['Room_ID'];
                        $type = $r['Room_Type'];
                        $price = $r['Price_Per_Night'];
                        $avail = $r['Availability'];
                        $desc = $r['Description'];

                        $statusLower = strtolower(trim($avail));
                        $statusClass = ($statusLower === 'available') ? 'status-available' : 'status-unavailable';
                        $statusLabel = ($statusLower === 'available') ? 'Available' : 'Unavailable';
                    ?>
                        <tr>
                            <td>
                                <?php if ($image && file_exists("assets/rooms/" . $image)): ?>
                                    <div class="room-image-cell">
                                        <img src="assets/rooms/<?= $image; ?>" alt="Room <?= $roomId ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="room-no-image">🖼️</div>
                                <?php endif; ?>
                            </td>
                            <td><span class="room-id-badge">Room <?= $roomId ?></span></td>
                            <td><?= htmlspecialchars($type) ?></td>
                            <td class="price-cell">₱<?= number_format($price, 2) ?></td>
                            <td>
                                <span class="status-pill <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($desc) ?></td>
                            <td>
                                <div class="action-group">
                                    <a href="edit_room.php?id=<?= $roomId ?>" class="icon-btn btn-edit" title="Edit">✏️</a>
                                    <a href="delete_room.php?id=<?= $roomId ?>" 
                                       class="icon-btn btn-delete" 
                                       title="Delete"
                                       onclick="return confirm('⚠️ Delete this room? This cannot be undone!');">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$hasRegular): ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <div style="font-size: 2rem;">📭</div>
                                <p>No Regular rooms found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- VIP Rooms Section -->
        <div class="data-section vip-section">
            <div class="section-header">
                <h2 class="section-title">
                    👑 VIP Rooms
                </h2>
            </div>

            <div class="modern-table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>IMAGE</th>
                            <th>ROOM NO.</th>
                            <th>TYPE</th>
                            <th>PRICE</th>
                            <th>STATUS</th>
                            <th>DESCRIPTION</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $hasVIP = false;
                    foreach ($rooms as $r):
                        if (($r['Category'] ?? 'Regular') !== 'VIP') continue;
                        $hasVIP = true;

                        $image = $r['Image'] ?? '';
                        $roomId = $r['Room_ID'];
                        $type = $r['Room_Type'];
                        $price = $r['Price_Per_Night'];
                        $avail = $r['Availability'];
                        $desc = $r['Description'];

                        $statusLower = strtolower(trim($avail));
                        $statusClass = ($statusLower === 'available') ? 'status-available' : 'status-unavailable';
                        $statusLabel = ($statusLower === 'available') ? 'Available' : 'Unavailable';
                    ?>
                        <tr>
                            <td>
                                <?php if ($image && file_exists("assets/rooms/" . $image)): ?>
                                    <div class="room-image-cell">
                                        <img src="assets/rooms/<?= $image; ?>" alt="Room <?= $roomId ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="room-no-image">🖼️</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="room-id-badge">Room <?= $roomId ?></span>
                                <span class="vip-badge">👑 VIP</span>
                            </td>
                            <td><?= htmlspecialchars($type) ?></td>
                            <td class="price-cell">₱<?= number_format($price, 2) ?></td>
                            <td>
                                <span class="status-pill <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($desc) ?></td>
                            <td>
                                <div class="action-group">
                                    <a href="edit_room.php?id=<?= $roomId ?>" class="icon-btn btn-edit" title="Edit">✏️</a>
                                    <a href="delete_room.php?id=<?= $roomId ?>" 
                                       class="icon-btn btn-delete" 
                                       title="Delete"
                                       onclick="return confirm('⚠️ Delete this room? This cannot be undone!');">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$hasVIP): ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <div style="font-size: 2rem;">📭</div>
                                <p>No VIP rooms found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>