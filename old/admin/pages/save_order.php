<?php
session_start();
require_once('../connect.php');

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
    $_SESSION['success'] = 'Order successfully ' . (isset($_POST['id']) ? 'updated' : 'added');
    header("Location: ../view_order.php");
    exit();
} else {
    $_SESSION['error'] = 'Error ' . (isset($_POST['id']) ? 'updating' : 'adding') . ' order: ' . $conn->error;
    header("Location: " . (isset($_POST['id']) ? "../edit_order.php?id=" . $_POST['id'] : "../add_order.php"));
    exit();
}
?>