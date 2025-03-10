<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/esewa_config.php';

// Set page title
$pageTitle = "eSewa Test";

// Include header
include 'includes/header.php';

// Test connection to eSewa
$testUrl = "https://uat.esewa.com.np/epay/main";
$isReachable = false;
$error = '';

// Try to connect to eSewa
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
    $isReachable = true;
} else {
    $error = curl_error($ch);
}

curl_close($ch);
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">eSewa Connection Test</h3>
                </div>
                <div class="card-body">
                    <?php if ($isReachable): ?>
                        <div class="alert alert-success">
                            <h4><i class="fas fa-check-circle"></i> Connection Successful</h4>
                            <p>Successfully connected to eSewa payment gateway.</p>
                            <p>HTTP Status Code: <?php echo $httpCode; ?></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <h4><i class="fas fa-exclamation-triangle"></i> Connection Failed</h4>
                            <p>Could not connect to eSewa payment gateway.</p>
                            <p>Error: <?php echo $error ? $error : 'Unknown error'; ?></p>
                            <p>HTTP Status Code: <?php echo $httpCode; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <h4 class="mt-4">eSewa Configuration</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>Merchant ID</th>
                            <td><?php echo ESEWA_MERCHANT_ID; ?></td>
                        </tr>
                        <tr>
                            <th>Payment URL</th>
                            <td><?php echo ESEWA_PAYMENT_URL; ?></td>
                        </tr>
                        <tr>
                            <th>Verification URL</th>
                            <td><?php echo ESEWA_VERIFICATION_URL; ?></td>
                        </tr>
                        <tr>
                            <th>Success URL</th>
                            <td><?php echo ESEWA_SUCCESS_URL; ?></td>
                        </tr>
                        <tr>
                            <th>Failure URL</th>
                            <td><?php echo ESEWA_FAILURE_URL; ?></td>
                        </tr>
                    </table>
                    
                    <h4 class="mt-4">Test Payment</h4>
                    <p>You can test the eSewa payment integration by creating a test order with a small amount.</p>
                    
                    <form action="process_esewa_payment.php" method="get">
                        <input type="hidden" name="order_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] . time() : '123456789'; ?>">
                        <button type="submit" class="btn btn-primary">Test eSewa Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?> 