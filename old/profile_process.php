<?php
session_start();
require_once('admin/connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to update your profile']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);
    
    // Validate inputs
    if (empty($fname) || empty($lname) || empty($contact) || empty($address) || empty($city) || empty($state) || empty($zip_code)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit;
    }

    // Update customer information
    $stmt = $conn->prepare("UPDATE customer SET fname=?, lname=?, contact=?, address=?, city=?, state=?, zip_code=? WHERE id=?");
    $stmt->bind_param("sssssssi", $fname, $lname, $contact, $address, $city, $state, $zip_code, $customer_id);
    
    if ($stmt->execute()) {
        // Update session name
        $_SESSION['customer_name'] = $fname . ' ' . $lname;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update profile. Please try again.'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 