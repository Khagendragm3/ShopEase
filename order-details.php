<?php
$pageTitle = "Order Details";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to view this page.');
    redirect('login.php');
}

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get order details
$order = getOrderById($orderId);

// Check if order exists and belongs to the current user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

// Get transaction details if available
$transaction = null;
if ($order['payment_method'] == 'esewa') {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $transaction = $result->fetch_assoc();
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <!-- Account Sidebar -->
        <div class="col-lg-3">
            <div class="account-sidebar">
                <h3>My Account</h3>
                <ul class="account-menu">
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/account.php" class="account-menu-link">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/orders.php" class="account-menu-link active">
                            <i class="fas fa-shopping-bag"></i> Orders
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/wishlist.php" class="account-menu-link">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/change-password.php" class="account-menu-link">
                            <i class="fas fa-lock"></i> Change Password
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/logout.php" class="account-menu-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Account Content -->
        <div class="col-lg-9">
            <div class="account-content">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="account-title">Order #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></h2>
                    <a href="<?php echo URL_ROOT; ?>/orders.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
                
                <div class="order-info mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="order-info-box">
                                <h4>Order Information</h4>
                                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                <p><strong>Order Status:</strong> <span class="badge bg-<?php 
                                    switch ($order['order_status']) {
                                        case 'pending':
                                            echo 'warning';
                                            break;
                                        case 'processing':
                                            echo 'info';
                                            break;
                                        case 'shipped':
                                            echo 'primary';
                                            break;
                                        case 'delivered':
                                            echo 'success';
                                            break;
                                        case 'cancelled':
                                            echo 'danger';
                                            break;
                                        default:
                                            echo 'secondary';
                                    }
                                ?>"><?php echo ucfirst($order['order_status']); ?></span></p>
                                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                <p><strong>Payment Status:</strong> <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></p>
                                
                                <?php if ($order['payment_method'] == 'esewa' && $transaction): ?>
                                <p><strong>eSewa Reference:</strong> <?php echo $transaction['transaction_reference']; ?></p>
                                <p><strong>Transaction Date:</strong> <?php echo date('F j, Y H:i:s', strtotime($transaction['created_at'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($order['tracking_number']): ?>
                                <p><strong>Tracking Number:</strong> <?php echo $order['tracking_number']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="order-info-box">
                                <h4>Shipping Address</h4>
                                <p><?php echo nl2br($order['shipping_address']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="order-items mt-4">
                    <h4>Order Items</h4>
                    
                    <?php if ($order['payment_status'] == 'pending' || $order['payment_status'] == 'failed'): ?>
                    <div class="alert alert-warning mb-3">
                        <p class="mb-2"><strong>Payment Pending</strong></p>
                        <p class="mb-3">Your order has been placed but payment is still pending. You can complete your payment using one of the options below:</p>
                        
                        <div class="payment-options">
                            <a href="process_esewa_payment.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-success mb-2">
                                <img src="assets/images/esewa-logo.svg" alt="eSewa" style="height: 20px; margin-right: 5px;"> Pay with eSewa
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td data-label="Product">
                                        <div class="order-product">
                                            <div class="order-product-image">
                                                <img src="<?php echo URL_ROOT; ?>/<?php echo $item['image'] ? $item['image'] : 'assets/images/product-placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                                            </div>
                                            <div class="order-product-info">
                                                <h5><a href="<?php echo URL_ROOT; ?>/product.php?id=<?php echo $item['product_id']; ?>"><?php echo $item['name']; ?></a></h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Price"><?php echo formatPrice($item['price']); ?></td>
                                    <td data-label="Quantity"><?php echo $item['quantity']; ?></td>
                                    <td data-label="Total"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td>Included</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <?php if ($order['order_status'] == 'delivered'): ?>
                <div class="order-actions mt-4">
                    <a href="<?php echo URL_ROOT; ?>/invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-invoice me-1"></i> Download Invoice
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Add custom CSS for order details page
$extraCSS = '
<style>
.order-info-box {
    background-color: var(--light-color);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.order-info-box h4 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--secondary-color);
}

.order-product {
    display: flex;
    align-items: center;
}

.order-product-image {
    width: 60px;
    height: 60px;
    border-radius: 4px;
    overflow: hidden;
    margin-right: 15px;
}

.order-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-product-info h5 {
    font-size: 0.9rem;
    margin-bottom: 0;
}

@media (max-width: 767.98px) {
    table thead {
        display: none;
    }
    
    table tbody tr {
        display: block;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--light-gray-color);
        padding-bottom: 20px;
    }
    
    table tbody td {
        display: block;
        text-align: right;
        padding: 5px 0;
    }
    
    table tbody td::before {
        content: attr(data-label);
        float: left;
        font-weight: 600;
        color: var(--secondary-color);
    }
    
    table tfoot tr {
        display: block;
        text-align: right;
    }
    
    table tfoot td {
        display: block;
        padding: 5px 0;
    }
}
</style>
';

include 'includes/footer.php';
?> 