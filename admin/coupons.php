<?php
$pageTitle = "Manage Coupons";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Handle coupon deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $couponId = (int) sanitize($_GET['delete']);
    
    // Check if coupon exists
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE coupon_id = ?");
    $stmt->bind_param("i", $couponId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Delete coupon
        $stmt = $conn->prepare("DELETE FROM coupons WHERE coupon_id = ?");
        $stmt->bind_param("i", $couponId);
        
        if ($stmt->execute()) {
            flash('success', 'Coupon deleted successfully.');
        } else {
            flash('error', 'Failed to delete coupon.');
        }
    } else {
        flash('error', 'Coupon not found.');
    }
    
    redirect('admin/coupons.php');
}

// Handle coupon add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(sanitize($_POST['code']));
    $discount_type = sanitize($_POST['discount_type']);
    $discount_value = (float) sanitize($_POST['discount_value']);
    $min_purchase = !empty($_POST['min_purchase']) ? (float) sanitize($_POST['min_purchase']) : 0;
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status']);
    $parent_id = !empty($_POST['parent_id']) ? (int) sanitize($_POST['parent_id']) : null;
    
    // Only set meta values if the columns exist
    $meta_title = $metaColumnsExist ? sanitize($_POST['meta_title'] ?? '') : '';
    $meta_description = $metaColumnsExist ? sanitize($_POST['meta_description'] ?? '') : '';
    $meta_keywords = $metaColumnsExist ? sanitize($_POST['meta_keywords'] ?? '') : '';
    
    // Handle empty max_discount
    $max_discount = !empty($_POST['max_discount']) ? (float) sanitize($_POST['max_discount']) : null;
    $start_date = sanitize($_POST['start_date']);
    $end_date = sanitize($_POST['end_date']);
    $usage_limit = !empty($_POST['usage_limit']) ? (int) sanitize($_POST['usage_limit']) : null;
    
    // Validation
    $errors = [];
    
    if (empty($code)) {
        $errors[] = "Coupon code is required.";
    }
    
    if ($discount_type != 'percentage' && $discount_type != 'fixed') {
        $errors[] = "Invalid discount type.";
    }
    
    if ($discount_value <= 0) {
        $errors[] = "Discount value must be greater than zero.";
    }
    
    if ($discount_type == 'percentage' && $discount_value > 100) {
        $errors[] = "Percentage discount cannot exceed 100%.";
    }
    
    if ($min_purchase < 0) {
        $errors[] = "Minimum purchase amount cannot be negative.";
    }
    
    if (!empty($max_discount) && $max_discount <= 0) {
        $errors[] = "Maximum discount amount must be greater than zero.";
    }
    
    if (empty($start_date)) {
        $errors[] = "Start date is required.";
    }
    
    if (empty($end_date)) {
        $errors[] = "End date is required.";
    }
    
    if (strtotime($end_date) < strtotime($start_date)) {
        $errors[] = "End date must be after start date.";
    }
    
    if (!empty($usage_limit) && $usage_limit <= 0) {
        $errors[] = "Usage limit must be greater than zero.";
    }
    
    // Check if coupon code already exists
    $stmt = $conn->prepare("SELECT coupon_id FROM coupons WHERE code = ? AND coupon_id != ?");
    $couponId = isset($_POST['coupon_id']) ? (int) sanitize($_POST['coupon_id']) : 0;
    $stmt->bind_param("si", $code, $couponId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "A coupon with this code already exists.";
    }
    
    if (empty($errors)) {
        if (isset($_POST['coupon_id']) && !empty($_POST['coupon_id'])) {
            // Update existing coupon
            $couponId = (int) sanitize($_POST['coupon_id']);
            
            // Check if max_discount column exists
            $checkColumn = $conn->query("SHOW COLUMNS FROM coupons LIKE 'max_discount'");
            if ($checkColumn->num_rows > 0) {
                // Column exists, include it in the query
                $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, min_purchase = ?, max_discount = ?, start_date = ?, end_date = ?, usage_limit = ?, status = ?, updated_at = NOW() WHERE coupon_id = ?");
                $stmt->bind_param("ssddsssssi", $code, $discount_type, $discount_value, $min_purchase, $max_discount, $start_date, $end_date, $usage_limit, $status, $couponId);
            } else {
                // Column doesn't exist, exclude it from the query
                $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, min_purchase = ?, start_date = ?, end_date = ?, usage_limit = ?, status = ?, updated_at = NOW() WHERE coupon_id = ?");
                $stmt->bind_param("ssdsssssi", $code, $discount_type, $discount_value, $min_purchase, $start_date, $end_date, $usage_limit, $status, $couponId);
            }
            
            if ($stmt->execute()) {
                flash('success', 'Coupon updated successfully.');
                redirect('admin/coupons.php');
            } else {
                $errors[] = "Failed to update coupon.";
            }
        } else {
            // Add new coupon
            // Check if max_discount column exists
            $checkColumn = $conn->query("SHOW COLUMNS FROM coupons LIKE 'max_discount'");
            if ($checkColumn->num_rows > 0) {
                // Column exists, include it in the query
                $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_purchase, max_discount, start_date, end_date, usage_limit, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                // Fix the parameter binding by using the correct number of type specifiers
                $stmt->bind_param("ssddsssss", $code, $discount_type, $discount_value, $min_purchase, $max_discount, $start_date, $end_date, $usage_limit, $status);
            } else {
                // Column doesn't exist, exclude it from the query
                $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_purchase, start_date, end_date, usage_limit, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssdsssss", $code, $discount_type, $discount_value, $min_purchase, $start_date, $end_date, $usage_limit, $status);
            }
            
            if ($stmt->execute()) {
                flash('success', 'Coupon added successfully.');
                redirect('admin/coupons.php');
            } else {
                $errors[] = "Failed to add coupon: " . $conn->error;
            }
        }
    }
}

// Get coupon to edit if ID is provided
$couponToEdit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $couponId = (int) sanitize($_GET['edit']);
    
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE coupon_id = ?");
    $stmt->bind_param("i", $couponId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $couponToEdit = $result->fetch_assoc();
    } else {
        flash('error', 'Coupon not found.');
        redirect('admin/coupons.php');
    }
}

// Get all coupons
$stmt = $conn->prepare("SELECT c.*, 0 as usage_count FROM coupons c ORDER BY c.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$coupons = [];
while ($row = $result->fetch_assoc()) {
    $coupons[] = $row;
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
                <h1 class="h2"><?php echo isset($couponToEdit) ? 'Edit Coupon' : 'Manage Coupons'; ?></h1>
                <?php if (!isset($couponToEdit)): ?>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                        <i class="fas fa-plus"></i> Add New Coupon
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php flash(); ?>
            
            <?php if (isset($couponToEdit)): ?>
                <!-- Edit Coupon Form -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="POST" action="coupons.php">
                            <input type="hidden" name="coupon_id" value="<?php echo $couponToEdit['coupon_id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($couponToEdit['code']); ?>" required>
                                    <small class="form-text text-muted">Coupon code will be automatically converted to uppercase.</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo ($couponToEdit['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($couponToEdit['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="discount_type" name="discount_type" required>
                                        <option value="percentage" <?php echo ($couponToEdit['discount_type'] == 'percentage') ? 'selected' : ''; ?>>Percentage</option>
                                        <option value="fixed" <?php echo ($couponToEdit['discount_type'] == 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="discount_value" name="discount_value" step="0.01" min="0" value="<?php echo htmlspecialchars($couponToEdit['discount_value']); ?>" required>
                                        <span class="input-group-text discount-symbol"><?php echo ($couponToEdit['discount_type'] == 'percentage') ? '%' : '$'; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="min_purchase" class="form-label">Minimum Purchase Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="min_purchase" name="min_purchase" step="0.01" min="0" value="<?php echo htmlspecialchars($couponToEdit['min_purchase']); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="max_discount" class="form-label">Maximum Discount Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="max_discount" name="max_discount" step="0.01" min="0" value="<?php echo htmlspecialchars($couponToEdit['max_discount'] ?? ''); ?>">
                                    </div>
                                    <small class="form-text text-muted">Leave empty for no maximum.</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($couponToEdit['start_date']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($couponToEdit['end_date']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="usage_limit" class="form-label">Usage Limit</label>
                                <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" value="<?php echo htmlspecialchars($couponToEdit['usage_limit'] ?? ''); ?>">
                                <small class="form-text text-muted">Leave empty for unlimited usage.</small>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="coupons.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Coupon</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Coupons Table -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="couponsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Code</th>
                                        <th>Discount</th>
                                        <th>Min. Purchase</th>
                                        <th>Validity</th>
                                        <th>Usage</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($coupons)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No coupons found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($coupons as $coupon): ?>
                                        <tr>
                                            <td><?php echo $coupon['coupon_id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($coupon['code']); ?></strong></td>
                                            <td>
                                                <?php if ($coupon['discount_type'] == 'percentage'): ?>
                                                    <?php echo $coupon['discount_value']; ?>%
                                                    <?php if (!empty($coupon['max_discount'])): ?>
                                                        <br><small class="text-muted">Max: <?php echo formatPrice($coupon['max_discount']); ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php echo formatPrice($coupon['discount_value']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($coupon['min_purchase']) ? formatPrice($coupon['min_purchase']) : 'None'; ?></td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($coupon['start_date'])); ?> to 
                                                <?php echo date('M j, Y', strtotime($coupon['end_date'])); ?>
                                                <?php
                                                $today = date('Y-m-d');
                                                if ($today < $coupon['start_date']) {
                                                    echo '<br><span class="badge bg-info">Upcoming</span>';
                                                } elseif ($today > $coupon['end_date']) {
                                                    echo '<br><span class="badge bg-danger">Expired</span>';
                                                } else {
                                                    echo '<br><span class="badge bg-success">Active</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo $coupon['usage_count']; ?> uses
                                                <?php if (!empty($coupon['usage_limit'])): ?>
                                                    <br><small class="text-muted">Limit: <?php echo $coupon['usage_limit']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $coupon['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($coupon['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="coupons.php?edit=<?php echo $coupon['coupon_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="coupons.php?delete=<?php echo $coupon['coupon_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this coupon?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Add Coupon Modal -->
                <div class="modal fade" id="addCouponModal" tabindex="-1" aria-labelledby="addCouponModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addCouponModalLabel">Add New Coupon</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="coupons.php">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="code" name="code" required>
                                            <small class="form-text text-muted">Coupon code will be automatically converted to uppercase.</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="discount_type" name="discount_type" required>
                                                <option value="percentage">Percentage</option>
                                                <option value="fixed">Fixed Amount</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="discount_value" name="discount_value" step="0.01" min="0" required>
                                                <span class="input-group-text discount-symbol">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="min_purchase" class="form-label">Minimum Purchase Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="min_purchase" name="min_purchase" step="0.01" min="0">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="max_discount" class="form-label">Maximum Discount Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="max_discount" name="max_discount" step="0.01" min="0">
                                            </div>
                                            <small class="form-text text-muted">Leave empty for no maximum.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="usage_limit" class="form-label">Usage Limit</label>
                                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1">
                                        <small class="form-text text-muted">Leave empty for unlimited usage.</small>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Add Coupon</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#couponsTable').DataTable({
            order: [[0, 'desc']]
        });
    }
    
    // Update discount symbol based on discount type
    const discountTypeSelects = document.querySelectorAll('select[name="discount_type"]');
    discountTypeSelects.forEach(select => {
        select.addEventListener('change', function() {
            const discountSymbol = this.closest('.row').querySelector('.discount-symbol');
            if (this.value === 'percentage') {
                discountSymbol.textContent = '%';
            } else {
                discountSymbol.textContent = '$';
            }
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?> 