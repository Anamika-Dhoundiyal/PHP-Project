<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connection.php'; // Ensure database connection is established first
require 'cart_functions.php';

header('Content-Type: application/json');
ob_start();

$response = ['success' => false, 'message' => 'Unknown error', 'cart_count' => 0, 'debug' => []];

try {
    // Debug: Check session status
    $response['debug']['session_status'] = session_status();
    $response['debug']['session_id'] = session_id();
    $response['debug']['session_vars'] = $_SESSION;
    
    // Debug: Check POST data
    $response['debug']['post_data'] = $_POST;
    
    if (!isset($_POST['cpid'])) {
        throw new Exception('Missing product ID parameter');
    }
    
    $product_id = intval($_POST['cpid']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Debug: Check parsed values
    $response['debug']['parsed_product_id'] = $product_id;
    $response['debug']['parsed_quantity'] = $quantity;
    
    // Validate product exists
    $columns = [];
    $colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
    if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }
    $colId = isset($columns['ID']) ? 'ID' : 'product_id';
    
    $product_check = mysqli_query($connect, "SELECT * FROM products WHERE $colId = '$product_id'");
    if (mysqli_num_rows($product_check) == 0) {
        throw new Exception('Product not found');
    }
    
    // Add to cart using our cart functions
    $result = addToCart($product_id, $quantity);
    
    // Debug: Check addToCart result
    $response['debug']['addtocart_result'] = $result;
    
    if ($result['success']) {
        $response['success'] = true;
        $response['message'] = 'Item added to cart successfully!';
        $response['cart_count'] = getCartCount();
    } else {
        $response['message'] = $result['message'];
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

if (ob_get_length()) { ob_clean(); }
echo json_encode($response);
