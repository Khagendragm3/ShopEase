<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Log out the user
logoutUser();

// Redirect to login page with success message
flash('success', 'You have been logged out successfully.');
redirect('login.php');
?> 