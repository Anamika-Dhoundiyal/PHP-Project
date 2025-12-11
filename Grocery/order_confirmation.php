<?php
session_start();
require_once 'cart_functions.php';
 

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: cart.php');
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['cid12'] ?? 0;

include 'dbconnection.php';

// Get order details with schema-aware customer column
$ordersCols = [];
$ordersRes = mysqli_query($conn, "SHOW COLUMNS FROM orders");
if ($ordersRes) { while ($oc = mysqli_fetch_assoc($ordersRes)) { $ordersCols[$oc['Field']] = true; } }
$colCust = isset($ordersCols['customer_id']) ? 'customer_id' : (isset($ordersCols['user_id']) ? 'user_id' : 'customer_id');
$order_query = "SELECT * FROM orders WHERE order_id = '$order_id' AND ".$colCust." = '".$user_id."'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    header('Location: cart.php');
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
    <title>Order Confirmation - FreshMart</title>
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

        .confirmation-container {
            margin-top: 120px;
            margin-bottom: 50px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
        }

        .success-icon i {
            font-size: 40px;
            color: var(--white);
        }

        .confirmation-card {
            background: var(--white);
            border-radius: 15px;
            border: none;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .confirmation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .order-details {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
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

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }

        .btn-secondary {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: var(--transition);
        }

        .status-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }

        .info-item {
            background: var(--white);
            padding: 15px;
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
        }
    </style>
</head>
<body>
    <div class="container confirmation-container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card confirmation-card">
                    <div class="card-body p-5 text-center">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        
                        <h2 class="text-success mb-3">Order Placed Successfully!</h2>
                        <p class="text-muted mb-4">Thank you for your order. We will process it shortly.</p>
                        
                        <div class="order-details">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Order #<?php echo $order_id; ?></h5>
                                <span class="status-badge"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                            
                            <div class="order-info">
                                <div class="info-item">
                                    <div class="info-label">Order Date</div>
                                    <div class="info-value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Total Amount</div>
                                    <div class="info-value">₹<?php echo number_format($order['total_amount'], 2); ?></div>
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
                            
                            <h6 class="text-start mb-3">Order Items</h6>
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
                            
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <h5 class="mb-0">Total</h5>
                                <h4 class="mb-0 text-primary">₹<?php echo number_format($order['total_amount'], 2); ?></h4>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                            <a href="orders.php" class="btn btn-secondary">
                                <i class="fas fa-list me-2"></i>View Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show order placed toast
        window.addEventListener('load', function() {
            if (typeof showToast === 'function') {
                showToast('Order placed successfully!', 'success');
            }
        });
    </script>
</body>
</html>
