<?php
// Start output buffering to prevent PHP errors from breaking JSON output
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable error display for debugging

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug array to collect all processing information
$debug = [
    'post_data' => $_POST,
    'session' => isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 'not set',
    'processing_steps' => [],
    'errors' => []
];

try {
    // Clear any previous output that might corrupt our JSON
    ob_clean();
    
    // Set content type to JSON for API-like response
    header('Content-Type: application/json');

    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $debug['errors'][] = 'Invalid request method: ' . $_SERVER['REQUEST_METHOD'];
        throw new Exception('Only POST requests are allowed');
    }
    $debug['processing_steps'][] = 'POST request validated';

    // Include database connection
    require_once 'admin/connect.php';
    $debug['processing_steps'][] = 'Database connection included';

    // Check if user is logged in
    if (!isset($_SESSION['customer_id'])) {
        $debug['errors'][] = 'User not logged in';
        throw new Exception('You must be logged in to process payments');
    }

    $user_id = $_SESSION['customer_id'];
    $debug['processing_steps'][] = 'User is logged in with ID: ' . $user_id;

    // Validate subscription_id
    if (!isset($_POST['subscription_id']) || empty($_POST['subscription_id'])) {
        $debug['errors'][] = 'Subscription ID is required but not provided';
        throw new Exception('Subscription ID is required');
    }

    $subscription_id = intval($_POST['subscription_id']);
    $debug['subscription_id'] = $subscription_id;
    $debug['processing_steps'][] = 'Subscription ID validated: ' . $subscription_id;

    // Get payment method (default to direct if not specified)
    $payment_method = isset($_POST['payment_method']) && !empty($_POST['payment_method']) 
        ? $_POST['payment_method'] 
        : 'direct';
    $debug['payment_method'] = $payment_method;
    $debug['processing_steps'][] = 'Payment method: ' . $payment_method;

    // Check all possible form field names for phone number
    if (isset($_POST['phone_number']) && !empty($_POST['phone_number'])) {
        $mobile_number = $_POST['phone_number'];
    } elseif (isset($_POST['mpesa_number']) && !empty($_POST['mpesa_number'])) {
        $mobile_number = $_POST['mpesa_number'];
    } else {
        $mobile_number = '';
    }
    
    $mpesa_code = isset($_POST['mpesa_code']) ? $_POST['mpesa_code'] : '';
    
    $debug['mobile_number'] = $mobile_number;
    $debug['mpesa_code'] = $mpesa_code;
    $debug['processing_steps'][] = 'Got payment details - Mobile: ' . $mobile_number . ', Code: ' . $mpesa_code;

    // Check if the subscription exists and belongs to the current user
    $stmt = $conn->prepare("SELECT * FROM user_subscriptions WHERE id = ? AND customer_id = ?");
    if (!$stmt) {
        $debug['errors'][] = 'Error preparing statement: ' . $conn->error;
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $subscription_id, $user_id);
    $stmt->execute();
    
    if ($stmt->error) {
        $debug['errors'][] = 'Error executing query: ' . $stmt->error;
        throw new Exception('Error executing query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $debug['processing_steps'][] = 'Query executed, found rows: ' . $result->num_rows;

    if ($result->num_rows === 0) {
        $debug['errors'][] = 'No subscription found with ID ' . $subscription_id . ' for user ' . $user_id;
        throw new Exception('Invalid subscription or access denied');
    }

    $subscription = $result->fetch_assoc();
    $debug['subscription_data'] = $subscription;
    $debug['processing_steps'][] = 'Subscription data retrieved';

    // Determine status based on payment method
    $status = ($payment_method === 'mpesa_manual') ? 'verifying' : 'active';
    $debug['new_status'] = $status;
    
    // Update the subscription
    $conn->begin_transaction();
    $debug['processing_steps'][] = 'Transaction started';

    try {
        // Simplify the update SQL to use direct field values rather than dynamic parameters
        // This ensures that all fields are properly set with appropriate data types
        $sql = "UPDATE user_subscriptions SET 
                status = ?, 
                payment_method = ?,
                mobile = ?,
                code = ?
                WHERE id = ? AND customer_id = ?";
                
        $update_stmt = $conn->prepare($sql);
        if (!$update_stmt) {
            $debug['errors'][] = 'Error preparing update statement: ' . $conn->error;
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $debug['processing_steps'][] = 'Update statement prepared';
        $debug['update_sql'] = $sql;
        
        // Bind all parameters directly
        $update_stmt->bind_param("ssssii", $status, $payment_method, $mobile_number, $mpesa_code, $subscription_id, $user_id);
        
        if ($update_stmt->error) {
            $debug['errors'][] = 'Error binding parameters: ' . $update_stmt->error;
            throw new Exception('Error binding parameters: ' . $update_stmt->error);
        }
        
        $debug['processing_steps'][] = 'Parameters bound successfully';
        $debug['binding_values'] = [
            'status' => $status,
            'payment_method' => $payment_method,
            'mobile' => $mobile_number,
            'code' => $mpesa_code,
            'id' => $subscription_id,
            'customer_id' => $user_id
        ];
        
        $update_result = $update_stmt->execute();
        
        if (!$update_result) {
            $debug['errors'][] = 'Error executing update: ' . $update_stmt->error;
            throw new Exception('Failed to update subscription status: ' . $update_stmt->error);
        }
        
        $debug['processing_steps'][] = 'Update executed successfully, affected rows: ' . $update_stmt->affected_rows;

        // Commit the transaction
        $conn->commit();
        $debug['processing_steps'][] = 'Transaction committed';
        
        // Response message depending on payment method
        $message = ($payment_method === 'mpesa_manual') 
            ? 'Payment details submitted successfully! Your subscription will be activated once payment is verified.' 
            : 'Payment processed successfully! Your subscription is now active.';
        
        // Clear any previous output
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'subscription_id' => $subscription_id,
            'status' => $status,
            'debug' => $debug
        ]);
        
    } catch (Exception $e) {
        // Rollback the transaction if an error occurs
        $conn->rollback();
        $debug['processing_steps'][] = 'Transaction rolled back due to error';
        $debug['errors'][] = 'Exception in transaction: ' . $e->getMessage();
        throw $e;
    }

    // Close the database connection
    $stmt->close();
    if (isset($update_stmt)) {
        $update_stmt->close();
    }
    $conn->close();
    $debug['processing_steps'][] = 'Database connection closed';

} catch (Exception $e) {
    $debug['exception'] = $e->getMessage();
    
    // Clear any previous output
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $debug
    ]);
    
    if (isset($conn) && $conn) {
        $conn->close();
        $debug['processing_steps'][] = 'Database connection closed after exception';
    }
}

// End output buffering and flush
ob_end_flush();
?> 