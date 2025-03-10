<?php
/**
 * eSewa Payment Gateway Configuration
 */

// eSewa Merchant ID (replace with your actual merchant ID)
define('ESEWA_MERCHANT_ID', 'EPAYTEST');

// eSewa URLs - Updated to the correct endpoints for the test environment
define('ESEWA_PAYMENT_URL', 'https://uat.esewa.com.np/epay/main');
define('ESEWA_VERIFICATION_URL', 'https://uat.esewa.com.np/epay/transrec');

// eSewa Success and Failure URLs - Make sure these are absolute URLs
define('ESEWA_SUCCESS_URL', URL_ROOT . '/esewa_success.php');
define('ESEWA_FAILURE_URL', URL_ROOT . '/esewa_failure.php');

/**
 * Generate eSewa payment form
 * 
 * @param int $orderId Order ID
 * @param float $amount Payment amount
 * @param string $productDetails Product details
 * @return string HTML form for eSewa payment
 */
function generateEsewaForm($orderId, $amount, $productDetails = 'Order Payment') {
    // Format amount to 2 decimal places
    $amount = number_format((float)$amount, 2, '.', '');
    
    // Calculate tax amount, service charge, and delivery charge (all 0 for simplicity)
    $taxAmount = 0;
    $serviceCharge = 0;
    $deliveryCharge = 0;
    
    // Calculate total amount
    $totalAmount = $amount + $taxAmount + $serviceCharge + $deliveryCharge;
    
    // Create the form
    $form = '<form action="' . ESEWA_PAYMENT_URL . '" method="POST" id="esewa-payment-form">';
    $form .= '<input value="' . $totalAmount . '" name="tAmt" type="hidden">';
    $form .= '<input value="' . $amount . '" name="amt" type="hidden">';
    $form .= '<input value="' . $taxAmount . '" name="txAmt" type="hidden">';
    $form .= '<input value="' . $serviceCharge . '" name="psc" type="hidden">';
    $form .= '<input value="' . $deliveryCharge . '" name="pdc" type="hidden">';
    $form .= '<input value="' . ESEWA_MERCHANT_ID . '" name="scd" type="hidden">';
    $form .= '<input value="' . $orderId . '" name="pid" type="hidden">';
    $form .= '<input value="' . ESEWA_SUCCESS_URL . '" type="hidden" name="su">';
    $form .= '<input value="' . ESEWA_FAILURE_URL . '" type="hidden" name="fu">';
    $form .= '</form>';
    
    return $form;
}

/**
 * Verify eSewa payment
 * 
 * @param string $refId eSewa reference ID
 * @param string $amount Payment amount
 * @param string $orderId Order ID
 * @return bool True if payment is verified, false otherwise
 */
function verifyEsewaPayment($refId, $amount, $orderId) {
    $url = ESEWA_VERIFICATION_URL;
    
    // Format amount to 2 decimal places
    $amount = number_format((float)$amount, 2, '.', '');
    
    // Prepare data for verification
    $data = [
        'amt' => $amount,
        'rid' => $refId,
        'pid' => $orderId,
        'scd' => ESEWA_MERCHANT_ID
    ];
    
    // Set up cURL with detailed error reporting
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // For testing only, enable in production
    
    $response = curl_exec($curl);
    
    // Log any cURL errors
    if(curl_errno($curl)) {
        error_log('eSewa Verification Error: ' . curl_error($curl));
    }
    
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    // Log the response for debugging
    error_log('eSewa Verification Response: ' . $response . ' (HTTP Code: ' . $httpCode . ')');
    
    if (strpos($response, 'Success') !== false) {
        return true;
    }
    
    return false;
} 