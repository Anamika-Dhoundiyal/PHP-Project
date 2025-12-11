<?php
session_start();
require_once 'cart_functions.php';

// Check if user is logged in
if (!isset($_SESSION['cid12'])) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

// Get cart items
$cartItems = getCartItems();
$cartCount = getCartCount();

if ($cartCount == 0) {
    header('Location: cart.php');
    exit();
}

$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['cost'] * $item['quantity'];
}

// Fetch existing addresses for the user (schema-aware)
$existing_addresses = [];
if (isset($_SESSION['cid12'])) {
    include 'dbconnection.php';
    $user_id = $_SESSION['cid12'];
    $ordersCols = [];
    $ordersRes = mysqli_query($conn, "SHOW COLUMNS FROM orders");
    if ($ordersRes) { while ($oc = mysqli_fetch_assoc($ordersRes)) { $ordersCols[$oc['Field']] = true; } }
    $colCustAddr = isset($ordersCols['customer_id']) ? 'customer_id' : (isset($ordersCols['user_id']) ? 'user_id' : 'user_id');
    $address_query = "SELECT DISTINCT address FROM orders WHERE " . $colCustAddr . " = '" . mysqli_real_escape_string($conn, $user_id) . "' AND address IS NOT NULL AND address != '' ORDER BY order_date DESC LIMIT 5";
    $address_result = mysqli_query($conn, $address_query);
    while ($row = mysqli_fetch_assoc($address_result)) {
        $existing_addresses[] = $row['address'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'dbconnection.php';
    
    $user_id = $_SESSION['cid12'];
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'cod');

    // Schema-aware columns
    $ordersCols = [];
    $ordersRes = mysqli_query($conn, "SHOW COLUMNS FROM orders");
    if ($ordersRes) { while ($oc = mysqli_fetch_assoc($ordersRes)) { $ordersCols[$oc['Field']] = true; } }
    $colCust = isset($ordersCols['customer_id']) ? 'customer_id' : (isset($ordersCols['user_id']) ? 'user_id' : 'customer_id');

    // Products schema-aware
    $prodCols = [];
    $prodRes = mysqli_query($conn, "SHOW COLUMNS FROM products");
    if ($prodRes) { while ($pc = mysqli_fetch_assoc($prodRes)) { $prodCols[$pc['Field']] = true; } }
    $colId = isset($prodCols['ID']) ? 'ID' : 'product_id';
    $colStock = isset($prodCols['no_of_items']) ? 'no_of_items' : 'stock_quantity';

    // Start transaction
    mysqli_begin_transaction($conn);
    try {
        // Check stock for each item
        foreach ($cartItems as $item) {
            $product_id = $item['pid'] ?? ($item['product_id'] ?? $item['ID']);
            $quantity = intval($item['quantity']);
            $stockRes = mysqli_query($conn, "SELECT ".$colStock." AS stock FROM products WHERE ".$colId." = '".$product_id."' FOR UPDATE");
            $stockRow = mysqli_fetch_assoc($stockRes);
            $available = intval($stockRow['stock'] ?? 0);
            if ($available < $quantity) {
                throw new Exception('Insufficient stock for one or more items');
            }
        }

        // Create order
        $order_query = "INSERT INTO orders (".$colCust.", total_amount, address, phone, payment_method, status, order_date) 
                        VALUES ('$user_id', '$totalAmount', '$address', '$phone', '$payment_method', 'pending', NOW())";
        if (!mysqli_query($conn, $order_query)) {
            throw new Exception('Failed to create order');
        }
        $order_id = mysqli_insert_id($conn);

        // Add order items and decrement stock
        foreach ($cartItems as $item) {
            $product_id = $item['pid'] ?? ($item['product_id'] ?? $item['ID']);
            $quantity = intval($item['quantity']);
            $price = floatval($item['cost']);
            $subtotal = $price * $quantity;

            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                           VALUES ('$order_id', '$product_id', '$quantity', '$price', '$subtotal')";
            if (!mysqli_query($conn, $item_query)) {
                throw new Exception('Failed to add order items');
            }

            $dec_query = "UPDATE products SET ".$colStock." = ".$colStock." - $quantity WHERE ".$colId." = '$product_id'";
            if (!mysqli_query($conn, $dec_query)) {
                throw new Exception('Failed to update stock');
            }
        }

        // Clear cart (no stock restoration)
        clearCart();

        mysqli_commit($conn);
        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit();
    } catch (Exception $ex) {
        mysqli_rollback($conn);
        $error = $ex->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - FreshMart</title>
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

        .checkout-container {
            margin-top: 120px;
            margin-bottom: 50px;
        }

        .checkout-card {
            background: var(--white);
            border-radius: 15px;
            border: none;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .checkout-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 16px;
            transition: var(--transition);
            font-size: 16px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .btn-checkout {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 14px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }

        .order-summary {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
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

        .payment-method {
            background: var(--white);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: var(--transition);
        }

        .payment-method:hover {
            border-color: var(--primary-color);
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background: rgba(76, 175, 80, 0.05);
        }

        .section-title {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar_simple.php'; ?>
    <div class="container checkout-container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card checkout-card mb-4">
                    <div class="card-body p-4">
                        <h4 class="section-title">
                            <i class="fas fa-map-marker-alt me-2"></i>Delivery Information
                        </h4>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="checkoutForm">
                            <?php if (!empty($existing_addresses)): ?>
                            <div class="mb-3">
                                <label for="existing_address" class="form-label">Select Existing Address</label>
                                <select class="form-select" id="existing_address" onchange="fillAddress()">
                                    <option value="">-- Select an existing address or enter new one --</option>
                                    <?php foreach ($existing_addresses as $addr): ?>
                                    <option value="<?php echo htmlspecialchars($addr); ?>"><?php echo htmlspecialchars(substr($addr, 0, 60)) . (strlen($addr) > 60 ? '...' : ''); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Or Enter New Delivery Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required 
                                          placeholder="Enter your complete delivery address"></textarea>
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required 
                                          placeholder="Enter your complete delivery address"></textarea>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required 
                                       placeholder="Enter your phone number">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Payment Method</label>
                                
                                <div class="payment-method selected" data-method="cod">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="cod" value="cod" checked>
                                        <label class="form-check-label" for="cod">
                                            <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="payment-method" data-method="card">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="card" value="card">
                                        <label class="form-check-label" for="card">
                                            <i class="fas fa-credit-card me-2"></i>Credit/Debit Card
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-checkout w-100">
                                <i class="fas fa-lock me-2"></i>Place Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card checkout-card">
                    <div class="card-body p-4">
                        <h4 class="section-title">
                            <i class="fas fa-shopping-cart me-2"></i>Order Summary
                        </h4>
                        
                        <div class="order-summary">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-item">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                        <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <strong>₹<?php echo number_format($item['cost'] * $item['quantity'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Total Amount</h5>
                                <h4 class="mb-0 text-primary">₹<?php echo number_format($totalAmount, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to fill address from existing address selection
        function fillAddress() {
            const select = document.getElementById('existing_address');
            const addressTextarea = document.getElementById('address');
            
            if (select.value) {
                addressTextarea.value = select.value;
            }
        }

        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });

        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const address = document.getElementById('address').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
            if (!address || !phone) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            if (phone.length < 10) {
                e.preventDefault();
                alert('Please enter a valid phone number.');
                return;
            }
        });
    </script>
</body>
</html>
