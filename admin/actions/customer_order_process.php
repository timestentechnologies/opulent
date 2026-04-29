<?php
/**
 * Process customer orders from the front-end
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../connect.php');
require_once('../includes/email_helper.php');

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define a function to validate and sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate tracking number
function generateTrackingNumber() {
    $prefix = 'LND';
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -6));
    return $prefix . $date . $random;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize form data
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $description = sanitizeInput($_POST['description'] ?? '');
    $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
    $pickup_date = sanitizeInput($_POST['pickup_date'] ?? date('Y-m-d'));
    $delivery_date = sanitizeInput($_POST['delivery_date'] ?? date('Y-m-d', strtotime('+2 days')));
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $pickup_address = sanitizeInput($_POST['pickup_address'] ?? '');
    
    // Set default values for customer orders
    $status = 'received';
    $payment_status = 'pending';
    
    // Validate required fields
    if (empty($customer_id) || empty($service_id) || empty($description) || empty($pickup_date) || empty($delivery_date) || $weight <= 0) {
        $_SESSION['error'] = "Please fill in all required fields";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=missing_fields");
        exit();
    }
    
    try {
        // Generate tracking number
        $tracking_number = generateTrackingNumber();
        
        // Insert new order
        $sql = "INSERT INTO `order` (
            customer_id, 
            service_id, 
            description, 
            weight,
            pickup_date, 
            delivery_date, 
            price, 
            status, 
            payment_status,
            tracking_number,
            pickup_address,
            created_at,
            email_sent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iisdssdsssss", 
            $customer_id, 
            $service_id, 
            $description, 
            $weight,
            $pickup_date, 
            $delivery_date, 
            $price, 
            $status, 
            $payment_status,
            $tracking_number,
            $pickup_address
        );
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Send email notification immediately
            try {
                if (sendOrderNotification($order_id)) {
                    // Mark as sent if successful
                    $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("i", $order_id);
                    $updateStmt->execute();
                } else {
                    // Email not sent, will be picked up by process_pending_emails.php
                    error_log("Failed to send order notification for order #" . $order_id);
                }
            } catch (Exception $e) {
                // Log the error but don't interrupt the flow
                error_log("Error sending order notification: " . $e->getMessage());
            }
            
            $_SESSION['success'] = "Your order has been successfully placed! Tracking number: " . $tracking_number;
            header("Location: ../my_orders.php?status=success&tracking=" . $tracking_number);
            exit();
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error processing order: " . $e->getMessage());
        $_SESSION['error'] = "There was a problem processing your order. Please try again later.";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=process_error");
        exit();
    }
} else {
    // If not POST request, redirect to homepage
    header("Location: ../index.php");
    exit();
} 