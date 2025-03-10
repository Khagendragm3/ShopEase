<?php
require_once '../includes/config.php';

// Check if meta_title column exists
$result = $conn->query("SHOW COLUMNS FROM categories LIKE 'meta_title'");
if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $conn->query("ALTER TABLE categories ADD COLUMN meta_title VARCHAR(255) AFTER image");
    echo "Added meta_title column.<br>";
}

// Check if meta_description column exists
$result = $conn->query("SHOW COLUMNS FROM categories LIKE 'meta_description'");
if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $conn->query("ALTER TABLE categories ADD COLUMN meta_description TEXT AFTER meta_title");
    echo "Added meta_description column.<br>";
}

// Check if meta_keywords column exists
$result = $conn->query("SHOW COLUMNS FROM categories LIKE 'meta_keywords'");
if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $conn->query("ALTER TABLE categories ADD COLUMN meta_keywords VARCHAR(255) AFTER meta_description");
    echo "Added meta_keywords column.<br>";
}

echo "Categories table update completed!";
?> 