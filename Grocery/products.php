<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Products - FreshMart</title>
<!-- for-mobile-apps -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Grocery Store Responsive web template, Bootstrap Web Templates, Flat Web Templates, Android Compatible web template, 
Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
		function hideURLbar(){ window.scrollTo(0,1); } </script>
<!-- //for-mobile-apps -->
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<!-- font-awesome icons -->
<link href="css/font-awesome.css" rel="stylesheet" type="text/css" media="all" /> 
<!-- //font-awesome icons -->
<!-- js -->
<script src="js/jquery-1.11.1.min.js"></script>
<!-- //js -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href='//fonts.googleapis.com/css?family=Ubuntu:400,300,300italic,400italic,500,500italic,700,700italic' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800,800italic' rel='stylesheet' type='text/css'>
<!-- start-smoth-scrolling -->
<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(".scroll").click(function(event){		
			event.preventDefault();
			$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
		});
	});
</script>
<!-- start-smoth-scrolling -->
<style>
    /* Modern Product Page Styles */
    :root {
        --primary-color: #4CAF50;
        --secondary-color: #45a049;
        --accent-color: #ff6b6b;
        --text-dark: #333;
        --text-light: #666;
        --bg-light: #f8f9fa;
        --white: #ffffff;
        --shadow: 0 5px 15px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
    }
    
    .section-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 2rem;
        text-align: center;
        position: relative;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: var(--primary-color);
        border-radius: 2px;
    }
    
    /* Filters Sidebar */
    .filters-sidebar {
        background: var(--white);
        border-radius: 15px;
        padding: 2rem;
        box-shadow: var(--shadow);
        position: sticky;
        top: 20px;
    }
    
    .filter-section {
        margin-bottom: 2rem;
    }
    
    .filter-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 1rem;
    }
    
    .form-check {
        margin-bottom: 0.5rem;
    }
    
    .form-check-label {
        color: var(--text-light);
        cursor: pointer;
    }
    
    .form-check-input:checked ~ .form-check-label {
        color: var(--primary-color);
        font-weight: 500;
    }
    
    /* Product Cards */
    .product-card {
        background: var(--white);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: var(--transition);
        margin-bottom: 2rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .product-image {
        height: 200px;
        background: var(--bg-light);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 1px #e0e0e0;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }
    
    .product-card:hover .product-image img {
        transform: scale(1.1);
    }
    
    .product-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--accent-color);
        color: var(--white);
        padding: 0.4rem 1rem;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        animation: float 3s ease-in-out infinite;
        z-index: 5;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-3px); }
    }
    
    .product-info {
        padding: 1.8rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        position: relative;
    }
    
    .product-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
        opacity: 0.3;
    }
    
    .product-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
        line-height: 1.3;
    }
    
    .product-category {
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    
    .product-price {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        margin-top: auto;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .btn-add-cart {
        background: var(--primary-color);
        border: none;
        border-radius: 30px;
        padding: 0.8rem 1.5rem;
        color: var(--white);
        font-weight: 600;
        transition: var(--transition);
        width: 100%;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .btn-add-cart:hover {
        background: var(--secondary-color);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
    }
    
    .btn-add-cart::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-add-cart:hover::before {
        left: 100%;
    }
    
    .sort-dropdown .form-select {
        border-radius: 25px;
        border: 2px solid var(--primary-color);
        padding: 0.5rem 1.5rem;
        font-weight: 500;
    }
    
    .sort-dropdown .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
    }
    
    /* Grid System for Equal Height */
    .row {
        display: flex;
        flex-wrap: wrap;
    }
    
    .row > [class*='col-'] {
        display: flex;
        flex-direction: column;
    }
    .navbar .nav-link::before,
    .navbar .nav-link::after {
        content: none !important;
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .toast-notify {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 14px;
        border-radius: 8px;
        color: #fff;
        z-index: 2000;
        box-shadow: var(--shadow);
        background: #27ae60;
    }
    .toast-notify.error { background: #e74c3c; }
</style>
</head>

<body>
    <?php include 'partials/navbar_simple.php'; ?>
    <!-- Modern Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <!-- Filters Sidebar -->
                <div class="filters-sidebar mb-4">
                    <h4 class="mb-3">Filters</h4>
                    
                    <!-- Search -->
                    <div class="filter-section mb-4">
                        <h5 class="filter-title">Search</h5>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                            <button class="btn btn-primary" type="button" onclick="filterProducts()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Categories -->
                    <div class="filter-section mb-4">
                        <h5 class="filter-title">Categories</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" id="all" value="" checked onchange="filterProducts()">
                            <label class="form-check-label" for="all">All Categories</label>
                        </div>
                        <?php
                        // Get unique categories
                        $category_query = mysqli_query($connect, "SELECT DISTINCT category_name FROM categories WHERE is_active = TRUE ORDER BY category_name");
                        while ($category = mysqli_fetch_array($category_query)) {
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" id="<?php echo strtolower($category['category_name']); ?>" value="<?php echo $category['category_name']; ?>" onchange="filterProducts()">
                                <label class="form-check-label" for="<?php echo strtolower($category['category_name']); ?>"><?php echo ucfirst($category['category_name']); ?></label>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="filter-section mb-4">
                        <h5 class="filter-title">Price Range</h5>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" class="form-control" id="minPrice" placeholder="Min" onchange="filterProducts()">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" id="maxPrice" placeholder="Max" onchange="filterProducts()">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Availability -->
                    <div class="filter-section mb-4">
                        <h5 class="filter-title">Availability</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="inStock" onchange="filterProducts()">
                            <label class="form-check-label" for="inStock">In Stock Only</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <!-- Products Grid -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">All Products</h2>
                    <div class="sort-dropdown">
                        <select class="form-select" id="sortBy" onchange="filterProducts()">
                            <option value="">Sort By</option>
                            <option value="name">Name (A-Z)</option>
                            <option value="price_low">Price (Low to High)</option>
                            <option value="price_high">Price (High to Low)</option>
                        </select>
                    </div>
                </div>
                
                <div class="row" id="productsContainer">
                    <?php
                    $search = isset($_GET['search']) ? $_GET['search'] : '';
                    $category = isset($_GET['category']) ? $_GET['category'] : '';
                    $min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
                    $max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
                    $in_stock = isset($_GET['in_stock']) ? $_GET['in_stock'] : '';
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
                    
                    $columns = [];
                    $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
                    if ($colRes) {
                        while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; }
                    }
                    $colId = isset($columns['ID']) ? 'ID' : 'product_id';
                    $colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
                    $colPrice = isset($columns['cost']) ? 'cost' : 'price';
                    $colStock = isset($columns['no_of_items']) ? 'no_of_items' : 'stock_quantity';
                    $colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
                    $colCategory = isset($columns['catogery']) ? 'catogery' : (isset($columns['category_id']) ? 'category_id' : null);

                    $where_conditions = [];
                    if ($search) {
                        $where_conditions[] = "(".$colName." LIKE '%$search%')";
                    }
                    if ($category) {
                        if ($colCategory === 'catogery') {
                            $where_conditions[] = "catogery = '".mysqli_real_escape_string($connect, $category)."'";
                        } elseif ($colCategory === 'category_id') {
                            $catIdRes = mysqli_query($connect, "SELECT category_id FROM categories WHERE category_name='".mysqli_real_escape_string($connect, $category)."' LIMIT 1");
                            if ($catIdRes && ($catRow = mysqli_fetch_assoc($catIdRes))) {
                                $where_conditions[] = "category_id = ".$catRow['category_id'];
                            }
                        }
                    }
                    if ($min_price) {
                        $where_conditions[] = $colPrice." >= ".floatval($min_price);
                    }
                    if ($max_price) {
                        $where_conditions[] = $colPrice." <= ".floatval($max_price);
                    }
                    if ($in_stock) {
                        $where_conditions[] = $colStock." > 0";
                    }
                    
                    $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
                    
                    $order_clause = "";
                    if ($sort) {
                        switch ($sort) {
                            case 'name':
                                $order_clause = "ORDER BY ".$colName." ASC";
                                break;
                            case 'price_low':
                                $order_clause = "ORDER BY ".$colPrice." ASC";
                                break;
                            case 'price_high':
                                $order_clause = "ORDER BY ".$colPrice." DESC";
                                break;
                        }
                    }
                    
                    $query = mysqli_query($connect, "SELECT * FROM products $where_clause $order_clause");
                    
                    if (mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_array($query)) {
                            $image_path = '';
                            if ($colImage && !empty($row[$colImage])) { $image_path = $row[$colImage]; }
                            if (!$image_path) { $image_path = 'images/default.jpg'; }
                            else {
                                if (strpos($image_path, '/') === false && strpos($image_path, '\\') === false) {
                                    $image_path = 'images/' . $image_path;
                                }
                            }
                            ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="product-card">
                                    <div class="product-image">
                                        <img src="<?php echo $image_path; ?>" alt="<?php echo $row[$colName]; ?>">
                                        <?php $stockVal = intval($row[$colStock] ?? 0); if ($stockVal <= 0) { ?>
                                            <span class="product-badge" style="background:#e74c3c">Out of Stock</span>
                                        <?php } elseif ($stockVal > 0 && $stockVal <= 10) { ?>
                                            <span class="product-badge">Limited Stock</span>
                                        <?php } ?>
                                    </div>
                                    <div class="product-info">
                                        <h5 class="product-title"><?php echo $row[$colName]; ?></h5>
                                        <?php
                                        $catLabel = '';
                                        if ($colCategory === 'catogery') { $catLabel = $row['catogery']; }
                                        elseif ($colCategory === 'category_id' && isset($row['category_id'])) {
                                            $cid = intval($row['category_id']);
                                            $cr = mysqli_query($connect, "SELECT category_name FROM categories WHERE category_id=$cid LIMIT 1");
                                            if ($cr && ($crow = mysqli_fetch_assoc($cr))) { $catLabel = $crow['category_name']; }
                                        }
                                        ?>
                                        <p class="product-category"><?php echo $catLabel; ?></p>
                                        <div class="product-price">₹<?php echo number_format(floatval($row[$colPrice] ?? 0), 2); ?></div>
                                        <p class="text-muted"><?php echo $stockVal; ?> items available</p>
                                        <?php if ($stockVal > 0) { ?>
                                            <form class="add-to-cart-form" onsubmit="addToCart(event, <?php echo $row[$colId]; ?>)">
                                                <input type="hidden" name="cpid" value="<?php echo $row[$colId]; ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-add-cart">
                                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                                </button>
                                            </form>
                                        <?php } else { ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-ban"></i> Out of Stock
                                            </button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h4>No products found</h4>
                                <p class="text-muted">Try adjusting your search or filter criteria</p>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'partials/footer.php'; ?>

<!-- JavaScript for filtering and cart functionality -->
<script>
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'toast-notify ' + (type === 'error' ? 'error' : 'success');
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.remove(); }, 2500);
    }
    function filterProducts() {
        const search = document.getElementById('searchInput').value;
        const category = document.querySelector('input[name="category"]:checked').value;
        const minPrice = document.getElementById('minPrice').value;
        const maxPrice = document.getElementById('maxPrice').value;
        const inStock = document.getElementById('inStock').checked;
        const sort = document.getElementById('sortBy').value;
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (category) params.append('category', category);
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        if (inStock) params.append('in_stock', '1');
        if (sort) params.append('sort', sort);
        
        window.location.search = params.toString();
    }
    
    // Function to add to cart via AJAX
    function addToCart(event, productId) {
        event.preventDefault();
        
        const button = (event.submitter) ? event.submitter : event.target.querySelector('button');
        const originalHTML = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        button.disabled = true;
        
        // Create form data
        const formData = new FormData();
        formData.append('cpid', productId);
        formData.append('quantity', 1);
        formData.append('submit', 'Add to Cart');
        
        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                updateCartCount();
                
                // Show success state
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                button.classList.remove('btn-add-cart');
                button.classList.add('btn-success');
                showToast('Item added to cart successfully!', 'success');
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-add-cart');
                }, 2000);
            } else {
                // Show error state
                button.innerHTML = '<i class="fas fa-times"></i> Failed';
                button.classList.remove('btn-add-cart');
                button.classList.add('btn-danger');
                showToast(data.message || 'Failed to add item', 'error');
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    button.classList.remove('btn-danger');
                    button.classList.add('btn-add-cart');
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = '<i class="fas fa-times"></i> Error';
            button.classList.remove('btn-add-cart');
            button.classList.add('btn-danger');
            showToast('Failed to add item to cart. Please try again.', 'error');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
                button.classList.remove('btn-danger');
                button.classList.add('btn-add-cart');
            }, 2000);
        });
    }
    
    // Function to update cart count
    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement && typeof data.count !== 'undefined') {
                    cartCountElement.textContent = data.count;
                    cartCountElement.style.display = data.count > 0 ? 'inline-block' : 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    // Update cart count on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
        
        // Set filter values from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('search')) {
            document.getElementById('searchInput').value = urlParams.get('search');
        }
        if (urlParams.get('category')) {
            document.querySelector(`input[name="category"][value="${urlParams.get('category')}"]`).checked = true;
        }
        if (urlParams.get('min_price')) {
            document.getElementById('minPrice').value = urlParams.get('min_price');
        }
        if (urlParams.get('max_price')) {
            document.getElementById('maxPrice').value = urlParams.get('max_price');
        }
        if (urlParams.get('in_stock')) {
            document.getElementById('inStock').checked = true;
        }
        if (urlParams.get('sort')) {
            document.getElementById('sortBy').value = urlParams.get('sort');
        }
    });
    
    // Allow Enter key to trigger search
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            filterProducts();
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
