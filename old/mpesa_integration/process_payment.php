<?php
/**
 * Payment Processing Handler
 * This file handles payment processing including M-Pesa
 */

session_start();
require_once('admin/connect.php');
require_once('includes/mpesa_stk_push.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to make a payment']);
    exit();
}

// Get order details
$order_id = $_POST['order_id'] ?? null;
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit();
}

// Fetch order details with service price
$sql = "SELECT o.*, s.prize as service_price, (o.weight * s.prize) as total_amount 
        FROM `order` o 
        JOIN service s ON o.service_id = s.id 
        WHERE o.id = ? AND o.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $order_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Fetch payment settings
$sql = "SELECT setting_name, value FROM payment_settings WHERE setting_name IN (
    'mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_passkey', 
    'mpesa_shortcode', 'mpesa_mode'
)";
$result = $conn->query($sql);
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['value'];
}

// Validate M-Pesa settings
$required_settings = [
    'mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_passkey', 
    'mpesa_shortcode', 'mpesa_mode'
];
foreach ($required_settings as $setting) {
    if (empty($settings[$setting])) {
        error_log("Missing M-Pesa setting: " . $setting);
        echo json_encode([
            'success' => false, 
            'message' => 'Payment configuration error. Please contact support.'
        ]);
        exit();
    }
}

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($payment_method === 'mpesa') {
        $phone = $_POST['phone'] ?? '';
        
        if (empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Phone number is required for M-Pesa payment']);
            exit();
        }

        try {
            // Initialize M-Pesa STK Push
            $mpesa = new MpesaSTKPush(
                $settings['mpesa_consumer_key'],
                $settings['mpesa_consumer_secret'],
                $settings['mpesa_passkey'],
                $settings['mpesa_shortcode'],
                $settings['mpesa_mode']
            );
            
            // Log the request details
            error_log("M-Pesa Request - Phone: " . $phone . ", Amount: " . $order['total_amount']);
            
            // Initiate STK Push
            $response = $mpesa->initiateSTKPush(
                $phone,
                $order['total_amount'],
                'ORDER_' . $order_id,
                'Payment for Order #' . $order_id
            );
            
            // Log the response
            error_log("M-Pesa Response: " . print_r($response, true));
            
            if (isset($response->ResponseCode) && $response->ResponseCode === '0') {
                // Update order status
                $sql = "UPDATE `order` SET payment_status = 'pending', payment_method = 'mpesa', 
                        payment_reference = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('si', $response->CheckoutRequestID, $order_id);
                $stmt->execute();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'M-Pesa STK Push initiated. Please check your phone to complete the payment.'
                ]);
            } else {
                $error_message = isset($response->errorMessage) ? $response->errorMessage : 'Failed to initiate M-Pesa payment';
                error_log("M-Pesa Error: " . $error_message);
                echo json_encode([
                    'success' => false,
                    'message' => $error_message
                ]);
            }
        } catch (Exception $e) {
            error_log("M-Pesa Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while processing your payment. Please try again.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please select a valid payment method'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 