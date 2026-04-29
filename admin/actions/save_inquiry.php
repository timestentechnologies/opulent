<?php
/*
 * Process customer inquiries from the frontend contact form
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

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize form data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    $date_created = date('Y-m-d H:i:s');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error'] = "Please fill all required fields";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=missing_fields");
        exit();
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=invalid_email");
        exit();
    }
    
    // Insert into database
    try {
        $sql = "INSERT INTO inquiries (name, email, phone, subject, message, status, date_created) 
                VALUES (?, ?, ?, ?, ?, 'new', ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $date_created);
        
        if ($stmt->execute()) {
            $inquiry_id = $conn->insert_id;
            
            // Send email notification
            try {
                sendInquiryNotification($inquiry_id);
            } catch (Exception $e) {
                // Log the error but continue the process
                error_log("Error sending inquiry notification: " . $e->getMessage());
            }
            
            $_SESSION['success'] = "Thank you for your inquiry. We will get back to you soon!";
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=success");
            exit();
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error processing inquiry: " . $e->getMessage());
        $_SESSION['error'] = "There was a problem submitting your inquiry. Please try again later.";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=db_error");
        exit();
    }
} else {
    // If not POST request, redirect to homepage
    header("Location: ../index.php");
    exit();
} 