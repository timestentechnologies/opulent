<?php
session_start();
require_once('admin/connect.php');

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

// Validate input
if (!isset($_POST['order_id']) || !isset($_POST['payment_method'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID and payment method are required']);
    exit();
}

$order_id = $_POST['order_id'];
$payment_method = $_POST['payment_method'];
$customer_id = $_SESSION['customer_id'];

// Verify the order belongs to the customer and get order details
$stmt = $conn->prepare("SELECT o.*, s.prize as service_price, (o.weight * s.prize) as total_amount 
                        FROM `order` o 
                        JOIN service s ON o.service_id = s.id 
                        WHERE o.id = ? AND o.customer_id = ?");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Process payment based on method
if ($payment_method === 'mpesa_manual') {
    // Manual M-Pesa payment
    if (!isset($_POST['mpesa_number']) || !isset($_POST['mpesa_code'])) {
        echo json_encode(['success' => false, 'message' => 'Phone number and M-Pesa code are required']);
        exit();
    }

    $mpesa_number = $_POST['mpesa_number'];
    $mpesa_code = $_POST['mpesa_code'];

    // Validate phone number format (Kenyan format)
    if (!preg_match('/^254[0-9]{9}$/', $mpesa_number)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid Safaricom phone number (e.g., 254700000000)']);
        exit();
    }

    // Validate M-Pesa code format
    if (!preg_match('/^[A-Z0-9]{10}$/', $mpesa_code)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid M-Pesa transaction code']);
        exit();
    }

    // Update the order with payment details
    $stmt = $conn->prepare("UPDATE `order` SET 
        payment_method = 'mpesa_manual',
        mpesa_number = ?,
        mpesa_code = ?,
        payment_status = 'pending',
        updated_at = CURRENT_TIMESTAMP
        WHERE id = ?");

    if ($stmt->execute([$mpesa_number, $mpesa_code, $order_id])) {
        echo json_encode(['success' => true, 'message' => 'Payment details submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit payment details']);
    }
} elseif ($payment_method === 'paypal' || $payment_method === 'stripe') {
    // Handle PayPal or Stripe payment
    // Add your PayPal/Stripe integration code here
    echo json_encode(['success' => false, 'message' => 'Payment method not yet implemented']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
}

$stmt->close();
$conn->close();
?> 