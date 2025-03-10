<?php
$pageTitle = "Login";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        $loginResult = loginUser($email, $password);
        
        if ($loginResult === true) {
            // Set remember me cookie if checked
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $userId = $_SESSION['user_id'];
                
                // Store token in database (you would need to create a remember_tokens table)
                // For simplicity, we'll just set a cookie for now
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
            }
            
            // Redirect based on user role
            if (isAdmin()) {
                redirect('admin/index.php');
            } else {
                redirect('index.php');
            }
        } elseif ($loginResult === 'inactive') {
            $errors[] = "Your account is inactive. Please contact the administrator.";
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <h2 class="auth-title">Login to Your Account</h2>
    <p class="auth-subtitle">Welcome back! Please login to your account.</p>
    
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
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember Me</label>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
    
    <div class="text-center mt-3">
        <a href="forgot-password.php">Forgot Password?</a>
    </div>
    
    <div class="auth-footer">
        Don't have an account? <a href="register.php">Register Now</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 