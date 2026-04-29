<?php
session_start();
require_once('admin/connect.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in production

// Start output buffering to catch any unwanted output
ob_start();

// Set JSON header - check if headers already sent
if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Verify database connection before proceeding
if (!isset($conn) || !$conn) {
    error_log("Database connection not established in place_order_process.php");
    ob_end_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(['success' => false, 'message' => 'Database connection error. Please try again later.']);
    exit;
}

if (!isset($_SESSION['customer_id'])) {
    ob_end_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(['success' => false, 'message' => 'Please login to place an order']);
    exit;
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
            ob_end_clean();
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit;
        }
        
        // Additional weight validation
        if ($weight > 10000) {
            ob_end_clean();
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode(['success' => false, 'message' => 'Weight cannot exceed 1000 kg. Please contact support for bulk orders.']);
            exit;
        }
        
        // Validate dates
        $pickup_timestamp = strtotime($pickup_date);
        $delivery_timestamp = strtotime($delivery_date);
        $today_timestamp = strtotime(date('Y-m-d'));
        
        if ($pickup_timestamp < $today_timestamp) {
            ob_end_clean();
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode(['success' => false, 'message' => 'Pickup date cannot be in the past']);
            exit;
        }
        
        if ($delivery_timestamp < $pickup_timestamp) {
            ob_end_clean();
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode(['success' => false, 'message' => 'Delivery date must be after pickup date']);
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
            ob_end_clean();
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
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
            ob_end_clean();
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode(['success' => false, 'message' => 'Invalid service selected']);
            exit;
        }
        
        $service = mysqli_fetch_assoc($result);
        $service_price = floatval(str_replace(',', '', $service['prize']));
        $price = number_format($weight * $service_price, 2, '.', '');
        
        // Generate tracking number
        $date = date('ymd');
        $random = rand(100, 999);
        $tracking_number = 'OPL' . $date . $random;
        
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
            
            ob_end_clean();
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
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
        
        // Determine error type and send appropriate message
        $errorMessage = 'An error occurred. Please try again.';
        
        if (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'connection') !== false) {
            $errorMessage = 'Database connection error. Please try again later.';
        } elseif (strpos($e->getMessage(), 'Prepare failed') !== false) {
            $errorMessage = 'System error. Please contact support.';
        } elseif (strpos($e->getMessage(), 'Execute failed') !== false) {
            $errorMessage = 'Order processing failed. Please try again.';
        }
        
        // Clean output buffer and send error response
        ob_end_clean();
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
    }
} else {
    ob_end_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 