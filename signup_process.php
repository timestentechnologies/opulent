<?php
session_start();
require_once('admin/connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);

    // Validation
    if (empty($email) || empty($password) || empty($fname) || empty($lname) || empty($contact) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new customer
    $stmt = $conn->prepare("INSERT INTO customer (email, password, fname, lname, contact, address, city, state, zip_code, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("sssssssss", $email, $hashed_password, $fname, $lname, $contact, $address, $city, $state, $zip_code);
    
    if ($stmt->execute()) {
        // Get the newly created user's ID
        $new_user_id = $conn->insert_id;
        
        // Set session variables for automatic login
        $_SESSION['customer_id'] = $new_user_id;
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_name'] = $fname . ' ' . $lname;
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 