<?php
$pageTitle = "Initialize Settings";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Create settings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS settings (
    setting_id INT(11) NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(255) NOT NULL,
    setting_value TEXT,
    PRIMARY KEY (setting_id),
    UNIQUE KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conn->query($sql)) {
    die("Error creating settings table: " . $conn->error);
}

// Default settings
$defaultSettings = [
    'site_name' => 'ShopEase',
    'site_description' => 'Your one-stop shop for all your shopping needs, offering a wide range of products with excellent customer service.',
    'site_email' => 'contact@example.com',
    'site_phone' => '+1 (123) 456-7890',
    'site_address' => '123 Main Street, City, Country',
    'facebook_url' => 'https://facebook.com/',
    'twitter_url' => 'https://twitter.com/',
    'instagram_url' => 'https://instagram.com/',
    'youtube_url' => 'https://youtube.com/',
    'meta_title' => 'ShopEase - Your One-Stop Shop',
    'meta_description' => 'ShopEase is your one-stop shop for all your shopping needs, offering a wide range of products with excellent customer service.',
    'meta_keywords' => 'ecommerce, shop, online shopping, products',
    'currency_symbol' => '$',
    'currency_code' => 'USD',
    'tax_rate' => '5',
    'free_shipping_min' => '50',
    'default_shipping_fee' => '10'
];

// Insert or update default settings
$stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

$success = true;
foreach ($defaultSettings as $key => $value) {
    $stmt->bind_param("ss", $key, $value);
    if (!$stmt->execute()) {
        $success = false;
        echo "Error inserting setting $key: " . $conn->error . "<br>";
    }
}

if ($success) {
    flash('success', 'Settings initialized successfully.');
} else {
    flash('error', 'There was an error initializing some settings.');
}

redirect('settings.php');
?> 