<?php
$pageTitle = "Manage Products";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Handle product deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $productId = sanitize($_GET['delete']);
    
    // Check if product exists
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        
        if ($stmt->execute()) {
            flash('success', 'Product deleted successfully.');
        } else {
            flash('error', 'Failed to delete product.');
        }
    } else {
        flash('error', 'Product not found.');
    }
    
    redirect('admin/products.php');
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['product_ids'])) {
    $action = sanitize($_POST['bulk_action']);
    $productIds = $_POST['product_ids'];
    
    if (!empty($productIds)) {
        switch ($action) {
            case 'delete':
                $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
                $stmt = $conn->prepare("DELETE FROM products WHERE product_id IN ($placeholders)");
                $types = str_repeat('i', count($productIds));
                $stmt->bind_param($types, ...$productIds);
                
                if ($stmt->execute()) {
                    flash('success', count($productIds) . ' products deleted successfully.');
                } else {
                    flash('error', 'Failed to delete products.');
                }
                break;
                
            case 'activate':
                $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
                $stmt = $conn->prepare("UPDATE products SET status = 'active' WHERE product_id IN ($placeholders)");
                $types = str_repeat('i', count($productIds));
                $stmt->bind_param($types, ...$productIds);
                
                if ($stmt->execute()) {
                    flash('success', count($productIds) . ' products activated successfully.');
                } else {
                    flash('error', 'Failed to activate products.');
                }
                break;
                
            case 'deactivate':
                $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
                $stmt = $conn->prepare("UPDATE products SET status = 'inactive' WHERE product_id IN ($placeholders)");
                $types = str_repeat('i', count($productIds));
                $stmt->bind_param($types, ...$productIds);
                
                if ($stmt->execute()) {
                    flash('success', count($productIds) . ' products deactivated successfully.');
                } else {
                    flash('error', 'Failed to deactivate products.');
                }
                break;
        }
    }
    
    redirect('admin/products.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM products p 
               LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $countQuery .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($category)) {
    $query .= " AND p.category_id = ?";
    $countQuery .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

if (!empty($status)) {
    $query .= " AND p.status = ?";
    $countQuery .= " AND p.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Order by
$query .= " ORDER BY p.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Get products
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get total products for pagination
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
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Get all categories for filter dropdown
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name ASC");
$stmt->execute();
$result = $stmt->get_result();
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
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
                <h1 class="h2">Manage Products</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="fix-products-sku.php" class="btn btn-sm btn-warning me-2">
                        <i class="fas fa-wrench"></i> Fix SKU Issues
                    </a>
                    <a href="add-product.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
            </div>
            
            <?php flash(); ?>
            
            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="products.php" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="products.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST" action="products.php">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th width="5%">ID</th>
                                        <th width="15%">Image</th>
                                        <th width="20%">Name</th>
                                        <th width="10%">Category</th>
                                        <th width="10%">Price</th>
                                        <th width="10%">Stock</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No products found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="product_ids[]" value="<?php echo $product['product_id']; ?>" class="product-checkbox">
                                            </td>
                                            <td><?php echo $product['product_id']; ?></td>
                                            <td>
                                                <?php if (!empty($product['image'])): ?>
                                                <img src="<?php echo getImageUrl($product['image'], 'uploads/products'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-thumbnail" style="max-height: 80px;">
                                                <?php else: ?>
                                                <img src="<?php echo URL_ROOT; ?>/assets/img/no-image.jpg" alt="No Image" class="img-thumbnail" style="max-height: 80px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                            <td><?php echo formatPrice($product['price']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['quantity'] <= 5 ? ($product['quantity'] <= 0 ? 'danger' : 'warning') : 'success'; ?>">
                                                    <?php echo $product['quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($product['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit-product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="products.php?delete=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
                                        <option value="delete">Delete</option>
                                        <option value="activate">Activate</option>
                                        <option value="deactivate">Deactivate</option>
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>">Previous</a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>">Next</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    const selectAll = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    
    selectAll.addEventListener('change', function() {
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
    
    // Update select all checkbox when individual checkboxes change
    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(productCheckboxes).some(cb => cb.checked);
            
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?> 