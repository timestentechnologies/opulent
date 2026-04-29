<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable display errors for debugging

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON for API-like response
header('Content-Type: application/json');

try {
    // Check if this is a GET request
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Only GET requests are allowed');
    }

    // Include database connection
    require_once 'includes/db_connection.php';

    // Check if user is logged in
    if (!isset($_SESSION['customer_id'])) {
        throw new Exception('You must be logged in to access subscription details');
    }

    $user_id = $_SESSION['customer_id'];

    // Validate subscription_id
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Subscription ID is required');
    }

    $subscription_id = intval($_GET['id']);

    // Simplify to just get the subscription without a join
    $stmt = $conn->prepare("SELECT * FROM user_subscriptions WHERE id = ? AND customer_id = ?");
    
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $subscription_id, $user_id);
    $stmt->execute();
    
    if ($stmt->error) {
        throw new Exception('Database query error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid subscription or access denied');
    }

    $subscription = $result->fetch_assoc();
    
    // Get price from subscription itself or use a default
    $price = 0;
    
    // Try to get price from plan_id if needed
    if (isset($subscription['plan_id'])) {
        $price_stmt = $conn->prepare("SELECT price FROM subscription_plans WHERE id = ?");
        if ($price_stmt) {
            $plan_id = $subscription['plan_id'];
            $price_stmt->bind_param("i", $plan_id);
            $price_stmt->execute();
            $price_result = $price_stmt->get_result();
            if ($price_result->num_rows > 0) {
                $price_row = $price_result->fetch_assoc();
                $price = $price_row['price'];
            }
            $price_stmt->close();
        }
    }

    // Return subscription details
    echo json_encode([
        'success' => true,
        'id' => $subscription['id'],
        'plan_id' => isset($subscription['plan_id']) ? $subscription['plan_id'] : null,
        'status' => $subscription['status'],
        'price' => (float)$price
    ]);

    // Close the database connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?> 