<?php
$pageTitle = "Shopping Cart";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Process cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in
    if (!isLoggedIn()) {
        flash('error', 'You must be logged in to perform this action.');
        redirect('login.php');
    }
    
    // Update cart item quantity
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $cartId => $quantity) {
            if ($quantity > 0) {
                updateCartItemQuantity($cartId, $quantity);
            } else {
                removeFromCart($cartId);
            }
        }
        
        flash('success', 'Cart updated successfully.');
        redirect('cart.php');
    }
    
    // Apply coupon
    if (isset($_POST['apply_coupon'])) {
        $couponCode = sanitize($_POST['coupon_code']);
        
        // Get cart total
        $cart = getUserCart($_SESSION['user_id']);
        $totalAmount = $cart['total'];
        
        // Validate coupon
        $coupon = validateCoupon($couponCode, $totalAmount);
        
        if ($coupon) {
            // Store coupon in session
            $_SESSION['coupon'] = $coupon;
            
            flash('success', 'Coupon applied successfully.');
        } else {
            flash('error', 'Invalid coupon code or minimum purchase requirement not met.');
        }
        
        redirect('cart.php');
    }
    
    // Remove coupon
    if (isset($_POST['remove_coupon'])) {
        unset($_SESSION['coupon']);
        
        flash('success', 'Coupon removed successfully.');
        redirect('cart.php');
    }
}

// Get cart items
$cart = [];
$totalAmount = 0;
$discount = 0;
$finalTotal = 0;

if (isLoggedIn()) {
    $cart = getUserCart($_SESSION['user_id']);
    $totalAmount = $cart['total'];
    
    // Apply coupon discount if available
    if (isset($_SESSION['coupon'])) {
        $coupon = $_SESSION['coupon'];
        $discount = applyCouponDiscount($coupon, $totalAmount);
        $finalTotal = $totalAmount - $discount;
    } else {
        $finalTotal = $totalAmount;
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title">Shopping Cart</h1>
        </div>
    </div>
    
    <?php if (!isLoggedIn()): ?>
    <div class="alert alert-info">
        Please <a href="<?php echo URL_ROOT; ?>/login.php">login</a> to view your cart.
    </div>
    <?php elseif (empty($cart['items'])): ?>
    <div class="alert alert-info">
        Your cart is empty. <a href="<?php echo URL_ROOT; ?>/shop.php">Continue shopping</a>.
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="cart-items">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <table class="table cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart['items'] as $item): 
                                $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
                            ?>
                            <tr id="cart-item-<?php echo $item['cart_id']; ?>" class="cart-item">
                                <td data-label="Product">
                                    <div class="cart-product">
                                        <div class="cart-product-image">
                                            <img src="<?php echo URL_ROOT; ?>/<?php echo $item['image'] ? $item['image'] : 'assets/images/product-placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                                        </div>
                                        <div class="cart-product-info">
                                            <h5><a href="<?php echo URL_ROOT; ?>/product.php?id=<?php echo $item['product_id']; ?>"><?php echo $item['name']; ?></a></h5>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Price"><?php echo formatPrice($price); ?></td>
                                <td data-label="Quantity">
                                    <div class="input-group cart-quantity">
                                        <button type="button" class="btn btn-outline-secondary decrement">-</button>
                                        <input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0" class="form-control text-center" data-cart-id="<?php echo $item['cart_id']; ?>">
                                        <button type="button" class="btn btn-outline-secondary increment">+</button>
                                    </div>
                                </td>
                                <td data-label="Total" id="item-total-<?php echo $item['cart_id']; ?>"><?php echo formatPrice($item['item_total']); ?></td>
                                <td>
                                    <a href="javascript:void(0)" onclick="removeFromCart(<?php echo $item['cart_id']; ?>)" class="cart-remove" title="Remove Item"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-actions">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="coupon-form">
                                    <div class="input-group">
                                        <input type="text" name="coupon_code" class="form-control" placeholder="Coupon Code" <?php echo isset($_SESSION['coupon']) ? 'disabled' : ''; ?>>
                                        <?php if (isset($_SESSION['coupon'])): ?>
                                        <button type="submit" name="remove_coupon" class="btn btn-outline-danger">Remove Coupon</button>
                                        <?php else: ?>
                                        <button type="submit" name="apply_coupon" class="btn btn-outline-primary">Apply Coupon</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" name="update_cart" class="btn btn-outline-secondary">Update Cart</button>
                                <a href="<?php echo URL_ROOT; ?>/shop.php" class="btn btn-outline-primary">Continue Shopping</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="cart-summary">
                <h3>Cart Summary</h3>
                
                <div class="summary-item">
                    <span>Subtotal</span>
                    <span class="subtotal-value"><?php echo formatPrice($totalAmount); ?></span>
                </div>
                
                <?php if ($discount > 0): ?>
                <div class="summary-item discount-row">
                    <span>Discount</span>
                    <span class="discount-value">-<?php echo formatPrice($discount); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="summary-item">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                
                <div class="summary-item total">
                    <span>Total</span>
                    <span class="total-value"><?php echo formatPrice($finalTotal); ?></span>
                </div>
                
                <div class="checkout-button mt-4">
                    <a href="<?php echo URL_ROOT; ?>/checkout.php" class="btn btn-primary btn-lg w-100">Proceed to Checkout</a>
                </div>
                
                <div class="payment-methods mt-4 text-center">
                    <p>We Accept</p>
                    <div>
                        <i class="fab fa-cc-visa fa-2x me-2"></i>
                        <i class="fab fa-cc-mastercard fa-2x me-2"></i>
                        <i class="fab fa-cc-amex fa-2x me-2"></i>
                        <i class="fab fa-cc-paypal fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 