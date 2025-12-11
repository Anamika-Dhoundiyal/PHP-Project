<?php
session_start();
require_once 'cart_functions.php';
 

// Check if user is logged in
if (!isset($_SESSION['cid12'])) {
    header('Location: login.php?redirect=order_details.php');
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['cid12'];

include 'db_connection.php';

// Get order details with schema-aware customer column
$ordersCols = [];
$ordersRes = mysqli_query($conn, "SHOW COLUMNS FROM orders");
if ($ordersRes) { while ($oc = mysqli_fetch_assoc($ordersRes)) { $ordersCols[$oc['Field']] = true; } }
$colCust = isset($ordersCols['customer_id']) ? 'customer_id' : (isset($ordersCols['user_id']) ? 'user_id' : 'customer_id');
$order_query = "SELECT * FROM orders WHERE order_id = '$order_id' AND ".$colCust." = '".$user_id."'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    header('Location: orders.php');
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items with product details
$columns = [];
$colRes = mysqli_query($conn, "SHOW COLUMNS FROM products");
if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
$colId = isset($columns['ID']) ? 'ID' : 'product_id';
$colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
$colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
$items_query = "SELECT oi.*, p.".$colName." as name, p.".$colImage." as image 
               FROM order_items oi 
               JOIN products p ON oi.product_id = p.".$colId." 
               WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);
$orderItems = [];
while ($row = mysqli_fetch_assoc($items_result)) {
    $orderItems[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - FreshMart</title>
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

        .order-details-container {
            margin-top: 120px;
            margin-bottom: 50px;
        }

        .order-details-card {
            background: var(--white);
            border-radius: 15px;
            border: none;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .order-details-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .order-header {
            background: var(--light-bg);
            border-radius: 15px 15px 0 0;
            padding: 25px;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
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
            padding: 10px 25px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }

        .btn-secondary {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
            transition: var(--transition);
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .info-item {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .info-label {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-value {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 16px;
        }

        .section-title {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .total-section {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-row:last-child {
            margin-bottom: 0;
            padding-top: 10px;
            border-top: 2px solid var(--border-color);
            font-weight: 600;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container order-details-container">
        <div class="row">
            <div class="col-12">
                <div class="card order-details-card">
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-2">
                                    <i class="fas fa-receipt me-2"></i>Order #<?php echo $order_id; ?>
                                </h4>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Placed on <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-lg-8">
                                <h5 class="section-title">
                                    <i class="fas fa-box me-2"></i>Order Items
                                </h5>
                                
                                <?php foreach ($orderItems as $item): ?>
                                    <div class="order-item">
                                        <img src="<?php echo getProductImageUrl($item['image']); ?>" alt="<?php echo $item['name']; ?>">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                            <div class="text-muted mb-2">
                                                <small>Quantity: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Price per item</span>
                                                <strong>₹<?php echo number_format($item['price'], 2); ?></strong>
                                            </div>
                                        </div>
                                        <div class="text-end ms-3">
                                            <h5 class="mb-1 text-primary">₹<?php echo number_format($item['subtotal'], 2); ?></h5>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="col-lg-4">
                                <h5 class="section-title">
                                    <i class="fas fa-info-circle me-2"></i>Order Information
                                </h5>
                                
                                <div class="order-info">
                                    <div class="info-item">
                                        <div class="info-label">Customer Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($_SESSION['customer_username']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Phone Number</div>
                                        <div class="info-value"><?php echo htmlspecialchars($order['phone']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Payment Method</div>
                                        <div class="info-value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Delivery Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($order['address']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="total-section">
                                    <h6 class="section-title">Order Summary</h6>
                                    <div class="total-row">
                                        <span>Subtotal</span>
                                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                    <div class="total-row">
                                        <span>Delivery Fee</span>
                                        <span>₹0.00</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Total Amount</span>
                                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <a href="orders.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Orders
                            </a>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-1"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
