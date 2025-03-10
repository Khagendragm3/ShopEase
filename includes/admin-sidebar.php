<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/index.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/orders.php">
                    <i class="fas fa-shopping-bag me-2"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'add-product.php' || basename($_SERVER['PHP_SELF']) == 'edit-product.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/products.php">
                    <i class="fas fa-box me-2"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' || basename($_SERVER['PHP_SELF']) == 'add-category.php' || basename($_SERVER['PHP_SELF']) == 'edit-category.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/categories.php">
                    <i class="fas fa-tags me-2"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/users.php">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/reviews.php">
                    <i class="fas fa-star me-2"></i> Reviews
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'coupons.php' || basename($_SERVER['PHP_SELF']) == 'add-coupon.php' || basename($_SERVER['PHP_SELF']) == 'edit-coupon.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/coupons.php">
                    <i class="fas fa-ticket-alt me-2"></i> Coupons
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Settings</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/settings.php">
                    <i class="fas fa-cog me-2"></i> General Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URL_ROOT; ?>" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i> View Website
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Content</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/testimonials.php">
                    <i class="fas fa-quote-left me-2"></i> Testimonials
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'blog-posts.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/blog-posts.php">
                    <i class="fas fa-blog me-2"></i> Blog Posts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'brands.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/brands.php">
                    <i class="fas fa-copyright me-2"></i> Brands
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact-messages.php' ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/admin/contact-messages.php">
                    <i class="fas fa-envelope me-2"></i> Contact Messages
                </a>
            </li>
        </ul>
    </div>
</nav> 