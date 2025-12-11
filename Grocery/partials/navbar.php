<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
     /* Modern Navbar - Define variables locally */
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --accent-color: #FF9800;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --white: #ffffff;
            --shadow: 0 8px 32px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Modern Navbar */
        .navbar {
            background: var(--white);
            box-shadow: var(--shadow);
            padding: 1rem 0;
            transition: var(--transition);
            position: relative;
            z-index: 1030;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color) !important;
            text-decoration: none;
        }

        .navbar-brand i {
            color: var(--accent-color);
            margin-right: 0.5rem;
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: var(--transition);
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        /* Removed nav-link::after to eliminate lines above nav links */

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
</style>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-leaf"></i>
            FreshMart
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span id="cart-count" class="badge bg-danger ms-1" style="display: none;">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-history"></i> Orders
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> Account
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (isset($_SESSION['cid12'])): ?>
                            <!-- Customer is logged in -->
                            <li><a class="dropdown-item" href="account.php"><i class="fas fa-user-circle me-2"></i>My Account</a></li>
                            <li><a class="dropdown-item" href="track_order.php"><i class="fas fa-truck me-2"></i>Track Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        <?php else: ?>
                            <!-- No one is logged in -->
                            <li><a class="dropdown-item" href="login.php"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="register.php"><i class="fas fa-user-plus me-2"></i>Register</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all Bootstrap components
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap loaded successfully');
        
        // Initialize dropdowns with better error handling
        var dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdownElements.forEach(function(element, index) {
            try {
                var dropdown = new bootstrap.Dropdown(element);
                console.log('Dropdown ' + index + ' initialized successfully');
            } catch(e) {
                console.error('Dropdown ' + index + ' initialization error:', e);
            }
        });
        
        // Initialize collapse (for mobile menu)
        var collapseElements = document.querySelectorAll('[data-bs-toggle="collapse"]');
        collapseElements.forEach(function(element, index) {
            try {
                var collapse = new bootstrap.Collapse(element, { toggle: false });
                console.log('Collapse ' + index + ' initialized successfully');
            } catch(e) {
                console.error('Collapse ' + index + ' initialization error:', e);
            }
        });
    } else {
        console.error('Bootstrap is not loaded');
        // Fallback: load Bootstrap from CDN
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
        document.head.appendChild(script);
    }
});
</script>
