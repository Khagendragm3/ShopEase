<?php
$pageTitle = "Manage Testimonials";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Create testimonials table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `position` varchar(100) NOT NULL,
    `content` text NOT NULL,
    `image` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$conn->query($sql);

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get testimonial image
    $stmt = $conn->prepare("SELECT image FROM testimonials WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $testimonial = $result->fetch_assoc();
        
        // Delete image file if exists
        if (!empty($testimonial['image'])) {
            $imagePath = dirname(__DIR__) . '/uploads/testimonials/' . $testimonial['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete testimonial from database
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            flash('success', 'Testimonial deleted successfully.');
        } else {
            flash('error', 'Failed to delete testimonial.');
        }
    } else {
        flash('error', 'Testimonial not found.');
    }
    
    redirect('testimonials.php');
}

// Handle status toggle
if (isset($_GET['toggle']) && !empty($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    
    // Get current status
    $stmt = $conn->prepare("SELECT status FROM testimonials WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $testimonial = $result->fetch_assoc();
        $newStatus = ($testimonial['status'] == 'active') ? 'inactive' : 'active';
        
        // Update status
        $stmt = $conn->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $id);
        
        if ($stmt->execute()) {
            flash('success', 'Testimonial status updated successfully.');
        } else {
            flash('error', 'Failed to update testimonial status.');
        }
    } else {
        flash('error', 'Testimonial not found.');
    }
    
    redirect('testimonials.php');
}

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitize($_POST['name']);
    $position = sanitize($_POST['position']);
    $content = sanitize($_POST['content']);
    $status = sanitize($_POST['status']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($position)) {
        $errors[] = 'Position is required.';
    }
    
    if (empty($content)) {
        $errors[] = 'Testimonial content is required.';
    }
    
    // If no errors, proceed with database operation
    if (empty($errors)) {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Get existing image if editing
            if ($id > 0) {
                $stmt = $conn->prepare("SELECT image FROM testimonials WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $oldImage = $result->fetch_assoc()['image'];
                }
            }
            
            $uploadResult = handleImageUpload(
                $_FILES['image'],
                'uploads/testimonials',
                $oldImage ?? '',
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'testimonial_'
            );
            
            if ($uploadResult['success']) {
                $image = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        } elseif ($id > 0) {
            // Keep existing image if editing and no new image uploaded
            $stmt = $conn->prepare("SELECT image FROM testimonials WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $image = $result->fetch_assoc()['image'];
            }
        }
        
        if (empty($errors)) {
            if ($id > 0) {
                // Update existing testimonial
                $stmt = $conn->prepare("UPDATE testimonials SET name = ?, position = ?, content = ?, image = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $name, $position, $content, $image, $status, $id);
                $successMessage = 'Testimonial updated successfully.';
            } else {
                // Add new testimonial
                $stmt = $conn->prepare("INSERT INTO testimonials (name, position, content, image, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $position, $content, $image, $status);
                $successMessage = 'Testimonial added successfully.';
            }
            
            if ($stmt->execute()) {
                flash('success', $successMessage);
                redirect('testimonials.php');
            } else {
                $errors[] = 'Database error: ' . $conn->error;
            }
        }
    }
}

// Get testimonial for editing
$testimonial = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    // If edit=new, we're adding a new testimonial, so no need to fetch from database
    if ($_GET['edit'] === 'new') {
        // Just leave $testimonial as null to indicate a new testimonial
    } else {
        // Otherwise, try to fetch the testimonial with the given ID
        $id = (int)$_GET['edit'];
        
        $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $testimonial = $result->fetch_assoc();
        } else {
            flash('error', 'Testimonial not found.');
            redirect('testimonials.php');
        }
    }
}

// Get all testimonials
$stmt = $conn->prepare("SELECT * FROM testimonials ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$testimonials = [];
while ($row = $result->fetch_assoc()) {
    $testimonials[] = $row;
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Testimonials</h1>
                <a href="testimonials.php?edit=new" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testimonialModal">
                    <i class="fas fa-plus"></i> Add New Testimonial
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
            
            <!-- Testimonials Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="15%">Image</th>
                                    <th width="15%">Name</th>
                                    <th width="15%">Position</th>
                                    <th width="30%">Content</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($testimonials)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No testimonials found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($testimonials as $item): ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td>
                                            <?php if (!empty($item['image'])): ?>
                                            <img src="<?php echo getImageUrl($item['image'], 'uploads/testimonials'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-thumbnail" style="max-height: 80px;">
                                            <?php else: ?>
                                            <img src="<?php echo URL_ROOT; ?>/assets/img/no-image.jpg" alt="No Image" class="img-thumbnail" style="max-height: 80px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['position']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($item['content'], 0, 100)) . (strlen($item['content']) > 100 ? '...' : ''); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $item['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="testimonials.php?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#testimonialModal" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-position="<?php echo htmlspecialchars($item['position']); ?>" data-content="<?php echo htmlspecialchars($item['content']); ?>" data-status="<?php echo $item['status']; ?>" data-image="<?php echo !empty($item['image']) ? getImageUrl($item['image'], 'uploads/testimonials') : ''; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="testimonials.php?toggle=<?php echo $item['id']; ?>" class="btn btn-sm btn-<?php echo $item['status'] == 'active' ? 'warning' : 'success'; ?>" title="<?php echo $item['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $item['status'] == 'active' ? 'times' : 'check'; ?>"></i>
                                                </a>
                                                <a href="testimonials.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this testimonial?');">
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

<!-- Testimonial Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1" aria-labelledby="testimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="testimonials.php" enctype="multipart/form-data">
                <input type="hidden" name="id" id="testimonial_id" value="<?php echo $testimonial ? $testimonial['id'] : ''; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="testimonialModalLabel"><?php echo $testimonial ? 'Edit' : 'Add'; ?> Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $testimonial ? htmlspecialchars($testimonial['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="position" name="position" value="<?php echo $testimonial ? htmlspecialchars($testimonial['position']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo ($testimonial && $testimonial['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($testimonial && $testimonial['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <?php if ($testimonial && !empty($testimonial['image'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo getImageUrl($testimonial['image'], 'uploads/testimonials'); ?>" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="form-text text-muted">Recommended size: 100x100 pixels</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="content" class="form-label">Testimonial Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo $testimonial ? htmlspecialchars($testimonial['content']) : ''; ?></textarea>
                            </div>
                        </div>
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
    const testimonialModal = document.getElementById('testimonialModal');
    if (testimonialModal) {
        testimonialModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // If button has data attributes, it's an edit operation
            if (button.hasAttribute('data-id')) {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const position = button.getAttribute('data-position');
                const content = button.getAttribute('data-content');
                const status = button.getAttribute('data-status');
                
                // Update modal title
                const modalTitle = this.querySelector('.modal-title');
                modalTitle.textContent = 'Edit Testimonial';
                
                // Fill form fields
                this.querySelector('#testimonial_id').value = id;
                this.querySelector('#name').value = name;
                this.querySelector('#position').value = position;
                this.querySelector('#content').value = content;
                this.querySelector('#status').value = status;
            } else {
                // Reset form for add operation
                const modalTitle = this.querySelector('.modal-title');
                modalTitle.textContent = 'Add Testimonial';
                
                this.querySelector('#testimonial_id').value = '';
                this.querySelector('#name').value = '';
                this.querySelector('#position').value = '';
                this.querySelector('#content').value = '';
                this.querySelector('#status').value = 'active';
                
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