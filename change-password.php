<?php
$pageTitle = "Change Password";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to change your password.');
    redirect('login.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    $errors = [];
    
    if (empty($currentPassword)) {
        $errors[] = "Current password is required";
    }
    
    if (empty($newPassword)) {
        $errors[] = "New password is required";
    } elseif (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters";
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New passwords do not match";
    }
    
    // If no errors, verify current password and update
    if (empty($errors)) {
        // Get user data
        $user = getUserById($_SESSION['user_id']);
        
        // Verify current password
        if (password_verify($currentPassword, $user['password'])) {
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password in database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                flash('success', 'Password updated successfully.');
                redirect('account.php');
            } else {
                flash('error', 'Failed to update password. Please try again.');
            }
        } else {
            $errors[] = "Current password is incorrect";
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <!-- Account Sidebar -->
        <div class="col-lg-3">
            <div class="account-sidebar">
                <h3>My Account</h3>
                <ul class="account-menu">
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/account.php" class="account-menu-link">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/orders.php" class="account-menu-link">
                            <i class="fas fa-shopping-bag"></i> Orders
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/wishlist.php" class="account-menu-link">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/change-password.php" class="account-menu-link active">
                            <i class="fas fa-lock"></i> Change Password
                        </a>
                    </li>
                    <li class="account-menu-item">
                        <a href="<?php echo URL_ROOT; ?>/logout.php" class="account-menu-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Account Content -->
        <div class="col-lg-9">
            <div class="account-content">
                <h2 class="account-title">Change Password</h2>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="text-muted">Password must be at least 6 characters long.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 