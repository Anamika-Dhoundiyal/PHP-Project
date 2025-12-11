<?php
session_start();
include 'dbconnection.php';

// Check if customer is logged in
if (!isset($_SESSION['cid12'])) {
    header("Location: customer_login.php");
    exit();
}

// Get customer info
$customer_id = $_SESSION['cid12'];
$customer_name = $_SESSION['customer_username'];

// Fetch products
$query = mysqli_query($conn, "SELECT * FROM products");
$columns = [];
$colRes = mysqli_query($conn, "SHOW COLUMNS FROM products");
if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
$colId = isset($columns['ID']) ? 'ID' : 'product_id';
$colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
$colPrice = isset($columns['cost']) ? 'cost' : 'price';
$colStock = isset($columns['no_of_items']) ? 'no_of_items' : 'stock_quantity';
$colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
$colCategory = isset($columns['catogery']) ? 'catogery' : (isset($columns['category_id']) ? 'category_id' : null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customer Dashboard - FreshMart</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --accent-color: #f39c12;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 2px 20px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            background: var(--bg-light);
            font-family: 'Arial', sans-serif;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .product-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        
        .product-category {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .btn-add-cart {
            background: var(--primary-color);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            color: white;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-add-cart:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-cart {
            background: var(--accent-color);
            border: none;
            border-radius: 25px;
            padding: 0.8rem 2rem;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            box-shadow: var(--shadow);
        }
        
        .btn-cart:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }
        
        .stock-low {
            color: #e74c3c;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .stock-available {
            color: var(--primary-color);
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar_simple.php'; ?>
    
    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="container text-center">
            <h1><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($customer_name); ?>!</h1>
            <p class="mb-0">Start shopping for fresh groceries and daily essentials</p>
        </div>
    </section>
    
    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <h2 class="text-center mb-4"><i class="fas fa-shopping-bag"></i> Available Products</h2>
            
            <div class="row">
                <?php while ($row = mysqli_fetch_array($query)): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="product-card">
                            <?php $image = $row[$colImage] ?? ''; ?>
                            <?php if (!empty($image)): ?>
                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                     alt="<?php echo htmlspecialchars($row[$colName] ?? ''); ?>" 
                                     class="product-image">
                            <?php else: ?>
                                <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-info">
                                <h5 class="product-title"><?php echo htmlspecialchars($row[$colName] ?? ''); ?></h5>
                                <p class="product-category">
                                    <i class="fas fa-tag"></i> <?php
                                    $catLabel = '';
                                    if ($colCategory === 'catogery') { $catLabel = $row['catogery'] ?? ''; }
                                    elseif ($colCategory === 'category_id' && isset($row['category_id'])) {
                                        $cid = intval($row['category_id']);
                                        $cr = mysqli_query($conn, "SELECT category_name FROM categories WHERE category_id=$cid LIMIT 1");
                                        if ($cr && ($crow = mysqli_fetch_assoc($cr))) { $catLabel = $crow['category_name']; }
                                    }
                                    echo htmlspecialchars($catLabel);
                                    ?>
                                </p>
                                <p class="product-price">$<?php echo number_format(floatval($row[$colPrice] ?? 0), 2); ?></p>
                                
                                <?php $stockVal = intval($row[$colStock] ?? 0); if ($stockVal > 0): ?>
                                    <p class="stock-available">
                                        <i class="fas fa-check-circle"></i> In Stock (<?php echo $stockVal; ?>)
                                    </p>
                                    <form class="d-inline add-to-cart-form" onsubmit="addToCart(event, <?php echo $row[$colId]; ?>)">
                                        <input type="hidden" name="cpid" value="<?php echo $row[$colId]; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-add-cart">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <p class="stock-low">
                                        <i class="fas fa-times-circle"></i> Out of Stock
                                    </p>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-cart-plus"></i> Out of Stock
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    
    <!-- Floating Cart Button -->
    <a href="cart.php" class="btn btn-cart">
        <i class="fas fa-shopping-cart"></i> View Cart
        <?php
        // Get cart count for this customer
        $cart_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM cart WHERE uid = '$customer_id'");
        $cart_count = mysqli_fetch_assoc($cart_count_query);
        if ($cart_count['count'] > 0): ?>
            <span class="badge bg-danger ms-1"><?php echo $cart_count['count']; ?></span>
        <?php endif; ?>
    </a>
    
    <?php include 'partials/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function addToCart(event, productId) {
            event.preventDefault();
            const button = (event.submitter) ? event.submitter : event.target.querySelector('button');
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            button.disabled = true;

            const formData = new FormData();
            formData.append('cpid', productId);
            formData.append('quantity', 1);
            formData.append('submit', 'Add to Cart');

            fetch('add_to_cart.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.innerHTML = '<i class="fas fa-check"></i> Added!';
                        button.classList.add('btn-success');
                        updateCartCount();
                    } else {
                        button.innerHTML = '<i class="fas fa-times"></i> Failed';
                        button.classList.add('btn-danger');
                    }
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('btn-success', 'btn-danger');
                        button.disabled = false;
                    }, 2000);
                })
                .catch(() => {
                    button.innerHTML = '<i class="fas fa-times"></i> Error';
                    button.classList.add('btn-danger');
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('btn-danger');
                        button.disabled = false;
                    }, 2000);
                });
        }

        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement && data.count !== undefined) {
                        cartCountElement.textContent = data.count;
                        cartCountElement.style.display = data.count > 0 ? 'inline-block' : 'none';
                    }
                })
                .catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>










