<?php
session_start();
require_once('admin/connect.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log payment attempts for debugging
$log_file = 'payment.log';
$log_data = "\n====== Payment Request " . date('Y-m-d H:i:s') . " ======\n";
$log_data .= "POST data: " . print_r($_POST, true) . "\n";
$log_data .= "SESSION: " . print_r($_SESSION, true) . "\n";
file_put_contents($log_file, $log_data, FILE_APPEND);

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    $error_msg = 'Please login to make a payment';
    file_put_contents($log_file, "ERROR: $error_msg\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $error_msg]);
    exit();
}

// Validate input
if (!isset($_POST['order_id']) || !isset($_POST['payment_method'])) {
    $error_msg = 'Order ID and payment method are required';
    file_put_contents($log_file, "ERROR: $error_msg\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $error_msg]);
    exit();
}

$order_id = $_POST['order_id'];
$payment_method = $_POST['payment_method'];
$customer_id = $_SESSION['customer_id'];

// Verify database connection
if (!$conn) {
    $error_msg = 'Database connection error';
    file_put_contents($log_file, "ERROR: $error_msg\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $error_msg]);
    exit();
}

try {
    // Verify the order belongs to the customer and get order details
    $stmt = $conn->prepare("SELECT o.*, s.prize as service_price, (o.weight * s.prize) as total_amount 
                            FROM `order` o 
                            JOIN service s ON o.service_id = s.id 
                            WHERE o.id = ? AND o.customer_id = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare statement error: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $order_id, $customer_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        throw new Exception("Order not found or does not belong to this customer");
    }

    // Process payment based on method
    if ($payment_method === 'mpesa_manual') {
        // Manual M-Pesa payment
        if (!isset($_POST['mpesa_number']) || !isset($_POST['mpesa_code'])) {
            throw new Exception("Phone number and M-Pesa code are required");
        }

        $mpesa_number = $_POST['mpesa_number'];
        $mpesa_code = $_POST['mpesa_code'];

        // Validate phone number format (Kenyan format)
        if (!preg_match('/^254[0-9]{9}$/', $mpesa_number)) {
            throw new Exception("Please enter a valid Safaricom phone number (e.g., 254700000000)");
        }

        // Validate M-Pesa code format
        if (!preg_match('/^[A-Z0-9]{10}$/', $mpesa_code)) {
            throw new Exception("Please enter a valid M-Pesa transaction code");
        }

        // Log the values before update for debugging
        file_put_contents($log_file, "Attempting to update order #$order_id with mpesa_number=$mpesa_number, mpesa_code=$mpesa_code\n", FILE_APPEND);
        
        // Make sure all database fields are correctly named
        $check_fields_sql = "SHOW COLUMNS FROM `order` LIKE 'mpesa_number'";
        $check_result = $conn->query($check_fields_sql);
        $field_exists = $check_result && $check_result->num_rows > 0;
        
        if (!$field_exists) {
            // Log the issue for debugging
            file_put_contents($log_file, "ERROR: mpesa_number field does not exist in order table, checking database schema\n", FILE_APPEND);
            
            // Try checking column names to help debug
            $check_columns = "SHOW COLUMNS FROM `order`";
            $columns_result = $conn->query($check_columns);
            $column_names = [];
            while ($column = $columns_result->fetch_assoc()) {
                $column_names[] = $column['Field'];
            }
            file_put_contents($log_file, "Available columns: " . implode(", ", $column_names) . "\n", FILE_APPEND);
            
            // Try to find phone-related and code-related columns
            $phone_field = null;
            $code_field = null;
            
            foreach ($column_names as $column) {
                if (strpos($column, 'phone') !== false || strpos($column, 'mpesa') !== false || strpos($column, 'mobile') !== false) {
                    $phone_field = $column;
                }
                if (strpos($column, 'code') !== false || strpos($column, 'transaction') !== false) {
                    $code_field = $column;
                }
            }
            
            if ($phone_field && $code_field) {
                file_put_contents($log_file, "Found alternative fields: phone=$phone_field, code=$code_field\n", FILE_APPEND);
                // Use the fields we found
                $update_sql = "UPDATE `order` SET 
                    payment_method = ?,
                    $phone_field = ?,
                    $code_field = ?,
                    payment_status = 'pending',
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            } else {
                throw new Exception("Required payment fields not found in database schema");
            }
        } else {
            // Use the standard fields
            $update_sql = "UPDATE `order` SET 
                payment_method = ?,
                mpesa_number = ?,
                mpesa_code = ?,
                payment_status = 'pending',
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        }
        
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Prepare update statement error: " . $conn->error);
        }
        
        $update_stmt->bind_param("sssi", $payment_method, $mpesa_number, $mpesa_code, $order_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update order: " . $update_stmt->error);
        }
        
        $affected_rows = $update_stmt->affected_rows;
        file_put_contents($log_file, "Update result: affected_rows=$affected_rows\n", FILE_APPEND);
        
        if ($affected_rows <= 0) {
            // Try to diagnose the issue
            $check_order = $conn->prepare("SELECT payment_status FROM `order` WHERE id = ?");
            $check_order->bind_param("i", $order_id);
            $check_order->execute();
            $check_result = $check_order->get_result();
            $order_status = $check_result->fetch_assoc();
            
            if ($order_status) {
                file_put_contents($log_file, "Order exists but no update occurred. Current payment status: " . $order_status['payment_status'] . "\n", FILE_APPEND);
                // If order exists but no rows were affected, assume it's already been updated
                echo json_encode(['success' => true, 'message' => 'Payment details already submitted. Current status: ' . $order_status['payment_status']]);
            } else {
                throw new Exception("No rows were updated. Order may not exist or there's a database issue.");
            }
        } else {
            file_put_contents($log_file, "SUCCESS: Order #$order_id updated successfully with payment details\n", FILE_APPEND);
            echo json_encode(['success' => true, 'message' => 'Payment details submitted successfully! We will verify your payment.']);
        }
        
    } elseif ($payment_method === 'paypal' || $payment_method === 'stripe') {
        // Handle PayPal or Stripe payment
        // Add your PayPal/Stripe integration code here
        throw new Exception("Payment method not yet implemented");
    } else {
        throw new Exception("Invalid payment method");
    }

} catch (Exception $e) {
    $error_msg = $e->getMessage();
    file_put_contents($log_file, "ERROR: $error_msg\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $error_msg]);
} finally {
    file_put_contents($log_file, "====== End Request ======\n\n", FILE_APPEND);
    if (isset($stmt)) $stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    $conn->close();
}
?> 