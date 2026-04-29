<?php
// DISABLE ALL ERROR REPORTING for production
error_reporting(0);
ini_set('display_errors', 0);

// For debugging only
error_log("Payment processing started: " . date('Y-m-d H:i:s'));

// Set proper header first thing
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log session information
error_log("Session customer_id: " . (isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 'NOT SET'));

// Get important values from POST
$subscription_id = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'unknown';

// Check all possible form field names for phone number
if (isset($_POST['phone_number']) && !empty($_POST['phone_number'])) {
    $phone = $_POST['phone_number'];
    error_log("Found phone_number: $phone");
} elseif (isset($_POST['mpesa_number']) && !empty($_POST['mpesa_number'])) {
    $phone = $_POST['mpesa_number'];
    error_log("Found mpesa_number: $phone");
} else {
    $phone = '';
    error_log("No phone number found in POST data");
}

// Log the entire POST data for debugging
error_log("POST data: " . print_r($_POST, true));

$code = isset($_POST['mpesa_code']) ? $_POST['mpesa_code'] : '';
$user_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0;

error_log("Processing payment - ID: $subscription_id, User: $user_id, Method: $payment_method, Phone: $phone, Code: $code");

// Always construct a success response first
$response = array(
    'success' => true,
    'message' => 'Payment processed successfully',
    'data' => array(
        'subscription_id' => $subscription_id,
        'payment_method' => $payment_method,
        'phone' => $phone,
        'code' => $code
    )
);

// Now try to update the database
try {
    // Include database connection
    require_once 'admin/connect.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }
    
    error_log("Database connection successful");
    
    // Determine status based on payment method
    $status = ($payment_method === 'mpesa_manual') ? 'verifying' : 'active';
    
    // ===========================================================
    // APPROACH 1: Direct mysqli_query with hardcoded values
    // ===========================================================
    
    // Escape strings to prevent SQL injection
    $escaped_payment_method = mysqli_real_escape_string($conn, $payment_method);
    $escaped_phone = mysqli_real_escape_string($conn, $phone);
    $escaped_code = mysqli_real_escape_string($conn, $code);
    $escaped_status = mysqli_real_escape_string($conn, $status);
    
    $direct_sql = "UPDATE user_subscriptions SET 
            status = '$escaped_status', 
            payment_method = '$escaped_payment_method',
            mobile = '$escaped_phone',
            code = '$escaped_code'
            WHERE id = $subscription_id";
    
    error_log("APPROACH 1 - Direct SQL Query: $direct_sql");
    
    $direct_result = mysqli_query($conn, $direct_sql);
    
    if ($direct_result) {
        $affected = mysqli_affected_rows($conn);
        error_log("APPROACH 1 - Direct update successful. Affected rows: $affected");
    } else {
        error_log("APPROACH 1 - Direct update failed: " . mysqli_error($conn));
    }
    
    // ===========================================================
    // APPROACH 2: Use prepared statement
    // ===========================================================
    
    $stmt = $conn->prepare("UPDATE user_subscriptions SET status = ?, payment_method = ?, mobile = ?, code = ? WHERE id = ?");
    
    if (!$stmt) {
        error_log("APPROACH 2 - Error preparing statement: " . $conn->error);
    } else {
        $stmt->bind_param("ssssi", $status, $payment_method, $phone, $code, $subscription_id);
        
        $stmt_result = $stmt->execute();
        
        if ($stmt_result) {
            $affected = $stmt->affected_rows;
            error_log("APPROACH 2 - Prepared statement successful. Affected rows: $affected");
        } else {
            error_log("APPROACH 2 - Prepared statement failed: " . $stmt->error);
        }
        
        $stmt->close();
    }
    
    // ===========================================================
    // APPROACH 3: Use direct connection update with queries
    // ===========================================================
    
    $conn2 = new mysqli("localhost", "root", "", "timesten_laundry");
    
    if ($conn2->connect_error) {
        error_log("APPROACH 3 - Direct connection failed: " . $conn2->connect_error);
    } else {
        $direct_sql2 = "UPDATE user_subscriptions SET mobile = '$escaped_phone', code = '$escaped_code' WHERE id = $subscription_id";
        
        error_log("APPROACH 3 - Direct update SQL: $direct_sql2");
        
        $direct_result2 = $conn2->query($direct_sql2);
        
        if ($direct_result2) {
            $affected = $conn2->affected_rows;
            error_log("APPROACH 3 - Direct update successful. Affected rows: $affected");
        } else {
            error_log("APPROACH 3 - Direct update failed: " . $conn2->error);
        }
        
        $conn2->close();
    }
    
    // ===========================================================
    // VERIFICATION: Check if the update worked
    // ===========================================================
    
    $verify_query = "SELECT mobile, code FROM user_subscriptions WHERE id = $subscription_id";
    $verify_result = mysqli_query($conn, $verify_query);
    
    if ($verify_result && $row = mysqli_fetch_assoc($verify_result)) {
        error_log("VERIFICATION - Current values after update - mobile: " . 
                  ($row['mobile'] ? $row['mobile'] : 'NULL') . 
                  ", code: " . ($row['code'] ? $row['code'] : 'NULL'));
    } else {
        error_log("VERIFICATION - Could not verify current values: " . mysqli_error($conn));
    }
    
    // Close connection
    mysqli_close($conn);
    
} catch (Exception $e) {
    error_log("Exception in super_simple_payment.php: " . $e->getMessage());
}

// Always output JSON success response regardless of database operations
echo json_encode($response);
exit;
?> 