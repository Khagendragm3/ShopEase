<?php
$pageTitle = "Payment Successful";
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/esewa_config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to view this page.');
    redirect('login.php');
}

// Log all received parameters for debugging
error_log('eSewa Success Callback - Received Parameters: ' . print_r($_GET, true));

// Get eSewa response parameters
$oid = isset($_GET['oid']) ? $_GET['oid'] : '';
$amt = isset($_GET['amt']) ? $_GET['amt'] : '';
$refId = isset($_GET['refId']) ? $_GET['refId'] : '';

// Alternative parameter names that might be used
if (empty($oid) && isset($_GET['pid'])) $oid = $_GET['pid'];
if (empty($refId) && isset($_GET['ref_id'])) $refId = $_GET['ref_id'];
if (empty($amt) && isset($_GET['amount'])) $amt = $_GET['amount'];

// Check if we have stored payment info in session
if ((empty($oid) || empty($amt)) && isset($_SESSION['esewa_payment'])) {
    if (empty($oid)) $oid = $_SESSION['esewa_payment']['order_id'];
    if (empty($amt)) $amt = $_SESSION['esewa_payment']['amount'];
    
    // Clear the session data after use
    unset($_SESSION['esewa_payment']);
}

// Validate parameters
if (empty($oid) || empty($refId)) {
    error_log('eSewa Success Callback - Missing required parameters');
    flash('error', 'Invalid payment response. Missing required parameters.');
    redirect('account.php');
}

// If amount is missing, get it from the order
if (empty($amt)) {
    $order = getOrderById($oid);
    if ($order) {
        $amt = $order['total_amount'];
    }
}

// Log verification attempt
error_log("eSewa Payment Verification Attempt - Order ID: $oid, Amount: $amt, Ref ID: $refId");

// Verify payment with eSewa
$verified = verifyEsewaPayment($refId, $amt, $oid);

if ($verified) {
    // Get order details
    $order = getOrderById($oid);
    
    // Check if order exists and belongs to the current user
    if (!$order || $order['user_id'] != $_SESSION['user_id']) {
        flash('error', 'Order not found.');
        redirect('account.php');
    }
    
    // Update order payment status
    updateOrderPaymentStatus($oid, 'completed');
    
    // Record transaction
    recordTransaction($oid, 'esewa', $refId, $amt, 'completed');
    
    // Set success message
    flash('success', 'Payment successful! Your order has been confirmed.');
    
    // Redirect to order confirmation page
    redirect("order-confirmation.php?id=$oid");
} else {
    // Payment verification failed
    error_log('eSewa Payment Verification Failed - Order ID: ' . $oid . ', Ref ID: ' . $refId);
    flash('error', 'Payment verification failed. Please contact customer support.');
    redirect("order-details.php?id=$oid");
}
?> 