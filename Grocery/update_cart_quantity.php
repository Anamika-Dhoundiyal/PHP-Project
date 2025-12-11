<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'cart_functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error', 'item_total' => 0, 'cart_total' => 0, 'cart_count' => 0];

try {
    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        throw new Exception('Missing required parameters');
    }
    
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity < 1) {
        throw new Exception('Quantity must be at least 1');
    }
    
    // Update cart quantity
    $result = updateCartQuantity($product_id, $quantity);
    
    if ($result['success']) {
        // Get updated cart information
        $cartItems = getCartItems();
        $cartTotal = 0;
        $itemTotal = 0;
        
        foreach ($cartItems as $item) {
            $cartTotal += $item['cost'] * $item['quantity'];
            if ($item['product_id'] == $product_id) {
                $itemTotal = $item['cost'] * $item['quantity'];
            }
        }
        
        $response['success'] = true;
        $response['message'] = 'Quantity updated successfully';
        $response['item_total'] = number_format($itemTotal, 2);
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