<?php
session_start();
require_once('admin/connect.php');

header('Content-Type: application/json');

$tracking_number = isset($_POST['tracking_number']) ? trim($_POST['tracking_number']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($tracking_number) || empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter both tracking number and email.'
    ]);
    exit;
}

$sql = "SELECT o.*, s.sname as service_name, c.email as customer_email 
        FROM `order` o 
        JOIN service s ON o.service_id = s.id 
        JOIN customer c ON o.customer_id = c.id 
        WHERE o.tracking_number = ? AND c.email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $tracking_number, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
    
    // Format dates
    $pickup_date = date('M d, Y', strtotime($order['pickup_date']));
    $delivery_date = date('M d, Y', strtotime($order['delivery_date']));
    
    // Format status
    $status = ucwords(str_replace('_', ' ', $order['status']));
    
    echo json_encode([
        'success' => true,
        'order' => [
            'status' => $status,
            'service' => $order['service_name'],
            'weight' => $order['weight'],
            'pickup_date' => $pickup_date,
            'delivery_date' => $delivery_date,
            'price' => number_format($order['price'], 2)
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No order found with the provided tracking number and email.'
    ]);
}

$stmt->close();
$conn->close();
?> 