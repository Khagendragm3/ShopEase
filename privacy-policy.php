<?php
$pageTitle = "Privacy Policy";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get privacy policy content from settings
$privacy_policy_content = getSetting('privacy_policy_content');
$privacy_policy_updated = getSetting('privacy_policy_updated', date('F d, Y'));

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-4">Privacy Policy</h1>
                    
                    <div class="mb-4">
                        <p class="text-muted">Last Updated: <?php echo htmlspecialchars($privacy_policy_updated); ?></p>
                    </div>
                    
                    <div class="privacy-policy-content">
                        <?php 
                        if (!empty($privacy_policy_content)) {
                            echo $privacy_policy_content; 
                        } else {
                            echo '<div class="alert alert-info">Privacy policy content has not been set up yet.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 