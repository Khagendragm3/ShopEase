<?php
$pageTitle = "User Details";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    flash('error', 'User ID is required.');
    redirect('admin/users.php');
}

$userId = (int) sanitize($_GET['id']);

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    flash('error', 'User not found.');
    redirect('admin/users.php');
}

$user = $result->fetch_assoc();

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$ordersResult = $stmt->get_result();
$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orders[] = $row;
}

// Get user reviews
$stmt = $conn->prepare("SELECT r.*, p.name as product_name FROM reviews r 
                        JOIN products p ON r.product_id = p.product_id 
                        WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$reviewsResult = $stmt->get_result();
$reviews = [];
while ($row = $reviewsResult->fetch_assoc()) {
    $reviews[] = $row;
}

// Get user wishlist
$stmt = $conn->prepare("SELECT w.*, p.name as product_name, p.price FROM wishlist w 
                        JOIN products p ON w.product_id = p.product_id 
                        WHERE w.user_id = ? ORDER BY w.created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$wishlistResult = $stmt->get_result();
$wishlist = [];
while ($row = $wishlistResult->fetch_assoc()) {
    $wishlist[] = $row;
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
                <h1 class="h2">User Details</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="edit-user.php?id=<?php echo $userId; ?>" class="btn btn-sm btn-primary me-2">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    <a href="admin/users.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
            
            <?php flash(); ?>
            
            <!-- User Information -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">User ID</th>
                                        <td><?php echo $user['user_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Full Name</th>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-<?php echo isset($user['status']) && $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($user['status'] ?? 'inactive'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Registered On</th>
                                        <td><?php echo date('F j, Y, g:i a', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Phone</th>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Address</th>
                                        <td><?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>City</th>
                                        <td><?php echo htmlspecialchars($user['city'] ?? 'Not provided'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>State/Province</th>
                                        <td><?php echo htmlspecialchars($user['state'] ?? 'Not provided'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>ZIP/Postal Code</th>
                                        <td><?php echo htmlspecialchars($user['zip_code'] ?? 'Not provided'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Country</th>
                                        <td><?php echo htmlspecialchars($user['country'] ?? 'Not provided'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User Orders -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order History</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                    <p class="text-center">No orders found for this user.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_id']; ?></td>
                                    <td><?php echo date('F j, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['payment_status'] == 'completed' ? 'success' : 
                                                ($order['payment_status'] == 'pending' ? 'warning' : 
                                                    ($order['payment_status'] == 'failed' ? 'danger' : 'info')); 
                                        ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['order_status'] == 'delivered' ? 'success' : 
                                                ($order['order_status'] == 'pending' ? 'warning' : 
                                                    ($order['order_status'] == 'cancelled' ? 'danger' : 'info')); 
                                        ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- User Reviews -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Product Reviews</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                    <p class="text-center">No reviews found for this user.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? ' text-warning' : '-o text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($review['comment']); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($review['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $review['status'] == 'approved' ? 'success' : 
                                                ($review['status'] == 'pending' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($review['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- User Wishlist -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Wishlist</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($wishlist)): ?>
                    <p class="text-center">No wishlist items found for this user.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Added On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($wishlist as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($item['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?> 