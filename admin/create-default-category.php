<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Check if categories exist
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM categories");
$stmt->execute();
$result = $stmt->get_result();
$categoryCount = $result->fetch_assoc()['count'];

if ($categoryCount == 0) {
    // No categories exist, create a default one
    $stmt = $conn->prepare("INSERT INTO categories (name, description, status) VALUES (?, ?, ?)");
    $name = "Uncategorized";
    $description = "Default category for products";
    $status = "active";
    $stmt->bind_param("sss", $name, $description, $status);
    
    if ($stmt->execute()) {
        flash('success', 'Default category "Uncategorized" created successfully.');
    } else {
        flash('error', 'Failed to create default category: ' . $conn->error);
    }
} else {
    flash('info', 'Categories already exist. No default category was created.');
}

redirect('admin/categories.php');
?> 