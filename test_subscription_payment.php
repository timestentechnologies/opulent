<?php
// Disable error output to browser
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering
ob_start();

// Clear any previous output
if (ob_get_level()) {
    ob_clean();
}

// Set JSON header
header('Content-Type: application/json');

// Get POST data
$subscription_id = isset($_POST['subscription_id']) ? $_POST['subscription_id'] : '';
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';
$mpesa_code = isset($_POST['mpesa_code']) ? $_POST['mpesa_code'] : '';

// Create simple response
$response = [
    'success' => true,
    'message' => 'Payment details received successfully',
    'data' => [
        'subscription_id' => $subscription_id,
        'payment_method' => $payment_method,
        'phone_number' => $phone_number,
        'mpesa_code' => $mpesa_code
    ],
    'timestamp' => time()
];

// Output JSON
echo json_encode($response);

// End and flush the output buffer
ob_end_flush();
?> 