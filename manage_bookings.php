<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all bookings with customer and room details - CORRECTED COLUMN NAMES
$stmt = $pdo->query("
    SELECT 
        b.Booking_ID, 
        b.Customer_ID, 
        b.Room_ID, 
        b.Book_In, 
        b.Book_Out, 
        b.Nights_Stayed, 
        b.Total_Price, 
        b.Status,
        c.Name as CustomerName, 
        c.Email
    FROM booking b
    LEFT JOIN customer c ON b.Customer_ID = c.Customer_ID
    ORDER BY b.Booking_ID DESC
");
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Solace Resort</title>
    <link rel="stylesheet" href="assets/admin.css">
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
                <h1>📅 Manage Bookings</h1>
                <p class="subtitle">View and manage all resort bookings</p>
            </div>
            <span class="live-indicator">
                <span class="pulse-dot"></span>
                Live Data
            </span>
        </div>

        <!-- Search and Filter Bar -->
        <div class="filter-bar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search by customer name, email, or booking ID..." onkeyup="filterBookings()">
            </div>
            <div class="filter-controls">
                <select id="statusFilter" onchange="filterBookings()">
                    <option value="">All Status</option>
                    <option value="Booked">Booked</option>
                    <option value="Cancelled">Cancelled</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="table-card">
            <div class="table-header">
                <h3>📋 All Bookings</h3>
                <span class="record-count"><?= count($bookings) ?> total bookings</span>
            </div>
            
            <div class="table-responsive">
                <table class="data-table" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Room</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Nights</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><strong>#<?= $booking['Booking_ID'] ?></strong></td>
                            <td><?= htmlspecialchars($booking['CustomerName']) ?></td>
                            <td><?= htmlspecialchars($booking['Email']) ?></td>
                            <td>
                                <span class="room-badge">
                                    Room #<?= $booking['Room_ID'] ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($booking['Book_In'])) ?></td>
                            <td><?= date('M d, Y', strtotime($booking['Book_Out'])) ?></td>
                            <td><span class="nights-badge"><?= $booking['Nights_Stayed'] ?> nights</span></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($booking['Status']) ?>">
                                    <?= $booking['Status'] ?>
                                </span>
                            </td>
                            <td><strong>₱<?= number_format($booking['Total_Price'], 2) ?></strong></td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="viewBooking(<?= $booking['Booking_ID'] ?>)" class="btn-action btn-view" title="View Details">👁️</button>
                                    <button onclick="editBooking(<?= $booking['Booking_ID'] ?>)" class="btn-action btn-edit" title="Edit Booking">✏️</button>
                                    <button onclick="cancelBooking(<?= $booking['Booking_ID'] ?>)" class="btn-action btn-delete" title="Cancel Booking">❌</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
// Filter bookings by search and status
function filterBookings() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const statusValue = document.getElementById('statusFilter').value.toLowerCase();
    const table = document.getElementById('bookingsTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        
        const bookingId = cells[0].textContent.toLowerCase();
        const customerName = cells[1].textContent.toLowerCase();
        const email = cells[2].textContent.toLowerCase();
        const status = cells[7].textContent.toLowerCase();

        const matchesSearch = bookingId.includes(searchValue) || 
                            customerName.includes(searchValue) || 
                            email.includes(searchValue);
        const matchesStatus = statusValue === '' || status.includes(statusValue);

        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// View booking details
function viewBooking(id) {
    window.location.href = `view_booking.php?id=${id}`;
}

// Edit booking
function editBooking(id) {
    window.location.href = `edit_booking.php?id=${id}`;
}

// Cancel booking
function cancelBooking(id) {
    if (confirm('Are you sure you want to cancel this booking?')) {
        fetch('cancel_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `booking_id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Booking cancelled successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the booking.');
        });
    }
}

// Auto-refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>

<style>
/* Additional styles for manage_booking page */
.filter-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 300px;
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-controls select {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-controls select:focus {
    outline: none;
    border-color: #3b82f6;
}

.table-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 2px solid #f3f4f6;
}

.table-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #1f2937;
}

.record-count {
    background: #eff6ff;
    color: #3b82f6;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f9fafb;
}

.data-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 1rem;
    border-top: 1px solid #f3f4f6;
    color: #374151;
    font-size: 0.95rem;
}

.data-table tbody tr {
    transition: background-color 0.2s ease;
}

.data-table tbody tr:hover {
    background: #f9fafb;
}

.room-badge {
    display: inline-block;
    background: #eff6ff;
    color: #3b82f6;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
}

.nights-badge {
    display: inline-block;
    background: #f3f4f6;
    color: #6b7280;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-booked {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.status-completed {
    background: #dbeafe;
    color: #1e40af;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-view {
    background: #dbeafe;
}

.btn-view:hover {
    background: #3b82f6;
    transform: scale(1.1);
}

.btn-edit {
    background: #fef3c7;
}

.btn-edit:hover {
    background: #f59e0b;
    transform: scale(1.1);
}

.btn-delete {
    background: #fee2e2;
}

.btn-delete:hover {
    background: #ef4444;
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .filter-bar {
        flex-direction: column;
    }
    
    .search-box {
        min-width: 100%;
    }
    
    .table-responsive {
        overflow-x: scroll;
    }
}
</style>

</body>
</html>