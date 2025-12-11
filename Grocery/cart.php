<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connection.php'; // Ensure database connection is established first
require 'cart_functions.php';

// Get cart items
$cartItems = getCartItems();
$cartCount = getCartCount();
$totalAmount = 0;

// Calculate total amount
foreach ($cartItems as $item) {
    $cost = isset($item['cost']) ? $item['cost'] : 0;
    $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
    $totalAmount += $cost * $quantity;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - FreshMart</title>
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

        .cart-container {
            margin-top: 120px;
            margin-bottom: 50px;
        }

        .cart-header {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .cart-item {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid var(--border-color);
        }

        .product-details h5 {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .product-category {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .product-price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            background: var(--primary-color);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }

        .quantity-btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            transform: none;
        }

        .quantity-display {
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }

        .item-total {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .remove-btn:hover {
            color: #c82333;
            transform: scale(1.1);
        }

        .cart-summary {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 120px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-dark);
        }

        .btn-checkout {
            background: var(--primary-color);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            transition: var(--transition);
            margin-top: 20px;
        }

        .btn-checkout:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-continue {
            background: var(--white);
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            transition: var(--transition);
            margin-top: 10px;
        }

        .btn-continue:hover {
            background: var(--primary-color);
            color: var(--white);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .empty-cart i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .empty-cart p {
            color: var(--text-light);
            margin-bottom: 30px;
        }

        .toast-container {
            z-index: 1055;
        }

        @media (max-width: 768px) {
            .cart-container {
                margin-top: 100px;
            }
            
            .product-image {
                width: 80px;
                height: 80px;
            }
            
            .cart-item {
                padding: 15px;
            }
            
            .quantity-controls {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar_simple.php'; ?>
    <div class="container cart-container">
        <div class="cart-header">
            <h1 class="mb-0">
                <i class="fas fa-shopping-cart text-success me-3"></i>
                Shopping Cart
                <span class="badge bg-secondary ms-2"><?php echo $cartCount; ?> items</span>
            </h1>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="<?php echo htmlspecialchars($item['image'] ?? 'images/default.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>" 
                                         class="product-image">
                                </div>
                                <div class="col-md-4">
                                    <div class="product-details">
                                        <h5><?php echo htmlspecialchars($item['name'] ?? 'Unknown Product'); ?></h5>
                                        <p class="product-category"><?php echo htmlspecialchars($item['catogery'] ?? $item['category'] ?? 'Uncategorized'); ?></p>
                                        <p class="product-price">₹<?php echo number_format($item['cost'] ?? 0, 2); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" 
                                                onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)"
                                                <?php echo ($item['quantity'] ?? 0) <= 1 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <span class="quantity-display" id="quantity-<?php echo $item['product_id']; ?>">
                                            <?php echo $item['quantity'] ?? 0; ?>
                                        </span>
                                        <button class="quantity-btn" 
                                                onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="item-total" id="total-<?php echo $item['product_id']; ?>">
                                        ₹<?php echo number_format(($item['cost'] ?? 0) * ($item['quantity'] ?? 0), 2); ?>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button class="remove-btn" onclick="removeItem(<?php echo $item['product_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="mb-4">Order Summary</h4>
                        <div class="summary-row">
                            <span>Subtotal (<?php echo $cartCount; ?> items)</span>
                            <span id="subtotal">₹<?php echo number_format($totalAmount, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-row">
                            <span>Total</span>
                            <span id="total">₹<?php echo number_format($totalAmount, 2); ?></span>
                        </div>
                        
                        <?php if (isset($_SESSION['cid12'])): ?>
                            <button class="btn btn-primary btn-checkout" onclick="checkout()">
                                <i class="fas fa-lock me-2"></i>Proceed to Checkout
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary btn-checkout" onclick="guestCheckout()">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Checkout
                            </button>
                        <?php endif; ?>
                        
                        <a href="index.php" class="btn btn-continue">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
    
    <!-- Toast Notification Template -->
    <div id="toast-template" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="display: none;">
        <div class="toast-header">
            <i class="fas fa-shopping-cart text-success me-2"></i>
            <strong class="me-auto">FreshMart</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <span class="toast-message"></span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            const toastTemplate = document.getElementById('toast-template');
            const toast = toastTemplate.cloneNode(true);
            
            // Remove template ID and show the toast
            toast.removeAttribute('id');
            toast.style.display = 'block';
            
            // Update message
            toast.querySelector('.toast-message').textContent = message;
            
            // Update icon based on type
            const icon = toast.querySelector('.toast-header i');
            if (type === 'error') {
                icon.className = 'fas fa-exclamation-triangle text-danger me-2';
            } else if (type === 'warning') {
                icon.className = 'fas fa-exclamation-circle text-warning me-2';
            }
            
            // Add to container
            toastContainer.appendChild(toast);
            
            // Initialize Bootstrap toast
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove from DOM after hiding
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        // Function to update quantity
        function updateQuantity(productId, change) {
            const quantityElement = document.getElementById('quantity-' + productId);
            const currentQuantity = parseInt(quantityElement.textContent);
            const newQuantity = currentQuantity + change;
            
            if (newQuantity < 1) return;
            
            fetch('update_cart_quantity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + newQuantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update quantity display
                    quantityElement.textContent = newQuantity;
                    
                    // Update total for this item
                    document.getElementById('total-' + productId).textContent = 
                        '₹' + data.item_total;
                    
                    // Update summary totals
                    document.getElementById('subtotal').textContent = '₹' + data.cart_total;
                    document.getElementById('total').textContent = '₹' + data.cart_total;
                    
                    // Update cart count in header
                    document.querySelector('.cart-header .badge').textContent = data.cart_count + ' items';
                    
                    // Enable/disable minus button
                    const minusBtn = quantityElement.previousElementSibling;
                    minusBtn.disabled = newQuantity <= 1;
                    
                    showToast('Cart updated successfully!', 'success');
                } else {
                    showToast(data.message || 'Failed to update quantity', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to update quantity. Please try again.', 'error');
            });
        }

        // Function to remove item from cart
        function removeItem(productId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }
            
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the item from the DOM
                    const cartItem = document.querySelector('[data-product-id="' + productId + '"]');
                    if (cartItem) {
                        cartItem.remove();
                    }
                    
                    // Update summary totals
                    document.getElementById('subtotal').textContent = '₹' + data.cart_total;
                    document.getElementById('total').textContent = '₹' + data.cart_total;
                    
                    // Update cart count in header
                    const cartHeaderBadge = document.querySelector('.cart-header .badge');
                    cartHeaderBadge.textContent = data.cart_count + ' items';
                    
                    // If cart is empty, reload page to show empty cart message
                    if (data.cart_count == 0) {
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                    
                    showToast('Item removed from cart!', 'success');
                } else {
                    showToast(data.message || 'Failed to remove item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to remove item. Please try again.', 'error');
            });
        }

        // Function for guest checkout
        function guestCheckout() {
            showToast('Please login to continue with checkout', 'warning');
            setTimeout(() => {
                window.location.href = 'login.php?redirect=cart.php';
            }, 2000);
        }

        // Function for checkout
        function checkout() {
            if (<?php echo $cartCount; ?> === 0) {
                showToast('Your cart is empty!', 'warning');
                return;
            }
            
            // Redirect to checkout page
            window.location.href = 'checkout.php';
        }

        // Update cart count in navbar
        function updateNavbarCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement && data.count !== undefined) {
                        cartCountElement.textContent = data.count;
                        
                        if (data.count > 0) {
                            cartCountElement.style.display = 'inline-block';
                        } else {
                            cartCountElement.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateNavbarCartCount();
        });
    </script>
</body>
</html>
