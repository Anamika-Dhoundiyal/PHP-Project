<?php
session_start();
include 'dbconnection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Detect products schema
$columns = [];
$colRes = mysqli_query($conn, "SHOW COLUMNS FROM products");
if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
$colId = isset($columns['ID']) ? 'ID' : 'product_id';
$colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
$colPrice = isset($columns['cost']) ? 'cost' : 'price';
$colStock = isset($columns['no_of_items']) ? 'no_of_items' : 'stock_quantity';
$colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
$colCategory = isset($columns['catogery']) ? 'catogery' : (isset($columns['category_id']) ? 'category_id' : null);
$colDesc = isset($columns['description']) ? 'description' : (isset($columns['product_description']) ? 'product_description' : null);

// Fetch existing categories for dropdown
$existing_categories = [];
$catResult = mysqli_query($conn, "SELECT category_name FROM categories ORDER BY category_name");
if ($catResult) { while ($row = mysqli_fetch_assoc($catResult)) { $existing_categories[] = $row['category_name']; } }

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
                $cost = floatval($_POST['cost']);
                $no_of_items = intval($_POST['no_of_items']);
                $catogery = mysqli_real_escape_string($conn, $_POST['catogery']);
                $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
                
                // Handle image upload
                $image_name = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'images/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $image_name = time() . '_' . basename($_FILES['image']['name']);
                    $target_path = $upload_dir . $image_name;
                    
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($_FILES['image']['type'], $allowed_types)) {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_name = $target_path;
                        } else {
                            $image_name = '';
                        }
                    }
                }
                
                if ($colCategory === 'category_id') {
                    $cid = null;
                    $cr = mysqli_query($conn, "SELECT category_id FROM categories WHERE category_name='".mysqli_real_escape_string($conn, $catogery)."' LIMIT 1");
                    if ($cr && ($crow = mysqli_fetch_assoc($cr))) { $cid = intval($crow['category_id']); }
                    $descCol = $colDesc ?: 'description';
                    $imgCol = $colImage ?: 'image';
                    $query = "INSERT INTO products ($colName, $colPrice, $colStock, category_id, $descCol, $imgCol) VALUES ('$item_name', $cost, $no_of_items, ".($cid?:'NULL').", '$description', '$image_name')";
                } else {
                    $imageCol = $colImage;
                    $descCol = $colDesc ?: 'description';
                    $query = "INSERT INTO products ($colName, $colPrice, $colStock, $colCategory, $descCol, $imageCol) VALUES ('$item_name', $cost, $no_of_items, '$catogery', '$description', '$image_name')";
                }
                mysqli_query($conn, $query);
                $_SESSION['message'] = 'Product added successfully!';
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
                $cost = floatval($_POST['cost']);
                $no_of_items = intval($_POST['no_of_items']);
                $catogery = mysqli_real_escape_string($conn, $_POST['catogery']);
                $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
                
                // Handle image upload if provided
                $image_update = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'images/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $image_name = time() . '_' . basename($_FILES['image']['name']);
                    $target_path = $upload_dir . $image_name;
                    
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($_FILES['image']['type'], $allowed_types)) {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_update = ", $colImage='".$target_path."'";
                        }
                    }
                }
                
                if ($colCategory === 'category_id') {
                    $cid = null;
                    $cr = mysqli_query($conn, "SELECT category_id FROM categories WHERE category_name='".mysqli_real_escape_string($conn, $catogery)."' LIMIT 1");
                    if ($cr && ($crow = mysqli_fetch_assoc($cr))) { $cid = intval($crow['category_id']); }
                    $descCol = $colDesc ?: 'description';
                    $query = "UPDATE products SET $colName='$item_name', $colPrice=$cost, $colStock=$no_of_items, category_id=".($cid?:'NULL').", $descCol='".$description."' $image_update WHERE $colId=$id";
                } else {
                    $descCol = $colDesc ?: 'description';
                    $query = "UPDATE products SET $colName='$item_name', $colPrice=$cost, $colStock=$no_of_items, $colCategory='".$catogery."', $descCol='".$description."' $image_update WHERE $colId=$id";
                }
                mysqli_query($conn, $query);
                $_SESSION['message'] = 'Product updated successfully!';
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                // First delete related purchase records to handle foreign key constraint
                $delete_purchases = "DELETE FROM purchase WHERE ppid = $id";
                mysqli_query($conn, $delete_purchases);
                // Then delete the product
                $query = "DELETE FROM products WHERE $colId=$id";
                mysqli_query($conn, $query);
                $_SESSION['message'] = 'Product deleted successfully!';
                break;
        }
        header('Location: admin_products.php');
        exit();
    }
}

// Get all products normalized to legacy keys
$products = [];
$result = mysqli_query($conn, "SELECT * FROM products ORDER BY ".$colId." DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $norm = [];
    $norm['ID'] = $row[$colId];
    $norm['Item_name'] = $row[$colName] ?? '';
    $norm['cost'] = floatval($row[$colPrice] ?? 0);
    $norm['no_of_items'] = intval($row[$colStock] ?? 0);
    $norm['image'] = $row[$colImage] ?? '';
    if ($colCategory === 'catogery') {
        $norm['catogery'] = $row['catogery'] ?? '';
    } elseif ($colCategory === 'category_id') {
        $cid = intval($row['category_id'] ?? 0);
        $catName = '';
        if ($cid) {
            $cr = mysqli_query($conn, "SELECT category_name FROM categories WHERE category_id=$cid LIMIT 1");
            if ($cr && ($crow = mysqli_fetch_assoc($cr))) { $catName = $crow['category_name']; }
        }
        $norm['catogery'] = $catName;
    } else {
        $norm['catogery'] = '';
    }
    $norm['description'] = $row[$colDesc ?: 'description'] ?? '';
    $products[] = $norm;
}

// Get categories
$categories = [];
$result = mysqli_query($conn, "SELECT category_name FROM categories ORDER BY category_name");
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row['category_name'];
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --text-dark: #111827;
            --text-secondary: #6b7280;
            --bg-light: #f9fafb;
            --white: #ffffff;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .sidebar {
            background: var(--white);
            box-shadow: var(--shadow);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            border-bottom: 1px solid #e5e7eb;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar-menu i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        .page-header {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--success-color);
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background: var(--warning-color);
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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
            transition: all 0.3s ease;
            filter: brightness(0.95);
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.1);
            filter: brightness(1.05);
        }

        .product-image .placeholder {
            color: white;
            font-size: 3rem;
            opacity: 0.7;
        }

        .product-info {
            padding: 1.8rem;
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
            background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
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
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .product-stock {
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .product-stock::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success-color);
            display: inline-block;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .stock-low {
            color: var(--danger-color);
        }

        .stock-medium {
            color: var(--warning-color);
        }

        .stock-high {
            color: var(--success-color);
        }

        .product-actions {
            display: flex;
            gap: 0.5rem;
        }

        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: var(--shadow);
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: var(--radius) var(--radius) 0 0;
            border: none;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .alert {
            border-radius: var(--radius);
            border: none;
            box-shadow: var(--shadow);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-shield-alt me-2"></i>Admin Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin_products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin_orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-box me-2"></i>Manage Products
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-2"></i>Add New Product
            </button>
        </div>

        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['Item_name']); ?>" class="img-fluid">
                    <?php else: ?>
                        <div class="placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['Item_name']); ?></h3>
                    <p class="product-category">
                        <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product['catogery']); ?>
                    </p>
                    <div class="product-price">₹<?php echo number_format($product['cost'], 2); ?></div>
                    <div class="product-stock <?php 
                        echo $product['no_of_items'] < 10 ? 'stock-low' : 
                             ($product['no_of_items'] < 50 ? 'stock-medium' : 'stock-high'); 
                    ?>">
                        <i class="fas fa-boxes me-1"></i><?php echo $product['no_of_items']; ?> items in stock
                    </div>
                    <?php if (!empty($product['description'])): ?>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($product['description']); ?></p>
                    <?php endif; ?>
                    <div class="product-actions">
                        <button class="btn btn-warning btn-sm" onclick="editProduct(<?php echo $product['ID']; ?>)">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $product['ID']; ?>, '<?php echo addslashes($product['Item_name']); ?>')">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Add New Product
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="cost" class="form-label">Price</label>
                            <input type="number" class="form-control" id="cost" name="cost" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="no_of_items" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="no_of_items" name="no_of_items" required>
                        </div>
                        <div class="mb-3">
                            <label for="catogery" class="form-label">Category</label>
                            <div class="input-group">
                                <select class="form-select" id="catogery_select" onchange="updateCategoryInput()">
                                    <option value="">Select existing category...</option>
                                    <?php foreach ($existing_categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                                    <?php endforeach; ?>
                                    <option value="_new_">+ Create new category</option>
                                </select>
                                <input type="text" class="form-control" id="catogery" name="catogery" required 
                                       placeholder="Enter category name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Product
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_item_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_cost" class="form-label">Price</label>
                            <input type="number" class="form-control" id="edit_cost" name="cost" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_no_of_items" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="edit_no_of_items" name="no_of_items" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_catogery" class="form-label">Category</label>
                            <div class="input-group">
                                <select class="form-select" id="edit_catogery_select" onchange="updateEditCategoryInput()">
                                    <option value="">Select existing category...</option>
                                    <?php foreach ($existing_categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                                    <?php endforeach; ?>
                                    <option value="_new_">+ Create new category</option>
                                </select>
                                <input type="text" class="form-control" id="edit_catogery" name="catogery" required 
                                       placeholder="Enter category name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Product Image (Optional)</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image. Accepted formats: JPG, PNG, GIF, WebP</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateCategoryInput() {
            const select = document.getElementById('catogery_select');
            const input = document.getElementById('catogery');
            
            if (select.value === '_new_') {
                input.value = '';
                input.placeholder = 'Enter new category name';
                input.focus();
            } else if (select.value) {
                input.value = select.value;
            }
        }
        
        function updateEditCategoryInput() {
            const select = document.getElementById('edit_catogery_select');
            const input = document.getElementById('edit_catogery');
            
            if (select.value === '_new_') {
                input.value = '';
                input.placeholder = 'Enter new category name';
                input.focus();
            } else if (select.value) {
                input.value = select.value;
            }
        }

        function editProduct(id) {
            // Fetch product data and populate edit modal
            const products = <?php echo json_encode($products); ?>;
            const product = products.find(p => p.ID == id);
            
            if (product) {
                document.getElementById('edit_id').value = product.ID;
                document.getElementById('edit_item_name').value = product.Item_name;
                document.getElementById('edit_cost').value = product.cost;
                document.getElementById('edit_no_of_items').value = product.no_of_items;
                document.getElementById('edit_catogery').value = product.catogery;
                document.getElementById('edit_catogery_select').value = product.catogery;
                document.getElementById('edit_description').value = product.description || '';
                
                new bootstrap.Modal(document.getElementById('editProductModal')).show();
            }
        }

        function deleteProduct(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
