<?php
$pageTitle = "My Wishlist";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to view your wishlist.');
    redirect('login.php');
}

// Process remove from wishlist
if (isset($_GET['remove'])) {
    $wishlistId = (int)$_GET['remove'];
    
    if (removeFromWishlist($wishlistId)) {
        flash('success', 'Item removed from wishlist successfully.');
    } else {
        flash('error', 'Failed to remove item from wishlist.');
    }
    
    redirect('wishlist.php');
}

// Get wishlist items
$wishlist = getUserWishlist($_SESSION['user_id']);

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <!-- Account Sidebar -->
        <div class="col-lg-3">
            <div class="account-sidebar">
                <h3>My Account</h3>
                <ul class="account-menu">
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/account.php" class="account-menu-link">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/orders.php" class="account-menu-link">
                            <i class="fas fa-shopping-bag"></i> Orders
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/wishlist.php" class="account-menu-link active">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/change-password.php" class="account-menu-link">
                            <i class="fas fa-lock"></i> Change Password
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/logout.php" class="account-menu-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Account Content -->
        <div class="col-lg-9">
            <div class="account-content">
                <h2 class="account-title">My Wishlist</h2>
                
                <?php if (empty($wishlist)): ?>
                <div class="alert alert-info">
                    Your wishlist is empty. <a href="<?php echo URL_ROOT; ?>/shop.php">Continue shopping</a>.
                </div>
                <?php else: ?>
                <div class="wishlist-items">
                    <div class="row">
                        <?php foreach ($wishlist as $item): 
                            $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="wishlist-item" id="wishlist-item-<?php echo $item['wishlist_id']; ?>">
                                <div class="wishlist-item-image">
                                    <img src="<?php echo URL_ROOT; ?>/<?php echo $item['image'] ? $item['image'] : 'assets/images/product-placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                                    <a href="<?php echo URL_ROOT; ?>/wishlist.php?remove=<?php echo $item['wishlist_id']; ?>" class="wishlist-remove" title="Remove from Wishlist">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                                <div class="wishlist-item-info">
                                    <h3 class="wishlist-item-title">
                                        <a href="<?php echo URL_ROOT; ?>/product.php?id=<?php echo $item['product_id']; ?>"><?php echo $item['name']; ?></a>
                                    </h3>
                                    <div class="wishlist-item-price">
                                        <?php if($item['sale_price']): ?>
                                        <span class="old-price"><?php echo formatPrice($item['price']); ?></span>
                                        <span class="price"><?php echo formatPrice($item['sale_price']); ?></span>
                                        <?php else: ?>
                                        <span class="price"><?php echo formatPrice($item['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="wishlist-item-actions">
                                        <a href="javascript:void(0)" onclick="addToCart(<?php echo $item['product_id']; ?>, 1)" class="btn btn-primary btn-sm">
                                            <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Add custom CSS for wishlist page
$extraCSS = '
<style>
.wishlist-item {
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.wishlist-item-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.wishlist-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.wishlist-item:hover .wishlist-item-image img {
    transform: scale(1.05);
}

.wishlist-remove {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--danger-color);
    transition: all 0.3s ease;
}

.wishlist-remove:hover {
    background-color: var(--danger-color);
    color: white;
}

.wishlist-item-info {
    padding: 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.wishlist-item-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.wishlist-item-title a {
    color: var(--secondary-color);
}

.wishlist-item-title a:hover {
    color: var(--primary-color);
}

.wishlist-item-price {
    margin-bottom: 15px;
}

.wishlist-item-actions {
    margin-top: auto;
}
</style>
';

include 'includes/footer.php';
?> 