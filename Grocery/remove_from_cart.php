<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'cart_functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error', 'cart_total' => 0, 'cart_count' => 0];

try {
    if (!isset($_POST['product_id'])) {
        throw new Exception('Missing required parameters');
    }
    
    $product_id = intval($_POST['product_id']);
    
    // Remove item from cart
    $result = removeFromCart($product_id);
    
    if ($result['success']) {
        // Get updated cart information
        $cartItems = getCartItems();
        $cartTotal = 0;
        
        foreach ($cartItems as $item) {
            $cartTotal += $item['cost'] * $item['quantity'];
        }
        
        $response['success'] = true;
        $response['message'] = 'Item removed successfully';
        $response['cart_total'] = number_format($cartTotal, 2);
        $response['cart_count'] = getCartCount();
    } else {
        $response['message'] = $result['message'];
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

