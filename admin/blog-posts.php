<?php
$pageTitle = "Manage Blog Posts";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Create blog_posts table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `excerpt` text NOT NULL,
    `content` text NOT NULL,
    `image` varchar(255) DEFAULT NULL,
    `publish_date` date NOT NULL,
    `status` enum('published','draft') NOT NULL DEFAULT 'published',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$conn->query($sql);

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get blog post image
    $stmt = $conn->prepare("SELECT image FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        
        // Delete image file if exists
        if (!empty($post['image'])) {
            $imagePath = dirname(__DIR__) . '/uploads/blog/' . $post['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete blog post from database
        $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            flash('success', 'Blog post deleted successfully.');
        } else {
            flash('error', 'Failed to delete blog post.');
        }
    } else {
        flash('error', 'Blog post not found.');
    }
    
    // Use explicit redirect
    header('Location: ' . URL_ROOT . '/admin/blog-posts.php');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle']) && !empty($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    
    // Get current status
    $stmt = $conn->prepare("SELECT status FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        $newStatus = ($post['status'] == 'published') ? 'draft' : 'published';
        
        // Update status
        $stmt = $conn->prepare("UPDATE blog_posts SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $id);
        
        if ($stmt->execute()) {
            flash('success', 'Blog post status updated successfully.');
        } else {
            flash('error', 'Failed to update blog post status.');
        }
    } else {
        flash('error', 'Blog post not found.');
    }
    
    // Use explicit redirect
    header('Location: ' . URL_ROOT . '/admin/blog-posts.php');
    exit;
}

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug information
    error_log('POST request received in blog-posts.php');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = sanitize($_POST['title'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? ''; // Don't sanitize to allow HTML
    $publish_date = sanitize($_POST['publish_date'] ?? '');
    $status = sanitize($_POST['status'] ?? 'draft');
    
    error_log('Processed form data:');
    error_log('ID: ' . $id);
    error_log('Title: ' . $title);
    error_log('Excerpt length: ' . strlen($excerpt));
    error_log('Content length: ' . strlen($content));
    error_log('Publish date: ' . $publish_date);
    error_log('Status: ' . $status);
    
    // Validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    
    if (empty($excerpt)) {
        $errors[] = 'Excerpt is required.';
    }
    
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    
    if (empty($publish_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $publish_date)) {
        $errors[] = 'Valid publish date is required (YYYY-MM-DD).';
    }
    
    if (!empty($errors)) {
        error_log('Validation errors: ' . print_r($errors, true));
    }
    
    // If no errors, proceed with database operation
    if (empty($errors)) {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Get existing image if editing
            if ($id > 0) {
                $stmt = $conn->prepare("SELECT image FROM blog_posts WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $oldImage = $result->fetch_assoc()['image'];
                }
            }
            
            $uploadResult = handleImageUpload(
                $_FILES['image'],
                'uploads/blog',
                $oldImage ?? '',
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'blog_'
            );
            
            if ($uploadResult['success']) {
                $image = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        } elseif ($id > 0) {
            // Keep existing image if editing and no new image uploaded
            $stmt = $conn->prepare("SELECT image FROM blog_posts WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $image = $result->fetch_assoc()['image'];
            }
        }
        
        if (empty($errors)) {
            if ($id > 0) {
                // Update existing blog post
                error_log("Updating existing blog post with ID: $id");
                $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, excerpt = ?, content = ?, image = ?, publish_date = ?, status = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $title, $excerpt, $content, $image, $publish_date, $status, $id);
                $successMessage = 'Blog post updated successfully.';
            } else {
                // Add new blog post
                error_log("Adding new blog post");
                $stmt = $conn->prepare("INSERT INTO blog_posts (title, excerpt, content, image, publish_date, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $title, $excerpt, $content, $image, $publish_date, $status);
                $successMessage = 'Blog post added successfully.';
            }
            
            if ($stmt->execute()) {
                error_log("Database operation successful: $successMessage");
                flash('success', $successMessage);
                header('Location: ' . URL_ROOT . '/admin/blog-posts.php');
                exit;
            } else {
                error_log("Database error: " . $conn->error);
                $errors[] = 'Database error: ' . $conn->error;
            }
        }
    }
}

// Get blog post for editing
$post = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    // If edit=new, we're adding a new post, so no need to fetch from database
    if ($_GET['edit'] === 'new') {
        // Just leave $post as null to indicate a new post
    } else {
        // Otherwise, try to fetch the post with the given ID
        $id = (int)$_GET['edit'];
        
        $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $post = $result->fetch_assoc();
        } else {
            flash('error', 'Blog post not found.');
            // Use explicit redirect
            header('Location: ' . URL_ROOT . '/admin/blog-posts.php');
            exit;
        }
    }
}

// Get all blog posts
$stmt = $conn->prepare("SELECT * FROM blog_posts ORDER BY publish_date DESC");
$stmt->execute();
$result = $stmt->get_result();
$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Blog Posts</h1>
                <a href="blog-posts.php?edit=new" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Post
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
            
            <?php if (isset($_GET['edit'])): ?>
                <!-- Add/Edit Blog Post Form -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><?php echo $post ? 'Edit' : 'Add New'; ?> Blog Post</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo URL_ROOT; ?>/admin/blog-posts.php" enctype="multipart/form-data" id="blogPostForm">
                            <?php if ($post): ?>
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $post ? htmlspecialchars($post['title']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Excerpt <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required><?php echo $post ? htmlspecialchars($post['excerpt']) : ''; ?></textarea>
                                        <small class="form-text text-muted">A short summary of the blog post (displayed on the homepage)</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo $post ? $post['content'] : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Publish</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="publish_date" class="form-label">Publish Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="publish_date" name="publish_date" value="<?php echo $post ? $post['publish_date'] : date('Y-m-d'); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="published" <?php echo ($post && $post['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                                    <option value="draft" <?php echo ($post && $post['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">Save</button>
                                            <button type="button" id="debugSave" class="btn btn-info">Debug Save</button>
                                            <button type="button" id="manualSave" class="btn btn-warning">Manual Save</button>
                                            <button type="button" id="directSave" class="btn btn-danger">Direct Save</button>
                                            <a href="blog-posts.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </div>
                                    
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Featured Image</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php if ($post && !empty($post['image'])): ?>
                                                <div class="mb-3">
                                                    <img src="<?php echo getImageUrl($post['image'], 'uploads/blog'); ?>" alt="Featured Image" class="img-fluid img-thumbnail">
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <label for="image" class="form-label">Upload Image</label>
                                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 800x500 pixels</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Blog Posts Table -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="15%">Image</th>
                                        <th width="30%">Title</th>
                                        <th width="20%">Excerpt</th>
                                        <th width="10%">Date</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No blog posts found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($posts as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id']; ?></td>
                                            <td>
                                                <?php if (!empty($item['image'])): ?>
                                                <img src="<?php echo getImageUrl($item['image'], 'uploads/blog'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-thumbnail" style="max-height: 80px;">
                                                <?php else: ?>
                                                <img src="<?php echo URL_ROOT; ?>/assets/img/no-image.jpg" alt="No Image" class="img-thumbnail" style="max-height: 80px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($item['excerpt'], 0, 100)) . (strlen($item['excerpt']) > 100 ? '...' : ''); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($item['publish_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $item['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="blog-posts.php?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="blog-posts.php?toggle=<?php echo $item['id']; ?>" class="btn btn-sm btn-<?php echo $item['status'] == 'published' ? 'warning' : 'success'; ?>" title="<?php echo $item['status'] == 'published' ? 'Set to Draft' : 'Publish'; ?>">
                                                        <i class="fas fa-<?php echo $item['status'] == 'published' ? 'times' : 'check'; ?>"></i>
                                                    </a>
                                                    <a href="blog-posts.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this blog post?');">
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
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/braohodt2kbyl43wj4uh47219bypwuk2q1tzvdmetj3q8050/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing TinyMCE...');
    
    // Initialize TinyMCE
    if (document.getElementById('content')) {
        console.log('Content textarea found, initializing editor...');
        tinymce.init({
            selector: '#content',
            height: 500,
            menubar: true,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount',
                'emoticons template paste textcolor colorpicker textpattern imagetools'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | link image media | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            image_advtab: true,
            image_caption: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            file_picker_callback: function (cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                input.onchange = function () {
                    var file = this.files[0];

                    var reader = new FileReader();
                    reader.onload = function () {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);

                        cb(blobInfo.blobUri(), { title: file.name });
                    };
                    reader.readAsDataURL(file);
                };

                input.click();
            },
            setup: function(editor) {
                console.log('TinyMCE editor setup...');
                
                editor.on('init', function() {
                    console.log('TinyMCE initialized successfully');
                });
                
                editor.on('change', function() {
                    console.log('TinyMCE content changed, saving to textarea...');
                    editor.save(); // Save content to textarea
                    
                    // Verify content was saved
                    const contentTextarea = document.getElementById('content');
                    if (contentTextarea) {
                        console.log(`Content textarea value length after change: ${contentTextarea.value.length}`);
                    }
                });
            }
        });
    } else {
        console.log('Content textarea not found');
    }
    
    // Add form submit handler to ensure TinyMCE content is saved
    const blogForm = document.getElementById('blogPostForm');
    if (blogForm) {
        blogForm.addEventListener('submit', function(e) {
            console.log('Form submission triggered');
            
            // Debug the form data
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value.substring ? (value.length > 100 ? value.substring(0, 100) + '...' : value) : 'File or other data type'}`);
            }
            
            // Make sure TinyMCE content is saved to the textarea before submission
            if (tinymce.activeEditor) {
                console.log('TinyMCE editor found, saving content...');
                tinymce.activeEditor.save();
                
                // Verify content was saved to textarea
                const contentTextarea = document.getElementById('content');
                if (contentTextarea) {
                    console.log(`Content textarea value length: ${contentTextarea.value.length}`);
                }
            } else {
                console.log('No TinyMCE editor found');
            }
        });
    } else {
        console.log('Blog form not found');
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
                    preview.style.maxHeight = '200px';
                    
                    previewContainer.appendChild(preview);
                    imageInput.insertAdjacentElement('afterend', previewContainer);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Debug button to manually trigger TinyMCE save
    const debugButton = document.getElementById('debugSave');
    if (debugButton) {
        debugButton.addEventListener('click', function() {
            console.log('Debug save button clicked');
            
            if (tinymce.activeEditor) {
                console.log('Manually saving TinyMCE content...');
                tinymce.activeEditor.save();
                
                const contentTextarea = document.getElementById('content');
                if (contentTextarea) {
                    console.log(`Content textarea value after manual save: ${contentTextarea.value.length} characters`);
                    console.log('Form will be submitted now...');
                    document.getElementById('blogPostForm').submit();
                }
            } else {
                console.log('No TinyMCE editor found for debug save');
            }
        });
    }
    
    // Manual save button as a fallback
    const manualSaveButton = document.getElementById('manualSave');
    if (manualSaveButton) {
        manualSaveButton.addEventListener('click', function() {
            console.log('Manual save button clicked');
            
            // Ensure TinyMCE content is saved to textarea
            if (tinymce.activeEditor) {
                tinymce.activeEditor.save();
            }
            
            // Get form data
            const form = document.getElementById('blogPostForm');
            const formData = new FormData(form);
            
            // Log form data for debugging
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value.substring ? (value.length > 100 ? value.substring(0, 100) + '...' : value) : 'File or other data type'}`);
            }
            
            // Send form data via fetch
            fetch('<?php echo URL_ROOT; ?>/admin/blog-posts.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.text();
                }
            })
            .then(html => {
                if (html) {
                    console.log('Response received, redirecting to blog posts page');
                    window.location.href = '<?php echo URL_ROOT; ?>/admin/blog-posts.php';
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                alert('Error saving blog post. Please check console for details.');
            });
        });
    }
    
    // Direct save button that bypasses TinyMCE completely
    const directSaveButton = document.getElementById('directSave');
    if (directSaveButton) {
        directSaveButton.addEventListener('click', function() {
            console.log('Direct save button clicked');
            
            // Create a new form element
            const directForm = document.createElement('form');
            directForm.method = 'POST';
            directForm.action = '<?php echo URL_ROOT; ?>/admin/blog-posts.php';
            directForm.enctype = 'multipart/form-data';
            
            // Copy all form fields from the original form
            const originalForm = document.getElementById('blogPostForm');
            const formElements = originalForm.elements;
            
            for (let i = 0; i < formElements.length; i++) {
                const element = formElements[i];
                
                // Skip buttons
                if (element.type === 'button' || element.type === 'submit') {
                    continue;
                }
                
                // Handle file inputs separately
                if (element.type === 'file') {
                    // We can't copy file inputs, so we'll skip them for this direct save
                    continue;
                }
                
                // Create a clone of the element
                const clone = document.createElement('input');
                clone.type = 'hidden';
                clone.name = element.name;
                
                // For the content field, get the raw HTML from TinyMCE if available
                if (element.id === 'content' && tinymce.activeEditor) {
                    clone.value = tinymce.activeEditor.getContent();
                    console.log('Using TinyMCE content: ' + (clone.value.length > 100 ? clone.value.substring(0, 100) + '...' : clone.value));
                } else {
                    clone.value = element.value;
                }
                
                directForm.appendChild(clone);
            }
            
            // Add the form to the document and submit it
            document.body.appendChild(directForm);
            console.log('Submitting direct form...');
            directForm.submit();
        });
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?> 