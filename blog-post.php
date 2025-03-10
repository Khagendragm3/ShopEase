<?php
$pageTitle = "Blog Post";
include 'includes/header.php';

// Get blog post ID from URL
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get blog post details
$post = null;
if ($postId > 0) {
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ? AND status = 'published'");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        // Update page title with blog post title
        $pageTitle = $post['title'];
    }
}

// If post not found, redirect to home page
if (!$post) {
    flash('error', 'Blog post not found.');
    redirect('index.php');
}

// Format date for display
$date = new DateTime($post['publish_date']);
$formattedDate = $date->format('F d, Y');
?>

<!-- Blog Post Section -->
<section class="blog-post-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <article class="blog-post">
                    <header class="blog-post-header">
                        <h1 class="blog-post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <div class="blog-post-meta">
                            <span class="blog-post-date"><i class="far fa-calendar-alt"></i> <?php echo $formattedDate; ?></span>
                        </div>
                    </header>
                    
                    <?php if (!empty($post['image'])): ?>
                    <div class="blog-post-image">
                        <img src="<?php echo getImageUrl($post['image'], 'uploads/blog', 'assets/images/blog-1.jpg'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid">
                    </div>
                    <?php endif; ?>
                    
                    <div class="blog-post-content">
                        <?php echo $post['content']; ?>
                    </div>
                </article>
                
                <div class="blog-post-navigation">
                    <a href="<?php echo URL_ROOT; ?>/blog.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to Blog</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 