<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['admin_username'] ?? 'Admin';

// Get dashboard statistics
$stats = [
    'total_products' => 0,
    'total_customers' => 0,
    'total_orders' => 0,
    'total_revenue' => 0
];

// Get product count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_products'] = $row['count'];
}

// Get order count from purchase table
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM purchase");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_orders'] = $row['count'];
}

// Get total revenue
$result = mysqli_query($conn, "SELECT SUM(cost_of_items) as total FROM purchase");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_revenue'] = $row['total'] ?? 0;
}

// Get recent orders
$recent_orders = [];
$result = mysqli_query($conn, "SELECT p.*, c.user as customer_name FROM purchase p LEFT JOIN customer c ON p.pcid = c.ID ORDER BY p.date_time DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $recent_orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FreshMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --danger-color: #ef4444;
            --text-dark: #111827;
            --text-secondary: #6b7280;
            --bg-light: #f9fafb;
            --white: #ffffff;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .sidebar {
            background: var(--white);
            box-shadow: var(--shadow);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            border-bottom: 1px solid #e5e7eb;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar-menu i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        .top-bar {
            background: var(--white);
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-logout {
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #dc2626;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.products {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
        }

        .stat-icon.customers {
            background: rgba(6, 182, 212, 0.1);
            color: var(--secondary-color);
        }

        .stat-icon.orders {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .stat-icon.revenue {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .recent-orders {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th,
        .order-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .order-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-badge.completed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .status-badge.pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }
        
        .status-badge.dispatched {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info-color);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-shield-alt me-2"></i>Admin Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin_orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div class="welcome-text">
                <i class="fas fa-hand-wave me-2"></i>Welcome back, <?php echo htmlspecialchars($username); ?>
            </div>
            <div class="user-info">
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders">
            <h3 class="section-title">
                <i class="fas fa-clock me-2"></i>Recent Orders
            </h3>
            <div class="table-responsive">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No recent orders found</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['pcid'] . '-' . $order['ppid']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                                <td><?php echo htmlspecialchars($order['Item_name'] ?? 'Product #' . $order['ppid']); ?></td>
                                <td><?php echo intval($order['no_of_items']); ?></td>
                                <td>₹<?php echo number_format($order['cost_of_items'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['date_time'])); ?></td>
                                <td><span class="status-badge <?php echo strtolower($order['status'] ?? 'pending'); ?>"><?php echo ucfirst($order['status'] ?? 'Pending'); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }

        // Add mobile menu button if needed
        if (window.innerWidth <= 768) {
            const topBar = document.querySelector('.top-bar');
            const menuButton = document.createElement('button');
            menuButton.className = 'btn btn-outline-primary me-3';
            menuButton.innerHTML = '<i class="fas fa-bars"></i>';
            menuButton.onclick = toggleSidebar;
            topBar.insertBefore(menuButton, topBar.firstChild);
        }
    </script>
</body>
</html>