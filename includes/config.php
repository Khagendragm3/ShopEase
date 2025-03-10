<?php
/**
 * Database Configuration
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce');

// App Root
define('APP_ROOT', dirname(dirname(__FILE__)));
define('URL_ROOT', 'http://localhost/E-commerceWebsite');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Session start
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get settings from database
function getSetting($key, $default = '') {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['setting_value'];
    }
    
    return $default;
}

// Define site name dynamically
$siteName = getSetting('site_name', 'ShopEase');
define('SITE_NAME', $siteName);

// Helper functions
function redirect($page) {
    // Debug information
    error_log("Redirect called with page: " . $page);
    error_log("Current PHP_SELF: " . $_SERVER['PHP_SELF']);
    
    // Check if the page is a full URL or starts with http
    if (filter_var($page, FILTER_VALIDATE_URL) || strpos($page, 'http') === 0) {
        error_log("Redirecting to full URL: " . $page);
        header('Location: ' . $page);
    } 
    // Check if we're in the admin directory and the target is also in admin
    elseif (strpos($_SERVER['PHP_SELF'], '/admin/') !== false && strpos($page, 'admin/') === false && strpos($page, '../') === false) {
        // We're in admin directory but target doesn't specify admin/ prefix
        $redirectUrl = URL_ROOT . '/admin/' . $page;
        error_log("In admin directory, redirecting to: " . $redirectUrl);
        header('Location: ' . $redirectUrl);
    }
    // Check if it's an admin page
    elseif (strpos($page, 'admin/') === 0) {
        $redirectUrl = URL_ROOT . '/' . $page;
        error_log("Admin page redirect to: " . $redirectUrl);
        header('Location: ' . $redirectUrl);
    }
    // Check if it's a relative path starting with ../
    elseif (strpos($page, '../') === 0) {
        $page = substr($page, 3); // Remove the ../
        $redirectUrl = URL_ROOT . '/' . $page;
        error_log("Relative path (../) redirect to: " . $redirectUrl);
        header('Location: ' . $redirectUrl);
    }
    // Otherwise, treat as a relative path
    else {
        $redirectUrl = URL_ROOT . '/' . $page;
        error_log("Standard redirect to: " . $redirectUrl);
        header('Location: ' . $redirectUrl);
    }
    exit;
}

function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } else if (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}
?> 