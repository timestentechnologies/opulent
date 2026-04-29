<?php
session_start();
require_once('admin/connect.php');

// Start output buffering
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to place an order']);
    exit;
}

// Check for duplicate submission
if (isset($_SESSION['last_order_time'])) {
    $time_diff = time() - $_SESSION['last_order_time'];
    
    if ($time_diff < 5) {
        // Clear the session variable to prevent future false positives
        unset($_SESSION['last_order_time']);
        echo json_encode([
            'success' => false, 
            'message' => 'Please wait a few seconds before submitting another order',
            'tracking_number' => 'N/A',
            'service_name' => 'N/A',
            'weight' => 'N/A',
            'price' => 'N/A',
            'pickup_date' => 'N/A',
            'delivery_date' => 'N/A'
        ]);
        exit;
    }
}

// Set last order time at the beginning of processing
$_SESSION['last_order_time'] = time();

// Add a small random delay to prevent race conditions
usleep(rand(100000, 300000));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if connection is valid
        if (!mysqli_ping($conn)) {
            throw new Exception("Database connection failed");
        }
        
        // Validate and sanitize inputs
        $customer_id = intval($_SESSION['customer_id']);
        $service_id = intval($_POST['service_id']);
        $description = trim($_POST['description']);
        $weight = floatval($_POST['weight']);
        $pickup_date = trim($_POST['pickup_date']);
        $delivery_date = trim($_POST['delivery_date']);
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;
        
        // Validate inputs
        if (empty($service_id) || empty($description) || $weight <= 0 || empty($pickup_date) || empty($delivery_date)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit;
        }

        // Check for duplicate order within last minute
        $check_duplicate = mysqli_prepare($conn, "SELECT id FROM `order` WHERE customer_id = ? AND service_id = ? AND weight = ? AND pickup_date = ? AND delivery_date = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
        mysqli_stmt_bind_param($check_duplicate, "iidss", $customer_id, $service_id, $weight, $pickup_date, $delivery_date);
        mysqli_stmt_execute($check_duplicate);
        $duplicate_result = mysqli_stmt_get_result($check_duplicate);
        
        if (mysqli_num_rows($duplicate_result) > 0) {
            echo json_encode(['success' => false, 'message' => 'A similar order was just placed. Please wait a moment before trying again.']);
            exit;
        }

        // Check if customer exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM customer WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $customer_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid customer']);
            exit;
        }

        // Get service price
        $stmt = mysqli_prepare($conn, "SELECT sname, prize FROM service WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $service_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid service selected']);
            exit;
        }
        
        $service = mysqli_fetch_assoc($result);
        $service_price = floatval(str_replace(',', '', $service['prize']));
        $price = number_format($weight * $service_price, 2, '.', '');
        
        // Generate tracking number
        $date = date('ymd');
        $random = rand(100, 999);
        $tracking_number = 'LND' . $date . $random;
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert order
            $stmt = mysqli_prepare($conn, "INSERT INTO `order` (customer_id, service_id, description, price, weight, pickup_date, delivery_date, status, payment_status, tracking_number, notes) VALUES (?, ?, ?, ?, ?, ?, ?, 'received', 'unpaid', ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "iisdsssss", $customer_id, $service_id, $description, $price, $weight, $pickup_date, $delivery_date, $tracking_number, $notes);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            $order_id = mysqli_insert_id($conn);
            
            // Update tracking number with order ID
            $final_tracking_number = $tracking_number . str_pad($order_id, 3, '0', STR_PAD_LEFT);
            $update_stmt = mysqli_prepare($conn, "UPDATE `order` SET tracking_number = ? WHERE id = ?");
            if (!$update_stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($update_stmt, "si", $final_tracking_number, $order_id);
            if (!mysqli_stmt_execute($update_stmt)) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($update_stmt));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Format dates for display
            $formatted_pickup_date = date('M d, Y', strtotime($pickup_date));
            $formatted_delivery_date = date('M d, Y', strtotime($delivery_date));
            
            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order_id,
                'tracking_number' => $final_tracking_number,
                'service_name' => $service['sname'],
                'weight' => $weight,
                'price' => $price,
                'pickup_date' => $formatted_pickup_date,
                'delivery_date' => $formatted_delivery_date
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            throw $e;
        }
        
    } catch (Exception $e) {
        // Log error with full details
        error_log("Order placement error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        
        // Send error response
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again.',
            'debug' => $e->getMessage() // Only include in development
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// End output buffering and send output
ob_end_flush(); 