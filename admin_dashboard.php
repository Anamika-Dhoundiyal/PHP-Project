<?php
session_start();
include 'Grocery/dbconnection.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle various admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_product':
            $product_name = $_POST['product_name'];
            $category_id = $_POST['category_id'];
            $product_description = $_POST['product_description'];
            $price = $_POST['price'];
            $stock_quantity = $_POST['stock_quantity'];
            $unit = $_POST['unit'];
            $brand = $_POST['brand'];
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            // Handle image upload
            $product_image = '';
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'images/products/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_name = time() . '_' . basename($_FILES['product_image']['name']);
                $target_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                    $product_image = $target_path;
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO products (product_name, category_id, product_description, price, stock_quantity, unit, brand, product_image, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisdisssi", $product_name, $category_id, $product_description, $price, $stock_quantity, $unit, $brand, $product_image, $is_featured);
            $stmt->execute();
            $_SESSION['message'] = "Product added successfully!";
            break;
            
        case 'update_product':
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $category_id = $_POST['category_id'];
            $product_description = $_POST['product_description'];
            $price = $_POST['price'];
            $stock_quantity = $_POST['stock_quantity'];
            $unit = $_POST['unit'];
            $brand = $_POST['brand'];
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            // Handle image upload if provided
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'images/products/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_name = time() . '_' . basename($_FILES['product_image']['name']);
                $target_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                    $product_image = $target_path;
                    $stmt = $conn->prepare("UPDATE products SET product_name=?, category_id=?, product_description=?, price=?, stock_quantity=?, unit=?, brand=?, product_image=?, is_featured=? WHERE product_id=?");
                    $stmt->bind_param("sisdisssii", $product_name, $category_id, $product_description, $price, $stock_quantity, $unit, $brand, $product_image, $is_featured, $product_id);
                } else {
                    $stmt = $conn->prepare("UPDATE products SET product_name=?, category_id=?, product_description=?, price=?, stock_quantity=?, unit=?, brand=?, is_featured=? WHERE product_id=?");
                    $stmt->bind_param("sisdisssi", $product_name, $category_id, $product_description, $price, $stock_quantity, $unit, $brand, $is_featured, $product_id);
                }
            } else {
                $stmt = $conn->prepare("UPDATE products SET product_name=?, category_id=?, product_description=?, price=?, stock_quantity=?, unit=?, brand=?, is_featured=? WHERE product_id=?");
                $stmt->bind_param("sisdisssi", $product_name, $category_id, $product_description, $price, $stock_quantity, $unit, $brand, $is_featured, $product_id);
            }
            
            $stmt->execute();
            $_SESSION['message'] = "Product updated successfully!";
            break;
            
        case 'delete_product':
            $product_id = $_POST['product_id'];
            $stmt = $conn->prepare("UPDATE products SET is_active = FALSE WHERE product_id=?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $_SESSION['message'] = "Product deleted successfully!";
            break;
    }
    
    header('Location: admin_dashboard.php');
    exit();
}

// Get statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE is_active = TRUE");
$stats['total_products'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE is_active = TRUE AND stock_quantity > 0");
$stats['active_products'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity <= min_stock_level");
$stats['low_stock'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM categories WHERE is_active = TRUE");
$stats['total_categories'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $result->fetch_assoc()['total'];

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.order_id, o.order_date, o.total_amount, o.status, 
           COALESCE(u.full_name, 'Guest Customer') as full_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC 
    LIMIT 10
");

// Get all products for management
$products = $conn->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.is_active = TRUE 
    ORDER BY p.created_at DESC
");

// Get all categories for dropdowns
$categories = $conn->query("SELECT * FROM categories WHERE is_active = TRUE ORDER BY category_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Grocery Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background-color: var(--primary-color);
            min-height: 100vh;
            color: white;
            padding: 20px 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 15px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid var(--secondary-color);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .table-modern {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-store"></i> Admin Panel</h4>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a class="nav-link" href="admin_products.php"><i class="fas fa-box"></i> Products</a>
                        <a class="nav-link" href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a>
                        <a class="nav-link" href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                        <a class="nav-link" href="admin_customers.php"><i class="fas fa-users"></i> Customers</a>
                        <a class="nav-link" href="admin_inventory.php"><i class="fas fa-warehouse"></i> Inventory</a>
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content py-4">
                <div class="container">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                        <div>
                            <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Administrator'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Success Message -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-5">
                        <div class="col-md-4 mb-3">
                            <div class="stats-card text-center">
                                <div class="mb-3">
                                    <i class="fas fa-box fa-3x text-primary"></i>
                                </div>
                                <h3><?php echo $stats['total_products']; ?></h3>
                                <p class="text-muted">Total Products</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card text-center">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle fa-3x text-success"></i>
                                </div>
                                <h3><?php echo $stats['active_products']; ?></h3>
                                <p class="text-muted">Active Products</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card text-center">
                                <div class="mb-3">
                                    <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                                </div>
                                <h3><?php echo $stats['low_stock']; ?></h3>
                                <p class="text-muted">Low Stock Items</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="fas fa-plus"></i> Add New Product
                                </button>
                                <a href="admin_products.php" class="btn btn-outline-primary">
                                    <i class="fas fa-box"></i> Manage Products
                                </a>
                                <a href="admin_orders.php" class="btn btn-outline-primary">
                                    <i class="fas fa-shopping-cart"></i> View Orders
                                </a>
                                <a href="admin_categories.php" class="btn btn-outline-primary">
                                    <i class="fas fa-tags"></i> Manage Categories
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Products Management -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3><i class="fas fa-box"></i> Product Management</h3>
                            </div>
                            
                            <div class="table-modern">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = $products->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $product['product_id']; ?></td>
                                                <td>
                                                    <?php if ($product['product_image']): ?>
                                                        <img src="<?php echo $product['product_image']; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                    <?php else: ?>
                                                        <div style="width: 50px; height: 50px; background-color: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                                    <?php if ($product['brand']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($product['brand']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                <td>
                                                    <?php if ($product['stock_quantity'] <= $product['min_stock_level']): ?>
                                                        <span class="badge bg-warning"><?php echo $product['stock_quantity']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><?php echo $product['stock_quantity']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($product['is_featured']): ?>
                                                        <span class="badge bg-primary">Featured</span>
                                                    <?php endif; ?>
                                                    <?php if ($product['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick="editProduct(<?php echo $product['product_id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_product">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="product_name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $categories->data_seek(0);
                                    while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" placeholder="e.g., kg, piece, liter">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="product_description" class="form-label">Description</label>
                            <textarea class="form-control" id="product_description" name="product_description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="product_image" class="form-label">Product Image</label>
                                <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                                    <label class="form-check-label" for="is_featured">
                                        Featured Product
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_product">
                        <input type="hidden" id="edit_product_id" name="product_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_product_name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_category_id" class="form-label">Category *</label>
                                <select class="form-select" id="edit_category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $categories->data_seek(0);
                                    while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_price" class="form-label">Price *</label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_stock_quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" id="edit_stock_quantity" name="stock_quantity" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="edit_unit" name="unit">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="edit_brand" name="brand">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_product_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_product_description" name="product_description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_product_image" class="form-label">Product Image</label>
                                <input type="file" class="form-control" id="edit_product_image" name="product_image" accept="image/*">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured" value="1">
                                    <label class="form-check-label" for="edit_is_featured">
                                        Featured Product
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'Grocery/partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(productId) {
            // Fetch product data via AJAX
            fetch('get_product.php?id=' + productId)
                .then(response => response.json())
                .then(data => {
                    // Populate edit modal with product data
                    document.getElementById('edit_product_id').value = data.product_id;
                    document.getElementById('edit_product_name').value = data.product_name;
                    document.getElementById('edit_category_id').value = data.category_id;
                    document.getElementById('edit_price').value = data.price;
                    document.getElementById('edit_stock_quantity').value = data.stock_quantity;
                    document.getElementById('edit_unit').value = data.unit;
                    document.getElementById('edit_brand').value = data.brand;
                    document.getElementById('edit_product_description').value = data.product_description;
                    document.getElementById('edit_is_featured').checked = data.is_featured == 1;
                    
                    new bootstrap.Modal(document.getElementById('editProductModal')).show();
                })
                .catch(error => {
                    alert('Error loading product data');
                    console.error('Error:', error);
                });
        }
        
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="${productId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
