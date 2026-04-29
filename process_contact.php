<?php
session_start();
require_once('admin/connect.php');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    $status = 'pending'; // Default status for new inquiries
    $created_at = date('Y-m-d H:i:s');
    
    // Get customer_id from session if customer is logged in
    $customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;

    // Validate inputs
    if (empty($name) || empty($phone) || empty($email) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ]);
        exit;
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // For guest users, use simpler query without customer_id
        if ($customer_id === null) {
            $sql = "INSERT INTO inquiries (name, phone, email, message, status, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $phone, $email, $message, $status, $created_at);
        } else {
            // For logged-in users, include customer_id
            $sql = "INSERT INTO inquiries (name, phone, email, message, status, created_at, customer_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $name, $phone, $email, $message, $status, $created_at, $customer_id);
        }

        // Execute the statement
        if ($stmt->execute()) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your message. We will get back to you soon!'
            ]);
        } else {
            throw new Exception("Error saving inquiry: " . $stmt->error);
        }

        // Close the statement
        $stmt->close();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        error_log("Error in process_contact.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while sending your message. Please try again. Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?> 