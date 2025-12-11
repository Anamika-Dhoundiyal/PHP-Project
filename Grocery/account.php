<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['cid12'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['cid12'];

// Database connection
include 'dbconnection.php';

// Check connection
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $cname = mysqli_real_escape_string($connect, $_POST['cname'] ?? '');
        $email = mysqli_real_escape_string($connect, $_POST['email'] ?? '');
        $phone = mysqli_real_escape_string($connect, $_POST['phone'] ?? '');
        $update_fields = [];
        $col_check = mysqli_query($connect, "SHOW COLUMNS FROM customer LIKE 'cname'");
        $name_col = mysqli_num_rows($col_check) > 0 ? 'cname' : 'user';
        if ($cname !== '') { $update_fields[] = "$name_col='$cname'"; }
        $email_exists = mysqli_num_rows(mysqli_query($connect, "SHOW COLUMNS FROM customer LIKE 'email'")) > 0;
        if (!$email_exists) {
            mysqli_query($connect, "ALTER TABLE customer ADD COLUMN email VARCHAR(150)");
            $email_exists = true;
        }
        if ($email_exists && $email !== '') { $update_fields[] = "email='$email'"; }
        if ($phone !== '') { $update_fields[] = "phone_no='$phone'"; }
        if (!empty($update_fields)) {
            $update_sql = "UPDATE customer SET " . implode(',', $update_fields) . " WHERE ID='$customer_id'";
            mysqli_query($connect, $update_sql);
        }
        header('Location: account.php');
        exit();
    } elseif ($action === 'change_password') {
        $current_password = mysqli_real_escape_string($connect, $_POST['current_password'] ?? '');
        $new_password = mysqli_real_escape_string($connect, $_POST['new_password'] ?? '');
        $confirm_password = mysqli_real_escape_string($connect, $_POST['confirm_password'] ?? '');
        $pwd_query = mysqli_query($connect, "SELECT password FROM customer WHERE ID='$customer_id'");
        $pwd_row = mysqli_fetch_assoc($pwd_query);
        $existing_pwd = $pwd_row['password'] ?? '';
        if ($existing_pwd === $current_password && $new_password !== '' && $new_password === $confirm_password) {
            mysqli_query($connect, "UPDATE customer SET password='$new_password' WHERE ID='$customer_id'");
        }
        header('Location: account.php');
        exit();
    } elseif ($action === 'update_address') {
        $address = mysqli_real_escape_string($connect, $_POST['address'] ?? '');
        $city = mysqli_real_escape_string($connect, $_POST['city'] ?? '');
        $state = mysqli_real_escape_string($connect, $_POST['state'] ?? '');
        $zip_code = mysqli_real_escape_string($connect, $_POST['zip_code'] ?? '');
        $update_fields = [];
        $addr_exists = mysqli_num_rows(mysqli_query($connect, "SHOW COLUMNS FROM customer LIKE 'address'")) > 0;
        $city_exists = mysqli_num_rows(mysqli_query($connect, "SHOW COLUMNS FROM customer LIKE 'city'")) > 0;
        $state_exists = mysqli_num_rows(mysqli_query($connect, "SHOW COLUMNS FROM customer LIKE 'state'")) > 0;
        $zip_exists = mysqli_num_rows(mysqli_query($connect, "SHOW COLUMNS FROM customer LIKE 'zip_code'")) > 0;
        if (!$addr_exists) { mysqli_query($connect, "ALTER TABLE customer ADD COLUMN address TEXT"); $addr_exists = true; }
        if (!$city_exists) { mysqli_query($connect, "ALTER TABLE customer ADD COLUMN city VARCHAR(100)"); $city_exists = true; }
        if (!$state_exists) { mysqli_query($connect, "ALTER TABLE customer ADD COLUMN state VARCHAR(100)"); $state_exists = true; }
        if (!$zip_exists) { mysqli_query($connect, "ALTER TABLE customer ADD COLUMN zip_code VARCHAR(20)"); $zip_exists = true; }
        if ($addr_exists && $address !== '') { $update_fields[] = "address='$address'"; }
        if ($city_exists && $city !== '') { $update_fields[] = "city='$city'"; }
        if ($state_exists && $state !== '') { $update_fields[] = "state='$state'"; }
        if ($zip_exists && $zip_code !== '') { $update_fields[] = "zip_code='$zip_code'"; }
        if (!empty($update_fields)) {
            $update_sql = "UPDATE customer SET " . implode(',', $update_fields) . " WHERE ID='$customer_id'";
            mysqli_query($connect, $update_sql);
        }
        header('Location: account.php');
        exit();
    }
}

// Get customer details
$query = "SELECT * FROM customer WHERE ID = '$customer_id'";
$result = mysqli_query($connect, $query);
$customer = mysqli_fetch_assoc($result);

// Get customer's orders using new schema (orders + order_items)
// Detect products schema columns
$columns = [];
$colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
$colId = isset($columns['ID']) ? 'ID' : 'product_id';
$colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
$colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');

// Detect customer column in orders
$ordersCols = [];
$ordersRes = mysqli_query($connect, "SHOW COLUMNS FROM orders");
if ($ordersRes) { while ($oc = mysqli_fetch_assoc($ordersRes)) { $ordersCols[$oc['Field']] = true; } }
$colCust = isset($ordersCols['customer_id']) ? 'customer_id' : (isset($ordersCols['user_id']) ? 'user_id' : 'customer_id');

$orders_query = "SELECT o.*, p.".$colName." AS Item_name, p.".$colImage." AS image, oi.quantity, oi.price, oi.subtotal 
                 FROM orders o 
                 JOIN order_items oi ON o.order_id = oi.order_id 
                 JOIN products p ON oi.product_id = p.".$colId." 
                 WHERE o.".$colCust." = '$customer_id' 
                 ORDER BY o.order_date DESC";
$orders_result = mysqli_query($connect, $orders_query);
$orders = [];
while ($row = mysqli_fetch_assoc($orders_result)) {
    $orders[] = $row;
}

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Account - FreshMart</title>
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
            --warning-color: #f59e0b;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
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

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: 600;
        }

        .account-section {
            padding: 3rem 0;
            min-height: calc(100vh - 76px);
        }

        .account-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .account-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .profile-email {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .nav-pills .nav-link {
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            font-weight: 500;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .nav-pills .nav-link:hover {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        .nav-pills .nav-link.active {
            background: var(--primary-color);
            color: white !important;
            box-shadow: var(--shadow-md);
        }

        .info-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .info-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 500;
            color: var(--text-primary);
        }

        .order-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .order-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-number {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-processing {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-shipped {
            background: #d1fae5;
            color: #065f46;
        }

        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: var(--radius-md);
            padding: 0.5rem 1rem;
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
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .account-section {
                padding: 1.5rem 0;
            }
            
            .profile-header {
                padding: 1.5rem;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar_simple.php'; ?>

    <!-- Account Section -->
    <section class="account-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <!-- Profile Card -->
                    <div class="account-card mb-4">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="profile-name"><?php echo htmlspecialchars($customer['cname'] ?? 'Customer'); ?></div>
                            <div class="profile-email"><?php echo htmlspecialchars($customer['email'] ?? ''); ?></div>
                        </div>
                        
                        <div class="p-4">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo count($orders); ?></div>
                                <div class="stats-label">Total Orders</div>
                            </div>
                            
                            <div class="d-grid">
                                <a href="track_order.php" class="btn btn-outline-primary mb-2">
                                    <i class="fas fa-truck me-2"></i>Track Orders
                                </a>
                                <a href="logout.php" class="btn btn-outline-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <!-- Account Content -->
                    <div class="account-card">
                        <div class="card-header bg-transparent p-4 border-bottom">
                            <ul class="nav nav-pills" id="accountTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab">
                                        <i class="fas fa-user me-2"></i>Profile
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="orders-tab" data-bs-toggle="pill" data-bs-target="#orders" type="button" role="tab">
                                        <i class="fas fa-box me-2"></i>Orders
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="address-tab" data-bs-toggle="pill" data-bs-target="#address" type="button" role="tab">
                                        <i class="fas fa-map-marker-alt me-2"></i>Address
                                    </button>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="card-body p-4">
                            <div class="tab-content" id="accountTabContent">
                                <!-- Profile Tab -->
                                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                    <div class="info-card">
                                        <h5 class="info-title">
                                            <i class="fas fa-user-circle text-primary"></i>
                                            Personal Information
                                        </h5>
                                        <div class="info-item">
                                            <span class="info-label">Full Name</span>
                                            <span class="info-value"><?php echo htmlspecialchars($customer['cname'] ?? ($customer['user'] ?? 'Not provided')); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Email Address</span>
                                            <span class="info-value"><?php echo htmlspecialchars($customer['email'] ?? 'Not provided'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Phone Number</span>
                                            <span class="info-value"><?php echo htmlspecialchars($customer['phone_no'] ?? ($customer['phone'] ?? 'Not provided')); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Member Since</span>
                                            <span class="info-value"><?php echo date('F j, Y', strtotime($customer['created_at'] ?? 'now')); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mt-3">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-primary mb-2" onclick="toggleForm('profile-form')">
                                                <i class="fas fa-edit me-2"></i>Edit Profile
                                            </button>
                                            <form method="post" class="account-form" id="profile-form" style="display:none">
                                                <input type="hidden" name="action" value="update_profile" />
                                                <div class="mb-2">
                                                    <label class="form-label">Full Name</label>
                                                    <input type="text" name="cname" class="form-control" value="<?php echo htmlspecialchars($customer['cname'] ?? ($customer['user'] ?? '')); ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone_no'] ?? ($customer['phone'] ?? '')); ?>">
                                                </div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-2"></i>Save Profile
                                                </button>
                                            </form>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-outline-primary mb-2" onclick="toggleForm('password-form')">
                                                <i class="fas fa-key me-2"></i>Change Password
                                            </button>
                                            <form method="post" class="account-form" id="password-form" style="display:none">
                                                <input type="hidden" name="action" value="change_password" />
                                                <div class="mb-2">
                                                    <label class="form-label">Current Password</label>
                                                    <input type="password" name="current_password" class="form-control" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">New Password</label>
                                                    <input type="password" name="new_password" class="form-control" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Confirm New Password</label>
                                                    <input type="password" name="confirm_password" class="form-control" required>
                                                </div>
                                                <button type="submit" class="btn btn-outline-primary">
                                                    <i class="fas fa-key me-2"></i>Change Password
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Orders Tab -->
                                <div class="tab-pane fade" id="orders" role="tabpanel">
                                    <h5 class="mb-4">
                                        <i class="fas fa-box text-primary me-2"></i>
                                        Recent Orders
                                    </h5>
                                    
                                    <?php if (empty($orders)): ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No orders yet</h6>
                                            <p class="text-muted">Start shopping to see your orders here</p>
                                            <a href="products.php" class="btn btn-primary mt-2">
                                                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($orders as $order): ?>
                                            <div class="order-card">
                                                <div class="order-header">
                                                    <div>
                                                        <div class="order-number">Order #<?php echo htmlspecialchars($order['order_id']); ?></div>
                                                        <small class="text-muted"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></small>
                                                    </div>
                                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <small class="text-muted">Product</small>
                                                        <div class="h6 mb-0"><?php echo htmlspecialchars($order['Item_name']); ?></div>
                                                        <small class="text-muted">Quantity: <?php echo intval($order['quantity']); ?></small>
                                                    </div>
                                                    <div class="col-md-6 text-md-end">
                                                        <small class="text-muted">Total (item)</small>
                                                        <div class="h6 mb-0">$<?php echo number_format(floatval($order['subtotal']), 2); ?></div>
                                                        <a href="track_order.php?order_id=<?php echo intval($order['order_id']); ?>" class="btn btn-sm btn-primary mt-2">
                                                            <i class="fas fa-truck me-1"></i>Track Order
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Address Tab -->
                                <div class="tab-pane fade" id="address" role="tabpanel">
                                    <div class="info-card">
                                        <h5 class="info-title">
                                            <i class="fas fa-map-marker-alt text-primary"></i>
                                            Default Address
                                        </h5>
                                        <div class="info-item">
                                            <span class="info-label">Street Address</span>
                                            <span class="info-value"><?php echo htmlspecialchars($customer['address'] ?? 'Not provided'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">City</span>
                                            <span class="info-value"><?php echo htmlspecialchars($customer['city'] ?? 'Not provided'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">State</span>
                                            <span class="info-value"><?php echo htmlspecialchars($customer['state'] ?? 'Not provided'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">ZIP Code</span>
                                            <span class="info-value"><?php echo htmlspecialchars($customer['zip_code'] ?? 'Not provided'); ?></span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary mb-2" onclick="toggleForm('address-form')">
                                        <i class="fas fa-plus me-2"></i>Edit Address
                                    </button>
                                    <form method="post" class="mt-3" id="address-form" style="display:none">
                                        <input type="hidden" name="action" value="update_address" />
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label">Street Address</label>
                                                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">City</label>
                                                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">State</label>
                                                <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">ZIP</label>
                                                <input type="text" name="zip_code" class="form-control" value="<?php echo htmlspecialchars($customer['zip_code'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3">
                                            <i class="fas fa-save me-2"></i>Save Address
                                        </button>
                                    </form>
                                </div>
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
        function toggleForm(id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
