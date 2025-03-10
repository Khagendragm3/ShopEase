<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <!-- Meta tags -->
    <meta name="description" content="<?php echo getSetting('meta_description', 'E-commerce website for all your shopping needs'); ?>">
    <meta name="keywords" content="<?php echo getSetting('meta_keywords', 'ecommerce, shop, online shopping'); ?>">
    <meta name="base-url" content="<?php echo URL_ROOT; ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
    <!-- Favicon -->
    <?php if(!empty(getSetting('site_favicon'))): ?>
    <link rel="icon" href="<?php echo URL_ROOT; ?>/uploads/settings/<?php echo getSetting('site_favicon'); ?>" type="image/x-icon">
    <?php else: ?>
    <link rel="icon" href="<?php echo URL_ROOT; ?>/assets/images/favicon.ico" type="image/x-icon">
    <?php endif; ?>
    <?php if(isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
    <!-- Header -->
    <header>
        <!-- Top Bar -->
        <div class="top-bar bg-dark text-white py-2">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <?php if(!empty(getSetting('site_email'))): ?>
                        <span><i class="fas fa-envelope me-2"></i><?php echo getSetting('site_email'); ?></span>
                        <?php endif; ?>
                        <?php if(!empty(getSetting('site_phone'))): ?>
                        <span class="ms-3"><i class="fas fa-phone me-2"></i><?php echo getSetting('site_phone'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="social-icons">
                            <?php if(!empty(getSetting('facebook_url'))): ?>
                            <a href="<?php echo getSetting('facebook_url'); ?>" class="text-white me-2" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php if(!empty(getSetting('twitter_url'))): ?>
                            <a href="<?php echo getSetting('twitter_url'); ?>" class="text-white me-2" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if(!empty(getSetting('instagram_url'))): ?>
                            <a href="<?php echo getSetting('instagram_url'); ?>" class="text-white me-2" target="_blank"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                            <?php if(!empty(getSetting('youtube_url'))): ?>
                            <a href="<?php echo getSetting('youtube_url'); ?>" class="text-white" target="_blank"><i class="fab fa-youtube"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="main-header py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <a href="<?php echo URL_ROOT; ?>" class="logo">
                            <?php if(!empty(getSetting('site_logo'))): ?>
                            <img src="<?php echo URL_ROOT; ?>/uploads/settings/<?php echo getSetting('site_logo'); ?>" alt="<?php echo SITE_NAME; ?>" class="img-fluid" style="max-height: 60px;">
                            <?php else: ?>
                            <h1 class="m-0"><?php echo SITE_NAME; ?></h1>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <form action="<?php echo URL_ROOT; ?>/search.php" method="GET" class="search-form">
                            <div class="input-group">
                                <input type="text" name="keyword" class="form-control" placeholder="Search for products..." required>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="header-icons">
                            <?php if(isLoggedIn()): ?>
                                <a href="<?php echo URL_ROOT; ?>/account.php" class="me-3" title="My Account">
                                    <i class="fas fa-user"></i>
                                </a>
                                <a href="<?php echo URL_ROOT; ?>/wishlist.php" class="me-3" title="Wishlist">
                                    <i class="fas fa-heart"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo URL_ROOT; ?>/login.php" class="me-3" title="Login">
                                    <i class="fas fa-sign-in-alt"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo URL_ROOT; ?>/cart.php" class="cart-icon" title="Cart">
                                <i class="fas fa-shopping-cart"></i>
                                <?php if(isLoggedIn()): 
                                    $cart = getUserCart($_SESSION['user_id']);
                                    $cartCount = 0;
                                    foreach ($cart['items'] as $item) {
                                        $cartCount += $item['quantity'];
                                    }
                                ?>
                                <span class="cart-count"><?php echo $cartCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URL_ROOT; ?>">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                Categories
                            </a>
                            <ul class="dropdown-menu">
                                <?php 
                                $categories = getAllCategories();
                                foreach($categories as $category): 
                                ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo URL_ROOT; ?>/category.php?id=<?php echo $category['category_id']; ?>">
                                        <?php echo $category['name']; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URL_ROOT; ?>/shop.php">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URL_ROOT; ?>/about.php">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URL_ROOT; ?>/blog.php">Blog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URL_ROOT; ?>/contact.php">Contact</a>
                        </li>
                    </ul>
                    <?php if(isLoggedIn()): ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/account.php">My Account</a></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/orders.php">My Orders</a></li>
                            <?php if(isAdmin()): ?>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/admin/index.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/logout.php">Logout</a></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content py-4">
        <div class="container">
            <?php flash('success'); ?>
            <?php flash('error', '', 'alert alert-danger'); ?> 