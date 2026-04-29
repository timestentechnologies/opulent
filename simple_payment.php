<?php
// Start session for user_id
session_start();

// Create log file with basic info
$log_file = __DIR__ . '/payment.log';
$log_content = "====== Payment Request " . date('Y-m-d H:i:s') . " ======\n";
$log_content .= "POST data: " . print_r($_POST, true) . "\n";
$log_content .= "SESSION: " . print_r($_SESSION, true) . "\n";

// Ensure log file is created
file_put_contents($log_file, $log_content, FILE_APPEND);

// Get data from POST
$subscription_id = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

// Get phone number from multiple possible field names
$phone = '';
if (!empty($_POST['phone_number'])) {
    $phone = $_POST['phone_number'];
    file_put_contents($log_file, "Found phone number in 'phone_number': $phone\n", FILE_APPEND);
} elseif (!empty($_POST['mpesa_number'])) {
    $phone = $_POST['mpesa_number'];
    file_put_contents($log_file, "Found phone number in 'mpesa_number': $phone\n", FILE_APPEND);
} else {
    file_put_contents($log_file, "No phone number found in POST data\n", FILE_APPEND);
}

// Get mpesa code
$code = isset($_POST['mpesa_code']) ? $_POST['mpesa_code'] : '';
file_put_contents($log_file, "MPESA code: $code\n", FILE_APPEND);

// Get user id
$user_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0;

// Set appropriate status
$status = ($payment_method === 'mpesa_manual') ? 'verifying' : 'active';

file_put_contents($log_file, "Final values: subscription_id=$subscription_id, user_id=$user_id, phone=$phone, code=$code, status=$status\n", FILE_APPEND);

// Database update
$db_result = [
    'success' => false,
    'message' => '',
    'query' => '',
    'affected_rows' => 0
];

try {
    // Connect to database
    include 'admin/connect.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }
    
    file_put_contents($log_file, "Database connection successful\n", FILE_APPEND);
    
    // Update the subscription record
    $sql = "UPDATE user_subscriptions SET 
            status = '" . mysqli_real_escape_string($conn, $status) . "', 
            mobile = '" . mysqli_real_escape_string($conn, $phone) . "',
            code = '" . mysqli_real_escape_string($conn, $code) . "'
            WHERE id = " . $subscription_id;
    
    $db_result['query'] = $sql;
    file_put_contents($log_file, "SQL Query: $sql\n", FILE_APPEND);
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Database update failed: " . mysqli_error($conn));
    }
    
    $affected_rows = mysqli_affected_rows($conn);
    $db_result['affected_rows'] = $affected_rows;
    $db_result['success'] = true;
    $db_result['message'] = "Update successful, affected rows: $affected_rows";
    
    file_put_contents($log_file, "Update successful! Affected rows: $affected_rows\n", FILE_APPEND);
    
    // Verify the update
    $verify_query = "SELECT mobile, code FROM user_subscriptions WHERE id = $subscription_id";
    $verify_result = mysqli_query($conn, $verify_query);
    
    if ($verify_result && $row = mysqli_fetch_assoc($verify_result)) {
        file_put_contents($log_file, "Verification - Mobile: " . ($row['mobile'] ?? 'NULL') . ", Code: " . ($row['code'] ?? 'NULL') . "\n", FILE_APPEND);
        $db_result['verification'] = [
            'mobile' => $row['mobile'] ?? null,
            'code' => $row['code'] ?? null
        ];
    } else {
        file_put_contents($log_file, "Verification failed: " . mysqli_error($conn) . "\n", FILE_APPEND);
    }
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    file_put_contents($log_file, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    $db_result['success'] = false;
    $db_result['message'] = $e->getMessage();
}

// Create response with detailed debug info
$response = [
    'success' => true,
    'message' => 'Payment processed successfully!',
    'data' => [
        'subscription_id' => $subscription_id,
        'status' => $status,
        'method' => $payment_method,
        'phone' => $phone,
        'code' => $code
    ],
    'debug' => [
        'post_data' => $_POST,
        'session' => ['customer_id' => $user_id],
        'database_result' => $db_result 
    ]
];

// Log response
file_put_contents($log_file, "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
file_put_contents($log_file, "====== End Request ======\n\n", FILE_APPEND);

// Send response
header('Content-Type: application/json');
echo json_encode($response);
?> 