<?php
$pageTitle = "Returns & Refunds Policy";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get returns and refunds content from settings
$returns_refunds_content = getSetting('returns_refunds_content');
$returns_refunds_updated = getSetting('returns_refunds_updated', date('F d, Y'));

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-4">Returns & Refunds Policy</h1>
                    
                    <div class="mb-4">
                        <p class="text-muted">Last Updated: <?php echo htmlspecialchars($returns_refunds_updated); ?></p>
                    </div>
                    
                    <div class="returns-refunds-content">
                        <?php 
                        if (!empty($returns_refunds_content)) {
                            echo $returns_refunds_content; 
                        } else {
                            echo '<div class="alert alert-info">Returns and refunds policy content has not been set up yet.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 