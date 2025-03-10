<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$product = getProductById($productId);

// If product not found, redirect to shop page
if (!$product) {
    flash('error', 'Product not found.');
    redirect('shop.php');
}

$pageTitle = $product['name'];
include 'includes/header.php';

// Get product reviews
$reviews = getProductReviews($productId);
$averageRating = getProductAverageRating($productId);

// Process review form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review_submit'])) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        flash('error', 'You must be logged in to submit a review.');
        redirect('login.php');
    }
    
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    
    // Validate input
    $errors = [];
    
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5";
    }
    
    if (empty($comment)) {
        $errors[] = "Comment is required";
    }
    
    // If no errors, add review
    if (empty($errors)) {
        if (addProductReview($productId, $_SESSION['user_id'], $rating, $comment)) {
            flash('success', 'Your review has been submitted and is pending approval.');
            redirect("product.php?id=$productId");
        } else {
            flash('error', 'Failed to submit review. Please try again.');
        }
    }
}

// Process add to cart form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        flash('error', 'You must be logged in to add items to cart.');
        redirect('login.php');
    }
    
    $quantity = (int)$_POST['quantity'];
    
    // Validate quantity
    if ($quantity < 1) {
        flash('error', 'Quantity must be at least 1.');
        redirect("product.php?id=$productId");
    }
    
    // Add to cart
    if (addToCart($_SESSION['user_id'], $productId, $quantity)) {
        flash('success', 'Product added to cart successfully.');
        redirect("product.php?id=$productId");
    } else {
        flash('error', 'Failed to add product to cart. Please try again.');
    }
}
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>/shop.php">Shop</a></li>
            <?php if ($product['category_id']): ?>
            <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>/category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
        </ol>
    </nav>
    
    <div class="product-detail">
        <div class="row">
            <!-- Product Images -->
            <div class="col-md-6">
                <div class="product-detail-image">
                    <?php 
                    // Check if product has images
                    if (!empty($product['images'])) {
                        $mainImage = getImageUrl($product['images'][0]['image_path'], 'uploads/products');
                    } else {
                        $mainImage = getImageUrl($product['image'], 'uploads/products', 'product-placeholder.jpg');
                    }
                    ?>
                    <img src="<?php echo $mainImage; ?>" alt="<?php echo $product['name']; ?>" id="main-product-image">
                </div>
                
                <?php if (!empty($product['images']) && count($product['images']) > 1): ?>
                <div class="product-thumbnails">
                    <?php foreach ($product['images'] as $index => $image): ?>
                    <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo getImageUrl($image['image_path'], 'uploads/products'); ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div class="col-md-6 product-detail-info">
                <h1><?php echo $product['name']; ?></h1>
                
                <div class="product-detail-category">
                    Category: <a href="<?php echo URL_ROOT; ?>/category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a>
                </div>
                
                <div class="product-detail-price">
                    <?php if($product['sale_price']): ?>
                    <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                    <span class="price"><?php echo formatPrice($product['sale_price']); ?></span>
                    <?php else: ?>
                    <span class="price"><?php echo formatPrice($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="product-detail-rating">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $averageRating): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 <= $averageRating): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <div class="review-count">
                        <?php echo count($reviews); ?> Reviews
                    </div>
                </div>
                
                <div class="product-detail-description">
                    <p><?php echo nl2br($product['description']); ?></p>
                </div>
                
                <div class="product-detail-meta">
                    <div class="meta-item">
                        <span class="meta-label">SKU:</span>
                        <span><?php echo $product['sku']; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Availability:</span>
                        <span><?php echo $product['quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?></span>
                    </div>
                </div>
                
                <?php if (!empty($product['attributes'])): ?>
                <div class="product-detail-attributes mb-4">
                    <h4>Specifications</h4>
                    <table class="table table-bordered">
                        <tbody>
                            <?php foreach ($product['attributes'] as $attribute): ?>
                            <tr>
                                <th><?php echo $attribute['attribute_name']; ?></th>
                                <td><?php echo $attribute['attribute_value']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php if ($product['quantity'] > 0): ?>
                <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $productId; ?>" method="POST">
                    <div class="product-detail-quantity">
                        <div class="input-group quantity-input">
                            <button type="button" class="btn btn-outline-secondary decrement">-</button>
                            <input type="number" class="form-control text-center" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>">
                            <button type="button" class="btn btn-outline-secondary increment">+</button>
                        </div>
                    </div>
                    
                    <div class="product-detail-actions">
                        <button type="submit" name="add_to_cart" class="btn btn-primary add-to-cart product-detail-page">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button type="button" onclick="addToWishlist(<?php echo $productId; ?>)" class="btn btn-outline-secondary wishlist-btn" data-product-id="<?php echo $productId; ?>">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-warning">
                    This product is currently out of stock.
                </div>
                <?php endif; ?>
                
                <!-- Social Share -->
                <div class="product-social-share mt-4">
                    <span>Share:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(URL_ROOT . '/product.php?id=' . $productId); ?>" target="_blank" class="me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(URL_ROOT . '/product.php?id=' . $productId); ?>&text=<?php echo urlencode($product['name']); ?>" target="_blank" class="me-2"><i class="fab fa-twitter"></i></a>
                    <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(URL_ROOT . '/product.php?id=' . $productId); ?>&media=<?php echo urlencode($mainImage); ?>&description=<?php echo urlencode($product['name']); ?>" target="_blank" class="me-2"><i class="fab fa-pinterest-p"></i></a>
                    <a href="mailto:?subject=<?php echo urlencode($product['name']); ?>&body=<?php echo urlencode('Check out this product: ' . URL_ROOT . '/product.php?id=' . $productId); ?>" class="me-2"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="product-tabs">
        <ul class="nav nav-tabs" id="productTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Description</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Reviews (<?php echo count($reviews); ?>)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">Shipping & Returns</button>
            </li>
        </ul>
        <div class="tab-content" id="productTabsContent">
            <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                <div class="product-description">
                    <?php echo nl2br($product['description']); ?>
                </div>
            </div>
            <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                <div class="product-reviews">
                    <h3>Customer Reviews</h3>
                    
                    <?php if (empty($reviews)): ?>
                    <div class="alert alert-info">
                        There are no reviews yet. Be the first to review this product.
                    </div>
                    <?php else: ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-author"><?php echo $review['username']; ?></div>
                                <div class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></div>
                            </div>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $review['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <div class="review-content">
                                <p><?php echo nl2br($review['comment']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="write-review mt-4">
                        <h4>Write a Review</h4>
                        
                        <?php if (isLoggedIn()): ?>
                        <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $productId; ?>" method="POST" id="review-form">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <div class="rating-select">
                                    <div class="rate">
                                        <input type="radio" id="star5" name="rating" value="5" />
                                        <label for="star5" title="5 stars">5 stars</label>
                                        <input type="radio" id="star4" name="rating" value="4" />
                                        <label for="star4" title="4 stars">4 stars</label>
                                        <input type="radio" id="star3" name="rating" value="3" />
                                        <label for="star3" title="3 stars">3 stars</label>
                                        <input type="radio" id="star2" name="rating" value="2" />
                                        <label for="star2" title="2 stars">2 stars</label>
                                        <input type="radio" id="star1" name="rating" value="1" />
                                        <label for="star1" title="1 star">1 star</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Your Review</label>
                                <textarea class="form-control" id="comment" name="comment" rows="5" required></textarea>
                            </div>
                            <button type="submit" name="review_submit" class="btn btn-primary">Submit Review</button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info">
                            Please <a href="<?php echo URL_ROOT; ?>/login.php">login</a> to write a review.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                <div class="shipping-info">
                    <h4>Shipping Information</h4>
                    <p>We offer standard shipping on all orders. Orders are typically processed within 1-2 business days and delivered within 3-7 business days.</p>
                    <p>Free shipping on orders over $50.</p>
                    
                    <h4>Return Policy</h4>
                    <p>We accept returns within 30 days of purchase. Items must be unused and in their original packaging.</p>
                    <p>To initiate a return, please contact our customer service team.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <section class="related-products">
        <div class="section-title">
            <h2>Related Products</h2>
        </div>
        <div class="row">
            <?php
            $relatedProducts = getProductsByCategory($product['category_id'], 4);
            
            foreach($relatedProducts as $relatedProduct):
                // Skip current product
                if ($relatedProduct['product_id'] == $productId) continue;
                
                $relatedImage = $relatedProduct['primary_image'] ? $relatedProduct['primary_image'] : 'assets/images/product-placeholder.jpg';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card">
                    <div class="product-image">
                        <?php if($relatedProduct['sale_price']): ?>
                        <div class="product-badge">Sale</div>
                        <?php endif; ?>
                        <img src="<?php echo URL_ROOT; ?>/<?php echo $relatedImage; ?>" alt="<?php echo $relatedProduct['name']; ?>">
                        <div class="product-actions">
                            <a href="<?php echo URL_ROOT; ?>/product.php?id=<?php echo $relatedProduct['product_id']; ?>" title="View Details"><i class="fas fa-eye"></i></a>
                            <a href="javascript:void(0)" onclick="addToWishlist(<?php echo $relatedProduct['product_id']; ?>)" title="Add to Wishlist" class="wishlist-btn" data-product-id="<?php echo $relatedProduct['product_id']; ?>"><i class="far fa-heart"></i></a>
                            <a href="javascript:void(0)" onclick="addToCart(<?php echo $relatedProduct['product_id']; ?>, 1)" title="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?php echo $relatedProduct['category_name']; ?></div>
                        <h3 class="product-title"><a href="<?php echo URL_ROOT; ?>/product.php?id=<?php echo $relatedProduct['product_id']; ?>"><?php echo $relatedProduct['name']; ?></a></h3>
                        <div class="product-price">
                            <div>
                                <?php if($relatedProduct['sale_price']): ?>
                                <span class="old-price"><?php echo formatPrice($relatedProduct['price']); ?></span>
                                <span class="price"><?php echo formatPrice($relatedProduct['sale_price']); ?></span>
                                <?php else: ?>
                                <span class="price"><?php echo formatPrice($relatedProduct['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="add-to-cart" data-product-id="<?php echo $relatedProduct['product_id']; ?>"><i class="fas fa-shopping-cart"></i> Add</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php
// Add custom CSS for rating stars
$extraCSS = '
<style>
.rate {
    float: left;
    height: 46px;
    padding: 0 10px;
}
.rate:not(:checked) > input {
    position:absolute;
    top:-9999px;
}
.rate:not(:checked) > label {
    float:right;
    width:1em;
    overflow:hidden;
    white-space:nowrap;
    cursor:pointer;
    font-size:30px;
    color:#ccc;
}
.rate:not(:checked) > label:before {
    content: "★ ";
}
.rate > input:checked ~ label {
    color: #ffc700;    
}
.rate:not(:checked) > label:hover,
.rate:not(:checked) > label:hover ~ label {
    color: #deb217;  
}
.rate > input:checked + label:hover,
.rate > input:checked + label:hover ~ label,
.rate > input:checked ~ label:hover,
.rate > input:checked ~ label:hover ~ label,
.rate > label:hover ~ input:checked ~ label {
    color: #c59b08;
}
</style>
';

include 'includes/footer.php';
?> 