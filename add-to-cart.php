<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to add items to cart',
        'redirect' => true,
        'redirect_url' => URL_ROOT . '/login.php'
    ]);
    exit;
}

// Get product ID and quantity from POST data
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate product ID
if ($productId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

// Validate quantity
if ($quantity <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Quantity must be at least 1'
    ]);
    exit;
}

// Check if product exists and is active
$product = getProductById($productId);
if (!$product || $product['status'] !== 'active') {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found or unavailable'
    ]);
    exit;
}

// Check if product is in stock
if ($product['quantity'] < $quantity) {
    echo json_encode([
        'success' => false,
        'message' => 'Not enough stock available. Only ' . $product['quantity'] . ' items left.'
    ]);
    exit;
}

// Add to cart
if (addToCart($_SESSION['user_id'], $productId, $quantity)) {
    // Get updated cart count
    $cart = getUserCart($_SESSION['user_id']);
    $cartCount = 0;
    
    foreach ($cart['items'] as $item) {
        $cartCount += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart successfully',
        'cart_count' => $cartCount
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add product to cart. Please try again.'
    ]);
}
?> 