<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Check if order ID and status are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status']) || empty($_GET['status'])) {
    flash('error', 'Order ID and status are required.');
    redirect('orders.php');
}

$orderId = (int) sanitize($_GET['id']);
$status = sanitize($_GET['status']);

// Validate status
$validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    flash('error', 'Invalid status.');
    redirect('orders.php');
}

// Check if order exists
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

// Update order status
$stmt = $conn->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE order_id = ?");
$stmt->bind_param("si", $status, $orderId);

if ($stmt->execute()) {
    // Get customer email for notification
    $stmt = $conn->prepare("SELECT o.order_id, u.email, u.first_name, u.last_name 
                           FROM orders o 
                           JOIN users u ON o.user_id = u.user_id 
                           WHERE o.order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orderData = $result->fetch_assoc();
        
        // Send email notification to customer about status change
        // This is a placeholder for email sending functionality
        // You would implement your email sending logic here
        
        // Example:
        // $to = $orderData['email'];
        // $subject = "Order #" . str_pad($orderId, 8, '0', STR_PAD_LEFT) . " Status Update";
        // $message = "Dear " . $orderData['first_name'] . " " . $orderData['last_name'] . ",\n\n";
        // $message .= "Your order #" . str_pad($orderId, 8, '0', STR_PAD_LEFT) . " status has been updated to " . ucfirst($status) . ".\n\n";
        // $message .= "Thank you for shopping with us!\n\n";
        // $message .= "Best regards,\nThe Team";
        // $headers = "From: noreply@example.com";
        // mail($to, $subject, $message, $headers);
    }
    
    flash('success', 'Order status updated to ' . ucfirst($status) . ' successfully.');
} else {
    flash('error', 'Failed to update order status.');
}

// Redirect back to order details or orders list
if (isset($_GET['return']) && $_GET['return'] === 'details') {
    redirect('order-details.php?id=' . $orderId);
} else {
    redirect('orders.php');
}
?> 