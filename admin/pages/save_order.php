<?php
require_once('session_handler.php');

// Include auto email checker to ensure notifications are processed
require_once('../auto_check_emails.php');
?>
<?php

require_once('../connect.php');
require_once('../includes/email_helper.php'); // Include email helper functions

// Check if admin is logged in
if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

// Get and validate input
$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
$service_id = isset($_POST['service_id']) ? $_POST['service_id'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
$pickup_date = isset($_POST['pickup_date']) ? $_POST['pickup_date'] : date('Y-m-d');
$delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : date('Y-m-d');
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : 'received';
$payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : 'pending';

// Generate tracking number
function generateTrackingNumber() {
    $prefix = 'LND';
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -6));
    return $prefix . $date . $random;
}

// Validate required fields
if (empty($customer_id) || empty($service_id) || empty($description) || empty($pickup_date) || empty($delivery_date) || $weight <= 0) {
    $_SESSION['error'] = 'Please fill in all required fields';
    header("Location: ../add_order.php");
    exit();
}

// Check if this is an update operation
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $sql = "UPDATE `order` SET 
            customer_id = ?, 
            service_id = ?, 
            description = ?, 
            weight = ?,
            pickup_date = ?, 
            delivery_date = ?, 
            price = ?, 
            status = ?, 
            payment_status = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iisdssdssi", 
        $customer_id, 
        $service_id, 
        $description, 
        $weight,
        $pickup_date, 
        $delivery_date, 
        $price, 
        $status, 
        $payment_status,
        $id
    );
} else {
    // Insert new order
    $tracking_number = generateTrackingNumber();
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
        tracking_number
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iisdssdsss", 
        $customer_id, 
        $service_id, 
        $description, 
        $weight,
        $pickup_date, 
        $delivery_date, 
        $price, 
        $status, 
        $payment_status,
        $tracking_number
    );
}

if ($stmt->execute()) {
    // Get the order ID for the notification
    $order_id = isset($_POST['id']) ? $_POST['id'] : $conn->insert_id;
    
    // For new orders, send email notifications
    if (!isset($_POST['id'])) {
        // Attempt to send notification
        try {
            if (sendOrderNotification($order_id)) {
                // Update the email_sent flag if successful
                $updateSql = "UPDATE `order` SET email_sent = 1 WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                if ($updateStmt) {
                    $updateStmt->bind_param("i", $order_id);
                    $updateStmt->execute();
                }
            }
        } catch (Exception $e) {
            // Log the error but don't interrupt the flow
            error_log("Failed to send order notification: " . $e->getMessage());
        }
    }
    
    $_SESSION['success'] = 'Order successfully ' . (isset($_POST['id']) ? 'updated' : 'added');
    header("Location: ../view_order.php");
    exit();
} else {
    $_SESSION['error'] = 'Error ' . (isset($_POST['id']) ? 'updating' : 'adding') . ' order: ' . $conn->error;
    header("Location: " . (isset($_POST['id']) ? "../edit_order.php?id=" . $_POST['id'] : "../add_order.php"));
    exit();
}
?>