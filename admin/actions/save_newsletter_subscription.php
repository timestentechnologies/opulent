<?php
/*
 * Process newsletter subscriptions from the frontend
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
    
    // Get and sanitize email
    $email = sanitizeInput($_POST['email'] ?? '');
    $date_subscribed = date('Y-m-d H:i:s');
    
    // Basic validation
    if (empty($email)) {
        $_SESSION['error'] = "Please provide your email address";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=missing_email");
        exit();
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=invalid_email");
        exit();
    }
    
    try {
        // Check if email already exists
        $check_sql = "SELECT * FROM newsletter_subscribers WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['info'] = "This email is already subscribed to our newsletter.";
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=already_subscribed");
            exit();
        }
        
        // Insert into database
        $sql = "INSERT INTO newsletter_subscribers (email, date_subscribed, status) 
                VALUES (?, ?, 'active')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $date_subscribed);
        
        if ($stmt->execute()) {
            $subscriber_id = $conn->insert_id;
            
            // Send confirmation email
            try {
                sendNewsletterConfirmation($subscriber_id);
            } catch (Exception $e) {
                // Log the error but continue the process
                error_log("Error sending newsletter confirmation: " . $e->getMessage());
            }
            
            $_SESSION['success'] = "Thank you for subscribing to our newsletter!";
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=subscribed");
            exit();
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error processing subscription: " . $e->getMessage());
        $_SESSION['error'] = "There was a problem with your subscription. Please try again later.";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=db_error");
        exit();
    }
} else {
    // If not POST request, redirect to homepage
    header("Location: ../index.php");
    exit();
} 