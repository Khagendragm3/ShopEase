<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// First, update any empty SKUs to NULL
$conn->query("UPDATE products SET sku = NULL WHERE sku = ''");

// Then, modify the table structure to allow NULL values for SKU
$conn->query("ALTER TABLE products MODIFY sku VARCHAR(50) UNIQUE NULL");

// Check if the operation was successful
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'sku'");
$column = $result->fetch_assoc();
$isNullable = ($column['Null'] === 'YES');

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Fix Products SKU</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="products.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>
            
            <?php flash(); ?>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Database Update Results</h6>
                </div>
                <div class="card-body">
                    <?php if ($isNullable): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> The SKU field has been successfully updated to allow NULL values.
                            <br>Empty SKU values have been converted to NULL.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Failed to update the SKU field. Please check the database manually.
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <h5>Next Steps:</h5>
                        <ol>
                            <li>Go back to the <a href="products.php">Products Management</a> page and try adding or editing products again.</li>
                            <li>If issues persist, please check the server error logs for more information.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?> 