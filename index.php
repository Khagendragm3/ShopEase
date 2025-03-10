<?php
$pageTitle = "Home";
include 'includes/header.php';

// Check if hero slider is enabled
$useHeroSlider = getSetting('use_hero_slider', '0') === '1';
?>

<!-- Hero Section -->
<?php if ($useHeroSlider): ?>
<!-- Hero Slider -->
<section class="hero-slider">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
                <img src="<?php echo getImageUrl(getSetting('hero_slide_image_1', ''), 'uploads/hero', 'assets/images/hero-slide-1.jpg'); ?>" class="d-block w-100" alt="Hero Slide 1">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <h2><?php echo getSetting('hero_slide_title_1', 'Welcome to our Store'); ?></h2>
                                <p><?php echo getSetting('hero_slide_description_1', 'Discover amazing products with great deals. Shop now and enjoy exclusive offers.'); ?></p>
                                <a href="<?php echo URL_ROOT . getSetting('hero_slide_button_url_1', '/shop.php'); ?>" class="btn btn-primary btn-lg"><?php echo getSetting('hero_slide_button_text_1', 'Shop Now'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="carousel-item">
                <img src="<?php echo getImageUrl(getSetting('hero_slide_image_2', ''), 'uploads/hero', 'assets/images/hero-slide-2.jpg'); ?>" class="d-block w-100" alt="Hero Slide 2">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <h2><?php echo getSetting('hero_slide_title_2', 'New Arrivals'); ?></h2>
                                <p><?php echo getSetting('hero_slide_description_2', 'Check out our latest products and collections. Find the perfect items for you.'); ?></p>
                                <a href="<?php echo URL_ROOT . getSetting('hero_slide_button_url_2', '/shop.php?new=1'); ?>" class="btn btn-primary btn-lg"><?php echo getSetting('hero_slide_button_text_2', 'View Collection'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slide 3 -->
            <div class="carousel-item">
                <img src="<?php echo getImageUrl(getSetting('hero_slide_image_3', ''), 'uploads/hero', 'assets/images/hero-slide-3.jpg'); ?>" class="d-block w-100" alt="Hero Slide 3">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <h2><?php echo getSetting('hero_slide_title_3', 'Special Offers'); ?></h2>
                                <p><?php echo getSetting('hero_slide_description_3', 'Get up to 50% off on selected items. Limited time offer, don\'t miss out!'); ?></p>
                                <a href="<?php echo URL_ROOT . getSetting('hero_slide_button_url_3', '/shop.php?sale=1'); ?>" class="btn btn-primary btn-lg"><?php echo getSetting('hero_slide_button_text_3', 'Shop Sale'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>
<?php else: ?>
<!-- Single Hero Image -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h2><?php echo getSetting('hero_title', 'Welcome to ' . SITE_NAME); ?></h2>
                <p><?php echo getSetting('hero_description', 'Discover amazing products with great deals. Shop now and enjoy exclusive offers on our wide range of products.'); ?></p>
                <a href="<?php echo URL_ROOT . getSetting('hero_button_url', '/shop.php'); ?>" class="btn btn-primary btn-lg"><?php echo getSetting('hero_button_text', 'Shop Now'); ?></a>
            </div>
            <div class="col-lg-6 hero-image">
                <img src="<?php echo getImageUrl(getSetting('hero_image', ''), 'uploads/settings', 'assets/images/hero-image.jpg'); ?>" alt="Hero Image" class="img-fluid">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Categories -->
<section class="featured-categories">
    <div class="container">
        <div class="section-title">
            <h2>Shop by Category</h2>
        </div>
        <div class="row">
            <?php
            $categories = getAllCategories();
            $featuredCategories = array_slice($categories, 0, 4);
            
            foreach($featuredCategories as $category):
            ?>
            <div class="col-md-3 col-sm-6">
                <div class="category-card">
                    <img src="<?php echo getImageUrl($category['image'], 'uploads/categories', 'assets/images/category-placeholder.jpg'); ?>" alt="<?php echo $category['name']; ?>" class="category-image">
                    <div class="category-overlay">
                        <h3 class="category-title"><?php echo $category['name']; ?></h3>
                        <a href="<?php echo URL_ROOT; ?>/shop.php?category=<?php echo $category['category_id']; ?>" class="btn btn-light btn-sm">View Products</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
    <div class="container">
        <div class="section-title">
            <h2>Featured Products</h2>
        </div>
        <div class="row">
            <?php
            $featuredProducts = getAllProducts(8);
            
            foreach($featuredProducts as $product):
                $productImage = $product['primary_image'] ? $product['primary_image'] : '';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card">
                    <div class="product-image">
                        <?php if($product['sale_price']): ?>
                        <div class="product-badge">Sale</div>
                        <?php endif; ?>
                        <img src="<?php echo !empty($productImage) ? URL_ROOT . '/' . $productImage : getImageUrl($product['image'], 'uploads/products', 'assets/images/product-placeholder.jpg'); ?>" alt="<?php echo $product['name']; ?>">
                        <div class="product-actions">
                            <a href="<?php echo URL_ROOT; ?>/product.php?id=<?php echo $product['product_id']; ?>" title="View Details"><i class="fas fa-eye"></i></a>
                            <a href="javascript:void(0)" onclick="addToWishlist(<?php echo $product['product_id']; ?>)" title="Add to Wishlist" class="wishlist-btn" data-product-id="<?php echo $product['product_id']; ?>"><i class="far fa-heart"></i></a>
                            <a href="javascript:void(0)" onclick="addToCart(<?php echo $product['product_id']; ?>, 1)" title="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?php echo $product['category_name']; ?></div>
                        <h3 class="product-title"><a href="<?php echo URL_ROOT; ?>/product.php?id=<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></a></h3>
                        <div class="product-price">
                            <div>
                                <?php if($product['sale_price']): ?>
                                <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                                <span class="price"><?php echo formatPrice($product['sale_price']); ?></span>
                                <?php else: ?>
                                <span class="price"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="add-to-cart" data-product-id="<?php echo $product['product_id']; ?>"><i class="fas fa-shopping-cart"></i> Add</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo URL_ROOT; ?>/shop.php" class="btn btn-outline-primary">View All Products</a>
        </div>
    </div>
</section>

<!-- Banner -->
<section class="banner">
    <div class="container">
        <h2>Special Offer</h2>
        <p>Get up to 50% off on selected items. Limited time offer, don't miss out!</p>
        <a href="<?php echo URL_ROOT; ?>/shop.php?sale=1" class="btn btn-light btn-lg">Shop Now</a>
    </div>
</section>

<!-- Features -->
<section class="features">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="feature-title">Free Shipping</h3>
                    <p>Free shipping on all orders over $50</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3 class="feature-title">Easy Returns</h3>
                    <p>30 days money back guarantee</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="feature-title">Secure Payment</h3>
                    <p>100% secure payment processing</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p>Dedicated support team</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials">
    <div class="container">
        <div class="section-title">
            <h2>What Our Customers Say</h2>
        </div>
        <div class="row">
            <?php
            // Check if testimonials table exists
            $tableExists = false;
            $checkTable = $conn->query("SHOW TABLES LIKE 'testimonials'");
            if ($checkTable && $checkTable->num_rows > 0) {
                $tableExists = true;
            }
            
            $testimonials = [];
            
            // Only query if table exists
            if ($tableExists) {
                // Get active testimonials from database
                $stmt = $conn->prepare("SELECT * FROM testimonials WHERE status = 'active' ORDER BY id DESC LIMIT 3");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $testimonials[] = $row;
                }
            }
            
            // If we have testimonials in the database, display them
            if (!empty($testimonials)):
                foreach ($testimonials as $testimonial):
            ?>
            <div class="col-md-4">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="<?php echo getImageUrl($testimonial['image'], 'uploads/testimonials', 'assets/images/testimonial-1.jpg'); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                        </div>
                        <div class="author-info">
                            <h5><?php echo htmlspecialchars($testimonial['name']); ?></h5>
                            <p><?php echo htmlspecialchars($testimonial['position']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else:
                // If no testimonials in database, display default ones
            ?>
            <div class="col-md-4">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"<?php echo getSetting('testimonial_1_text', "I'm extremely satisfied with the quality of products and the excellent customer service. Will definitely shop here again!"); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="<?php echo getImageUrl(getSetting('testimonial_image_1', ''), 'uploads/testimonials', 'assets/images/testimonial-1.jpg'); ?>" alt="<?php echo getSetting('testimonial_1_name', 'John Doe'); ?>">
                        </div>
                        <div class="author-info">
                            <h5><?php echo getSetting('testimonial_1_name', 'John Doe'); ?></h5>
                            <p><?php echo getSetting('testimonial_1_position', 'Regular Customer'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"<?php echo getSetting('testimonial_2_text', "Fast shipping and great prices. The products are of high quality and exactly as described. Highly recommended!"); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="<?php echo getImageUrl(getSetting('testimonial_image_2', ''), 'uploads/testimonials', 'assets/images/testimonial-2.jpg'); ?>" alt="<?php echo getSetting('testimonial_2_name', 'Jane Smith'); ?>">
                        </div>
                        <div class="author-info">
                            <h5><?php echo getSetting('testimonial_2_name', 'Jane Smith'); ?></h5>
                            <p><?php echo getSetting('testimonial_2_position', 'Loyal Customer'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"<?php echo getSetting('testimonial_3_text', "The website is easy to navigate, and the checkout process is smooth. I received my order earlier than expected. Great experience!"); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="<?php echo getImageUrl(getSetting('testimonial_image_3', ''), 'uploads/testimonials', 'assets/images/testimonial-3.jpg'); ?>" alt="<?php echo getSetting('testimonial_3_name', 'Mike Johnson'); ?>">
                        </div>
                        <div class="author-info">
                            <h5><?php echo getSetting('testimonial_3_name', 'Mike Johnson'); ?></h5>
                            <p><?php echo getSetting('testimonial_3_position', 'New Customer'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Latest Blog Posts -->
<section class="latest-blog">
    <div class="container">
        <div class="section-title">
            <h2>Latest Blog Posts</h2>
        </div>
        <div class="row">
            <?php
            // Check if blog_posts table exists
            $tableExists = false;
            $checkTable = $conn->query("SHOW TABLES LIKE 'blog_posts'");
            if ($checkTable && $checkTable->num_rows > 0) {
                $tableExists = true;
            }
            
            $blogPosts = [];
            
            // Only query if table exists
            if ($tableExists) {
                // Get published blog posts from database
                $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY publish_date DESC LIMIT 3");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $blogPosts[] = $row;
                }
            }
            
            // If we have blog posts in the database, display them
            if (!empty($blogPosts)):
                foreach ($blogPosts as $post):
                    // Format date for display
                    $date = new DateTime($post['publish_date']);
                    $day = $date->format('d');
                    $month = $date->format('M');
            ?>
            <div class="col-md-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="<?php echo getImageUrl($post['image'], 'uploads/blog', 'assets/images/blog-1.jpg'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid">
                        <div class="blog-date">
                            <span class="day"><?php echo $day; ?></span>
                            <span class="month"><?php echo $month; ?></span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <h3 class="blog-title"><a href="<?php echo URL_ROOT; ?>/blog-post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <p class="blog-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        <a href="<?php echo URL_ROOT; ?>/blog-post.php?id=<?php echo $post['id']; ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else:
                // If no blog posts in database, display default ones
            ?>
            <div class="col-md-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="<?php echo getImageUrl(getSetting('blog_image_1', ''), 'uploads/blog', 'assets/images/blog-1.jpg'); ?>" alt="<?php echo getSetting('blog_1_title', 'Blog Post 1'); ?>" class="img-fluid">
                        <div class="blog-date">
                            <?php 
                            $date = getSetting('blog_1_date', '15 Jun');
                            $dateParts = explode(' ', $date);
                            ?>
                            <span class="day"><?php echo $dateParts[0] ?? '15'; ?></span>
                            <span class="month"><?php echo $dateParts[1] ?? 'Jun'; ?></span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <h3 class="blog-title"><a href="<?php echo getSetting('blog_1_url', '#'); ?>"><?php echo getSetting('blog_1_title', 'Summer Fashion Trends'); ?></a></h3>
                        <p class="blog-excerpt"><?php echo getSetting('blog_1_excerpt', 'Discover the hottest fashion trends for this summer season...'); ?></p>
                        <a href="<?php echo getSetting('blog_1_url', '#'); ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="<?php echo getImageUrl(getSetting('blog_image_2', ''), 'uploads/blog', 'assets/images/blog-2.jpg'); ?>" alt="<?php echo getSetting('blog_2_title', 'Blog Post 2'); ?>" class="img-fluid">
                        <div class="blog-date">
                            <?php 
                            $date = getSetting('blog_2_date', '10 Jun');
                            $dateParts = explode(' ', $date);
                            ?>
                            <span class="day"><?php echo $dateParts[0] ?? '10'; ?></span>
                            <span class="month"><?php echo $dateParts[1] ?? 'Jun'; ?></span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <h3 class="blog-title"><a href="<?php echo getSetting('blog_2_url', '#'); ?>"><?php echo getSetting('blog_2_title', 'Top 10 Gadgets of 2023'); ?></a></h3>
                        <p class="blog-excerpt"><?php echo getSetting('blog_2_excerpt', 'Check out our list of the best tech gadgets released this year...'); ?></p>
                        <a href="<?php echo getSetting('blog_2_url', '#'); ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="<?php echo getImageUrl(getSetting('blog_image_3', ''), 'uploads/blog', 'assets/images/blog-3.jpg'); ?>" alt="<?php echo getSetting('blog_3_title', 'Blog Post 3'); ?>" class="img-fluid">
                        <div class="blog-date">
                            <?php 
                            $date = getSetting('blog_3_date', '05 Jun');
                            $dateParts = explode(' ', $date);
                            ?>
                            <span class="day"><?php echo $dateParts[0] ?? '05'; ?></span>
                            <span class="month"><?php echo $dateParts[1] ?? 'Jun'; ?></span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <h3 class="blog-title"><a href="<?php echo getSetting('blog_3_url', '#'); ?>"><?php echo getSetting('blog_3_title', 'Home Decor Ideas'); ?></a></h3>
                        <p class="blog-excerpt"><?php echo getSetting('blog_3_excerpt', 'Transform your living space with these creative home decor ideas...'); ?></p>
                        <a href="<?php echo getSetting('blog_3_url', '#'); ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Brands -->
<section class="brands">
    <div class="container">
        <div class="section-title">
            <h2>Our Brands</h2>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="brands-container">
                    <?php
                    // Check if brands table exists
                    $tableExists = false;
                    $checkTable = $conn->query("SHOW TABLES LIKE 'brands'");
                    if ($checkTable && $checkTable->num_rows > 0) {
                        $tableExists = true;
                    }
                    
                    $brands = [];
                    
                    // Only query if table exists
                    if ($tableExists) {
                        // Get active brands from database
                        $stmt = $conn->prepare("SELECT * FROM brands WHERE status = 'active' ORDER BY display_order, name LIMIT 6");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            $brands[] = $row;
                        }
                    }
                    
                    if (!empty($brands)):
                        foreach ($brands as $brand):
                    ?>
                    <div class="brand-item">
                        <?php if (!empty($brand['url'])): ?>
                        <a href="<?php echo htmlspecialchars($brand['url']); ?>" target="_blank" title="<?php echo htmlspecialchars($brand['name']); ?>">
                        <?php endif; ?>
                            <img src="<?php echo getImageUrl($brand['image'], 'uploads/brands'); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>">
                        <?php if (!empty($brand['url'])): ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php 
                        endforeach;
                    else:
                        // Display default brands if none in database
                    ?>
                    <div class="brand-item">
                        <img src="<?php echo URL_ROOT; ?>/assets/images/brand-1.svg" alt="Brand 1">
                    </div>
                    <div class="brand-item">
                        <img src="<?php echo URL_ROOT; ?>/assets/images/brand-2.svg" alt="Brand 2">
                    </div>
                    <div class="brand-item">
                        <img src="<?php echo URL_ROOT; ?>/assets/images/brand-3.svg" alt="Brand 3">
                    </div>
                    <div class="brand-item">
                        <img src="<?php echo URL_ROOT; ?>/assets/images/brand-4.svg" alt="Brand 4">
                    </div>
                    <div class="brand-item">
                        <img src="<?php echo URL_ROOT; ?>/assets/images/brand-5.svg" alt="Brand 5">
                    </div>
                    <div class="brand-item">
                        <img src="<?php echo URL_ROOT; ?>/assets/images/brand-6.svg" alt="Brand 6">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 