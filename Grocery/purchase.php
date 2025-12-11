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

// Get cart items
$query = "SELECT c.*, p.Item_name AS name, p.cost AS price, p.no_of_items AS stock, p.ID AS id 
          FROM cart c 
          JOIN products p ON c.pid = p.ID 
          WHERE c.cid = '$customer_id'";
$result = mysqli_query($connect, $query);
$cart_items = [];
$cart_total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $item_total = $row['no_of_items'] * $row['price'];
    $cart_total += $item_total;
    $row['item_total'] = $item_total;
    $cart_items[] = $row;
}

// If cart is empty, redirect to cart page
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Process order when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = mysqli_real_escape_string($connect, $_POST['payment_method'] ?? 'cash_on_delivery');
    $shipping_address = mysqli_real_escape_string($connect, $_POST['shipping_address'] ?? '');
    $customer_notes = mysqli_real_escape_string($connect, $_POST['customer_notes'] ?? '');
    
    $tax_amount = $cart_total * 0.05;
    $shipping_amount = $cart_total > 50 ? 0 : 5;
    $final_amount = $cart_total + $tax_amount + $shipping_amount;
    
    mysqli_query($connect, "DELETE FROM cart WHERE cid = '$customer_id'");
    header('Location: cos_transaction.php?placed=1');
    exit();
}

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout - FreshMart</title>
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
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
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

        .checkout-section {
            padding: 3rem 0;
            min-height: calc(100vh - 76px);
        }

        .checkout-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .checkout-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            margin-right: 1rem;
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

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }

        .payment-option {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .payment-option:hover {
            border-color: var(--primary-color);
        }

        .payment-option.selected {
            border-color: var(--primary-color);
            background: rgba(79, 70, 229, 0.05);
        }

        .alert {
            border: none;
            border-radius: var(--radius-md);
        }

        @media (max-width: 768px) {
            .checkout-section {
                padding: 1.5rem 0;
            }
            
            .checkout-header {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar_simple.php'; ?>

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="checkout-card mb-4">
                        <div class="checkout-header">
                            <h2 class="mb-2">
                                <i class="fas fa-credit-card me-3"></i>Checkout
                            </h2>
                            <p class="mb-0 opacity-75">Complete your order</p>
                        </div>
                        
                        <div class="p-4">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" id="checkoutForm">
                                <!-- Shipping Address -->
                                <div class="mb-4">
                                    <h5 class="mb-3">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        Shipping Address
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="shipping_address" class="form-label">Full Address</label>
                                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required placeholder="Enter your complete shipping address"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Method -->
                                <div class="mb-4">
                                    <h5 class="mb-3">
                                        <i class="fas fa-credit-card text-primary me-2"></i>
                                        Payment Method
                                    </h5>
                                    <div class="payment-option selected" data-payment="cash_on_delivery">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cash_on_delivery" checked>
                                            <label class="form-check-label" for="cod">
                                                <strong>Cash on Delivery</strong>
                                                <br><small class="text-muted">Pay when you receive your order</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="payment-option" data-payment="credit_card">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="credit_card">
                                            <label class="form-check-label" for="card">
                                                <strong>Credit/Debit Card</strong>
                                                <br><small class="text-muted">Secure online payment</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Additional Notes -->
                                <div class="mb-4">
                                    <label for="customer_notes" class="form-label">Additional Notes (Optional)</label>
                                    <textarea class="form-control" id="customer_notes" name="customer_notes" rows="2" placeholder="Any special instructions for delivery"></textarea>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="cart.php" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Cart
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check me-2"></i>Place Order
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Order Summary -->
                    <div class="checkout-card">
                        <div class="p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-receipt text-primary me-2"></i>
                                Order Summary
                            </h5>
                            
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item">
                                    <img src="<?php echo htmlspecialchars($item['image'] ?? 'images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="item-image">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted">Qty: <?php echo $item['no_of_items']; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <strong>$<?php echo number_format($item['item_total'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <strong>$<?php echo number_format($cart_total, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (5%)</span>
                                <strong>$<?php echo number_format($cart_total * 0.05, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <strong><?php echo $cart_total > 50 ? 'Free' : '$5.00'; ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">Total</h6>
                                <h6 class="mb-0 text-primary">
                                    $<?php echo number_format($cart_total + ($cart_total * 0.05) + ($cart_total > 50 ? 0 : 5), 2); ?>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Payment method selection
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.querySelector('input[name="payment_method"][value="' + this.dataset.payment + '"]').checked = true;
            });
        });
    </script>
</body>
</html>
