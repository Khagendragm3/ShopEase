<?php
require_once 'config.php';

/**
 * User Authentication Functions
 */

// Register a new user
function registerUser($username, $email, $password, $firstName, $lastName) {
    global $conn;
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if status column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
    if ($checkColumn->num_rows > 0) {
        // Status column exists, include it in the insert
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("sssss", $username, $email, $hashedPassword, $firstName, $lastName);
    } else {
        // Status column doesn't exist, use original query
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashedPassword, $firstName, $lastName);
    }
    
    // Execute statement
    if ($stmt->execute()) {
        return $conn->insert_id;
    } else {
        return false;
    }
}

// Login user
function loginUser($email, $password) {
    global $conn;
    
    // Check if status column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
    
    if ($checkColumn->num_rows > 0) {
        // Status column exists, include it in the query and check for active status
        $stmt = $conn->prepare("SELECT user_id, username, email, password, role, status FROM users WHERE email = ?");
    } else {
        // Status column doesn't exist, use original query
        $stmt = $conn->prepare("SELECT user_id, username, email, password, role FROM users WHERE email = ?");
    }
    
    $stmt->bind_param("s", $email);
    
    // Execute statement
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Check if user is active (if status column exists)
        if (isset($user['status']) && $user['status'] !== 'active') {
            return 'inactive';
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            return true;
        }
    }
    
    return false;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Logout user
function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['role']);
    session_destroy();
    
    redirect('login.php');
}

// Get user by ID
function getUserById($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    }
    
    return false;
}

// Update user profile
function updateUserProfile($userId, $firstName, $lastName, $address, $city, $state, $zipCode, $country, $phone) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, address = ?, city = ?, state = ?, zip_code = ?, country = ?, phone = ? WHERE user_id = ?");
    $stmt->bind_param("ssssssssi", $firstName, $lastName, $address, $city, $state, $zipCode, $country, $phone, $userId);
    
    return $stmt->execute();
}

/**
 * Product Functions
 */

// Get all products
function getAllProducts($limit = null, $offset = 0) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name, 
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.status = 'active' 
            ORDER BY p.created_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $offset, $limit);
    } else {
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Get product by ID
function getProductById($productId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.category_id 
                           WHERE p.product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
        
        // Get product images
        $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $imagesResult = $stmt->get_result();
        
        $product['images'] = [];
        while ($image = $imagesResult->fetch_assoc()) {
            $product['images'][] = $image;
        }
        
        // Get product attributes
        $stmt = $conn->prepare("SELECT * FROM product_attributes WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $attributesResult = $stmt->get_result();
        
        $product['attributes'] = [];
        while ($attribute = $attributesResult->fetch_assoc()) {
            $product['attributes'][] = $attribute;
        }
        
        return $product;
    }
    
    return false;
}

// Get products by category
function getProductsByCategory($categoryId, $limit = null, $offset = 0) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name, 
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.category_id = ? AND p.status = 'active' 
            ORDER BY p.created_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $categoryId, $offset, $limit);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Search products
function searchProducts($keyword) {
    global $conn;
    
    $search = "%$keyword%";
    
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, 
                           (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.category_id 
                           WHERE (p.name LIKE ? OR p.description LIKE ?) AND p.status = 'active' 
                           ORDER BY p.created_at DESC");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Category Functions
 */

// Get all categories
function getAllCategories() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

// Get category by ID
function getCategoryById($categoryId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Cart Functions
 */

// Add product to cart
function addToCart($userId, $productId, $quantity = 1) {
    global $conn;
    
    // Check if product already in cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $cart = $result->fetch_assoc();
        $newQuantity = $cart['quantity'] + $quantity;
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $stmt->bind_param("ii", $newQuantity, $cart['cart_id']);
        return $stmt->execute();
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        return $stmt->execute();
    }
}

// Get user cart
function getUserCart($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.sale_price, 
                           (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.product_id 
                           WHERE c.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart = [];
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $price = $row['sale_price'] ? $row['sale_price'] : $row['price'];
        $row['item_total'] = $price * $row['quantity'];
        $total += $row['item_total'];
        $cart[] = $row;
    }
    
    return [
        'items' => $cart,
        'total' => $total,
        'count' => count($cart)
    ];
}

// Update cart item quantity
function updateCartItemQuantity($cartId, $quantity) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $stmt->bind_param("ii", $quantity, $cartId);
    return $stmt->execute();
}

// Remove item from cart
function removeFromCart($cartId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
    $stmt->bind_param("i", $cartId);
    return $stmt->execute();
}

// Clear user cart
function clearUserCart($userId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}

/**
 * Order Functions
 */

// Create new order
function createOrder($userId, $totalAmount, $shippingAddress, $billingAddress, $paymentMethod) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, billing_address, payment_method) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $userId, $totalAmount, $shippingAddress, $billingAddress, $paymentMethod);
        $stmt->execute();
        
        $orderId = $conn->insert_id;
        
        // Get cart items
        $cart = getUserCart($userId);
        
        // Insert order items
        foreach ($cart['items'] as $item) {
            $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
            
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                   VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $price);
            $stmt->execute();
            
            // Update product quantity
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }
        
        // Clear cart
        clearUserCart($userId);
        
        // Commit transaction
        $conn->commit();
        
        return $orderId;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        return false;
    }
}

// Get user orders
function getUserOrders($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    return $orders;
}

// Get order by ID
function getOrderById($orderId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o 
                           JOIN users u ON o.user_id = u.user_id 
                           WHERE o.order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $order = $result->fetch_assoc();
        
        // Get order items
        $stmt = $conn->prepare("SELECT oi.*, p.name, 
                               (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.product_id 
                               WHERE oi.order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $itemsResult = $stmt->get_result();
        
        $order['items'] = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $order['items'][] = $item;
        }
        
        return $order;
    }
    
    return false;
}

/**
 * Wishlist Functions
 */

// Add product to wishlist
function addToWishlist($userId, $productId) {
    global $conn;
    
    // Check if product already in wishlist
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $productId);
        return $stmt->execute();
    }
    
    return true;
}

// Remove from wishlist
function removeFromWishlist($wishlistId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE wishlist_id = ?");
    $stmt->bind_param("i", $wishlistId);
    return $stmt->execute();
}

// Get user wishlist
function getUserWishlist($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT w.*, p.name, p.price, p.sale_price, 
                           (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image 
                           FROM wishlist w 
                           JOIN products p ON w.product_id = p.product_id 
                           WHERE w.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $wishlist = [];
    while ($row = $result->fetch_assoc()) {
        $wishlist[] = $row;
    }
    
    return $wishlist;
}

/**
 * Review Functions
 */

// Add product review
function addProductReview($productId, $userId, $rating, $comment) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $productId, $userId, $rating, $comment);
    return $stmt->execute();
}

// Get product reviews
function getProductReviews($productId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT r.*, u.username FROM reviews r 
                           JOIN users u ON r.user_id = u.user_id 
                           WHERE r.product_id = ? AND r.status = 'approved' 
                           ORDER BY r.created_at DESC");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    
    return $reviews;
}

// Get product average rating
function getProductAverageRating($productId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = ? AND status = 'approved'");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return round($row['avg_rating'], 1);
}

/**
 * Coupon Functions
 */

// Validate coupon
function validateCoupon($code, $totalAmount) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND (end_date IS NULL OR end_date >= CURDATE())");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $coupon = $result->fetch_assoc();
        
        // Check minimum purchase
        if ($coupon['min_purchase'] && $totalAmount < $coupon['min_purchase']) {
            return false;
        }
        
        // Check usage limit
        if ($coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit']) {
            return false;
        }
        
        return $coupon;
    }
    
    return false;
}

// Apply coupon discount
function applyCouponDiscount($coupon, $totalAmount) {
    if ($coupon['discount_type'] == 'percentage') {
        $discount = ($totalAmount * $coupon['discount_value']) / 100;
    } else {
        $discount = $coupon['discount_value'];
    }
    
    // Ensure discount doesn't exceed total amount
    if ($discount > $totalAmount) {
        $discount = $totalAmount;
    }
    
    return $discount;
}

// Update coupon usage count
function updateCouponUsage($couponId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE coupons SET usage_count = usage_count + 1 WHERE coupon_id = ?");
    $stmt->bind_param("i", $couponId);
    return $stmt->execute();
}

/**
 * Utility Functions
 */

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Generate pagination
function generatePagination($currentPage, $totalPages, $url) {
    $pagination = '<ul class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($currentPage - 1) . '">Previous</a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><a class="page-link">Previous</a></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $pagination .= '<li class="page-item active"><a class="page-link">' . $i . '</a></li>';
        } else {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($currentPage + 1) . '">Next</a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><a class="page-link">Next</a></li>';
    }
    
    $pagination .= '</ul>';
    
    return $pagination;
}

// Upload image
function uploadImage($file, $directory = 'assets/images/products/') {
    // Create directory if it doesn't exist
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    $targetDir = APP_ROOT . '/' . $directory;
    $fileName = basename($file['name']);
    $targetFile = $targetDir . time() . '_' . $fileName;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['error' => 'File is not an image.'];
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        return ['error' => 'File is too large. Max size is 5MB.'];
    }
    
    // Allow certain file formats
    if ($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg' && $imageFileType != 'gif') {
        return ['error' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'path' => $directory . time() . '_' . $fileName];
    } else {
        return ['error' => 'There was an error uploading your file.'];
    }
}

/**
 * Handle image upload
 * 
 * @param array $file The $_FILES array element
 * @param string $uploadDir The directory to upload to (relative to site root)
 * @param string $oldImage The old image path to delete (if any)
 * @param array $allowedTypes Array of allowed MIME types
 * @param string $prefix Optional prefix for the filename
 * @return array Array with 'success', 'filename', and 'error' keys
 */
function handleImageUpload($file, $uploadDir, $oldImage = '', $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $prefix = '') {
    $result = [
        'success' => false,
        'filename' => '',
        'error' => ''
    ];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'No file uploaded or upload error occurred.';
        return $result;
    }
    
    // Validate file type
    $fileType = $file['type'];
    if (!in_array($fileType, $allowedTypes)) {
        $result['error'] = 'Invalid file type. Only ' . implode(', ', array_map(function($type) {
            return strtoupper(str_replace('image/', '', $type));
        }, $allowedTypes)) . ' files are allowed.';
        return $result;
    }
    
    // Ensure upload directory exists
    $fullUploadDir = dirname(__DIR__) . '/' . ltrim($uploadDir, '/');
    if (!file_exists($fullUploadDir)) {
        if (!mkdir($fullUploadDir, 0777, true)) {
            $result['error'] = 'Failed to create upload directory.';
            return $result;
        }
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = $prefix . time() . '_' . uniqid() . '.' . $extension;
    $targetFilePath = $fullUploadDir . '/' . $fileName;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // Delete old image if exists
        if (!empty($oldImage)) {
            $oldImagePath = $fullUploadDir . '/' . $oldImage;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        
        $result['success'] = true;
        $result['filename'] = $fileName;
    } else {
        $result['error'] = 'Failed to upload image.';
    }
    
    return $result;
}

/**
 * Get image URL
 * 
 * @param string $imagePath The image path
 * @param string $uploadDir The directory where the image is stored
 * @param string $defaultImage The default image to use if the image doesn't exist
 * @return string The full URL to the image
 */
function getImageUrl($imagePath, $uploadDir, $defaultImage = 'no-image.jpg') {
    if (!empty($imagePath)) {
        $fullPath = dirname(__DIR__) . '/' . ltrim($uploadDir, '/') . '/' . $imagePath;
        if (file_exists($fullPath)) {
            return URL_ROOT . '/' . ltrim($uploadDir, '/') . '/' . $imagePath;
        }
    }
    
    return URL_ROOT . '/assets/img/' . $defaultImage;
}

/**
 * Media Functions
 */

/**
 * Get media by ID
 * 
 * @param int $mediaId The media ID
 * @return array|bool The media data or false if not found
 */
function getMediaById($mediaId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM media WHERE id = ?");
    $stmt->bind_param("i", $mediaId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get media by category
 * 
 * @param string $category The media category (hero, profiles, blog, testimonials, banners, other)
 * @param int $limit Optional limit for number of results
 * @return array The media data
 */
function getMediaByCategory($category, $limit = null) {
    global $conn;
    
    $sql = "SELECT * FROM media WHERE category = ? ORDER BY uploaded_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $category, $limit);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $media = [];
    while ($row = $result->fetch_assoc()) {
        $media[] = $row;
    }
    
    return $media;
}

/**
 * Get media URL
 * 
 * @param int|array $media The media ID or media array
 * @param string $defaultImage The default image to use if the media doesn't exist
 * @return string The full URL to the media file
 */
function getMediaUrl($media, $defaultImage = 'no-image.jpg') {
    global $conn;
    
    // If $media is an ID, get the media data
    if (is_numeric($media)) {
        $mediaData = getMediaById($media);
    } else {
        $mediaData = $media;
    }
    
    if ($mediaData && !empty($mediaData['file_name'])) {
        $fullPath = dirname(__DIR__) . '/' . $mediaData['path'] . '/' . $mediaData['file_name'];
        if (file_exists($fullPath)) {
            return URL_ROOT . '/' . $mediaData['path'] . '/' . $mediaData['file_name'];
        }
    }
    
    return URL_ROOT . '/assets/img/' . $defaultImage;
}

/**
 * Record a payment transaction
 * 
 * @param int $orderId Order ID
 * @param string $paymentMethod Payment method
 * @param string $transactionReference Transaction reference
 * @param float $amount Transaction amount
 * @param string $status Transaction status
 * @return bool True if transaction is recorded, false otherwise
 */
function recordTransaction($orderId, $paymentMethod, $transactionReference, $amount, $status = 'completed') {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO transactions (order_id, payment_method, transaction_reference, amount, status) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issds", $orderId, $paymentMethod, $transactionReference, $amount, $status);
    
    return $stmt->execute();
}

/**
 * Update order payment status
 * 
 * @param int $orderId Order ID
 * @param string $status Payment status
 * @return bool True if status is updated, false otherwise
 */
function updateOrderPaymentStatus($orderId, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $orderId);
    
    return $stmt->execute();
}
?> 