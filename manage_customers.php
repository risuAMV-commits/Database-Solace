<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

/* ------------ HANDLE STATUS UPDATE ------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === "update") {
    $custID = intval($_POST['customer_id']);
    $status = $_POST['status'];
    $update = $pdo->prepare("UPDATE customer SET VIP_Status = ? WHERE Customer_ID = ?");
    $update->execute([$status, $custID]);
    header("Location: manage_customers.php?updated=1");
    exit();
}

/* ------------ HANDLE CUSTOMER DELETE ------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === "delete") {
    $custID = intval($_POST['customer_id']);
    $del = $pdo->prepare("DELETE FROM customer WHERE Customer_ID = ?");
    $del->execute([$custID]);
    header("Location: manage_customers.php?deleted=1");
    exit();
}

/* ------------ FETCH CUSTOMERS ------------- */
$stmt = $pdo->query("SELECT Customer_ID, Name, Email, Contact_Number, VIP_Status FROM customer ORDER BY Customer_ID DESC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count statistics
$totalCustomers = count($customers);
$vipCount = count(array_filter($customers, fn($c) => $c['VIP_Status'] === 'VIP'));
$regularCount = $totalCustomers - $vipCount;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Solace Resort</title>
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        /* Modern Booking-Style Design for Customers */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            background: #f0f2f5;
            min-height: 100vh;
        }

        .page-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .page-title-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .title-group h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .title-icon {
            font-size: 2.5rem;
        }

        .title-group p {
            color: #666;
            margin: 0.5rem 0 0 0;
            font-size: 0.95rem;
        }

        .live-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 0.625rem 1.25rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Search and Filter Bar */
        .controls-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .search-wrapper {
            flex: 1;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.25rem;
            color: #9ca3af;
        }

        .filter-select {
            padding: 0.875rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
        }

        /* Data Section */
        .data-section {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .section-header {
            padding: 1.5rem 2rem;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .count-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Modern Table */
        .modern-table-wrapper {
            overflow-x: auto;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table thead {
            background: #f9fafb;
        }

        .modern-table th {
            padding: 1.125rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #6b7280;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .modern-table td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-size: 0.9375rem;
        }

        .modern-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .modern-table tbody tr:hover {
            background: #f9fafb;
        }

        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Customer Cell */
        .customer-cell {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .customer-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .customer-name {
            font-weight: 500;
            color: #1f2937;
        }

        /* Status Badges */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-vip {
            background: #fef3c7;
            color: #92400e;
        }

        .status-regular {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Action Buttons */
        .action-group {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .status-dropdown {
            padding: 0.5rem 0.875rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.8125rem;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .status-dropdown:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }

        .btn-save {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-save:hover {
            background: #3b82f6;
            color: white;
            transform: scale(1.05);
        }

        .btn-delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
            transform: scale(1.05);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .page-card {
                padding: 1.5rem;
            }

            .controls-bar {
                flex-direction: column;
            }

            .title-group h1 {
                font-size: 1.5rem;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.875rem;
                font-size: 0.8125rem;
            }

            .action-group {
                flex-direction: column;
                gap: 0.375rem;
            }
        }
    </style>
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
                        <span class="title-icon">👥</span>
                        Manage Customers
                    </h1>
                    <p>View and manage customer accounts and VIP status</p>
                </div>
                <div class="live-badge">
                    <span class="pulse-dot"></span>
                    Live Data
                </div>
            </div>

            <!-- Success Messages -->
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">
                    <span style="font-size: 1.25rem;">✓</span>
                    <span>Customer status updated successfully!</span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">
                    <span style="font-size: 1.25rem;">✓</span>
                    <span>Customer deleted successfully!</span>
                </div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="controls-bar">
                <div class="search-wrapper">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search by customer name, email, or contact..." onkeyup="filterCustomers()">
                </div>
                <select id="statusFilter" class="filter-select" onchange="filterCustomers()">
                    <option value="">All Status</option>
                    <option value="VIP">VIP</option>
                    <option value="Regular">Regular</option>
                </select>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="data-section">
            <div class="section-header">
                <h2 class="section-title">
                    📋 All Customers
                </h2>
                <span class="count-badge"><?= $totalCustomers ?> total customers</span>
            </div>

            <div class="modern-table-wrapper">
                <table class="modern-table" id="customersTable">
                    <thead>
                        <tr>
                            <th>CUSTOMER ID</th>
                            <th>CUSTOMER</th>
                            <th>EMAIL</th>
                            <th>CONTACT</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><strong>#<?= $c['Customer_ID'] ?></strong></td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar"><?= strtoupper(substr($c['Name'], 0, 1)); ?></div>
                                    <span class="customer-name"><?= htmlspecialchars($c['Name']); ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($c['Email']); ?></td>
                            <td><?= htmlspecialchars($c['Contact_Number']); ?></td>
                            <td>
                                <?php if ($c['VIP_Status'] === 'VIP'): ?>
                                    <span class="status-pill status-vip">👑 VIP</span>
                                <?php else: ?>
                                    <span class="status-pill status-regular">⭐ Regular</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="customer_id" value="<?= $c['Customer_ID']; ?>">
                                        <input type="hidden" name="action" value="update">
                                        <select name="status" class="status-dropdown">
                                            <option value="Regular" <?= ($c['VIP_Status'] === 'Regular' ? 'selected' : '') ?>>Regular</option>
                                            <option value="VIP" <?= ($c['VIP_Status'] === 'VIP' ? 'selected' : '') ?>>VIP</option>
                                        </select>
                                        <button type="submit" class="icon-btn btn-save" title="Save changes">💾</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('⚠️ Delete this customer? This cannot be undone!');">
                                        <input type="hidden" name="customer_id" value="<?= $c['Customer_ID']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="icon-btn btn-delete" title="Delete">🗑️</button>
                                    </form>
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
function filterCustomers() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const statusValue = document.getElementById('statusFilter').value;
    const table = document.getElementById('customersTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        const customerId = cells[0].textContent.toLowerCase();
        const customerName = cells[1].textContent.toLowerCase();
        const email = cells[2].textContent.toLowerCase();
        const contact = cells[3].textContent.toLowerCase();
        const status = cells[4].textContent;

        const matchesSearch = customerId.includes(searchValue) || 
                            customerName.includes(searchValue) || 
                            email.includes(searchValue) ||
                            contact.includes(searchValue);
        const matchesStatus = statusValue === '' || status.includes(statusValue);

        rows[i].style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    });
});
</script>

</body>
</html>