<?php
$pageTitle = "Initialize Content Tables";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Create testimonials table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `position` varchar(100) NOT NULL,
    `content` text NOT NULL,
    `image` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    flash('success', 'Testimonials table created successfully.');
} else {
    flash('error', 'Error creating testimonials table: ' . $conn->error);
}

// Create blog_posts table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `excerpt` text NOT NULL,
    `content` text NOT NULL,
    `image` varchar(255) DEFAULT NULL,
    `publish_date` date NOT NULL,
    `status` enum('published','draft') NOT NULL DEFAULT 'published',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    flash('success', 'Blog posts table created successfully.');
} else {
    flash('error', 'Error creating blog posts table: ' . $conn->error);
}

// Create brands table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `brands` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `image` varchar(255) NOT NULL,
    `url` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `display_order` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    flash('success', 'Brands table created successfully.');
} else {
    flash('error', 'Error creating brands table: ' . $conn->error);
}

// Create upload directories if they don't exist
$directories = [
    'uploads/testimonials',
    'uploads/blog',
    'uploads/brands'
];

foreach ($directories as $dir) {
    $fullPath = dirname(__DIR__) . '/' . $dir;
    if (!file_exists($fullPath)) {
        if (mkdir($fullPath, 0777, true)) {
            flash('success', "Directory {$dir} created successfully.");
        } else {
            flash('error', "Failed to create directory {$dir}.");
        }
    } else {
        flash('info', "Directory {$dir} already exists.");
    }
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Initialize Content Tables</h1>
            </div>
            
            <?php flash(); ?>
            
            <div class="alert alert-success">
                <h4 class="alert-heading">Setup Complete!</h4>
                <p>All content tables have been initialized successfully. You can now start managing your content.</p>
                <hr>
                <p class="mb-0">Use the links below to manage your content:</p>
                <ul class="mt-2">
                    <li><a href="testimonials.php">Manage Testimonials</a></li>
                    <li><a href="blog-posts.php">Manage Blog Posts</a></li>
                    <li><a href="brands.php">Manage Brands</a></li>
                </ul>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?> 