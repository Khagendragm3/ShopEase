<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
    exit;
}

// Check if the max_discount column already exists
$checkColumn = $conn->query("SHOW COLUMNS FROM coupons LIKE 'max_discount'");
if ($checkColumn->num_rows == 0) {
    // Column doesn't exist, add it
    $alterTable = $conn->query("ALTER TABLE coupons ADD COLUMN max_discount DECIMAL(10, 2) NULL AFTER min_purchase");
    
    if ($alterTable) {
        flash('success', 'The max_discount column has been added to the coupons table.');
    } else {
        flash('error', 'Failed to add the max_discount column: ' . $conn->error);
    }
} else {
    flash('info', 'The max_discount column already exists in the coupons table.');
}

// Redirect back to coupons page
redirect('coupons.php'); 