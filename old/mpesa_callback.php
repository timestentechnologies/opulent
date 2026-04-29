<?php
include('includes/db_connection.php');

// Get the callback data
$callbackData = file_get_contents('php://input');
$data = json_decode($callbackData);

if ($data) {
    // Extract relevant information
    $merchantRequestID = $data->MerchantRequestID;
    $checkoutRequestID = $data->CheckoutRequestID;
    $resultCode = $data->ResultCode;
    $resultDesc = $data->ResultDesc;
    
    // Get order details using checkoutRequestID
    $sql = "SELECT * FROM orders WHERE payment_reference = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $checkoutRequestID);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if ($order) {
        if ($resultCode === 0) {
            // Payment successful
            $sql = "UPDATE orders SET 
                    payment_status = 'completed',
                    payment_response = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE payment_reference = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $callbackData, $checkoutRequestID);
            $stmt->execute();
            
            // Send confirmation email to customer
            // TODO: Implement email sending
            
            // Log successful payment
            error_log("M-Pesa payment successful for order #" . $order['id']);
        } else {
            // Payment failed
            $sql = "UPDATE orders SET 
                    payment_status = 'failed',
                    payment_response = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE payment_reference = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $callbackData, $checkoutRequestID);
            $stmt->execute();
            
            // Log failed payment
            error_log("M-Pesa payment failed for order #" . $order['id'] . ": " . $resultDesc);
        }
    }
}

// Send response to M-Pesa
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?> 