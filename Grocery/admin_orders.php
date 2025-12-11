<?php
session_start();
include 'dbconnection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pcid = intval($_POST['pcid']);
    $ppid = intval($_POST['ppid']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE purchase SET status = '$new_status' WHERE pcid = $pcid AND ppid = $ppid";
    mysqli_query($conn, $update_query);
    
    $_SESSION['success_message'] = "Order status updated successfully!";
    header('Location: admin_orders.php');
    exit();
}

// Handle status updates for new orders table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_new_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status = '$new_status' WHERE order_id = $order_id");
    $_SESSION['success_message'] = "Order status updated successfully!";
    header('Location: admin_orders.php');
    exit();
}

// Detect products schema
$columns = [];
$colRes = mysqli_query($conn, "SHOW COLUMNS FROM products");
if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
$colId = isset($columns['ID']) ? 'ID' : 'product_id';
$colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
$colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');

// Legacy purchase-based orders
$orders_query = "SELECT p.*, c.user as customer_name, c.phone_no, pr.".$colName." AS Item_name, pr.".$colImage." AS image 
                 FROM purchase p 
                 LEFT JOIN customer c ON p.pcid = c.ID 
                 LEFT JOIN products pr ON p.ppid = pr.".$colId." 
                 ORDER BY p.date_time DESC";
$orders_result = mysqli_query($conn, $orders_query);
$orders = [];
while ($row = mysqli_fetch_assoc($orders_result)) { $orders[] = $row; }

// New orders table
$ordersCols = [];
$ordersRes = mysqli_query($conn, "SHOW COLUMNS FROM orders");
if ($ordersRes) { while ($oc = mysqli_fetch_assoc($ordersRes)) { $ordersCols[$oc['Field']] = true; } }
$colCust = isset($ordersCols['customer_id']) ? 'customer_id' : (isset($ordersCols['user_id']) ? 'user_id' : 'customer_id');
$new_orders = [];
$orders_query2 = "SELECT o.*, c.user as customer_name 
                  FROM orders o 
                  LEFT JOIN customer c ON o.".$colCust." = c.ID 
                  ORDER BY o.order_date DESC";
$orders_result2 = mysqli_query($conn, $orders_query2);
while ($row = mysqli_fetch_assoc($orders_result2)) { $new_orders[] = $row; }

// Get order status counts
$status_counts = [
    'pending' => 0,
    'in_process' => 0,
    'dispatched' => 0,
    'delivered' => 0,
    'cancelled' => 0
];

foreach ($orders as $order) {
    $status = strtolower($order['status']);
    if (isset($status_counts[$status])) {
        $status_counts[$status]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
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
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .sidebar-header h4 {
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li {
            margin: 0.25rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: var(--primary-color);
            background: rgba(79, 70, 229, 0.1);
            border-right: 3px solid var(--primary-color);
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
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logout-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .orders-table {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: var(--bg-light);
            color: var(--text-dark);
            font-weight: 600;
            border: none;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e5e7eb;
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-in_process {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-dispatched {
            background: #e0e7ff;
            color: #4338ca;
        }

        .status-delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .alert {
            border: none;
            border-radius: var(--radius);
            padding: 1rem 1.5rem;
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
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

            .table-responsive {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-store me-2"></i>FreshMart</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="admin_products.php"><i class="fas fa-box"></i>Products</a></li>
            <li><a href="admin_orders.php" class="active"><i class="fas fa-shopping-cart"></i>Orders</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1 class="page-title">Manage Orders</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Order Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $status_counts['pending']; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $status_counts['in_process']; ?></div>
                <div class="stat-label">In Process</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $status_counts['dispatched']; ?></div>
                <div class="stat-label">Dispatched</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $status_counts['delivered']; ?></div>
                <div class="stat-label">Delivered</div>
            </div>
        </div>

        <!-- Legacy Orders Table -->
        <div class="orders-table">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No legacy orders found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['pcid'] . '-' . $order['ppid']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?>
                                        <?php if ($order['phone_no']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($order['phone_no']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($order['image'] && file_exists($order['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($order['image']); ?>" alt="" class="product-img me-2">
                                            <?php else: ?>
                                                <img src="images/1.jpg" alt="" class="product-img me-2">
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($order['Item_name'] ?? 'Product #' . $order['ppid']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo intval($order['no_of_items']); ?></td>
                                    <td>₹<?php echo number_format($order['cost_of_items'], 2); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['date_time'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status']))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="pcid" value="<?php echo $order['pcid']; ?>">
                                            <input type="hidden" name="ppid" value="<?php echo $order['ppid']; ?>">
                                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_process" <?php echo $order['status'] === 'in_process' ? 'selected' : ''; ?>>In Process</option>
                                                <option value="dispatched" <?php echo $order['status'] === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- New Orders Table -->
        <div class="orders-table mt-4">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($new_orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No new orders found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($new_orders as $order): ?>
                                <?php
                                $order_id = intval($order['order_id']);
                                $items = [];
                                $itemsRes = mysqli_query($conn, "SELECT oi.*, p.".$colName." AS name, p.".$colImage." AS image FROM order_items oi JOIN products p ON oi.product_id = p.".$colId." WHERE oi.order_id = '".$order_id."'");
                                while ($ir = mysqli_fetch_assoc($itemsRes)) { $items[] = $ir; }
                                $itemCount = count($items);
                                $first = $items ? $items[0] : null;
                                ?>
                                <tr>
                                    <td>#<?php echo $order_id; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Customer'); ?></td>
                                    <td>
                                        <?php if ($first): ?>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($first['image'] ?: 'images/1.jpg'); ?>" class="product-img me-2" alt="">
                                                <span><?php echo htmlspecialchars($first['name']); ?></span>
                                                <?php if ($itemCount > 1): ?>
                                                    <small class="text-muted ms-2">+<?php echo $itemCount - 1; ?> more</small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No items</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹<?php echo number_format(floatval($order['total_amount'] ?? 0), 2); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                            <input type="hidden" name="update_new_status" value="1">
                                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
