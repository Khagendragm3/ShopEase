<?php
$pageTitle = "Checkout";
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/esewa_config.php'; // Include eSewa configuration

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to checkout.');
    redirect('login.php');
}

// Get cart items
$cart = getUserCart($_SESSION['user_id']);

// Check if cart is empty
if (empty($cart['items'])) {
    flash('error', 'Your cart is empty. Please add items to your cart before checkout.');
    redirect('shop.php');
}

// Calculate totals
$subtotal = $cart['total'];
$discount = 0;
$shippingCost = 10; // Default shipping cost
$taxRate = 5; // Default tax rate (5%)

// Apply coupon discount if available
if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    $discount = applyCouponDiscount($coupon, $subtotal);
}

// Calculate tax
$taxAmount = (($subtotal - $discount) * $taxRate) / 100;

// Calculate total
$total = $subtotal - $discount + $shippingCost + $taxAmount;

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zipCode = sanitize($_POST['zip_code']);
    $country = sanitize($_POST['country']);
    $paymentMethod = sanitize($_POST['payment_method']);
    
    // Validate input
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastName)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (empty($city)) {
        $errors[] = "City is required";
    }
    
    if (empty($state)) {
        $errors[] = "State is required";
    }
    
    if (empty($zipCode)) {
        $errors[] = "ZIP code is required";
    }
    
    if (empty($country)) {
        $errors[] = "Country is required";
    }
    
    if (empty($paymentMethod)) {
        $errors[] = "Payment method is required";
    }
    
    // If no errors, create order
    if (empty($errors)) {
        // Format shipping address
        $shippingAddress = "$address, $city, $state $zipCode, $country";
        
        // Use same billing address for now
        $billingAddress = $shippingAddress;
        
        // Create order
        $orderId = createOrder($_SESSION['user_id'], $total, $shippingAddress, $billingAddress, $paymentMethod);
        
        if ($orderId) {
            // Update coupon usage if used
            if (isset($_SESSION['coupon'])) {
                updateCouponUsage($_SESSION['coupon']['coupon_id']);
                unset($_SESSION['coupon']);
            }
            
            // Handle payment method
            if ($paymentMethod == 'esewa') {
                // Store order ID in session for reference
                $_SESSION['pending_order_id'] = $orderId;
                
                // Redirect to the eSewa processing page instead of directly submitting the form
                redirect("process_esewa_payment.php?order_id=$orderId");
            } else {
                // For other payment methods, redirect to order confirmation
                redirect("order-confirmation.php?id=$orderId");
            }
        } else {
            flash('error', 'Failed to create order. Please try again.');
        }
    }
}

// Get user data for pre-filling form
$user = getUserById($_SESSION['user_id']);

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title">Checkout</h1>
        </div>
    </div>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="checkout-form">
        <div class="row">
            <!-- Billing Details -->
            <div class="col-lg-8">
                <div class="checkout-section">
                    <h3 class="section-title">Billing Details</h3>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo $user['address'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo $user['city'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State/Province *</label>
                            <input type="text" class="form-control" id="state" name="state" value="<?php echo $user['state'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="zip_code" class="form-label">ZIP Code *</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo $user['zip_code'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country *</label>
                            <input type="text" class="form-control" id="country" name="country" value="<?php echo $user['country'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="save_info" name="save_info">
                        <label class="form-check-label" for="save_info">Save this information for next time</label>
                    </div>
                </div>
                
                <div class="checkout-section mt-4">
                    <h3 class="section-title">Payment Method</h3>
                    
                    <div class="payment-methods">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                            <label class="form-check-label" for="credit_card">
                                Credit Card
                                <div class="payment-icons">
                                    <i class="fab fa-cc-visa"></i>
                                    <i class="fab fa-cc-mastercard"></i>
                                    <i class="fab fa-cc-amex"></i>
                                    <i class="fab fa-cc-discover"></i>
                                </div>
                            </label>
                        </div>
                        
                        <div id="credit_card_fields" class="payment-fields">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control" id="card_name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="esewa" value="esewa">
                            <label class="form-check-label" for="esewa">
                                eSewa
                                <img src="assets/images/esewa-logo.svg" alt="eSewa" class="payment-logo ms-2" style="height: 24px;">
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                            <label class="form-check-label" for="paypal">
                                PayPal
                                <i class="fab fa-paypal ms-2"></i>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" value="cash_on_delivery">
                            <label class="form-check-label" for="cash_on_delivery">
                                Cash on Delivery
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="checkout-summary">
                    <h3>Order Summary</h3>
                    
                    <div class="order-items">
                        <?php foreach ($cart['items'] as $item): 
                            $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
                        ?>
                        <div class="order-item">
                            <div class="order-item-image">
                                <img src="<?php echo URL_ROOT; ?>/<?php echo $item['image'] ? $item['image'] : 'assets/images/product-placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="order-item-details">
                                <h5><?php echo $item['name']; ?></h5>
                                <div class="order-item-meta">
                                    <span>Qty: <?php echo $item['quantity']; ?></span>
                                    <span><?php echo formatPrice($price); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <?php if ($discount > 0): ?>
                        <div class="summary-item">
                            <span>Discount</span>
                            <span>-<?php echo formatPrice($discount); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-item">
                            <span>Shipping</span>
                            <span><?php echo formatPrice($shippingCost); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Tax (<?php echo $taxRate; ?>%)</span>
                            <span><?php echo formatPrice($taxAmount); ?></span>
                        </div>
                        
                        <div class="summary-item total">
                            <span>Total</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                    
                    <div class="checkout-button mt-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                    </div>
                    
                    <div class="checkout-terms mt-3">
                        <p class="small text-muted">
                            By placing your order, you agree to our <a href="<?php echo URL_ROOT; ?>/terms.php">Terms and Conditions</a> and <a href="<?php echo URL_ROOT; ?>/privacy-policy.php">Privacy Policy</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
// Add custom CSS for checkout page
$extraCSS = '
<style>
.checkout-section {
    background-color: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--light-gray-color);
    color: var(--secondary-color);
}

.payment-icons {
    display: inline-block;
    margin-left: 10px;
}

.payment-icons i {
    font-size: 1.5rem;
    margin-right: 5px;
    color: var(--gray-color);
}

.payment-fields {
    padding: 15px;
    background-color: var(--light-color);
    border-radius: 4px;
    margin-bottom: 20px;
}

.checkout-summary {
    background-color: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 20px;
}

.order-items {
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 10px;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--light-gray-color);
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 60px;
    height: 60px;
    border-radius: 4px;
    overflow: hidden;
    margin-right: 15px;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-details h5 {
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.order-item-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: var(--gray-color);
}

.order-totals {
    padding-top: 20px;
    border-top: 1px solid var(--light-gray-color);
}
</style>
';

// Add custom JavaScript for payment method selection
$extraJS = '
<script>
$(document).ready(function() {
    // Show/hide payment fields based on selected payment method
    $("input[name=\'payment_method\']").change(function() {
        if ($(this).val() == "credit_card") {
            $("#credit_card_fields").slideDown();
        } else {
            $("#credit_card_fields").slideUp();
        }
    });
});
</script>
';

include 'includes/footer.php';
?> 