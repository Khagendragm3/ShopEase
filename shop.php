<?php
$pageTitle = "Shop";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get filter parameters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 1000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$sale = isset($_GET['sale']) ? (int)$_GET['sale'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get products based on filters
$products = [];
$totalProducts = 0;

// Build SQL query
$sql = "SELECT p.*, c.name as category_name, 
        (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.status = 'active'";

$countSql = "SELECT COUNT(*) as total FROM products p WHERE p.status = 'active'";
$params = [];
$types = "";

// Apply category filter
if ($categoryId) {
    $sql .= " AND p.category_id = ?";
    $countSql .= " AND p.category_id = ?";
    $params[] = $categoryId;
    $types .= "i";
}

// Apply price filter
$sql .= " AND p.price >= ? AND p.price <= ?";
$countSql .= " AND p.price >= ? AND p.price <= ?";
$params[] = $minPrice;
$params[] = $maxPrice;
$types .= "dd";

// Apply sale filter
if ($sale) {
    $sql .= " AND p.sale_price IS NOT NULL";
    $countSql .= " AND p.sale_price IS NOT NULL";
}

// Apply search filter
if (!empty($search)) {
    $searchTerm = "%$search%";
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $countSql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Apply sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.sale_price IS NULL, p.sale_price, p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.sale_price DESC, p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    default: // newest
        $sql .= " ORDER BY p.created_at DESC";
}

// Add pagination
$sql .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= "ii";

// Get total products count
$stmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countTypes = substr($types, 0, -2); // Remove the last two characters (for pagination)
    $countParams = array_slice($params, 0, -2); // Remove the last two parameters (for pagination)
    
    // Bind parameters for count query
    $countBindParams = array($countTypes);
    foreach ($countParams as $key => $value) {
        $countBindParams[] = &$countParams[$key];
    }
    
    call_user_func_array(array($stmt, 'bind_param'), $countBindParams);
}
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalProducts = $row['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    // Bind parameters for main query
    $bindParams = array($types);
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get all categories for filter
$categories = getAllCategories();

// Get min and max price for filter
$stmt = $conn->prepare("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE status = 'active'");
$stmt->execute();
$result = $stmt->get_result();
$priceRange = $result->fetch_assoc();

$minPriceFilter = $priceRange['min_price'];
$maxPriceFilter = $priceRange['max_price'];

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="shop-sidebar">
                <div class="sidebar-widget">
                    <h4 class="widget-title">Categories</h4>
                    <ul class="category-list">
                        <li>
                            <a href="<?php echo URL_ROOT; ?>/shop.php" class="<?php echo !$categoryId ? 'active' : ''; ?>">
                                All Categories
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="<?php echo URL_ROOT; ?>/shop.php?category=<?php echo $category['category_id']; ?>" class="<?php echo $categoryId == $category['category_id'] ? 'active' : ''; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="sidebar-widget">
                    <h4 class="widget-title">Filter by Price</h4>
                    <form action="<?php echo URL_ROOT; ?>/shop.php" method="GET" class="filter-form">
                        <?php if ($categoryId): ?>
                        <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        
                        <?php if ($sale): ?>
                        <input type="hidden" name="sale" value="1">
                        <?php endif; ?>
                        
                        <?php if (!empty($search)): ?>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                        <?php endif; ?>
                        
                        <?php if ($sort != 'newest'): ?>
                        <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                        <?php endif; ?>
                        
                        <?php if ($minPrice != $minPriceFilter): ?>
                        <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                        <?php endif; ?>
                        
                        <?php if ($maxPrice != $maxPriceFilter): ?>
                        <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                        <?php endif; ?>
                        
                        <div class="price-range-slider">
                            <div id="price-range" data-min="<?php echo $minPriceFilter; ?>" data-max="<?php echo $maxPriceFilter; ?>"></div>
                            <div class="price-inputs">
                                <div class="row">
                                    <div class="col-6">
                                        <label for="min-price">Min Price:</label>
                                        <input type="number" id="price-min" name="min_price" value="<?php echo $minPrice; ?>" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label for="max-price">Max Price:</label>
                                        <input type="number" id="price-max" name="max_price" value="<?php echo $maxPrice; ?>" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="price-range-value mt-2">
                                $<?php echo $minPrice; ?> - $<?php echo $maxPrice; ?>
                            </div>
                        </div>
                        
                        <div class="filter-buttons mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
                            <a href="<?php echo URL_ROOT; ?>/shop.php" class="btn btn-outline-secondary btn-sm clear-filters">Clear All</a>
                        </div>
                    </form>
                </div>
                
                <div class="sidebar-widget">
                    <h4 class="widget-title">Product Status</h4>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="sale-filter" <?php echo $sale ? 'checked' : ''; ?> onchange="window.location.href='<?php echo URL_ROOT; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['sale' => $sale ? 0 : 1])); ?>'">
                        <label class="custom-control-label" for="sale-filter">On Sale</label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="shop-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="shop-title">
                            <?php 
                            if ($categoryId && isset($categories)) {
                                foreach ($categories as $category) {
                                    if ($category['category_id'] == $categoryId) {
                                        echo $category['name'];
                                        break;
                                    }
                                }
                            } elseif ($sale) {
                                echo "Sale Products";
                            } elseif (!empty($search)) {
                                echo "Search Results for \"$search\"";
                            } else {
                                echo "All Products";
                            }
                            ?>
                        </h1>
                        <p class="shop-result-count">Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products</p>
                    </div>
                    <div class="col-md-6">
                        <div class="shop-sort">
                            <form action="<?php echo URL_ROOT; ?>/shop.php" method="GET" class="sort-form">
                                <?php if ($categoryId): ?>
                                <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                                <?php endif; ?>
                                
                                <?php if ($sale): ?>
                                <input type="hidden" name="sale" value="1">
                                <?php endif; ?>
                                
                                <?php if (!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo $search; ?>">
                                <?php endif; ?>
                                
                                <?php if ($minPrice != $minPriceFilter): ?>
                                <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                                <?php endif; ?>
                                
                                <?php if ($maxPrice != $maxPriceFilter): ?>
                                <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                                <?php endif; ?>
                                
                                <div class="input-group">
                                    <label class="input-group-text" for="sort">Sort By:</label>
                                    <select class="form-select" id="sort" name="sort" onchange="this.form.submit()">
                                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
            <div class="alert alert-info">
                No products found matching your criteria.
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): 
                    $productImage = $product['primary_image'] ? $product['primary_image'] : '';
                ?>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="product-card">
                        <div class="product-image">
                            <?php if($product['sale_price']): ?>
                            <div class="product-badge">Sale</div>
                            <?php endif; ?>
                            <img src="<?php echo !empty($productImage) ? URL_ROOT . '/' . $productImage : getImageUrl($product['image'], 'uploads/products', 'product-placeholder.jpg'); ?>" alt="<?php echo $product['name']; ?>">
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
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo URL_ROOT; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo URL_ROOT; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo URL_ROOT; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Add custom CSS for shop page
$extraCSS = '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.6.3/nouislider.min.css">
<style>
.shop-sidebar {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.sidebar-widget {
    margin-bottom: 30px;
}

.widget-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--light-gray-color);
    color: var(--secondary-color);
}

.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-list li {
    margin-bottom: 10px;
}

.category-list a {
    display: block;
    padding: 5px 0;
    color: var(--gray-color);
    transition: all 0.3s ease;
}

.category-list a:hover, .category-list a.active {
    color: var(--primary-color);
    padding-left: 5px;
}

.price-range-slider {
    margin-top: 20px;
}

.price-inputs {
    margin-top: 15px;
}

.shop-header {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.shop-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: var(--secondary-color);
}

.shop-result-count {
    color: var(--gray-color);
    margin-bottom: 0;
}

.shop-sort {
    display: flex;
    justify-content: flex-end;
}
</style>
';

// Add custom JS for price range slider
$extraJS = '
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.6.3/nouislider.min.js"></script>
';

include 'includes/footer.php';
?> 