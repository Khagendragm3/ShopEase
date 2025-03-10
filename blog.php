<?php
$pageTitle = "Blog";
include 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Number of posts per page
$offset = ($page - 1) * $limit;

// Get total number of published blog posts
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published'");
$stmt->execute();
$result = $stmt->get_result();
$totalPosts = $result->fetch_assoc()['total'];
$totalPages = ceil($totalPosts / $limit);

// Get blog posts for current page
$stmt = $conn->prepare("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY publish_date DESC LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

$blogPosts = [];
while ($row = $result->fetch_assoc()) {
    $blogPosts[] = $row;
}
?>

<!-- Blog Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Our Blog</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Blog</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Blog Posts -->
<section class="blog-section">
    <div class="container">
        <div class="row">
            <?php if (empty($blogPosts)): ?>
                <div class="col-12 text-center">
                    <p>No blog posts found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($blogPosts as $post): 
                    // Format date for display
                    $date = new DateTime($post['publish_date']);
                    $day = $date->format('d');
                    $month = $date->format('M');
                ?>
                <div class="col-md-4 mb-4">
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="row">
            <div class="col-12">
                <nav aria-label="Page navigation">
                    <?php echo generatePagination($page, $totalPages, 'blog.php'); ?>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 