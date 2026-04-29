<?php
// DISABLE ALL ERROR REPORTING
error_reporting(0);
ini_set('display_errors', 0);

// Set proper header first
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login and get data
$logged_in = isset($_SESSION['customer_id']);
$user_id = $logged_in ? $_SESSION['customer_id'] : 0;
$subscription_id = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'direct';
$phone = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';
$code = isset($_POST['mpesa_code']) ? $_POST['mpesa_code'] : '';

// Validate input
if (!$logged_in) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit;
}

if ($subscription_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid subscription ID']);
    exit;
}

// Set appropriate status
$status = ($payment_method === 'mpesa_manual') ? 'verifying' : 'active';

try {
    // Include database connection - minimal error chance
    include 'includes/db_connection.php';
    
    // Verify subscription exists
    $check = mysqli_query($conn, "SELECT * FROM user_subscriptions WHERE id = $subscription_id AND customer_id = $user_id");
    $subscription_exists = mysqli_num_rows($check) > 0;
    
    if (!$subscription_exists) {
        echo json_encode(['success' => false, 'message' => 'Subscription not found']);
        exit;
    }
    
    // Escape strings to prevent SQL injection
    $payment_method = mysqli_real_escape_string($conn, $payment_method);
    $phone = mysqli_real_escape_string($conn, $phone);
    $code = mysqli_real_escape_string($conn, $code);
    
    // Update subscription
    $sql = "UPDATE user_subscriptions SET 
            status = '$status', 
            payment_method = '$payment_method',
            mobile = '$phone',
            code = '$code'
            WHERE id = $subscription_id AND customer_id = $user_id";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo json_encode([
            'success' => false, 
            'message' => 'Database update failed',
            'error' => mysqli_error($conn)
        ]);
        exit;
    }
    
    // Success!
    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully!',
        'data' => [
            'subscription_id' => $subscription_id,
            'status' => $status,
            'method' => $payment_method
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 