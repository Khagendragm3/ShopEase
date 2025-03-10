<?php
$pageTitle = "Order Confirmation";
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
    redirect('account.php');
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12 text-center">
            <div class="order-confirmation">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Thank You for Your Order!</h1>
                <p class="lead">Your order has been placed successfully.</p>
                <div class="order-number">
                    <p>Order Number: <strong>#<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></strong></p>
                </div>
                <p>A confirmation email has been sent to <strong><?php echo $order['email']; ?></strong></p>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
            <div class="order-details">
                <h2>Order Details</h2>
                
                <div class="order-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Order Information</h4>
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            <p><strong>Order Status:</strong> <span class="badge bg-primary"><?php echo ucfirst($order['order_status']); ?></span></p>
                            <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                            <p><strong>Payment Status:</strong> <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <h4>Shipping Address</h4>
                            <p><?php echo nl2br($order['shipping_address']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="order-items mt-4">
                    <h4>Order Items</h4>
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
                                    <td>
                                        <div class="order-product">
                                            <div class="order-product-image">
                                                <img src="<?php echo URL_ROOT; ?>/<?php echo $item['image'] ? $item['image'] : 'assets/images/product-placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                                            </div>
                                            <div class="order-product-info">
                                                <h5><?php echo $item['name']; ?></h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
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
            </div>
            
            <div class="order-actions mt-4 text-center">
                <a href="<?php echo URL_ROOT; ?>/orders.php" class="btn btn-outline-primary me-2">View All Orders</a>
                <a href="<?php echo URL_ROOT; ?>/shop.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php
// Add custom CSS for order confirmation page
$extraCSS = '
<style>
.order-confirmation {
    background-color: white;
    border-radius: 8px;
    padding: 40px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.confirmation-icon {
    font-size: 5rem;
    color: var(--success-color);
    margin-bottom: 20px;
}

.order-number {
    background-color: var(--light-color);
    padding: 10px 20px;
    border-radius: 4px;
    display: inline-block;
    margin: 20px 0;
}

.order-details {
    background-color: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.order-details h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--light-gray-color);
    color: var(--secondary-color);
}

.order-info {
    margin-bottom: 30px;
}

.order-info h4 {
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
</style>
';

include 'includes/footer.php';
?> 