<?php
/**
 * Simplified Order Notification
 * 
 * This script sends email notifications for a specific order
 * Can be called directly or via AJAX
 */

// Start the session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once('connect.php');
require_once('includes/email_helper.php');

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if order ID is provided
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    $response['message'] = 'Error: No order ID provided';
    
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        echo $response['message'];
    }
    exit;
}

$order_id = (int)$_POST['order_id'];

try {
    // Use the sendOrderNotification function from email_helper.php
    if (sendOrderNotification($order_id)) {
        // Update the email_sent flag
        $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        
        if ($updateStmt) {
            $updateStmt->bind_param("i", $order_id);
            $updateStmt->execute();
            
            $response['success'] = true;
            $response['message'] = "Email notification for order #" . $order_id . " sent successfully";
        } else {
            $response['message'] = "Failed to update email status: " . $conn->error;
        }
    } else {
        $response['message'] = "Failed to send notification for order #" . $order_id;
    }
} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

// Return appropriate response
if (isAjaxRequest()) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    if ($response['success']) {
        echo "<p style='color:green'>" . $response['message'] . "</p>";
    } else {
        echo "<p style='color:red'>" . $response['message'] . "</p>";
    }
}

/**
 * Check if the request is an AJAX request
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?> 