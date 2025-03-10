<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

$issues = [];
$fixes = [];

// Check if weight column exists
$checkWeightColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'weight'");
if ($checkWeightColumn->num_rows == 0) {
    $issues[] = "Weight column is missing in the products table.";
    
    // Add weight column
    $alterTable = $conn->query("ALTER TABLE products ADD COLUMN weight DECIMAL(10, 2) AFTER sku");
    
    if ($alterTable) {
        $fixes[] = "Added weight column to products table.";
    } else {
        $issues[] = "Failed to add weight column to products table: " . $conn->error;
    }
}

// Check if dimensions column exists
$checkDimensionsColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'dimensions'");
if ($checkDimensionsColumn->num_rows == 0) {
    $issues[] = "Dimensions column is missing in the products table.";
    
    // Add dimensions column
    $alterTable = $conn->query("ALTER TABLE products ADD COLUMN dimensions VARCHAR(100) AFTER weight");
    
    if ($alterTable) {
        $fixes[] = "Added dimensions column to products table.";
    } else {
        $issues[] = "Failed to add dimensions column to products table: " . $conn->error;
    }
}

// Check if image column exists
$checkImageColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'image'");
if ($checkImageColumn->num_rows == 0) {
    $issues[] = "Image column is missing in the products table.";
    
    // Add image column
    $alterTable = $conn->query("ALTER TABLE products ADD COLUMN image VARCHAR(255) AFTER dimensions");
    
    if ($alterTable) {
        $fixes[] = "Added image column to products table.";
    } else {
        $issues[] = "Failed to add image column to products table: " . $conn->error;
    }
}

// Check if meta_title column exists
$checkMetaTitleColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'meta_title'");
if ($checkMetaTitleColumn->num_rows == 0) {
    $issues[] = "Meta title column is missing in the products table.";
    
    // Add meta_title column
    $alterTable = $conn->query("ALTER TABLE products ADD COLUMN meta_title VARCHAR(255) AFTER image");
    
    if ($alterTable) {
        $fixes[] = "Added meta_title column to products table.";
    } else {
        $issues[] = "Failed to add meta_title column to products table: " . $conn->error;
    }
}

// Check if meta_description column exists
$checkMetaDescColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'meta_description'");
if ($checkMetaDescColumn->num_rows == 0) {
    $issues[] = "Meta description column is missing in the products table.";
    
    // Add meta_description column
    $alterTable = $conn->query("ALTER TABLE products ADD COLUMN meta_description TEXT AFTER meta_title");
    
    if ($alterTable) {
        $fixes[] = "Added meta_description column to products table.";
    } else {
        $issues[] = "Failed to add meta_description column to products table: " . $conn->error;
    }
}

// Check if meta_keywords column exists
$checkMetaKeywordsColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'meta_keywords'");
if ($checkMetaKeywordsColumn->num_rows == 0) {
    $issues[] = "Meta keywords column is missing in the products table.";
    
    // Add meta_keywords column
    $alterTable = $conn->query("ALTER TABLE products ADD COLUMN meta_keywords VARCHAR(255) AFTER meta_description");
    
    if ($alterTable) {
        $fixes[] = "Added meta_keywords column to products table.";
    } else {
        $issues[] = "Failed to add meta_keywords column to products table: " . $conn->error;
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
                <h1 class="h2">Update Products Table</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="products.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>
            
            <?php flash(); ?>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Diagnostic Results</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($issues) && empty($fixes)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> All product table columns are present and correct.
                        </div>
                    <?php else: ?>
                        <?php if (!empty($issues)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> Issues Found:</h5>
                                <ul>
                                    <?php foreach ($issues as $issue): ?>
                                        <li><?php echo $issue; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($fixes)): ?>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> Fixes Applied:</h5>
                                <ul>
                                    <?php foreach ($fixes as $fix): ?>
                                        <li><?php echo $fix; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <h5>Next Steps:</h5>
                        <ol>
                            <li>Go back to the <a href="products.php">Products Management</a> page and try adding a product again.</li>
                            <li>If issues persist, please check the server error logs for more information.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?> 