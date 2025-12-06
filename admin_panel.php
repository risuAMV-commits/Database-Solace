<?php
session_start();
include 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// REAL-TIME COUNTS
$total_customers = $pdo->query("SELECT COUNT(*) FROM customer")->fetchColumn();
$total_rooms = $pdo->query("SELECT COUNT(*) FROM room")->fetchColumn();

// Occupied rooms = rooms with active bookings (Status = 'Booked')
$occupied_rooms = $pdo->query("
    SELECT COUNT(DISTINCT Room_ID) 
    FROM booking 
    WHERE Status = 'Booked'
")->fetchColumn();

// Available rooms = Total rooms - Occupied rooms
$available_rooms = $total_rooms - $occupied_rooms;

$total_bookings = $pdo->query("SELECT COUNT(*) FROM booking WHERE Status = 'Booked'")->fetchColumn();
$cancelled_bookings = $pdo->query("SELECT COUNT(*) FROM booking WHERE Status = 'Cancelled'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Solace Resort</title>
    <link rel="stylesheet" href="assets/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <h1>📊 Dashboard</h1>
                <p class="subtitle">Welcome back, <strong><?php echo $_SESSION['admin']; ?></strong></p>
            </div>
            <span class="live-indicator">
                <span class="pulse-dot"></span>
                Live Data
            </span>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">👥</div>
                <div class="stat-details">
                    <h3><?= $total_customers ?></h3>
                    <p>Total Customers</p>
                    <span class="trend positive">↑ 5% vs last week</span>
                </div>
            </div>

            <div class="stat-card sky">
                <div class="stat-icon">🏨</div>
                <div class="stat-details">
                    <h3><?= $total_rooms ?></h3>
                    <p>Total Rooms</p>
                </div>
            </div>

            <div class="stat-card available">
                <div class="stat-icon">✓</div>
                <div class="stat-details">
                    <h3><?= $available_rooms ?></h3>
                    <p>Available Rooms</p>
                </div>
            </div>

            <div class="stat-card occupied">
                <div class="stat-icon">⛔</div>
                <div class="stat-details">
                    <h3><?= $occupied_rooms ?></h3>
                    <p>Occupied Rooms</p>
                </div>
            </div>

            <div class="stat-card bookings">
                <div class="stat-icon">📅</div>
                <div class="stat-details">
                    <h3><?= $total_bookings ?></h3>
                    <p>Active Bookings</p>
                </div>
            </div>

            <div class="stat-card cancelled">
                <div class="stat-icon">✕</div>
                <div class="stat-details">
                    <h3><?= $cancelled_bookings ?></h3>
                    <p>Cancelled Bookings</p>
                    <span class="trend negative">↓ 3% vs last week</span>
                </div>
            </div>

            <div class="stat-card admins">
                <div class="stat-icon">🔒</div>
                <div class="stat-details">
                    <h3><?= $total_admins ?></h3>
                    <p>Admin Accounts</p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-wrapper">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>📈 Customer Growth</h3>
                    <span class="chart-period">Last 7 Days</span>
                </div>
                <div class="chart-body">
                    <canvas id="customerChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3>📊 Booking Status</h3>
                    <span class="chart-period">Current</span>
                </div>
                <div class="chart-body">
                    <canvas id="bookingStatusChart"></canvas>
                </div>
            </div>

            <div class="chart-card full-width">
                <div class="chart-header">
                    <h3>📉 Cancelled Bookings Trend</h3>
                    <span class="chart-period">Last 30 Days</span>
                </div>
                <div class="chart-body">
                    <canvas id="cancelledChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Initialize Charts
let customerChart, bookingStatusChart, cancelledChart;

// Customer Growth Chart
const ctxCustomer = document.getElementById('customerChart').getContext('2d');
customerChart = new Chart(ctxCustomer, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'New Customers',
            data: [12, 19, 15, 25, 22, 30, <?= $total_customers ?>],
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                cornerRadius: 8,
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 }
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    color: '#6b7280',
                    font: { size: 12 }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#6b7280',
                    font: { size: 12 }
                }
            }
        }
    }
});

// Booking Status Pie Chart
const ctxStatus = document.getElementById('bookingStatusChart').getContext('2d');
bookingStatusChart = new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Cancelled', 'Completed'],
        datasets: [{
            data: [<?= $total_bookings ?>, <?= $cancelled_bookings ?>, 45],
            backgroundColor: ['#3b82f6', '#f59e0b', '#10b981'],
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    color: '#374151',
                    font: {
                        size: 13,
                        weight: '600'
                    },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                cornerRadius: 8,
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 }
            }
        }
    }
});

// Cancelled Bookings Trend Chart
const ctxCancelled = document.getElementById('cancelledChart').getContext('2d');
cancelledChart = new Chart(ctxCancelled, {
    type: 'bar',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
            label: 'Cancelled Bookings',
            data: [8, 12, 6, <?= $cancelled_bookings ?>],
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderColor: '#ef4444',
            borderWidth: 0,
            borderRadius: 10,
            barThickness: 60
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                cornerRadius: 8,
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 }
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    color: '#6b7280',
                    font: { size: 12 }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#6b7280',
                    font: { size: 12 }
                }
            }
        }
    }
});

// Real-Time Update Function
function updateDashboard() {
    fetch('get_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            // Update stat cards with animation
            document.querySelectorAll('.stat-card h3').forEach((element, index) => {
                const values = [
                    data.total_customers,
                    data.total_rooms,
                    data.available_rooms,
                    data.occupied_rooms,
                    data.total_bookings,
                    data.cancelled_bookings,
                    data.total_admins
                ];
                if (values[index] !== undefined) {
                    element.textContent = values[index];
                }
            });

            // Update charts
            customerChart.data.datasets[0].data[6] = data.total_customers;
            customerChart.update();

            bookingStatusChart.data.datasets[0].data = [
                data.total_bookings,
                data.cancelled_bookings,
                45
            ];
            bookingStatusChart.update();

            cancelledChart.data.datasets[0].data[3] = data.cancelled_bookings;
            cancelledChart.update();
        })
        .catch(error => console.error('Error:', error));
}

// Update every 5 seconds
setInterval(updateDashboard, 5000);
</script>

</body>
</html>