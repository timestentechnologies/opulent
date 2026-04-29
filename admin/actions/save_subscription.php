<?php
/*
 * Process subscription plan purchases from customers
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

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data
    $customer_id = $_POST['customer_id'] ?? 0;
    $plan_id = $_POST['plan_id'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $payment_status = $_POST['payment_status'] ?? 'pending';
    $transaction_id = $_POST['transaction_id'] ?? '';
    $start_date = date('Y-m-d');
    
    // Basic validation
    if (empty($customer_id) || empty($plan_id)) {
        $_SESSION['error'] = "Missing required information";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=missing_data");
        exit();
    }
    
    try {
        // Get plan details to calculate end date
        $plan_query = "SELECT * FROM subscription_plans WHERE id = ?";
        $plan_stmt = $conn->prepare($plan_query);
        $plan_stmt->bind_param("i", $plan_id);
        $plan_stmt->execute();
        $plan_result = $plan_stmt->get_result();
        
        if ($plan_result->num_rows === 0) {
            throw new Exception("Invalid subscription plan");
        }
        
        $plan = $plan_result->fetch_assoc();
        $duration = $plan['duration']; // Duration in days
        
        // Calculate end date
        $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration . ' days'));
        
        // Insert subscription
        $sql = "INSERT INTO user_subscriptions (
            customer_id, 
            plan_id, 
            start_date, 
            end_date, 
            payment_method, 
            payment_status, 
            transaction_id,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iisssss", 
            $customer_id, 
            $plan_id, 
            $start_date, 
            $end_date, 
            $payment_method, 
            $payment_status, 
            $transaction_id
        );
        
        if ($stmt->execute()) {
            $subscription_id = $conn->insert_id;
            
            // Send confirmation email
            try {
                sendSubscriptionNotification($subscription_id);
            } catch (Exception $e) {
                // Log the error but continue the process
                error_log("Error sending subscription notification: " . $e->getMessage());
            }
            
            $_SESSION['success'] = "Your subscription has been successfully processed!";
            header("Location: ../my_subscriptions.php?status=success");
            exit();
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error processing subscription purchase: " . $e->getMessage());
        $_SESSION['error'] = "There was a problem processing your subscription. Please try again later.";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=process_error");
        exit();
    }
} else {
    // If not POST request, redirect to homepage
    header("Location: ../index.php");
    exit();
} 