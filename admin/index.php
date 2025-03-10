<?php
$pageTitle = "Admin Dashboard";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Get statistics for dashboard
// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$result = $stmt->get_result();
$totalUsers = $result->fetch_assoc()['total'];

// Total products
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
$stmt->execute();
$result = $stmt->get_result();
$totalProducts = $result->fetch_assoc()['total'];

// Total orders
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$result = $stmt->get_result();
$totalOrders = $result->fetch_assoc()['total'];

// Total revenue
$stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'");
$stmt->execute();
$result = $stmt->get_result();
$totalRevenue = $result->fetch_assoc()['total'] ?? 0;

// Recent orders
$stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.created_at DESC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
$recentOrders = [];
while ($row = $result->fetch_assoc()) {
    $recentOrders[] = $row;
}

// Low stock products
$stmt = $conn->prepare("SELECT * FROM products WHERE quantity <= 5 AND status = 'active' ORDER BY quantity ASC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
$lowStockProducts = [];
while ($row = $result->fetch_assoc()) {
    $lowStockProducts[] = $row;
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
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <i class="fas fa-calendar"></i> This week
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalUsers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatPrice($totalRevenue); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Products</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalProducts; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-box fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalOrders; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders and Low Stock Products -->
            <div class="row">
                <!-- Recent Orders -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                            <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentOrders)): ?>
                            <p class="text-center">No orders found.</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo $order['username']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
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
                                            <td>
                                                <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">View</a>
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
                
                <!-- Low Stock Products -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Low Stock Products</h6>
                            <a href="products.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($lowStockProducts)): ?>
                            <p class="text-center">No low stock products found.</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Stock</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lowStockProducts as $product): ?>
                                        <tr>
                                            <td><?php echo $product['name']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['quantity'] <= 0 ? 'danger' : 'warning'; ?>">
                                                    <?php echo $product['quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit-product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
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
            
            <!-- System Utilities -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">System Utilities</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Content Management Setup</h5>
                                            <p class="card-text">Initialize database tables for testimonials, blog posts, and brands.</p>
                                            <a href="initialize-content-tables.php" class="btn btn-primary">Initialize Content System</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?> 