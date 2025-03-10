<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Check if status column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($checkColumn->num_rows == 0) {
    // Status column doesn't exist, add it
    $alterTable = $conn->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER role");
    
    if ($alterTable) {
        // Set all existing users to active
        $updateUsers = $conn->query("UPDATE users SET status = 'active'");
        
        if ($updateUsers) {
            flash('success', 'Users table updated successfully. Status column added and all users set to active.');
        } else {
            flash('error', 'Failed to update users status: ' . $conn->error);
        }
    } else {
        flash('error', 'Failed to add status column to users table: ' . $conn->error);
    }
} else {
    flash('info', 'Status column already exists in users table.');
}

redirect('admin/users.php');
?> 