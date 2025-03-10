<?php
$pageTitle = "Edit Product";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    flash('error', 'Product ID is required.');
    redirect('admin/products.php');
}

$productId = (int) sanitize($_GET['id']);

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    flash('error', 'Product not found.');
    redirect('admin/products.php');
}

$product = $result->fetch_assoc();

// Get additional images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$additionalImages = [];
while ($row = $result->fetch_assoc()) {
    $additionalImages[] = $row;
}

// Get all categories for dropdown
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name ASC");
$stmt->execute();
$result = $stmt->get_result();
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float) sanitize($_POST['price']);
    $sale_price = !empty($_POST['sale_price']) ? (float) sanitize($_POST['sale_price']) : null;
    $quantity = (int) sanitize($_POST['quantity']);
    $category_id = (int) sanitize($_POST['category_id']);
    // If category_id is 0 (Uncategorized), set it to NULL to avoid foreign key constraint error
    $category_id = ($category_id === 0) ? null : $category_id;
    $status = sanitize($_POST['status']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $sku = sanitize($_POST['sku']);
    // If SKU is empty, set it to NULL to avoid duplicate entry error
    $sku = empty($sku) ? null : $sku;
    $weight = !empty($_POST['weight']) ? (float) sanitize($_POST['weight']) : null;
    $dimensions = sanitize($_POST['dimensions']);
    $meta_title = sanitize($_POST['meta_title']);
    $meta_description = sanitize($_POST['meta_description']);
    $meta_keywords = sanitize($_POST['meta_keywords']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    
    if (empty($description)) {
        $errors[] = "Product description is required.";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than zero.";
    }
    
    if (!empty($sale_price) && $sale_price >= $price) {
        $errors[] = "Sale price must be less than regular price.";
    }
    
    if ($quantity < 0) {
        $errors[] = "Quantity cannot be negative.";
    }
    
    // Check if SKU already exists (excluding current product)
    if (!empty($sku)) {
        $stmt = $conn->prepare("SELECT product_id FROM products WHERE sku = ? AND product_id != ?");
        $stmt->bind_param("si", $sku, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "SKU already exists. Please use a different SKU.";
        }
    }
    
    // Process image upload
    $image = $product['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload(
            $_FILES['image'],
            'uploads/products',
            $product['image'],
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'product_main_'
        );
        
        if ($uploadResult['success']) {
            $image = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }
    
    // Process additional images
    $new_additional_images = [];
    if (isset($_FILES['additional_images']) && !empty($_FILES['additional_images']['name'][0])) {
        $fileCount = count($_FILES['additional_images']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            // Create a temporary file array structure for the current file
            $currentFile = [
                'name' => $_FILES['additional_images']['name'][$i],
                'type' => $_FILES['additional_images']['type'][$i],
                'tmp_name' => $_FILES['additional_images']['tmp_name'][$i],
                'error' => $_FILES['additional_images']['error'][$i],
                'size' => $_FILES['additional_images']['size'][$i]
            ];
            
            if ($currentFile['error'] === UPLOAD_ERR_OK) {
                $uploadResult = handleImageUpload(
                    $currentFile,
                    'uploads/products',
                    '',
                    ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                    'product_additional_' . $i . '_'
                );
                
                if ($uploadResult['success']) {
                    $new_additional_images[] = $uploadResult['filename'];
                } else {
                    $errors[] = "Additional image " . ($i + 1) . ": " . $uploadResult['error'];
                }
            }
        }
    }
    
    // Handle image deletions
    $delete_main_image = isset($_POST['delete_main_image']) && $_POST['delete_main_image'] == 1;
    $delete_additional_images = isset($_POST['delete_additional_image']) ? $_POST['delete_additional_image'] : [];
    
    if ($delete_main_image && empty($_FILES['image']['name'])) {
        $uploadDir = dirname(__DIR__) . '/uploads/products/';
        if (!empty($product['image']) && file_exists($uploadDir . '/' . $product['image'])) {
            unlink($uploadDir . '/' . $product['image']);
        }
        $image = '';
    }
    
    // If no errors, update product
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update product
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, sale_price = ?, quantity = ?, category_id = ?, status = ?, featured = ?, sku = ?, weight = ?, dimensions = ?, image = ?, meta_title = ?, meta_description = ?, meta_keywords = ? WHERE product_id = ?");
            $stmt->bind_param("ssddiisisdsssssi", $name, $description, $price, $sale_price, $quantity, $category_id, $status, $featured, $sku, $weight, $dimensions, $image, $meta_title, $meta_description, $meta_keywords, $productId);
            $stmt->execute();
            
            // Delete selected additional images
            if (!empty($delete_additional_images)) {
                $uploadDir = dirname(__DIR__) . '/uploads/products/';
                foreach ($delete_additional_images as $imageId) {
                    // Get image path
                    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE image_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $imageId, $productId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $imagePath = $result->fetch_assoc()['image_path'];
                        
                        // Delete file
                        if (!empty($imagePath) && file_exists($uploadDir . $imagePath)) {
                            unlink($uploadDir . $imagePath);
                        }
                        
                        // Delete from database
                        $stmt = $conn->prepare("DELETE FROM product_images WHERE image_id = ? AND product_id = ?");
                        $stmt->bind_param("ii", $imageId, $productId);
                        $stmt->execute();
                    }
                }
            }
            
            // Insert new additional images if any
            if (!empty($new_additional_images)) {
                $additionalImagesStmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                
                foreach ($new_additional_images as $img) {
                    $additionalImagesStmt->bind_param("is", $productId, $img);
                    $additionalImagesStmt->execute();
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            flash('success', 'Product updated successfully.');
            redirect('admin/products.php');
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Product: <?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="products.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
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
            
            <?php flash(); ?>
            
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST" action="edit-product.php?id=<?php echo $productId; ?>" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="price" class="form-label">Regular Price <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="sale_price" class="form-label">Sale Price</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['sale_price'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="sku" class="form-label">SKU</label>
                                                <input type="text" class="form-control" id="sku" name="sku" value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Additional Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="weight" class="form-label">Weight (kg)</label>
                                                <input type="number" class="form-control" id="weight" name="weight" step="0.01" min="0" value="<?php echo htmlspecialchars($product['weight'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="dimensions" class="form-label">Dimensions (L x W x H)</label>
                                                <input type="text" class="form-control" id="dimensions" name="dimensions" placeholder="e.g., 10 x 5 x 3 cm" value="<?php echo htmlspecialchars($product['dimensions'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">SEO Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="meta_title" class="form-label">Meta Title</label>
                                            <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($product['meta_title'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="meta_description" class="form-label">Meta Description</label>
                                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?php echo htmlspecialchars($product['meta_description'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" placeholder="keyword1, keyword2, keyword3" value="<?php echo htmlspecialchars($product['meta_keywords'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Product Status</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" <?php echo $product['featured'] == 1 ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="featured">
                                                    Featured Product
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Category</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Select Category</label>
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="0" <?php echo is_null($product['category_id']) ? 'selected' : ''; ?>>-- No Category --</option>
                                                <?php if (empty($categories)): ?>
                                                <option disabled>No categories available. Please create categories first.</option>
                                                <?php else: ?>
                                                <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['category_id']; ?>" <?php echo $product['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <?php if (empty($categories)): ?>
                                            <div class="form-text text-danger">Warning: No categories found. <a href="categories.php">Create categories</a> before editing products.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Product Images</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="image" class="form-label">Main Product Image</label>
                                            <?php if (!empty($product['image'])): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo getImageUrl($product['image'], 'uploads/products'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-thumbnail" style="max-height: 150px;">
                                                <div class="form-check mt-1">
                                                    <input class="form-check-input" type="checkbox" id="delete_main_image" name="delete_main_image" value="1">
                                                    <label class="form-check-label" for="delete_main_image">Delete this image</label>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                            <small class="form-text text-muted">Recommended size: 800x800 pixels</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="additional_images" class="form-label">Additional Images</label>
                                            <?php if (!empty($additionalImages)): ?>
                                            <div class="row mb-3">
                                                <?php foreach ($additionalImages as $img): ?>
                                                <div class="col-md-3 mb-2">
                                                    <div class="card">
                                                        <img src="<?php echo getImageUrl($img['image_path'], 'uploads/products'); ?>" alt="Additional Image" class="card-img-top" style="height: 120px; object-fit: cover;">
                                                        <div class="card-body p-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" id="delete_additional_image_<?php echo $img['image_id']; ?>" name="delete_additional_image[]" value="<?php echo $img['image_id']; ?>">
                                                                <label class="form-check-label" for="delete_additional_image_<?php echo $img['image_id']; ?>">Delete</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="additional_images" name="additional_images[]" accept="image/*" multiple>
                                            <small class="form-text text-muted">You can select multiple files. Recommended size: 800x800 pixels</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="products.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize rich text editor for description
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    }
    
    // Preview image before upload
    const imageInput = document.getElementById('image');
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'img-thumbnail mt-2';
                preview.style.maxHeight = '200px';
                
                const previewContainer = imageInput.parentElement;
                const existingPreview = previewContainer.querySelector('img.preview');
                if (existingPreview) {
                    previewContainer.removeChild(existingPreview);
                }
                
                // Add preview class to distinguish from existing product image
                preview.classList.add('preview');
                
                // Insert after the input
                imageInput.insertAdjacentElement('afterend', preview);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Handle delete main image checkbox
    const deleteMainImageCheckbox = document.getElementById('delete_main_image');
    if (deleteMainImageCheckbox) {
        deleteMainImageCheckbox.addEventListener('change', function() {
            const imageInput = document.getElementById('image');
            if (this.checked) {
                imageInput.setAttribute('required', 'required');
            } else {
                imageInput.removeAttribute('required');
            }
        });
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?> 