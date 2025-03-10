<?php
$pageTitle = "Order Details";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    flash('error', 'Order ID is required.');
    redirect('orders.php');
}

$orderId = (int) sanitize($_GET['id']);

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.username, u.email, u.first_name, u.last_name, u.phone 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.user_id 
                        WHERE o.order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

$order = $result->fetch_assoc();

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image 
                        FROM order_items oi 
                        LEFT JOIN products p ON oi.product_id = p.product_id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$orderItems = [];
while ($row = $result->fetch_assoc()) {
    $orderItems[] = $row;
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitize($_POST['order_status']);
    $notes = sanitize($_POST['admin_notes']);
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ?, admin_notes = ?, updated_at = NOW() WHERE order_id = ?");
    $stmt->bind_param("ssi", $newStatus, $notes, $orderId);
    
    if ($stmt->execute()) {
        // Send email notification to customer about status change
        $emailSent = false;
        
        // Update order status in session
        $order['order_status'] = $newStatus;
        $order['admin_notes'] = $notes;
        
        flash('success', 'Order status updated successfully.');
    } else {
        flash('error', 'Failed to update order status.');
    }
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Order #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="orders.php" class="btn btn-sm btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            
            <?php flash(); ?>
            
            <div class="row mb-4">
                <!-- Order Information -->
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Order Information</h6>
                            <span class="badge bg-<?php 
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
                            ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Order ID:</th>
                                    <td><?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></td>
                                </tr>
                                <tr>
                                    <th>Order Date:</th>
                                    <td><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td><?php echo ucfirst($order['payment_method']); ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch ($order['payment_status']) {
                                                case 'pending':
                                                    echo 'warning';
                                                    break;
                                                case 'completed':
                                                    echo 'success';
                                                    break;
                                                case 'failed':
                                                    echo 'danger';
                                                    break;
                                                default:
                                                    echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Transaction ID:</th>
                                    <td><?php echo !empty($order['transaction_id']) ? $order['transaction_id'] : 'N/A'; ?></td>
                                </tr>
                                <?php if (!empty($order['coupon_code'])): ?>
                                <tr>
                                    <th>Coupon Code:</th>
                                    <td><?php echo $order['coupon_code']; ?></td>
                                </tr>
                                <tr>
                                    <th>Discount:</th>
                                    <td><?php echo formatPrice($order['discount_amount']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Information -->
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Name:</th>
                                    <td>
                                        <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                        <a href="users.php?id=<?php echo $order['user_id']; ?>" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-user"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Username:</th>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <!-- Shipping Address -->
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Shipping Address</h6>
                        </div>
                        <div class="card-body">
                            <address>
                                <?php echo htmlspecialchars($order['shipping_name']); ?><br>
                                <?php echo htmlspecialchars($order['shipping_address_line1']); ?><br>
                                <?php if (!empty($order['shipping_address_line2'])): ?>
                                <?php echo htmlspecialchars($order['shipping_address_line2']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_postal_code']); ?><br>
                                <?php echo htmlspecialchars($order['shipping_country']); ?><br>
                                <?php if (!empty($order['shipping_phone'])): ?>
                                Phone: <?php echo htmlspecialchars($order['shipping_phone']); ?>
                                <?php endif; ?>
                            </address>
                        </div>
                    </div>
                </div>
                
                <!-- Billing Address -->
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Billing Address</h6>
                        </div>
                        <div class="card-body">
                            <address>
                                <?php echo htmlspecialchars($order['billing_name']); ?><br>
                                <?php echo htmlspecialchars($order['billing_address_line1']); ?><br>
                                <?php if (!empty($order['billing_address_line2'])): ?>
                                <?php echo htmlspecialchars($order['billing_address_line2']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($order['billing_city'] . ', ' . $order['billing_state'] . ' ' . $order['billing_postal_code']); ?><br>
                                <?php echo htmlspecialchars($order['billing_country']); ?><br>
                                <?php if (!empty($order['billing_phone'])): ?>
                                Phone: <?php echo htmlspecialchars($order['billing_phone']); ?>
                                <?php endif; ?>
                            </address>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="10%">Image</th>
                                    <th width="40%">Product</th>
                                    <th width="15%">Price</th>
                                    <th width="10%">Quantity</th>
                                    <th width="15%">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['image'])): ?>
                                        <img src="<?php echo URL_ROOT . '/uploads/products/' . $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-thumbnail" style="max-height: 50px;">
                                        <?php else: ?>
                                        <img src="<?php echo URL_ROOT; ?>/assets/img/no-image.jpg" alt="No Image" class="img-thumbnail" style="max-height: 50px;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($item['name']); ?>
                                        <a href="../product.php?id=<?php echo $item['product_id']; ?>" class="btn btn-sm btn-outline-info ms-2" target="_blank">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><?php echo formatPrice($order['subtotal']); ?></td>
                                </tr>
                                <?php if (!empty($order['discount_amount'])): ?>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                                    <td>-<?php echo formatPrice($order['discount_amount']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                    <td><?php echo formatPrice($order['shipping_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                                    <td><?php echo formatPrice($order['tax_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Order Notes and Status Update -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Order Notes</h6>
                        </div>
                        <div class="card-body">
                            <h6>Customer Notes:</h6>
                            <p><?php echo !empty($order['customer_notes']) ? htmlspecialchars($order['customer_notes']) : 'No notes from customer.'; ?></p>
                            
                            <h6 class="mt-4">Admin Notes:</h6>
                            <p><?php echo !empty($order['admin_notes']) ? htmlspecialchars($order['admin_notes']) : 'No admin notes.'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Update Order Status</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="order-details.php?id=<?php echo $orderId; ?>">
                                <div class="mb-3">
                                    <label for="order_status" class="form-label">Order Status</label>
                                    <select class="form-select" id="order_status" name="order_status">
                                        <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="admin_notes" class="form-label">Admin Notes</label>
                                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4"><?php echo htmlspecialchars($order['admin_notes'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?> 