<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'cart_functions.php';

// Check if user is logged in
if (!isset($_SESSION['cid12'])) {
    header('Location: login.php?redirect=orders.php');
    exit();
}

$user_id = $_SESSION['cid12'];
include 'dbconnection.php';

// Get user's orders with schema-aware customer column
$ordersCols = [];
$ordersRes = mysqli_query($conn, "SHOW COLUMNS FROM orders");
if ($ordersRes) { while ($oc = mysqli_fetch_assoc($ordersRes)) { $ordersCols[$oc['Field']] = true; } }
$colCust = isset($ordersCols['customer_id']) ? 'customer_id' : (isset($ordersCols['user_id']) ? 'user_id' : 'customer_id');
$orders_query = "SELECT * FROM orders WHERE ".$colCust." = '".$user_id."' ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);
$orders = [];
while ($row = mysqli_fetch_assoc($orders_result)) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - FreshMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --accent-color: #FF9800;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --white: #ffffff;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --shadow: 0 8px 32px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .orders-container {
            margin-top: 120px;
            margin-bottom: 50px;
        }

        .order-card {
            background: var(--white);
            border-radius: 15px;
            border: none;
            box-shadow: var(--shadow);
            transition: var(--transition);
            margin-bottom: 25px;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .order-header {
            background: var(--light-bg);
            border-radius: 15px 15px 0 0;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }

        .empty-orders {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-orders i {
            font-size: 80px;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .section-title {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .order-total {
            background: var(--primary-color);
            color: var(--white);
            padding: 10px 15px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar_simple.php'; ?>
    <div class="container orders-container">
        <div class="row">
            <div class="col-12">
                <h2 class="section-title">
                    <i class="fas fa-shopping-bag me-2"></i>My Orders
                </h2>
                
                <?php if (empty($orders)): ?>
                    <div class="card order-card">
                        <div class="card-body">
                            <div class="empty-orders">
                                <i class="fas fa-shopping-basket"></i>
                                <h4>No Orders Yet</h4>
                                <p class="text-muted">You haven't placed any orders yet. Start shopping now!</p>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        // Get order items
                        $order_id = $order['order_id'];
                        $columns = [];
                        $colRes = mysqli_query($conn, "SHOW COLUMNS FROM products");
                        if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
                        $colId = isset($columns['ID']) ? 'ID' : 'product_id';
                        $colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
                        $colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
                        $items_query = "SELECT oi.*, p.".$colName." as name, p.".$colImage." as image 
                                       FROM order_items oi 
                                       JOIN products p ON oi.product_id = p.".$colId." 
                                       WHERE oi.order_id = '$order_id' LIMIT 3";
                        $items_result = mysqli_query($conn, $items_query);
                        $orderItems = [];
                        while ($row = mysqli_fetch_assoc($items_result)) {
                            $orderItems[] = $row;
                        }
                        ?>
                        
                        <div class="card order-card">
                            <div class="order-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1">Order #<?php echo $order['order_id']; ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <?php foreach ($orderItems as $item): ?>
                                    <div class="order-item">
                                        <img src="<?php echo getProductImageUrl($item['image']); ?>" alt="<?php echo $item['name']; ?>">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                            <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                        </div>
                                        <div class="text-end">
                                            <strong>₹<?php echo number_format($item['subtotal'], 2); ?></strong>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php 
                                // Get total item count for this order
                                $item_count_query = "SELECT COUNT(*) as count FROM order_items WHERE order_id = '$order_id'";
                                $item_count_result = mysqli_query($conn, $item_count_query);
                                $item_count_row = mysqli_fetch_assoc($item_count_result);
                                $item_count = $item_count_row['count'];
                                ?>
                                <?php if ($item_count > 3): ?>
                                    <div class="text-center py-2">
                                        <small class="text-muted">+<?php echo $item_count - 3; ?> more items</small>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <div class="order-total">
                                        Total: ₹<?php echo number_format($order['total_amount'], 2); ?>
                                    </div>
                                    <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
