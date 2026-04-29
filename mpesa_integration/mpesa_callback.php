<?php
/**
 * M-Pesa Callback Handler
 * This file handles M-Pesa payment callbacks
 */

require_once('includes/db_connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the callback data
$callbackData = file_get_contents('php://input');
$data = json_decode($callbackData);

// Log the callback data
error_log("M-Pesa Callback Received: " . $callbackData);

if ($data) {
    try {
        // Extract relevant information
        $merchantRequestID = $data->MerchantRequestID;
        $checkoutRequestID = $data->CheckoutRequestID;
        $resultCode = $data->ResultCode;
        $resultDesc = $data->ResultDesc;
        
        // Get order details using checkoutRequestID
        $sql = "SELECT * FROM `order` WHERE payment_reference = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $checkoutRequestID);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if ($order) {
            if ($resultCode === 0) {
                // Payment successful
                $sql = "UPDATE `order` SET 
                        payment_status = 'paid',
                        payment_response = ?,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE payment_reference = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $callbackData, $checkoutRequestID);
                $stmt->execute();
                
                // Log successful payment
                error_log("M-Pesa payment successful for order #" . $order['id']);
                
                // TODO: Send confirmation email to customer
                // sendPaymentConfirmationEmail($order);
            } else {
                // Payment failed
                $sql = "UPDATE `order` SET 
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
        } else {
            error_log("Order not found for checkoutRequestID: " . $checkoutRequestID);
        }
    } catch (Exception $e) {
        error_log("Error processing M-Pesa callback: " . $e->getMessage());
    }
}

// Send response to M-Pesa
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?> 