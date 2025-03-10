<?php
$pageTitle = "Manage Orders";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['order_ids'])) {
    $action = sanitize($_POST['bulk_action']);
    $orderIds = $_POST['order_ids'];
    
    if (!empty($orderIds)) {
        switch ($action) {
            case 'processing':
            case 'shipped':
            case 'delivered':
            case 'cancelled':
                $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
                $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id IN ($placeholders)");
                $types = 's' . str_repeat('i', count($orderIds));
                $params = array_merge([$action], $orderIds);
                $stmt->bind_param($types, ...$params);
                
                if ($stmt->execute()) {
                    flash('success', count($orderIds) . ' orders updated to ' . ucfirst($action) . ' status.');
                } else {
                    flash('error', 'Failed to update orders.');
                }
                break;
        }
    }
    
    redirect('orders.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';

// Build query
$query = "SELECT o.*, u.username, u.email FROM orders o 
          LEFT JOIN users u ON o.user_id = u.user_id WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM orders o 
               LEFT JOIN users u ON o.user_id = u.user_id WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (o.order_id LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $countQuery .= " AND (o.order_id LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if (!empty($status)) {
    $query .= " AND o.order_status = ?";
    $countQuery .= " AND o.order_status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($dateFrom)) {
    $query .= " AND DATE(o.created_at) >= ?";
    $countQuery .= " AND DATE(o.created_at) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if (!empty($dateTo)) {
    $query .= " AND DATE(o.created_at) <= ?";
    $countQuery .= " AND DATE(o.created_at) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

// Order by
$query .= " ORDER BY o.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Get orders
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Get total orders for pagination
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    // Remove the last two parameters (offset and limit) for the count query
    array_pop($params);
    array_pop($params);
    $types = substr($types, 0, -2);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalOrders = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Orders</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="orders.php" class="btn btn-sm btn-outline-secondary">Refresh</a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();">Print</button>
                    </div>
                </div>
            </div>
            
            <?php flash(); ?>
            
            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="orders.php" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $status == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $status == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $status == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_from" placeholder="From Date" value="<?php echo htmlspecialchars($dateFrom); ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_to" placeholder="To Date" value="<?php echo htmlspecialchars($dateTo); ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="orders.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST" action="orders.php">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th width="10%">Order ID</th>
                                        <th width="15%">Customer</th>
                                        <th width="15%">Date</th>
                                        <th width="10%">Total</th>
                                        <th width="10%">Payment</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No orders found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="order_ids[]" value="<?php echo $order['order_id']; ?>" class="order-checkbox">
                                            </td>
                                            <td><?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($order['username']); ?><br>
                                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                                            </td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
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
                                                <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Status
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="update-order-status.php?id=<?php echo $order['order_id']; ?>&status=processing">Processing</a></li>
                                                        <li><a class="dropdown-item" href="update-order-status.php?id=<?php echo $order['order_id']; ?>&status=shipped">Shipped</a></li>
                                                        <li><a class="dropdown-item" href="update-order-status.php?id=<?php echo $order['order_id']; ?>&status=delivered">Delivered</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="update-order-status.php?id=<?php echo $order['order_id']; ?>&status=cancelled">Cancelled</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select name="bulk_action" class="form-select">
                                        <option value="">Bulk Actions</option>
                                        <option value="processing">Mark as Processing</option>
                                        <option value="shipped">Mark as Shipped</option>
                                        <option value="delivered">Mark as Delivered</option>
                                        <option value="cancelled">Mark as Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to perform this action?');">Apply</button>
                                </div>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="col-md-6">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">Previous</a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">Next</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Order Statistics -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
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
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo formatPrice($result->fetch_assoc()['total'] ?? 0);
                                        ?>
                                    </div>
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
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Delivered Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE order_status = 'delivered'");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    const selectAll = document.getElementById('select-all');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    
    selectAll.addEventListener('change', function() {
        orderCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
    
    // Update select all checkbox when individual checkboxes change
    orderCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(orderCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(orderCheckboxes).some(cb => cb.checked);
            
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?> 