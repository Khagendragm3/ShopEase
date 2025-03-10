<?php
$pageTitle = "Manage Brands";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Create brands table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `brands` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `image` varchar(255) NOT NULL,
    `url` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `display_order` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$conn->query($sql);

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get brand image
    $stmt = $conn->prepare("SELECT image FROM brands WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $brand = $result->fetch_assoc();
        
        // Delete image file if exists
        if (!empty($brand['image'])) {
            $imagePath = dirname(__DIR__) . '/uploads/brands/' . $brand['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete brand from database
        $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            flash('success', 'Brand deleted successfully.');
        } else {
            flash('error', 'Failed to delete brand.');
        }
    } else {
        flash('error', 'Brand not found.');
    }
    
    redirect('brands.php');
}

// Handle status toggle
if (isset($_GET['toggle']) && !empty($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    
    // Get current status
    $stmt = $conn->prepare("SELECT status FROM brands WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $brand = $result->fetch_assoc();
        $newStatus = ($brand['status'] == 'active') ? 'inactive' : 'active';
        
        // Update status
        $stmt = $conn->prepare("UPDATE brands SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $id);
        
        if ($stmt->execute()) {
            flash('success', 'Brand status updated successfully.');
        } else {
            flash('error', 'Failed to update brand status.');
        }
    } else {
        flash('error', 'Brand not found.');
    }
    
    redirect('brands.php');
}

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitize($_POST['name']);
    $url = sanitize($_POST['url']);
    $status = sanitize($_POST['status']);
    $display_order = (int)sanitize($_POST['display_order']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Brand name is required.';
    }
    
    // If no errors, proceed with database operation
    if (empty($errors)) {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Get existing image if editing
            if ($id > 0) {
                $stmt = $conn->prepare("SELECT image FROM brands WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $oldImage = $result->fetch_assoc()['image'];
                }
            }
            
            $uploadResult = handleImageUpload(
                $_FILES['image'],
                'uploads/brands',
                $oldImage ?? '',
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
                'brand_'
            );
            
            if ($uploadResult['success']) {
                $image = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        } elseif ($id > 0) {
            // Keep existing image if editing and no new image uploaded
            $stmt = $conn->prepare("SELECT image FROM brands WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $image = $result->fetch_assoc()['image'];
            }
        } else {
            // New brand requires an image
            $errors[] = 'Brand image is required.';
        }
        
        if (empty($errors)) {
            if ($id > 0) {
                // Update existing brand
                $stmt = $conn->prepare("UPDATE brands SET name = ?, image = ?, url = ?, status = ?, display_order = ? WHERE id = ?");
                $stmt->bind_param("ssssii", $name, $image, $url, $status, $display_order, $id);
                $successMessage = 'Brand updated successfully.';
            } else {
                // Add new brand
                $stmt = $conn->prepare("INSERT INTO brands (name, image, url, status, display_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $name, $image, $url, $status, $display_order);
                $successMessage = 'Brand added successfully.';
            }
            
            if ($stmt->execute()) {
                flash('success', $successMessage);
                redirect('brands.php');
            } else {
                $errors[] = 'Database error: ' . $conn->error;
            }
        }
    }
}

// Get brand for editing
$brand = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    // If edit=new, we're adding a new brand, so no need to fetch from database
    if ($_GET['edit'] === 'new') {
        // Just leave $brand as null to indicate a new brand
    } else {
        // Otherwise, try to fetch the brand with the given ID
        $id = (int)$_GET['edit'];
        
        $stmt = $conn->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $brand = $result->fetch_assoc();
        } else {
            flash('error', 'Brand not found.');
            redirect('brands.php');
        }
    }
}

// Get all brands
$stmt = $conn->prepare("SELECT * FROM brands ORDER BY display_order, name");
$stmt->execute();
$result = $stmt->get_result();
$brands = [];
while ($row = $result->fetch_assoc()) {
    $brands[] = $row;
}

// Get next display order
$nextDisplayOrder = 0;
if (!empty($brands)) {
    $maxDisplayOrder = 0;
    foreach ($brands as $b) {
        if ($b['display_order'] > $maxDisplayOrder) {
            $maxDisplayOrder = $b['display_order'];
        }
    }
    $nextDisplayOrder = $maxDisplayOrder + 1;
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Brands</h1>
                <a href="brands.php?edit=new" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#brandModal">
                    <i class="fas fa-plus"></i> Add New Brand
                </a>
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
            
            <!-- Brands Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="20%">Image</th>
                                    <th width="20%">Name</th>
                                    <th width="20%">URL</th>
                                    <th width="10%">Order</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($brands)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No brands found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($brands as $item): ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td>
                                            <img src="<?php echo getImageUrl($item['image'], 'uploads/brands'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-thumbnail" style="max-height: 80px;">
                                        </td>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td>
                                            <?php if (!empty($item['url'])): ?>
                                            <a href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank"><?php echo htmlspecialchars($item['url']); ?></a>
                                            <?php else: ?>
                                            <span class="text-muted">No URL</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $item['display_order']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $item['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="brands.php?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#brandModal" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-url="<?php echo htmlspecialchars($item['url']); ?>" data-status="<?php echo $item['status']; ?>" data-order="<?php echo $item['display_order']; ?>" data-image="<?php echo getImageUrl($item['image'], 'uploads/brands'); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="brands.php?toggle=<?php echo $item['id']; ?>" class="btn btn-sm btn-<?php echo $item['status'] == 'active' ? 'warning' : 'success'; ?>" title="<?php echo $item['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $item['status'] == 'active' ? 'times' : 'check'; ?>"></i>
                                                </a>
                                                <a href="brands.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this brand?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Brand Modal -->
<div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="brands.php" enctype="multipart/form-data">
                <input type="hidden" name="id" id="brand_id" value="<?php echo $brand ? $brand['id'] : ''; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="brandModalLabel"><?php echo $brand ? 'Edit' : 'Add'; ?> Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $brand ? htmlspecialchars($brand['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url" class="form-label">Website URL</label>
                        <input type="url" class="form-control" id="url" name="url" value="<?php echo $brand ? htmlspecialchars($brand['url']) : ''; ?>" placeholder="https://example.com">
                        <small class="form-text text-muted">Optional: Link to the brand's website</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo ($brand && $brand['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($brand && $brand['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $brand ? $brand['display_order'] : $nextDisplayOrder; ?>" min="0">
                                <small class="form-text text-muted">Lower numbers appear first</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Brand Logo <span class="text-danger">*</span></label>
                        <?php if ($brand && !empty($brand['image'])): ?>
                        <div class="mb-2">
                            <img src="<?php echo getImageUrl($brand['image'], 'uploads/brands'); ?>" alt="Current Logo" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $brand ? '' : 'required'; ?>>
                        <small class="form-text text-muted">Recommended size: 200x100 pixels. Transparent PNG or SVG recommended.</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit modal
    const brandModal = document.getElementById('brandModal');
    if (brandModal) {
        brandModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // If button has data attributes, it's an edit operation
            if (button.hasAttribute('data-id')) {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const url = button.getAttribute('data-url');
                const status = button.getAttribute('data-status');
                const order = button.getAttribute('data-order');
                
                // Update modal title
                const modalTitle = this.querySelector('.modal-title');
                modalTitle.textContent = 'Edit Brand';
                
                // Fill form fields
                this.querySelector('#brand_id').value = id;
                this.querySelector('#name').value = name;
                this.querySelector('#url').value = url;
                this.querySelector('#status').value = status;
                this.querySelector('#display_order').value = order;
                
                // Remove required attribute from image input
                this.querySelector('#image').removeAttribute('required');
            } else {
                // Reset form for add operation
                const modalTitle = this.querySelector('.modal-title');
                modalTitle.textContent = 'Add Brand';
                
                this.querySelector('#brand_id').value = '';
                this.querySelector('#name').value = '';
                this.querySelector('#url').value = '';
                this.querySelector('#status').value = 'active';
                this.querySelector('#display_order').value = '<?php echo $nextDisplayOrder; ?>';
                
                // Add required attribute to image input
                this.querySelector('#image').setAttribute('required', 'required');
                
                // Clear image preview if exists
                const imagePreview = this.querySelector('.img-thumbnail');
                if (imagePreview) {
                    imagePreview.parentNode.remove();
                }
            }
        });
    }
    
    // Preview image before upload
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove existing preview if any
                    const existingPreview = imageInput.parentNode.querySelector('.preview-container');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Create new preview
                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'preview-container mt-2';
                    
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'img-thumbnail';
                    preview.style.maxHeight = '100px';
                    
                    previewContainer.appendChild(preview);
                    imageInput.insertAdjacentElement('afterend', previewContainer);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?> 