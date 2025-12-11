<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'cart_functions.php';

header('Content-Type: application/json');

echo json_encode(['count' => getCartCount()]);
?>