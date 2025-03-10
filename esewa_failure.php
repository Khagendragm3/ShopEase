<?php
$pageTitle = "Payment Failed";
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/esewa_config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to view this page.');
    redirect('login.php');
}

// Log all received parameters for debugging
error_log('eSewa Failure Callback - Received Parameters: ' . print_r($_GET, true));

// Get order ID from URL
$orderId = isset($_GET['oid']) ? $_GET['oid'] : '';

// Alternative parameter names that might be used
if (empty($orderId) && isset($_GET['pid'])) $orderId = $_GET['pid'];

// If still empty, try to get from session
if (empty($orderId) && isset($_SESSION['pending_order_id'])) {
    $orderId = $_SESSION['pending_order_id'];
    unset($_SESSION['pending_order_id']); // Clear it after use
}

if (empty($orderId)) {
    error_log('eSewa Failure Callback - Missing order ID');
    flash('error', 'Invalid order reference.');
    redirect('account.php');
}

// Get order details
$order = getOrderById($orderId);

// Check if order exists and belongs to the current user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    flash('error', 'Order not found.');
    redirect('account.php');
}

// Update order payment status
updateOrderPaymentStatus($orderId, 'failed');

// Record failed transaction if there's a reference ID
$refId = '';
if (isset($_GET['refId']) && !empty($_GET['refId'])) {
    $refId = $_GET['refId'];
} elseif (isset($_GET['ref_id']) && !empty($_GET['ref_id'])) {
    $refId = $_GET['ref_id'];
}

if (!empty($refId)) {
    $amount = isset($_GET['amt']) ? $_GET['amt'] : (isset($_GET['amount']) ? $_GET['amount'] : $order['total_amount']);
    recordTransaction($orderId, 'esewa', $refId, $amount, 'failed');
    error_log('eSewa Payment Failed - Order ID: ' . $orderId . ', Ref ID: ' . $refId);
} else {
    error_log('eSewa Payment Failed - Order ID: ' . $orderId . ' (No reference ID)');
}

// Set error message
flash('error', 'Payment failed. Please try again or choose a different payment method.');

// Redirect to order details page
redirect("order-details.php?id=$orderId");
?> 