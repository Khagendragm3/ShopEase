<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/esewa_config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to make a payment.');
    redirect('login.php');
}

// Get order ID from POST or GET data
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : (isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0);

// Validate order ID
if (empty($orderId)) {
    flash('error', 'Invalid order reference.');
    redirect('orders.php');
}

// Get order details
$order = getOrderById($orderId);

// Check if order exists and belongs to the current user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

// Check if payment is already completed
if ($order['payment_status'] == 'completed') {
    flash('info', 'Payment for this order has already been completed.');
    redirect("order-details.php?id=$orderId");
}

// Format amount to 2 decimal places
$amount = number_format((float)$order['total_amount'], 2, '.', '');

// Log payment attempt for debugging
error_log("eSewa Payment Attempt - Order ID: $orderId, Amount: $amount");

// Generate eSewa form
$esewaForm = generateEsewaForm($orderId, $amount, 'Order #' . $orderId);

// Store order details in session for reference
$_SESSION['esewa_payment'] = [
    'order_id' => $orderId,
    'amount' => $amount,
    'timestamp' => time()
];

// Output the form and auto-submit
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - <?php echo SITE_NAME; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #60BB46;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .debug-info {
            margin-top: 30px;
            text-align: left;
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            font-size: 12px;
            display: none;
        }
        .show-debug {
            margin-top: 20px;
            color: #999;
            cursor: pointer;
            font-size: 12px;
        }
        .manual-button {
            margin-top: 20px;
            display: none;
        }
        .timer {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <h2>Processing Your Payment</h2>
    <p>Please wait while we redirect you to eSewa...</p>
    <div class="loader"></div>
    <div class="timer" id="timer">Redirecting in <span id="countdown">5</span> seconds...</div>
    
    <?php echo $esewaForm; ?>
    
    <div class="manual-button" id="manual-button">
        <p>If you are not automatically redirected, please click the button below:</p>
        <button onclick="document.getElementById('esewa-payment-form').submit();" class="btn btn-primary">Pay with eSewa</button>
    </div>
    
    <div class="show-debug" onclick="document.getElementById('debug-info').style.display='block';">
        Show technical details
    </div>
    
    <div id="debug-info" class="debug-info">
        <h4>Debug Information</h4>
        <p><strong>Order ID:</strong> <?php echo $orderId; ?></p>
        <p><strong>Amount:</strong> <?php echo $amount; ?></p>
        <p><strong>Payment URL:</strong> <?php echo ESEWA_PAYMENT_URL; ?></p>
        <p><strong>Success URL:</strong> <?php echo ESEWA_SUCCESS_URL; ?></p>
        <p><strong>Failure URL:</strong> <?php echo ESEWA_FAILURE_URL; ?></p>
        <p><strong>Merchant ID:</strong> <?php echo ESEWA_MERCHANT_ID; ?></p>
        <p><strong>Form HTML:</strong> <pre><?php echo htmlspecialchars($esewaForm); ?></pre></p>
    </div>
    
    <script>
        // Countdown timer
        var seconds = 5;
        var countdownElement = document.getElementById('countdown');
        var timerElement = document.getElementById('timer');
        var manualButtonElement = document.getElementById('manual-button');
        
        function updateCountdown() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                timerElement.style.display = 'none';
                submitForm();
            }
        }
        
        var countdownInterval = setInterval(updateCountdown, 1000);
        
        // Auto-submit the form with a slight delay to ensure the page is fully loaded
        function submitForm() {
            var form = document.getElementById('esewa-payment-form');
            if(form) {
                console.log('Submitting eSewa payment form...');
                try {
                    form.submit();
                    // Show manual button after a delay in case the form submission fails
                    setTimeout(function() {
                        manualButtonElement.style.display = 'block';
                    }, 5000);
                } catch(e) {
                    console.error('Error submitting form:', e);
                    manualButtonElement.style.display = 'block';
                }
            } else {
                console.error('eSewa payment form not found!');
                document.body.innerHTML += '<div class="alert alert-danger">Error: Payment form not found. Please contact support.</div>';
            }
        }
    </script>
</body>
</html>
<?php exit; ?> 