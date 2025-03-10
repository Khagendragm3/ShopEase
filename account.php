<?php
$pageTitle = "My Account";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to view this page.');
    redirect('login.php');
}

// Get user data
$user = getUserById($_SESSION['user_id']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zipCode = sanitize($_POST['zip_code']);
    $country = sanitize($_POST['country']);
    $phone = sanitize($_POST['phone']);
    
    // Validate input
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastName)) {
        $errors[] = "Last name is required";
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        if (updateUserProfile($_SESSION['user_id'], $firstName, $lastName, $address, $city, $state, $zipCode, $country, $phone)) {
            flash('success', 'Profile updated successfully.');
            redirect('account.php');
        } else {
            flash('error', 'Failed to update profile. Please try again.');
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
                        <a href="<?php echo URL_ROOT; ?>/account.php" class="account-menu-link active">
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
                        <a href="<?php echo URL_ROOT; ?>/change-password.php" class="account-menu-link">
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
                <h2 class="account-title">My Profile</h2>
                
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo $user['address']; ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo $user['city']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="state" name="state" value="<?php echo $user['state']; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="zip_code" class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo $user['zip_code']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="<?php echo $user['country']; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 