<?php
$pageTitle = "My Orders";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to view this page.');
    redirect('login.php');
}

// Get user orders
$orders = getUserOrders($_SESSION['user_id']);

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
                <h2 class="account-title">My Orders</h2>
                
                <?php if (empty($orders)): ?>
                <div class="alert alert-info">
                    You haven't placed any orders yet. <a href="<?php echo URL_ROOT; ?>/shop.php">Start shopping</a>.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table orders-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td data-label="Order #"><?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></td>
                                <td data-label="Date"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td data-label="Status">
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
                                </td>
                                <td data-label="Total"><?php echo formatPrice($order['total_amount']); ?></td>
                                <td data-label="Actions">
                                    <a href="<?php echo URL_ROOT; ?>/order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Add custom CSS for orders page
$extraCSS = '
<style>
@media (max-width: 767.98px) {
    .orders-table thead {
        display: none;
    }
    
    .orders-table tbody tr {
        display: block;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--light-gray-color);
        padding-bottom: 20px;
    }
    
    .orders-table tbody td {
        display: block;
        text-align: right;
        padding: 5px 0;
    }
    
    .orders-table tbody td::before {
        content: attr(data-label);
        float: left;
        font-weight: 600;
        color: var(--secondary-color);
    }
}
</style>
';

include 'includes/footer.php';
?> 