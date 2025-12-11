<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['cid12'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['cid12'];

// Database connection
include 'db_connection.php';

// Check connection
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get order ID from URL or show all orders
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

// Use new schema: orders + order_items
$columns = [];
$colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
$colId = isset($columns['ID']) ? 'ID' : 'product_id';
$colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
$colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');

$ordersCols = [];
$ordersRes = mysqli_query($connect, "SHOW COLUMNS FROM orders");
if ($ordersRes) { while ($oc = mysqli_fetch_assoc($ordersRes)) { $ordersCols[$oc['Field']] = true; } }
$colCust = isset($ordersCols['customer_id']) ? 'customer_id' : (isset($ordersCols['user_id']) ? 'user_id' : 'customer_id');

if ($order_id) {
    $query = "SELECT o.* FROM orders o WHERE o.order_id = '$order_id' AND o.".$colCust." = '$customer_id'";
    $result = mysqli_query($connect, $query);
    $order = mysqli_fetch_assoc($result);
    if (!$order) { header('Location: track_order.php'); exit(); }
    $items_query = "SELECT oi.*, p.".$colName." AS Item_name, p.".$colImage." AS image FROM order_items oi JOIN products p ON oi.product_id = p.".$colId." WHERE oi.order_id = '$order_id'";
    $items_result = mysqli_query($connect, $items_query);
    $order_items = [];
    while ($row = mysqli_fetch_assoc($items_result)) { $order_items[] = $row; }
} else {
    $orders_query = "SELECT o.*, p.".$colName." AS Item_name, p.".$colImage." AS image, oi.quantity, oi.price, oi.subtotal 
                     FROM orders o 
                     JOIN order_items oi ON o.order_id = oi.order_id 
                     JOIN products p ON oi.product_id = p.".$colId." 
                     WHERE o.".$colCust." = '$customer_id' 
                     ORDER BY o.order_date DESC";
    $orders_result = mysqli_query($connect, $orders_query);
    $orders = [];
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $row;
    }
}

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Track Order - FreshMart</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        .navbar {
            background: var(--bg-primary);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border-color);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: 600;
        }

        .tracking-section {
            padding: 3rem 0;
            min-height: calc(100vh - 76px);
        }

        .tracking-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .tracking-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .order-info {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .timeline {
            position: relative;
            padding: 2rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            padding-left: 80px;
            margin-bottom: 2rem;
        }

        .timeline-icon {
            position: absolute;
            left: 20px;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            z-index: 2;
        }

        .timeline-item.completed .timeline-icon {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .timeline-item.active .timeline-icon {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
        }

        .timeline-content {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .timeline-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .timeline-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .timeline-time {
            font-size: 0.8rem;
            color: var(--text-secondary);
            opacity: 0.8;
        }

        .order-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .order-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-processing {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-shipped {
            background: #d1fae5;
            color: #065f46;
        }

        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .progress-bar {
            height: 8px;
            border-radius: var(--radius-sm);
            background: var(--bg-secondary);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
            border-radius: var(--radius-sm);
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .timeline::before {
                left: 20px;
            }
            
            .timeline-item {
                padding-left: 60px;
            }
            
            .timeline-icon {
                left: 10px;
            }
            
            .tracking-header {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<?php include 'partials/navbar_simple.php'; ?>

    <!-- Tracking Section -->
    <section class="tracking-section">
        <div class="container">
<?php if ($order_id && $order): ?>
                <!-- Specific Order Tracking -->
                <div class="tracking-card mb-4">
                    <div class="tracking-header">
                        <h2 class="mb-2">
                            <i class="fas fa-truck me-3"></i>Track Your Order
                        </h2>
                        <p class="mb-0 opacity-75">Order #<?php echo htmlspecialchars($order['order_id']); ?></p>
                    </div>
                    
                    <div class="p-4">
                        <!-- Order Info -->
                        <div class="order-info">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-2">Order Date</h6>
                                    <p class="mb-0 fw-semibold"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-2">Estimated Delivery</h6>
                                    <p class="mb-0 fw-semibold"><?php echo date('F j, Y', strtotime($order['order_date'] . ' + 3 days')); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted mb-2">Status</h6>
                                    <span class="status-badge status-<?php echo strtolower($order['status'] ?? 'pending'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($order['status'] ?? 'pending')); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="progress-bar">
                                <?php
                                $status_progress = [
                                    'pending' => 20,
                                    'confirmed' => 40,
                                    'processing' => 60,
                                    'shipped' => 80,
                                    'delivered' => 100
                                ];
                                $progress = $status_progress[strtolower($order['status'] ?? 'pending')] ?? 0;
                                ?>
                                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                        </div>
                        
                        <!-- Timeline -->
                        <div class="timeline">
                            <?php
                            $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
                            $status_icons = [
                                'pending' => 'fas fa-clock',
                                'confirmed' => 'fas fa-check-circle',
                                'processing' => 'fas fa-cog',
                                'shipped' => 'fas fa-truck',
                                'delivered' => 'fas fa-home'
                            ];
                            $status_titles = [
                                'pending' => 'Order Placed',
                                'confirmed' => 'Order Confirmed',
                                'processing' => 'Processing',
                                'shipped' => 'Out for Delivery',
                                'delivered' => 'Delivered'
                            ];
                            $status_descriptions = [
                                'pending' => 'Your order has been received and is being prepared',
                                'confirmed' => 'Your order has been confirmed and payment verified',
                                'processing' => 'Your order is being prepared for shipment',
                                'shipped' => 'Your order is on its way to your delivery address',
                                'delivered' => 'Your order has been delivered successfully'
                            ];
                            
                            $current_status = strtolower($order['status'] ?? 'pending');
                            $current_reached = false;
                            
                            foreach ($statuses as $status):
                                if ($status === $current_status) {
                                    $current_reached = true;
                                }
                                ?>
                                <div class="timeline-item <?php echo $current_reached ? ($status === $current_status ? 'active' : 'completed') : ''; ?>">
                                    <div class="timeline-icon">
                                        <i class="<?php echo $status_icons[$status]; ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title"><?php echo $status_titles[$status]; ?></h6>
                                        <p class="timeline-description"><?php echo $status_descriptions[$status]; ?></p>
                                        <?php if ($status === $current_status): ?>
                                            <small class="timeline-time">
                                                <i class="fas fa-clock me-1"></i>
                                                Updated: <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="order-info">
                            <h5 class="mb-3">
                                <i class="fas fa-box text-primary me-2"></i>
                                Order Items
                            </h5>
                            <div class="row">
                                <?php foreach ($order_items as $item): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['image'] ?? 'images/placeholder.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="img-thumbnail me-3" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted">
                                                    Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="track_order.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to All Orders
                            </a>
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- All Orders -->
                <div class="tracking-card">
                    <div class="tracking-header">
                        <h2 class="mb-2">
                            <i class="fas fa-history me-3"></i>Order History
                        </h2>
                        <p class="mb-0 opacity-75">Track all your orders in one place</p>
                    </div>
                    
                    <div class="p-4">
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                                <h5 class="text-muted mb-2">No orders found</h5>
                                <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                                <a href="products.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <h6 class="mb-1">Order #<?php echo htmlspecialchars($order['order_id']); ?></h6>
                                            <small class="text-muted"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></small>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Total Amount</small>
                                            <div class="fw-semibold">$<?php echo number_format($order['total_amount'] ?? 0, 2); ?></div>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="status-badge status-<?php echo strtolower($order['status'] ?? 'pending'); ?>"><?php echo htmlspecialchars(ucfirst($order['status'] ?? 'pending')); ?></span>
                                            </span>
                                        </div>
                                        <div class="col-md-3 text-md-end">
                                            <a href="track_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-truck me-1"></i>Track Order
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
