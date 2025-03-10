<?php
$pageTitle = "Manage Categories";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Check if meta columns exist in the categories table
$metaColumnsExist = true;
$checkMetaTitle = $conn->query("SHOW COLUMNS FROM categories LIKE 'meta_title'");
if ($checkMetaTitle->num_rows == 0) {
    $metaColumnsExist = false;
}

// Handle category deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $categoryId = sanitize($_GET['delete']);
    
    // Check if category exists
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Check if category has products
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $productCount = $result->fetch_assoc()['total'];
        
        if ($productCount > 0) {
            flash('error', 'Cannot delete category. It has ' . $productCount . ' products associated with it.');
        } else {
            // Delete category
            $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->bind_param("i", $categoryId);
            
            if ($stmt->execute()) {
                flash('success', 'Category deleted successfully.');
            } else {
                flash('error', 'Failed to delete category.');
            }
        }
    } else {
        flash('error', 'Category not found.');
        redirect('admin/categories.php');
    }
    
    redirect('admin/categories.php');
}

// Handle category add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $status = sanitize($_POST['status']);
    $parent_id = !empty($_POST['parent_id']) ? (int) sanitize($_POST['parent_id']) : null;
    
    // Only set meta values if the columns exist
    $meta_title = $metaColumnsExist ? sanitize($_POST['meta_title']) : '';
    $meta_description = $metaColumnsExist ? sanitize($_POST['meta_description']) : '';
    $meta_keywords = $metaColumnsExist ? sanitize($_POST['meta_keywords']) : '';
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    
    // Check if category with same name already exists
    $stmt = $conn->prepare("SELECT category_id FROM categories WHERE name = ? AND category_id != ?");
    $categoryId = isset($_POST['category_id']) ? (int) sanitize($_POST['category_id']) : 0;
    $stmt->bind_param("si", $name, $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "A category with this name already exists.";
    }
    
    // Process image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload(
            $_FILES['image'],
            'uploads/categories',
            isset($_POST['category_id']) && !empty($_POST['category_id']) ? $currentCategory['image'] ?? '' : '',
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'category_'
        );
        
        if ($uploadResult['success']) {
            $image = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }
    
    if (empty($errors)) {
        if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            // Update existing category
            $categoryId = (int) sanitize($_POST['category_id']);
            
            // Get current category data
            $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
            $stmt->bind_param("i", $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $currentCategory = $result->fetch_assoc();
                
                // Keep existing image if no new one uploaded
                if (empty($image)) {
                    $image = $currentCategory['image'];
                }
                
                // Update category
                if ($metaColumnsExist) {
                    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, status = ?, parent_id = ?, image = ?, meta_title = ?, meta_description = ?, meta_keywords = ? WHERE category_id = ?");
                    $stmt->bind_param("sssississi", $name, $description, $status, $parent_id, $image, $meta_title, $meta_description, $meta_keywords, $categoryId);
                } else {
                    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, status = ?, parent_id = ?, image = ? WHERE category_id = ?");
                    $stmt->bind_param("sssisi", $name, $description, $status, $parent_id, $image, $categoryId);
                }
                
                if ($stmt->execute()) {
                    flash('success', 'Category updated successfully.');
                    redirect('admin/categories.php');
                } else {
                    $errors[] = "Failed to update category.";
                }
            } else {
                $errors[] = "Category not found.";
            }
        } else {
            // Add new category
            if ($metaColumnsExist) {
                $stmt = $conn->prepare("INSERT INTO categories (name, description, status, parent_id, image, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssissss", $name, $description, $status, $parent_id, $image, $meta_title, $meta_description, $meta_keywords);
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (name, description, status, parent_id, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssis", $name, $description, $status, $parent_id, $image);
            }
            
            if ($stmt->execute()) {
                flash('success', 'Category added successfully.');
                redirect('admin/categories.php');
            } else {
                $errors[] = "Failed to add category.";
            }
        }
    }
}

// Get category to edit if ID is provided
$categoryToEdit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $categoryId = (int) sanitize($_GET['edit']);
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $categoryToEdit = $result->fetch_assoc();
    } else {
        flash('error', 'Category not found.');
        redirect('admin/categories.php');
    }
}

// Get all categories
$stmt = $conn->prepare("SELECT c.*, p.name as parent_name, 
                        (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count 
                        FROM categories c 
                        LEFT JOIN categories p ON c.parent_id = p.category_id 
                        ORDER BY c.name ASC");
$stmt->execute();
$result = $stmt->get_result();
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Get parent categories for dropdown (exclude the category being edited)
$parentCategories = [];
foreach ($categories as $category) {
    if (!isset($categoryToEdit) || $category['category_id'] != $categoryToEdit['category_id']) {
        $parentCategories[] = $category;
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
                <h1 class="h2"><?php echo isset($categoryToEdit) ? 'Edit Category' : 'Manage Categories'; ?></h1>
                <?php if (!isset($categoryToEdit)): ?>
                 <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if (!$metaColumnsExist): ?>
                    <a href="fix-categories-table.php" class="btn btn-sm btn-warning me-2">
                        <i class="fas fa-wrench"></i> Fix Database Structure
                    </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                         <i class="fas fa-plus"></i> Add New Category
                    </button>
                 </div>
                <?php endif; ?>
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
            
            <?php if (!$metaColumnsExist): ?>
            <div class="alert alert-warning">
                <strong>Warning:</strong> The database structure is missing required columns for SEO metadata. 
                <a href="fix-categories-table.php" class="alert-link">Click here to fix the database structure</a>.
            </div>
            <?php endif; ?>
            
            <?php if (isset($categoryToEdit)): ?>
                <!-- Edit Category Form -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="POST" action="categories.php" enctype="multipart/form-data">
                            <input type="hidden" name="category_id" value="<?php echo $categoryToEdit['category_id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($categoryToEdit['name']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="parent_id" class="form-label">Parent Category</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="">None (Top Level)</option>
                                        <?php foreach ($parentCategories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>" <?php echo ($categoryToEdit['parent_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($categoryToEdit['description']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo ($categoryToEdit['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($categoryToEdit['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="image" class="form-label">Category Image</label>
                                    <?php if (!empty($categoryToEdit['image'])): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo getImageUrl($categoryToEdit['image'], 'uploads/categories'); ?>" alt="<?php echo htmlspecialchars($categoryToEdit['name']); ?>" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">SEO Information</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!$metaColumnsExist): ?>
                                    <div class="alert alert-warning">
                                        <strong>Note:</strong> SEO fields are not available because the database structure is missing required columns.
                                        <a href="fix-categories-table.php" class="alert-link">Click here to fix the database structure</a>.
                                    </div>
                                    <?php else: ?>
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($categoryToEdit['meta_title'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?php echo htmlspecialchars($categoryToEdit['meta_description'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" placeholder="keyword1, keyword2, keyword3" value="<?php echo htmlspecialchars($categoryToEdit['meta_keywords'] ?? ''); ?>">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary me-2">Update Category</button>
                                <a href="categories.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Categories
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Categories Table -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="categoriesTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Parent</th>
                                        <th>Products</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No categories found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['category_id']; ?></td>
                                            <td>
                                                <?php if (!empty($category['image'])): ?>
                                                <img src="<?php echo getImageUrl($category['image'], 'uploads/categories'); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="img-thumbnail" style="max-height: 50px;">
                                                <?php else: ?>
                                                <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($category['parent_name'] ?? 'None'); ?></td>
                                            <td><?php echo $category['product_count']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $category['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($category['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="categories.php?edit=<?php echo $category['category_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="categories.php?delete=<?php echo $category['category_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Add Category Modal -->
                <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="categories.php" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="parent_id" class="form-label">Parent Category</label>
                                            <select class="form-select" id="parent_id" name="parent_id">
                                                <option value="">None (Top Level)</option>
                                                <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['category_id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="image" class="form-label">Category Image</label>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        </div>
                                    </div>
                                    
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h5 class="mb-0">SEO Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (!$metaColumnsExist): ?>
                                            <div class="alert alert-warning">
                                                <strong>Note:</strong> SEO fields are not available because the database structure is missing required columns.
                                                <a href="fix-categories-table.php" class="alert-link">Click here to fix the database structure</a>.
                                            </div>
                                            <?php else: ?>
                                            <div class="mb-3">
                                                <label for="meta_title" class="form-label">Meta Title</label>
                                                <input type="text" class="form-control" id="meta_title" name="meta_title">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="meta_description" class="form-label">Meta Description</label>
                                                <textarea class="form-control" id="meta_description" name="meta_description" rows="2"></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" placeholder="keyword1, keyword2, keyword3">
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Add Category</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#categoriesTable').DataTable({
            order: [[0, 'asc']]
        });
    }
    
    // Preview image before upload
    const imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'img-thumbnail mt-2';
                    preview.style.maxHeight = '100px';
                    
                    const previewContainer = input.parentElement;
                    const existingPreview = previewContainer.querySelector('img.preview');
                    if (existingPreview) {
                        previewContainer.removeChild(existingPreview);
                    }
                    
                    // Add preview class to distinguish from existing category image
                    preview.classList.add('preview');
                    
                    // Insert after the input
                    input.insertAdjacentElement('afterend', preview);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?> 