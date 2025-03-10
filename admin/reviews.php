<?php
$pageTitle = "Manage Reviews";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Handle review approval
if (isset($_GET['approve']) && !empty($_GET['approve'])) {
    $reviewId = (int) sanitize($_GET['approve']);
    
    // Check if review exists
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $reviewId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Approve review
        $stmt = $conn->prepare("UPDATE reviews SET status = 'approved' WHERE review_id = ?");
        $stmt->bind_param("i", $reviewId);
        
        if ($stmt->execute()) {
            flash('success', 'Review approved successfully.');
        } else {
            flash('error', 'Failed to approve review.');
        }
    } else {
        flash('error', 'Review not found.');
    }
    
    redirect('reviews.php');
}

// Handle review rejection
if (isset($_GET['reject']) && !empty($_GET['reject'])) {
    $reviewId = (int) sanitize($_GET['reject']);
    
    // Check if review exists
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $reviewId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Reject review
        $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected' WHERE review_id = ?");
        $stmt->bind_param("i", $reviewId);
        
        if ($stmt->execute()) {
            flash('success', 'Review rejected successfully.');
        } else {
            flash('error', 'Failed to reject review.');
        }
    } else {
        flash('error', 'Review not found.');
    }
    
    redirect('reviews.php');
}

// Handle review deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $reviewId = (int) sanitize($_GET['delete']);
    
    // Check if review exists
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $reviewId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Delete review
        $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
        $stmt->bind_param("i", $reviewId);
        
        if ($stmt->execute()) {
            flash('success', 'Review deleted successfully.');
        } else {
            flash('error', 'Failed to delete review.');
        }
    } else {
        flash('error', 'Review not found.');
    }
    
    redirect('reviews.php');
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['review_ids'])) {
    $action = sanitize($_POST['bulk_action']);
    $reviewIds = $_POST['review_ids'];
    
    if (!empty($reviewIds)) {
        switch ($action) {
            case 'approve':
                $placeholders = str_repeat('?,', count($reviewIds) - 1) . '?';
                $stmt = $conn->prepare("UPDATE reviews SET status = 'approved' WHERE review_id IN ($placeholders)");
                $types = str_repeat('i', count($reviewIds));
                $stmt->bind_param($types, ...$reviewIds);
                
                if ($stmt->execute()) {
                    flash('success', count($reviewIds) . ' reviews approved successfully.');
                } else {
                    flash('error', 'Failed to approve reviews.');
                }
                break;
                
            case 'reject':
                $placeholders = str_repeat('?,', count($reviewIds) - 1) . '?';
                $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected' WHERE review_id IN ($placeholders)");
                $types = str_repeat('i', count($reviewIds));
                $stmt->bind_param($types, ...$reviewIds);
                
                if ($stmt->execute()) {
                    flash('success', count($reviewIds) . ' reviews rejected successfully.');
                } else {
                    flash('error', 'Failed to reject reviews.');
                }
                break;
                
            case 'delete':
                $placeholders = str_repeat('?,', count($reviewIds) - 1) . '?';
                $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id IN ($placeholders)");
                $types = str_repeat('i', count($reviewIds));
                $stmt->bind_param($types, ...$reviewIds);
                
                if ($stmt->execute()) {
                    flash('success', count($reviewIds) . ' reviews deleted successfully.');
                } else {
                    flash('error', 'Failed to delete reviews.');
                }
                break;
        }
    }
    
    redirect('reviews.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$rating = isset($_GET['rating']) ? (int) sanitize($_GET['rating']) : 0;

// Build query
$query = "SELECT r.*, p.name as product_name, u.username 
          FROM reviews r 
          LEFT JOIN products p ON r.product_id = p.product_id 
          LEFT JOIN users u ON r.user_id = u.user_id 
          WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total 
               FROM reviews r 
               LEFT JOIN products p ON r.product_id = p.product_id 
               LEFT JOIN users u ON r.user_id = u.user_id 
               WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (r.review_text LIKE ? OR p.name LIKE ? OR u.username LIKE ?)";
    $countQuery .= " AND (r.review_text LIKE ? OR p.name LIKE ? OR u.username LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if (!empty($status)) {
    $query .= " AND r.status = ?";
    $countQuery .= " AND r.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($rating > 0) {
    $query .= " AND r.rating = ?";
    $countQuery .= " AND r.rating = ?";
    $params[] = $rating;
    $types .= "i";
}

// Order by
$query .= " ORDER BY r.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Get reviews
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

// Get total reviews for pagination
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    // Remove the last two parameters (offset and limit) for the count query
    array_pop($params);
    array_pop($params);
    $types = substr($types, 0, -2);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalReviews = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalReviews / $limit);

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Reviews</h1>
            </div>
            
            <?php flash(); ?>
            
            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="reviews.php" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search reviews..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="rating">
                                <option value="0">All Ratings</option>
                                <option value="5" <?php echo $rating == 5 ? 'selected' : ''; ?>>5 Stars</option>
                                <option value="4" <?php echo $rating == 4 ? 'selected' : ''; ?>>4 Stars</option>
                                <option value="3" <?php echo $rating == 3 ? 'selected' : ''; ?>>3 Stars</option>
                                <option value="2" <?php echo $rating == 2 ? 'selected' : ''; ?>>2 Stars</option>
                                <option value="1" <?php echo $rating == 1 ? 'selected' : ''; ?>>1 Star</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="reviews.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Reviews Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST" action="reviews.php">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th width="5%">ID</th>
                                        <th width="15%">Product</th>
                                        <th width="10%">User</th>
                                        <th width="10%">Rating</th>
                                        <th width="25%">Review</th>
                                        <th width="10%">Date</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reviews)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No reviews found.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($reviews as $review): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="review_ids[]" value="<?php echo $review['review_id']; ?>" class="review-checkbox">
                                            </td>
                                            <td><?php echo $review['review_id']; ?></td>
                                            <td>
                                                <a href="../product.php?id=<?php echo $review['product_id']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($review['product_name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($review['username']); ?></td>
                                            <td>
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $review['rating']): ?>
                                                            <i class="fas fa-star text-warning"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star text-warning"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($review['review_text']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    switch ($review['status']) {
                                                        case 'pending':
                                                            echo 'warning';
                                                            break;
                                                        case 'approved':
                                                            echo 'success';
                                                            break;
                                                        case 'rejected':
                                                            echo 'danger';
                                                            break;
                                                        default:
                                                            echo 'secondary';
                                                    }
                                                ?>">
                                                    <?php echo ucfirst($review['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($review['status'] == 'pending' || $review['status'] == 'rejected'): ?>
                                                <a href="reviews.php?approve=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($review['status'] == 'pending' || $review['status'] == 'approved'): ?>
                                                <a href="reviews.php?reject=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-warning" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <a href="reviews.php?delete=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?');" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select name="bulk_action" class="form-select">
                                        <option value="">Bulk Actions</option>
                                        <option value="approve">Approve</option>
                                        <option value="reject">Reject</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to perform this action?');">Apply</button>
                                </div>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="col-md-6">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&rating=<?php echo $rating; ?>">Previous</a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&rating=<?php echo $rating; ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&rating=<?php echo $rating; ?>">Next</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Review Statistics -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Reviews</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-comments fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved Reviews</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE status = 'approved'");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Reviews</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE status = 'pending'");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        echo $result->fetch_assoc()['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Average Rating</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE status = 'approved'");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $avgRating = $result->fetch_assoc()['avg_rating'];
                                        echo number_format($avgRating ?? 0, 1);
                                        ?>
                                        <small class="text-muted">/ 5</small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-star fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    const selectAll = document.getElementById('select-all');
    const reviewCheckboxes = document.querySelectorAll('.review-checkbox');
    
    selectAll.addEventListener('change', function() {
        reviewCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
    
    // Update select all checkbox when individual checkboxes change
    reviewCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(reviewCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(reviewCheckboxes).some(cb => cb.checked);
            
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?> 