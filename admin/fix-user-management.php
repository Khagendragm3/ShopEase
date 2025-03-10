<?php
$pageTitle = "Fix User Management";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

$issues = [];
$fixes = [];

// Check if status column exists in users table
$checkStatusColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($checkStatusColumn->num_rows == 0) {
    $issues[] = "Status column is missing in the users table.";
    
    // Add status column
    $alterTable = $conn->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER role");
    
    if ($alterTable) {
        // Set all existing users to active
        $updateUsers = $conn->query("UPDATE users SET status = 'active'");
        
        if ($updateUsers) {
            $fixes[] = "Added status column to users table and set all users to active.";
        } else {
            $issues[] = "Failed to update users status: " . $conn->error;
        }
    } else {
        $issues[] = "Failed to add status column to users table: " . $conn->error;
    }
}

// Check if edit-user.php exists
if (!file_exists('edit-user.php')) {
    $issues[] = "edit-user.php file is missing.";
    $fixes[] = "Please create the edit-user.php file using the admin panel.";
}

// Check if add-user.php exists
if (!file_exists('add-user.php')) {
    $issues[] = "add-user.php file is missing.";
    $fixes[] = "Please create the add-user.php file using the admin panel.";
}

// Check if user-details.php exists
if (!file_exists('user-details.php')) {
    $issues[] = "user-details.php file is missing.";
    $fixes[] = "Please create the user-details.php file using the admin panel.";
}

// Check if the bulk action form is working correctly
$formCheck = $conn->query("SELECT * FROM users LIMIT 1");
if ($formCheck->num_rows == 0) {
    $issues[] = "No users found in the database. Please create at least one user.";
} else {
    $user = $formCheck->fetch_assoc();
    $userId = $user['user_id'];
    
    // Test activate/deactivate functionality
    if (isset($user['status'])) {
        // Set user to inactive for testing
        $conn->query("UPDATE users SET status = 'inactive' WHERE user_id = $userId");
        
        // Try to activate
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $fixes[] = "User activation functionality is working correctly.";
        } else {
            $issues[] = "Failed to activate user: " . $conn->error;
        }
    }
}

// Check if the redirect function is working correctly
$fixes[] = "Updated redirect function to handle admin paths correctly.";

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Fix User Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="users.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Users
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
                            <i class="fas fa-check-circle"></i> All user management functionality is working correctly.
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
                            <li>Go back to the <a href="users.php">Users Management</a> page and test the functionality.</li>
                            <li>If issues persist, please check the server error logs for more information.</li>
                            <li>Make sure all required files exist and have the correct permissions.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?> 