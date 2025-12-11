<?php
// Include guard to prevent redeclaration errors
if (!defined('CART_FUNCTIONS_INCLUDED')) {
    define('CART_FUNCTIONS_INCLUDED', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($connect)) {
    require_once 'db_connection.php';
}

/**
 * Cart Management System
 * Supports both guest and logged-in users
 */

/**
 * Get cart items for current user (guest or logged-in)
 */
function getCartItems() {
    global $connect;
    
    if (isset($_SESSION['cid12'])) {
        // Logged-in user - get from database
        $user_id = $_SESSION['cid12'];
        $columns = [];
        $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
        if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
        $colId = isset($columns['ID']) ? 'ID' : 'product_id';
        $colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
        $colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
        $colPrice = isset($columns['cost']) ? 'cost' : 'price';
        $query = "SELECT c.*, p.".$colName." as name, p.".$colImage." as image, p.".$colPrice." as cost, c.no_of_items as quantity 
                  FROM cart c 
                  JOIN products p ON c.pid = p.".$colId." 
                  WHERE c.uid = '$user_id' 
                  ORDER BY c.cart_id DESC";
        $result = mysqli_query($connect, $query);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Ensure consistent image URL and map quantity field
            $row['image'] = getProductImageUrl($row['image'] ?? '');
            $row['product_id'] = $row['pid'];
            $items[] = $row;
        }
        return $items;
    } else {
        // Guest user - get from session
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
        
        $items = [];
        // Schema-aware product fetch
        $columns = [];
        $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
        if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
        $colId = isset($columns['ID']) ? 'ID' : 'product_id';
        $colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
        $colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
        $colPrice = isset($columns['cost']) ? 'cost' : 'price';
        foreach ($_SESSION['guest_cart'] as $product_id => $quantity) {
            $product_query = "SELECT ".$colId." AS ID, ".$colName." AS name, ".$colImage." AS image, ".$colPrice." AS cost 
                             FROM products WHERE ".$colId." = '".$product_id."'";
            $product_result = mysqli_query($connect, $product_query);
            if ($product = mysqli_fetch_assoc($product_result)) {
                // Ensure consistent image URL and map fields
                $product['image'] = getProductImageUrl($product['image'] ?? '');
                $product['quantity'] = $quantity;
                $product['product_id'] = $product['ID'];
                $product['cart_id'] = 'guest_' . $product_id;
                $items[] = $product;
            }
        }
        return $items;
    }
}

/**
 * Add item to cart
 */
function addToCart($product_id, $quantity = 1) {
    global $connect;
    
    $columns = [];
    $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
    if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
    $colId = isset($columns['ID']) ? 'ID' : 'product_id';
    $colStock = isset($columns['no_of_items']) ? 'no_of_items' : 'stock_quantity';
    $colPrice = isset($columns['cost']) ? 'cost' : 'price';
    // Check stock availability against desired total quantity
    $stock_query = "SELECT ".$colStock." FROM products WHERE ".$colId." = '".$product_id."'";
    $stock_result = mysqli_query($connect, $stock_query);
    $stock_row = mysqli_fetch_assoc($stock_result);
    $available_stock = intval($stock_row[$colStock] ?? 0);
    
    if (isset($_SESSION['cid12'])) {
        // Logged-in user - update database
        $user_id = $_SESSION['cid12'];
        
        // Check if item already exists in cart
        $check_query = "SELECT no_of_items FROM cart WHERE pid = '$product_id' AND uid = '$user_id'";
        $check_result = mysqli_query($connect, $check_query);
        $current_qty = 0;
        if (mysqli_num_rows($check_result) > 0) {
            $current_row = mysqli_fetch_assoc($check_result);
            $current_qty = intval($current_row['no_of_items'] ?? 0);
        }

        $desired_total = $current_qty + $quantity;
        if ($desired_total > $available_stock) {
            return ['success' => false, 'message' => 'Insufficient stock available'];
        }

        if ($current_qty > 0) {
            $update_query = "UPDATE cart SET no_of_items = no_of_items + $quantity 
                           WHERE pid = '$product_id' AND uid = '$user_id'";
            mysqli_query($connect, $update_query);
        } else {
            $price_query = "SELECT ".$colPrice." as price FROM products WHERE ".$colId." = '".$product_id."'";
            $price_result = mysqli_query($connect, $price_query);
            $price_row = mysqli_fetch_assoc($price_result);
            $price = floatval($price_row['price'] ?? 0);
            
            $insert_query = "INSERT INTO cart (uid, pid, no_of_items, cost_of_item) 
                           VALUES ('$user_id', '$product_id', '$quantity', '$price')";
            mysqli_query($connect, $insert_query);
        }
        
    } else {
        // Guest user - update session
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
        $current_qty = isset($_SESSION['guest_cart'][$product_id]) ? intval($_SESSION['guest_cart'][$product_id]) : 0;
        $desired_total = $current_qty + $quantity;
        if ($desired_total > $available_stock) {
            return ['success' => false, 'message' => 'Insufficient stock available'];
        }
        $_SESSION['guest_cart'][$product_id] = $desired_total;
    }
    
    return ['success' => true, 'message' => 'Item added to cart successfully!'];
}

/**
 * Update cart item quantity
 */
function updateCartQuantity($product_id, $new_quantity) {

    global $connect;
    
    if ($new_quantity <= 0) {
        return removeFromCart($product_id);
    }
    
    if (isset($_SESSION['cid12'])) {
        // Logged-in user
        $user_id = $_SESSION['cid12'];
        
        // Get current quantity to calculate difference
        $current_query = "SELECT no_of_items FROM cart WHERE pid = '$product_id' AND uid = '$user_id'";
        $current_result = mysqli_query($connect, $current_query);
        $current_row = mysqli_fetch_assoc($current_result);
        $current_quantity = $current_row['no_of_items'];
        
        $quantity_diff = $new_quantity - $current_quantity;
        
        // Check stock availability against final desired quantity
        $columns = [];
        $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
        if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
        $colId = isset($columns['ID']) ? 'ID' : 'product_id';
        $colStock = isset($columns['no_of_items']) ? 'no_of_items' : 'stock_quantity';
        $stock_query = "SELECT ".$colStock." FROM products WHERE ".$colId." = '".$product_id."'";
        $stock_result = mysqli_query($connect, $stock_query);
        $stock_row = mysqli_fetch_assoc($stock_result);
        $available_stock = intval($stock_row[$colStock] ?? 0);
        if ($new_quantity > $available_stock) {
            return ['success' => false, 'message' => 'Insufficient stock available'];
        }
        
        // Update cart
        $update_query = "UPDATE cart SET no_of_items = '$new_quantity' 
                       WHERE pid = '$product_id' AND uid = '$user_id'";
        mysqli_query($connect, $update_query);
        
        // Stock not changed at cart time
        
    } else {
        // Guest user
        if (!isset($_SESSION['guest_cart']) || !isset($_SESSION['guest_cart'][$product_id])) {
            return ['success' => false, 'message' => 'Item not found in cart'];
        }
        
        $current_quantity = $_SESSION['guest_cart'][$product_id];
        
        // Check stock availability against final desired quantity
        $columns = [];
        $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
        if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
        $colId = isset($columns['ID']) ? 'ID' : 'product_id';
        $colStock = isset($columns['no_of_items']) ? 'no_of_items' : 'stock_quantity';
        $stock_query = "SELECT ".$colStock." FROM products WHERE ".$colId." = '".$product_id."'";
        $stock_result = mysqli_query($connect, $stock_query);
        $stock_row = mysqli_fetch_assoc($stock_result);
        $available_stock = intval($stock_row[$colStock] ?? 0);
        
        if ($new_quantity > $available_stock) {
            return ['success' => false, 'message' => 'Insufficient stock available'];
        }
        
        // Update session
        $_SESSION['guest_cart'][$product_id] = $new_quantity;
        
        // Stock not changed at cart time
    }
    
    return ['success' => true, 'message' => 'Cart updated successfully!'];
}

/**
 * Remove item from cart
 */
function removeFromCart($product_id) {

    global $connect;
    
    if (isset($_SESSION['cid12'])) {
        // Logged-in user
        $user_id = $_SESSION['cid12'];
        
        // Get quantity to restore stock
        $quantity_query = "SELECT no_of_items FROM cart WHERE pid = '$product_id' AND uid = '$user_id'";
        $quantity_result = mysqli_query($connect, $quantity_query);
        $quantity_row = mysqli_fetch_assoc($quantity_result);
        $quantity = $quantity_row['no_of_items'];
        
        // Remove from cart
        $delete_query = "DELETE FROM cart WHERE pid = '$product_id' AND uid = '$user_id'";
        mysqli_query($connect, $delete_query);
        
        // No stock restoration at cart removal
        
    } else {
        // Guest user
        if (!isset($_SESSION['guest_cart']) || !isset($_SESSION['guest_cart'][$product_id])) {
            return ['success' => false, 'message' => 'Item not found in cart'];
        }
        
        $quantity = $_SESSION['guest_cart'][$product_id];
        
        // Remove from session
        unset($_SESSION['guest_cart'][$product_id]);
        
        // No stock restoration at cart removal
    }
    
    return ['success' => true, 'message' => 'Item removed from cart!'];
}

/**
 * Get total cart items count
 */
function getCartCount() {
    if (isset($_SESSION['cid12'])) {
        // Logged-in user
    
        global $connect; // Use the global connection variable
        $user_id = $_SESSION['cid12'];
        $query = "SELECT SUM(no_of_items) as total FROM cart WHERE uid = '$user_id'";
        $result = mysqli_query($connect, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['total'] ? intval($row['total']) : 0;
        }
        return 0;
    } else {
        // Guest user
        if (!isset($_SESSION['guest_cart'])) {
            return 0;
        }
        return array_sum($_SESSION['guest_cart']);
    }
}

/**
 * Get total cart value
 */
function getCartTotal() {

    global $connect;
    
    if (isset($_SESSION['cid12'])) {
        // Logged-in user
        $user_id = $_SESSION['cid12'];
        $query = "SELECT SUM(c.no_of_items * c.cost_of_item) as total 
                 FROM cart c 
                 WHERE c.uid = '$user_id'";
        $result = mysqli_query($connect, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    } else {
        // Guest user
        if (!isset($_SESSION['guest_cart']) || empty($_SESSION['guest_cart'])) {
            return 0;
        }
        
        $total = 0;
        // Schema-aware price lookup
        $columns = [];
        $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
        if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
        $colId = isset($columns['ID']) ? 'ID' : 'product_id';
        $colPrice = isset($columns['cost']) ? 'cost' : 'price';
        foreach ($_SESSION['guest_cart'] as $product_id => $quantity) {
            $price_query = "SELECT ".$colPrice." FROM products WHERE ".$colId." = '".$product_id."'";
            $price_result = mysqli_query($connect, $price_query);
            $price_row = mysqli_fetch_assoc($price_result);
            $total += floatval($price_row[$colPrice] ?? 0) * $quantity;
        }
        return $total;
    }
}

/**
 * Merge guest cart with user cart after login
 */
function mergeGuestCartWithUser($user_id) {

    global $connect;
    
    if (!isset($_SESSION['guest_cart']) || empty($_SESSION['guest_cart'])) {
        return;
    }
    
    // Schema-aware
    $columns = [];
    $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
    if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
    $colId = isset($columns['ID']) ? 'ID' : 'product_id';
    $colPrice = isset($columns['cost']) ? 'cost' : 'price';
    foreach ($_SESSION['guest_cart'] as $product_id => $quantity) {
        // Use new cart schema columns: uid, pid, no_of_items, cost_of_item
        $check_query = "SELECT * FROM cart WHERE pid = '$product_id' AND uid = '$user_id'";
        $check_result = mysqli_query($connect, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing item
            $update_query = "UPDATE cart SET no_of_items = no_of_items + $quantity 
                           WHERE pid = '$product_id' AND uid = '$user_id'";
            mysqli_query($connect, $update_query);
        } else {
            // Add new item
            $price_query = "SELECT ".$colPrice." FROM products WHERE ".$colId." = '".$product_id."'";
            $price_result = mysqli_query($connect, $price_query);
            $price_row = mysqli_fetch_assoc($price_result);
            $price = floatval($price_row[$colPrice] ?? 0);
            
            $insert_query = "INSERT INTO cart (uid, pid, no_of_items, cost_of_item) 
                           VALUES ('$user_id', '$product_id', '$quantity', '$price')";
            mysqli_query($connect, $insert_query);
        }
    }
    
    // Clear guest cart
    unset($_SESSION['guest_cart']);
}

/**
 * Clear entire cart
 */
function clearCart() {

    global $connect;
    
    if (isset($_SESSION['cid12'])) {
        // Logged-in user
        $user_id = $_SESSION['cid12'];
        
        // Clear cart (no stock restoration here)
        $delete_query = "DELETE FROM cart WHERE uid = '$user_id'";
        mysqli_query($connect, $delete_query);
    } else {
        // Guest user
        // Clear session cart (no stock restoration)
        $_SESSION['guest_cart'] = [];
    }
}

/**
 * Get consistent product image URL
 * Ensures all product images use the same format across the application
 */
function getProductImageUrl($image_path, $default_image = 'images/default.jpg') {
    // If no image path provided, use default
    if (empty($image_path)) {
        return $default_image;
    }
    
    // If image path already starts with 'images/', return as is
    if (strpos($image_path, 'images/') === 0) {
        return $image_path;
    }
    
    // If image path is just a filename, prepend 'images/'
    if (!strpos($image_path, '/') && !strpos($image_path, '\\')) {
        return 'images/' . $image_path;
    }
    
    // Return the path as is (it's likely already a complete path)
    return $image_path;
}

/**
 * Get product with consistent image URL
 */
function getProductWithImage($product_id) {

    global $connect;
    
    $columns = [];
    $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
    if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
    $colId = isset($columns['ID']) ? 'ID' : 'product_id';
    $query = "SELECT * FROM products WHERE ".$colId." = '".$product_id."'";
    $result = mysqli_query($connect, $query);
    
    if ($product = mysqli_fetch_assoc($result)) {
        // Ensure consistent image URL
        $imgCol = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
        $product['image'] = getProductImageUrl($product[$imgCol] ?? '');
        return $product;
    }
    
    return null;
}

} // End of include guard
